<?php
/**
 * API: Obtener información de un ciclo de facturación
 */

require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/permissions.php';
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json; charset=UTF-8');

requiereAutenticacion();

$database = new Database();
$conn = $database->getConnection();

if (!$conn) {
    echo json_encode([
        'success' => false,
        'message' => 'Error de conexion'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$edificio_id = isset($_GET['edificio_id']) ? intval($_GET['edificio_id']) : 0;
$fecha_periodo = isset($_GET['fecha_periodo']) ? trim($_GET['fecha_periodo']) : '';

if ($edificio_id <= 0 || empty($fecha_periodo)) {
    $conn->close();
    echo json_encode([
        'success' => false,
        'message' => 'Datos incompletos'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$stmt = $conn->prepare("
    SELECT c.id, c.fecha_periodo, c.estado,
           COUNT(g.id) as total_gastos,
           COALESCE(SUM(g.monto_total), 0) as monto_total
    FROM ciclos_facturacion c
    LEFT JOIN gastos_edificio g ON c.id = g.ciclo_id AND g.activo = 1
    WHERE c.edificio_id = ? AND c.fecha_periodo = ? AND c.activo = 1
    GROUP BY c.id
");
$stmt->bind_param("is", $edificio_id, $fecha_periodo);
$stmt->execute();
$result = $stmt->get_result();
$ciclo = $result->fetch_assoc();
$stmt->close();

if ($ciclo) {
    echo json_encode([
        'success' => true,
        'data' => $ciclo
    ], JSON_UNESCAPED_UNICODE);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Ciclo no encontrado'
    ], JSON_UNESCAPED_UNICODE);
}

$conn->close();
exit;
?>

