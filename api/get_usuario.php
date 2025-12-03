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

if (!tienePermiso('gestionar_usuarios')) {
    echo json_encode([
        'success' => false,
        'message' => 'No tienes permisos para gestionar usuarios'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Verificar que se envió el ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'ID de usuario no especificado'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$usuario_id = intval($_GET['id']);

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

// Obtener datos del usuario
$sql = "SELECT id, nombre, email, username, rol_id 
        FROM usuarios 
        WHERE id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $stmt->close();
    $conn->close();
    echo json_encode([
        'success' => false,
        'message' => 'Usuario no encontrado'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$usuario = $result->fetch_assoc();

// Obtener edificios asignados
$sql_edificios = "SELECT edificio_id 
                  FROM usuario_edificios 
                  WHERE usuario_id = ? AND activo = 1";
$stmt_edificios = $conn->prepare($sql_edificios);
$stmt_edificios->bind_param("i", $usuario_id);
$stmt_edificios->execute();
$result_edificios = $stmt_edificios->get_result();

$edificios_asignados = [];
while ($row = $result_edificios->fetch_assoc()) {
    $edificios_asignados[] = intval($row['edificio_id']);
}

$stmt->close();
$stmt_edificios->close();
$conn->close();

echo json_encode([
    'success' => true,
    'usuario' => $usuario,
    'edificios_asignados' => $edificios_asignados
], JSON_UNESCAPED_UNICODE);
exit;
?>
