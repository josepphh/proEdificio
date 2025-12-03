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

if (!tienePermiso('acceso_completo')) {
    echo json_encode([
        'success' => false,
        'message' => 'No tienes permisos para gestionar roles'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Verificar que se envió el ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'ID de rol no especificado'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$rol_id = intval($_GET['id']);

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

// Obtener datos del rol
$sql = "SELECT id, nombre, descripcion, fecha_creacion 
        FROM roles 
        WHERE id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $rol_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $stmt->close();
    $conn->close();
    echo json_encode([
        'success' => false,
        'message' => 'Rol no encontrado'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$rol = $result->fetch_assoc();
$stmt->close();
$conn->close();

echo json_encode([
    'success' => true,
    'rol' => $rol
], JSON_UNESCAPED_UNICODE);
exit;
?>
