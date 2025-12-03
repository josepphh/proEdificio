<?php
/**
 * Procesar Cierre Mensual
 * 
 * Este script procesa el cierre mensual de un edificio:
 * 1. Crea un ciclo de facturación
 * 2. Guarda los gastos del edificio
 * 3. Calcula prorrateo por días vividos (CUOTA_FIJA)
 * 4. Calcula prorrateo por consumo (CONSUMO)
 * 5. Genera recibos para cada inquilino
 */

// Desactivar display de errores para evitar HTML en respuestas JSON
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Establecer header JSON primero
header('Content-Type: application/json; charset=UTF-8');

// Función para manejar errores fatales y convertirlos a JSON
function jsonErrorHandler($errno, $errstr, $errfile, $errline) {
    if (!(error_reporting() & $errno)) {
        return false;
    }
    
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $errstr . ' en ' . basename($errfile) . ' línea ' . $errline
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

set_error_handler('jsonErrorHandler');

try {
    require_once 'config/database.php';
    require_once 'includes/funciones_gastos.php';
    require_once 'includes/session.php';
    require_once 'includes/permissions.php';
    require_once 'includes/mail.php';
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error al cargar archivos: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Verificar autenticación y permisos
// Nota: requiereAutenticacion y requierePermiso hacen header() y exit() si fallan
// Por eso no usamos try-catch aquí
if (!estaAutenticado()) {
    echo json_encode([
        'success' => false,
        'message' => 'No autenticado'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

if (!tienePermiso('gestionar_gastos')) {
    echo json_encode([
        'success' => false,
        'message' => 'No tiene permisos para esta accion'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Verificar método POST
if ($_SERVER["REQUEST_METHOD"] != "POST") {
    echo json_encode([
        'success' => false,
        'message' => 'Metodo no permitido'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Leer datos JSON si se envía como JSON, sino usar $_POST
$input_data = null;
if (isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) {
    $json = file_get_contents('php://input');
    $input_data = json_decode($json, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        echo json_encode([
            'success' => false,
            'message' => 'Error al decodificar JSON: ' . json_last_error_msg()
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
} else {
    $input_data = $_POST;
}

// Validar campos requeridos
if (!isset($input_data['edificio_id']) || !isset($input_data['mes']) || !isset($input_data['gastos'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Faltan datos requeridos'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$edificio_id = intval($input_data['edificio_id']);
$mes = trim($input_data['mes']);
$gastos = $input_data['gastos'];

// Validar edificio
if (!perteneceAlEdificio($edificio_id) && !esRol('Administrador Total')) {
    echo json_encode([
        'success' => false,
        'message' => 'No tiene permisos para este edificio'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Validar formato de mes (YYYY-MM)
if (!preg_match('/^\d{4}-\d{2}$/', $mes)) {
    echo json_encode([
        'success' => false,
        'message' => 'Formato de mes invalido'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Convertir mes a fecha_periodo (YYYY-MM-01)
$fecha_periodo = $mes . '-01';
$dias_del_mes = obtenerDiasDelMes($fecha_periodo);

// Validar que no exista un ciclo para este edificio y período
$database = new Database();
$conn = $database->getConnection();

if (!$conn) {
    echo json_encode([
        'success' => false,
        'message' => 'Error de conexion a la base de datos'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Verificar si ya existe un ciclo para este período
$stmt = $conn->prepare("SELECT id FROM ciclos_facturacion WHERE edificio_id = ? AND fecha_periodo = ?");
$stmt->bind_param("is", $edificio_id, $fecha_periodo);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $stmt->close();
    $conn->close();
    echo json_encode([
        'success' => false,
        'message' => 'Ya existe un ciclo de facturacion para este periodo'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}
$stmt->close();

// Iniciar transacción
$conn->autocommit(FALSE);

try {
    // ============================================================
    // 1. CREAR CICLO DE FACTURACIÓN
    // ============================================================
    $usuario_id = $_SESSION['usuario_id'];
    $stmt = $conn->prepare("
        INSERT INTO ciclos_facturacion (edificio_id, fecha_periodo, estado, usuario_cierre_id) 
        VALUES (?, ?, 'ABIERTO', ?)
    ");
    $stmt->bind_param("isi", $edificio_id, $fecha_periodo, $usuario_id);
    
    if (!$stmt->execute()) {
        throw new Exception("Error al crear ciclo de facturacion: " . $conn->error);
    }
    
    $ciclo_id = $conn->insert_id;
    $stmt->close();
    
    // ============================================================
    // 2. GUARDAR GASTOS DEL EDIFICIO
    // ============================================================
    $servicios_gastos = [];
    
    foreach ($gastos as $gasto) {
        $servicio_id = intval($gasto['servicio_id']);
        $monto = floatval($gasto['monto']);
        $descripcion = isset($gasto['descripcion']) ? trim($gasto['descripcion']) : '';
        
        // Validar monto
        if ($monto <= 0) {
            throw new Exception("El monto debe ser mayor a cero");
        }
        
        // Obtener tipo de cálculo del servicio
        $stmt = $conn->prepare("SELECT tipo_calculo FROM servicios WHERE id = ? AND activo = 1");
        $stmt->bind_param("i", $servicio_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows == 0) {
            $stmt->close();
            throw new Exception("Servicio no encontrado o inactivo");
        }
        
        $servicio = $result->fetch_assoc();
        $tipo_calculo = $servicio['tipo_calculo'];
        $stmt->close();
        
        // Insertar gasto
        $stmt = $conn->prepare("
            INSERT INTO gastos_edificio (ciclo_id, servicio_id, monto_total, descripcion, fecha_gasto) 
            VALUES (?, ?, ?, ?, ?)
        ");
        $fecha_gasto = $fecha_periodo;
        $stmt->bind_param("iidss", $ciclo_id, $servicio_id, $monto, $descripcion, $fecha_gasto);
        
        if (!$stmt->execute()) {
            throw new Exception("Error al guardar gasto: " . $conn->error);
        }
        
        $stmt->close();
        
        // Guardar para procesamiento posterior
        $servicios_gastos[] = [
            'id' => $servicio_id,
            'tipo_calculo' => $tipo_calculo,
            'monto_total' => $monto
        ];
    }
    
    // ============================================================
    // 3. PROCESAR LECTURAS DE MEDIDORES (si existen)
    // ============================================================
    if (isset($input_data['lecturas']) && is_array($input_data['lecturas'])) {
        foreach ($input_data['lecturas'] as $lectura) {
            $lectura_servicio_id = intval($lectura['servicio_id']);
            $usuario_lectura_id = intval($lectura['usuario_id']);
            $lectura_anterior = floatval($lectura['lectura_anterior']);
            $lectura_actual = floatval($lectura['lectura_actual']);
            
            // Validar lecturas
            if ($lectura_actual < $lectura_anterior) {
                throw new Exception("La lectura actual no puede ser menor a la anterior");
            }
            
            $consumo = $lectura_actual - $lectura_anterior;
            
            // Insertar lectura
            $stmt = $conn->prepare("
                INSERT INTO lecturas_medidor 
                (ciclo_id, servicio_id, usuario_id, lectura_anterior, lectura_actual, consumo_calculado, fecha_lectura) 
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->bind_param("iiiddds", $ciclo_id, $lectura_servicio_id, $usuario_lectura_id, 
                           $lectura_anterior, $lectura_actual, $consumo, $fecha_periodo);
            
            if (!$stmt->execute()) {
                throw new Exception("Error al guardar lectura: " . $conn->error);
            }
            
            $stmt->close();
        }
    }
    
    // ============================================================
    // 4. CALCULAR DEUDAS POR INQUILINO
    // ============================================================
    $deudas_finales = [];
    
    foreach ($servicios_gastos as $servicio) {
        if ($servicio['tipo_calculo'] == 'CUOTA_FIJA') {
            // Prorrateo por días vividos
            $deudas_servicio = calcularProrrateoCuotaFija(
                $conn, 
                $edificio_id, 
                $fecha_periodo, 
                $dias_del_mes, 
                $servicio['monto_total']
            );
            
            // Sumar a deudas finales
            foreach ($deudas_servicio as $usuario_id => $monto) {
                $deudas_finales[$usuario_id] = ($deudas_finales[$usuario_id] ?? 0) + $monto;
            }
            
        } elseif ($servicio['tipo_calculo'] == 'CONSUMO') {
            // Prorrateo por consumo
            $deudas_servicio = calcularProrrateoConsumo(
                $conn,
                $ciclo_id,
                $servicio['id'],
                $servicio['monto_total']
            );
            
            // Sumar a deudas finales
            foreach ($deudas_servicio as $usuario_id => $monto) {
                $deudas_finales[$usuario_id] = ($deudas_finales[$usuario_id] ?? 0) + $monto;
            }
        }
    }
    
    // ============================================================
    // 5. GENERAR RECIBOS PARA CADA INQUILINO
    // ============================================================
    $fecha_vencimiento = date('Y-m-d', strtotime($fecha_periodo . ' +1 month +15 days'));
    
    foreach ($deudas_finales as $usuario_id => $monto) {
        if ($monto > 0) {
            $stmt = $conn->prepare("
                INSERT INTO recibos_inquilino 
                (ciclo_id, usuario_id, monto_deuda, estado, fecha_vencimiento) 
                VALUES (?, ?, ?, 'PENDIENTE', ?)
            ");
            $stmt->bind_param("iids", $ciclo_id, $usuario_id, $monto, $fecha_vencimiento);
            
            if (!$stmt->execute()) {
                throw new Exception("Error al generar recibo: " . $conn->error);
            }
            
            $stmt->close();
        }
    }
    
    // ============================================================
    // 6. CERRAR EL CICLO
    // ============================================================
    $stmt = $conn->prepare("
        UPDATE ciclos_facturacion 
        SET estado = 'CERRADO', fecha_cierre = NOW() 
        WHERE id = ?
    ");
    $stmt->bind_param("i", $ciclo_id);
    
    if (!$stmt->execute()) {
        throw new Exception("Error al cerrar ciclo: " . $conn->error);
    }
    
    $stmt->close();
    
    // Confirmar transacción
    $conn->commit();
    
    // ============================================================
    // 7. ENVIAR NOTIFICACIONES POR EMAIL A INQUILINOS
    // ============================================================
    $mailer = new Mailer();
    $stmt = $conn->prepare("
        SELECT u.nombre, u.email, r.monto_deuda, r.fecha_vencimiento
        FROM recibos_inquilino r
        INNER JOIN usuarios u ON r.usuario_id = u.id
        WHERE r.ciclo_id = ?
    ");
    $stmt->bind_param("i", $ciclo_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    // Obtener nombre del edificio
    $stmt_edificio = $conn->prepare("SELECT e.nombre FROM edificios e INNER JOIN ciclos_facturacion c ON e.id = c.edificio_id WHERE c.id = ?");
    $stmt_edificio->bind_param("i", $ciclo_id);
    $stmt_edificio->execute();
    $edificio_result = $stmt_edificio->get_result();
    $edificio_data = $edificio_result->fetch_assoc();
    $edificio_nombre = $edificio_data['nombre'] ?? 'Edificio';
    $stmt_edificio->close();
    
    $emails_enviados = 0;
    while ($inquilino = $result->fetch_assoc()) {
        if ($inquilino['email']) {
            $datos_recibo = [
                'periodo' => $fecha_periodo,
                'edificio' => $edificio_nombre,
                'monto' => $inquilino['monto_deuda'],
                'vencimiento' => $inquilino['fecha_vencimiento'],
                'link_sistema' => 'http://localhost:8012/proyectoEdificio/mis_pagos.php'
            ];
            if ($mailer->enviarNotificacionRecibo($inquilino['email'], $inquilino['nombre'], $datos_recibo)) {
                $emails_enviados++;
            }
        }
    }
    $stmt->close();
    
    echo json_encode([
        'success' => true,
        'message' => 'Cierre mensual procesado exitosamente. Emails enviados: ' . $emails_enviados,
        'ciclo_id' => $ciclo_id,
        'recibos_generados' => count($deudas_finales),
        'emails_enviados' => $emails_enviados
    ], JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    // Revertir transacción en caso de error
    $conn->rollback();
    
    error_log("Error en procesar_cierre.php: " . $e->getMessage());
    
    echo json_encode([
        'success' => false,
        'message' => 'Error al procesar el cierre: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
    
} finally {
    $conn->close();
}

exit;

?>

