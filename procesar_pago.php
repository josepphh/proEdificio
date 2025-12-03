<?php
/**
 * Procesar Pago de Inquilino
 * 
 * Permite a los inquilinos registrar sus pagos y subir vouchers
 */

require_once 'config/database.php';
require_once 'includes/session.php';
require_once 'includes/permissions.php';
require_once 'includes/mail.php';

// Verificar autenticación
requiereAutenticacion('login.php');

header('Content-Type: application/json; charset=UTF-8');

// Verificar método POST
if ($_SERVER["REQUEST_METHOD"] != "POST") {
    echo json_encode([
        'success' => false,
        'message' => 'Metodo no permitido'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Validar campos requeridos
if (!isset($_POST['recibo_id']) || !isset($_POST['monto']) || !isset($_POST['metodo_pago']) || !isset($_POST['fecha_pago'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Faltan datos requeridos'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$recibo_id = intval($_POST['recibo_id']);
$monto = floatval($_POST['monto']);
$metodo_pago = trim($_POST['metodo_pago']);
$fecha_pago = trim($_POST['fecha_pago']);
$numero_comprobante = isset($_POST['numero_comprobante']) ? trim($_POST['numero_comprobante']) : '';
$observaciones = isset($_POST['observaciones']) ? trim($_POST['observaciones']) : '';

$usuario_id = $_SESSION['usuario_id'];

// Validar monto
if ($monto <= 0) {
    echo json_encode([
        'success' => false,
        'message' => 'El monto debe ser mayor a cero'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Validar método de pago
$metodos_validos = ['EFECTIVO', 'YAPE', 'PLIN', 'TRANSFERENCIA', 'OTRO'];
if (!in_array($metodo_pago, $metodos_validos)) {
    echo json_encode([
        'success' => false,
        'message' => 'Metodo de pago invalido'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Validar fecha
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha_pago)) {
    echo json_encode([
        'success' => false,
        'message' => 'Formato de fecha invalido'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$database = new Database();
$conn = $database->getConnection();

if (!$conn) {
    echo json_encode([
        'success' => false,
        'message' => 'Error de conexion a la base de datos'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Verificar que el recibo pertenece al usuario
$stmt = $conn->prepare("SELECT id, monto_deuda, monto_pagado, estado FROM recibos_inquilino WHERE id = ? AND usuario_id = ?");
$stmt->bind_param("ii", $recibo_id, $usuario_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    $stmt->close();
    $conn->close();
    echo json_encode([
        'success' => false,
        'message' => 'Recibo no encontrado o no pertenece a tu cuenta'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$recibo = $result->fetch_assoc();
$stmt->close();

// Verificar que el recibo esté pendiente
if ($recibo['estado'] != 'PENDIENTE' && $recibo['estado'] != 'VENCIDO') {
    $conn->close();
    echo json_encode([
        'success' => false,
        'message' => 'Este recibo ya fue procesado'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Verificar que el monto no exceda lo pendiente
$pendiente = $recibo['monto_deuda'] - $recibo['monto_pagado'];
if ($monto > $pendiente) {
    $conn->close();
    echo json_encode([
        'success' => false,
        'message' => 'El monto excede el saldo pendiente (S/ ' . number_format($pendiente, 2) . ')'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Procesar archivo de voucher si existe
$voucher_url = null;
if (isset($_FILES['voucher']) && $_FILES['voucher']['error'] == 0) {
    $upload_dir = __DIR__ . '/uploads/vouchers/';
    
    // Crear directorio si no existe
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    
    $file = $_FILES['voucher'];
    $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed_ext = ['jpg', 'jpeg', 'png', 'pdf'];
    
    // Validar extensión
    if (!in_array($file_ext, $allowed_ext)) {
        $conn->close();
        echo json_encode([
            'success' => false,
            'message' => 'Formato de archivo no permitido. Use JPG, PNG o PDF'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // Validar tamaño (5MB máximo)
    if ($file['size'] > 5 * 1024 * 1024) {
        $conn->close();
        echo json_encode([
            'success' => false,
            'message' => 'El archivo es demasiado grande (maximo 5MB)'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // Generar nombre único
    $filename = 'voucher_' . $recibo_id . '_' . time() . '_' . uniqid() . '.' . $file_ext;
    $filepath = $upload_dir . $filename;
    
    // Mover archivo
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        $voucher_url = '/proyectoEdificio/uploads/vouchers/' . $filename;
    } else {
        $conn->close();
        echo json_encode([
            'success' => false,
            'message' => 'Error al subir el archivo'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
}

// Iniciar transacción
$conn->autocommit(FALSE);

try {
    // Insertar pago
    $stmt = $conn->prepare("
        INSERT INTO pagos_inquilino 
        (recibo_id, usuario_id, monto, metodo_pago, numero_comprobante, voucher_url, fecha_pago, observaciones, estado) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'PENDIENTE')
    ");
    $stmt->bind_param("iidsssss", $recibo_id, $usuario_id, $monto, $metodo_pago, 
                     $numero_comprobante, $voucher_url, $fecha_pago, $observaciones);
    
    if (!$stmt->execute()) {
        throw new Exception("Error al registrar pago: " . $conn->error);
    }
    
    $pago_id = $conn->insert_id;
    $stmt->close();
    
    // Actualizar monto pagado en el recibo
    $nuevo_monto_pagado = $recibo['monto_pagado'] + $monto;
    $nuevo_estado = ($nuevo_monto_pagado >= $recibo['monto_deuda']) ? 'PAGADO' : $recibo['estado'];
    
    $stmt = $conn->prepare("
        UPDATE recibos_inquilino 
        SET monto_pagado = ?, estado = ? 
        WHERE id = ?
    ");
    $stmt->bind_param("dsi", $nuevo_monto_pagado, $nuevo_estado, $recibo_id);
    
    if (!$stmt->execute()) {
        throw new Exception("Error al actualizar recibo: " . $conn->error);
    }
    
    $stmt->close();
    
    // Confirmar transacción
    $conn->commit();
    
    // Enviar notificación por email
    $stmt = $conn->prepare("
        SELECT u.nombre, u.email, c.fecha_periodo
        FROM usuarios u
        INNER JOIN recibos_inquilino r ON u.id = r.usuario_id
        INNER JOIN ciclos_facturacion c ON r.ciclo_id = c.id
        WHERE r.id = ?
    ");
    $stmt->bind_param("i", $recibo_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $datos_usuario = $result->fetch_assoc();
    $stmt->close();
    
    if ($datos_usuario && $datos_usuario['email']) {
        $mailer = new Mailer();
        $datos_pago = [
            'periodo' => $datos_usuario['fecha_periodo'],
            'monto' => $monto_pagado,
            'metodo' => $metodo_pago,
            'fecha' => date('Y-m-d H:i:s')
        ];
        $mailer->enviarConfirmacionPago($datos_usuario['email'], $datos_usuario['nombre'], $datos_pago);
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Pago registrado exitosamente. Será verificado por el administrador.',
        'pago_id' => $pago_id
    ], JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    // Revertir transacción
    $conn->rollback();
    
    // Eliminar archivo si se subió
    if ($voucher_url && file_exists(__DIR__ . $voucher_url)) {
        unlink(__DIR__ . $voucher_url);
    }
    
    error_log("Error en procesar_pago.php: " . $e->getMessage());
    
    echo json_encode([
        'success' => false,
        'message' => 'Error al procesar el pago: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
    
} finally {
    $conn->close();
}

exit;

?>

