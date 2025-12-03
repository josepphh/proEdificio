<?php
require_once '../includes/session.php';
require_once '../includes/permissions.php';
require_once '../config/database.php';

header('Content-Type: application/json; charset=UTF-8');

requiereAutenticacion();

if (!isset($_GET['id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'ID de incidencia no especificado'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$incidencia_id = intval($_GET['id']);

$database = new Database();
$conn = $database->getConnection();

// Obtener incidencia
$stmt = $conn->prepare("
    SELECT i.*, 
           u.nombre as usuario_nombre, u.email as usuario_email,
           e.nombre as edificio_nombre
    FROM incidencias i
    INNER JOIN usuarios u ON i.usuario_id = u.id
    INNER JOIN edificios e ON i.edificio_id = e.id
    WHERE i.id = ? AND i.activo = 1
");
$stmt->bind_param("i", $incidencia_id);
$stmt->execute();
$result = $stmt->get_result();
$incidencia = $result->fetch_assoc();
$stmt->close();

if (!$incidencia) {
    $conn->close();
    echo json_encode([
        'success' => false,
        'message' => 'Incidencia no encontrada'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Obtener comentarios
$stmt = $conn->prepare("
    SELECT c.*, u.nombre as usuario_nombre
    FROM comentarios_incidencia c
    INNER JOIN usuarios u ON c.usuario_id = u.id
    WHERE c.incidencia_id = ?
    ORDER BY c.fecha_creacion ASC
");
$stmt->bind_param("i", $incidencia_id);
$stmt->execute();
$result = $stmt->get_result();

$comentarios = [];
while ($row = $result->fetch_assoc()) {
    $comentarios[] = $row;
}
$stmt->close();

$conn->close();

echo json_encode([
    'success' => true,
    'incidencia' => $incidencia,
    'comentarios' => $comentarios
], JSON_UNESCAPED_UNICODE);
?>
