<?php
require_once '../includes/session.php';
require_once '../includes/permissions.php';
require_once '../config/database.php';
require_once '../includes/mail.php';

header('Content-Type: application/json; charset=UTF-8');

requiereAutenticacion();

if (!tienePermiso('gestionar_gastos')) {
    echo json_encode([
        'success' => false,
        'message' => 'No tiene permisos para validar pagos'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] != "POST") {
    echo json_encode([
        'success' => false,
        'message' => 'Método no permitido'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$action = $_POST['action'] ?? '';

if ($action !== 'validar_pago') {
    echo json_encode([
        'success' => false,
        'message' => 'Acción no válida'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$pago_id = intval($_POST['pago_id'] ?? 0);
$nuevo_estado = $_POST['nuevo_estado'] ?? '';
$motivo_rechazo = trim($_POST['motivo_rechazo'] ?? '');

if ($pago_id <= 0 || !in_array($nuevo_estado, ['VERIFICADO', 'RECHAZADO'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Datos inválidos'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$database = new Database();
$conn = $database->getConnection();

if (!$conn) {
    echo json_encode([
        'success' => false,
        'message' => 'Error de conexión'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Verificar que el pago existe y obtener datos
$stmt = $conn->prepare("
    SELECT p.recibo_id, p.monto_pagado, p.estado, r.monto_deuda, r.usuario_id
    FROM pagos_inquilino p
    INNER JOIN recibos_inquilino r ON p.recibo_id = r.id
    WHERE p.id = ? AND p.activo = 1
");
$stmt->bind_param("i", $pago_id);
$stmt->execute();
$result = $stmt->get_result();
$pago = $result->fetch_assoc();
$stmt->close();

if (!$pago) {
    $conn->close();
    echo json_encode([
        'success' => false,
        'message' => 'Pago no encontrado'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Iniciar transacción
$conn->begin_transaction();

try {
    // Actualizar estado del pago
    $observaciones_update = '';
    if ($nuevo_estado === 'RECHAZADO' && $motivo_rechazo) {
        $observaciones_update = ", observaciones = CONCAT(COALESCE(observaciones, ''), '\nRechazado: " . 
                               $conn->real_escape_string($motivo_rechazo) . "')";
    }
    
    $sql = "UPDATE pagos_inquilino SET estado = ?{$observaciones_update} WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $nuevo_estado, $pago_id);
    $stmt->execute();
    $stmt->close();
    
    // Si se VERIFICA el pago, actualizar el recibo
    if ($nuevo_estado === 'VERIFICADO') {
        // Marcar recibo como PAGADO
        $stmt = $conn->prepare("
            UPDATE recibos_inquilino 
            SET estado = 'PAGADO', fecha_pago = NOW() 
            WHERE id = ?
        ");
        $stmt->bind_param("i", $pago['recibo_id']);
        $stmt->execute();
        $stmt->close();
    }
    
    // Si se RECHAZA el pago, mantener recibo como PENDIENTE
    // El inquilino deberá registrar el pago nuevamente
    
    // Obtener datos para notificación
    $stmt = $conn->prepare("
        SELECT u.nombre, u.email, c.fecha_periodo
        FROM usuarios u
        INNER JOIN recibos_inquilino r ON u.id = r.usuario_id
        INNER JOIN ciclos_facturacion c ON r.ciclo_id = c.id
        WHERE r.id = ?
    ");
    $stmt->bind_param("i", $pago['recibo_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $datos_usuario = $result->fetch_assoc();
    $stmt->close();
    
    $conn->commit();
    
    // Enviar notificación por email
    if ($datos_usuario && $datos_usuario['email']) {
        $mailer = new Mailer();
        $datos_pago = [
            'periodo' => $datos_usuario['fecha_periodo'],
            'monto' => $pago['monto_pagado'],
            'link_sistema' => 'http://localhost:8012/proyectoEdificio/mis_pagos.php'
        ];
        
        if ($nuevo_estado === 'VERIFICADO') {
            $mailer->enviarPagoVerificado($datos_usuario['email'], $datos_usuario['nombre'], $datos_pago);
        } else {
            $mailer->enviarPagoRechazado($datos_usuario['email'], $datos_usuario['nombre'], $datos_pago, $motivo_rechazo);
        }
    }
    
    $mensaje = $nuevo_estado === 'VERIFICADO' 
        ? 'Pago aprobado exitosamente. El recibo ha sido marcado como pagado.' 
        : 'Pago rechazado. El inquilino será notificado.';
    
    $conn->close();
    
    echo json_encode([
        'success' => true,
        'message' => $mensaje
    ], JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    $conn->rollback();
    $conn->close();
    
    echo json_encode([
        'success' => false,
        'message' => 'Error al validar el pago: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>
