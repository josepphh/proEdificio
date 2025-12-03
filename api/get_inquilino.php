<?php
/**
 * API: Obtener información detallada de un inquilino
 */

require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/permissions.php';
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json; charset=UTF-8');

requiereAutenticacion();

if (!tienePermiso('gestionar_inquilinos')) {
    echo json_encode([
        'success' => false,
        'message' => 'No tiene permisos'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$usuario_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($usuario_id <= 0) {
    echo json_encode([
        'success' => false,
        'message' => 'ID de usuario invalido'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$database = new Database();
$conn = $database->getConnection();

if (!$conn) {
    echo json_encode([
        'success' => false,
        'message' => 'Error de conexion'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Obtener información del usuario
$stmt = $conn->prepare("
    SELECT u.id, u.nombre, u.email, u.username, u.fecha_registro, u.activo,
           ue.fecha_asignacion
    FROM usuarios u
    LEFT JOIN usuario_edificios ue ON u.id = ue.usuario_id AND ue.activo = 1
    WHERE u.id = ?
    LIMIT 1
");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();
$usuario = $result->fetch_assoc();
$stmt->close();

// Obtener edificios asignados
$stmt_edificios = $conn->prepare("
    SELECT e.nombre as edificio_nombre
    FROM usuario_edificios ue
    INNER JOIN edificios e ON ue.edificio_id = e.id
    WHERE ue.usuario_id = ? AND ue.activo = 1
    LIMIT 1
");
$stmt_edificios->bind_param("i", $usuario_id);
$stmt_edificios->execute();
$result_edificios = $stmt_edificios->get_result();
if ($row = $result_edificios->fetch_assoc()) {
    $usuario['edificio_nombre'] = $row['edificio_nombre'];
}
$stmt_edificios->close();

if (!$usuario) {
    $conn->close();
    echo json_encode([
        'success' => false,
        'message' => 'Usuario no encontrado'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Obtener estadísticas de recibos
$stmt = $conn->prepare("
    SELECT 
        COUNT(*) as total_recibos,
        COUNT(CASE WHEN estado = 'PENDIENTE' THEN 1 END) as recibos_pendientes,
        COUNT(CASE WHEN estado = 'PAGADO' THEN 1 END) as recibos_pagados,
        COALESCE(SUM(CASE WHEN estado = 'PENDIENTE' THEN monto_deuda ELSE 0 END), 0) as monto_pendiente
    FROM recibos_inquilino
    WHERE usuario_id = ? AND activo = 1
");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();
$stats = $result->fetch_assoc();
$stmt->close();

// Combinar datos
$usuario['total_recibos'] = $stats['total_recibos'];
$usuario['recibos_pendientes'] = $stats['recibos_pendientes'];
$usuario['recibos_pagados'] = $stats['recibos_pagados'];
$usuario['monto_pendiente'] = $stats['monto_pendiente'];

$conn->close();

echo json_encode([
    'success' => true,
    'data' => $usuario
], JSON_UNESCAPED_UNICODE);

exit;
?>

