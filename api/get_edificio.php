<?php
require_once '../includes/session.php';
require_once '../includes/permissions.php';
require_once '../config/database.php';

header('Content-Type: application/json; charset=UTF-8');

// Verificar autenticación y permisos
if (!estaAutenticado()) {
    echo json_encode([
        'success' => false,
        'message' => 'No autorizado'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

if (!tienePermiso('gestionar_edificios')) {
    echo json_encode([
        'success' => false,
        'message' => 'No tienes permisos para gestionar edificios'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Verificar que se envió el ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'ID de edificio no especificado'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$edificio_id = intval($_GET['id']);

// Conectar a la base de datos
$database = new Database();
$conn = $database->getConnection();

if (!$conn) {
    echo json_encode([
        'success' => false,
        'message' => 'Error de conexion a la base de datos'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Obtener datos del edificio
$sql = "SELECT id, nombre, direccion, ciudad, num_pisos, num_departamentos, fecha_creacion 
        FROM edificios 
        WHERE id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $edificio_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $stmt->close();
    $conn->close();
    echo json_encode([
        'success' => false,
        'message' => 'Edificio no encontrado'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$edificio = $result->fetch_assoc();
$stmt->close();
$conn->close();

echo json_encode([
    'success' => true,
    'edificio' => $edificio
], JSON_UNESCAPED_UNICODE);
exit;
?>
