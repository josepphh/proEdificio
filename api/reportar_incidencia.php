<?php
/**
 * API: Reportar Incidencia
 */

require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/permissions.php';
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json; charset=UTF-8');

requiereAutenticacion();

if (!tienePermiso('reportar_incidencias')) {
    echo json_encode([
        'success' => false,
        'message' => 'No tiene permisos para reportar incidencias'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Metodo no permitido'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Leer datos JSON
$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (!$data) {
    echo json_encode([
        'success' => false,
        'message' => 'Datos invalidos'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$edificio_id = isset($data['edificio_id']) ? intval($data['edificio_id']) : 0;
$titulo = isset($data['titulo']) ? trim($data['titulo']) : '';
$tipo = isset($data['tipo']) ? trim($data['tipo']) : '';
$prioridad = isset($data['prioridad']) ? trim($data['prioridad']) : '';
$descripcion = isset($data['descripcion']) ? trim($data['descripcion']) : '';

if (empty($edificio_id) || empty($titulo) || empty($tipo) || empty($prioridad) || empty($descripcion)) {
    echo json_encode([
        'success' => false,
        'message' => 'Todos los campos son requeridos'
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

$usuario_id = $_SESSION['usuario_id'];

// Verificar que el usuario pertenece al edificio
$stmt_check = $conn->prepare("SELECT COUNT(*) as count FROM usuario_edificios WHERE usuario_id = ? AND edificio_id = ? AND activo = 1");
$stmt_check->bind_param("ii", $usuario_id, $edificio_id);
$stmt_check->execute();
$result_check = $stmt_check->get_result();
$row_check = $result_check->fetch_assoc();
$stmt_check->close();

if ($row_check['count'] == 0) {
    $conn->close();
    echo json_encode([
        'success' => false,
        'message' => 'No tienes acceso al edificio seleccionado'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Verificar que la tabla existe
$result = $conn->query("SHOW TABLES LIKE 'incidencias'");
if ($result->num_rows == 0) {
    $conn->close();
    echo json_encode([
        'success' => false,
        'message' => 'Tabla de incidencias no existe'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$stmt = $conn->prepare("
    INSERT INTO incidencias (usuario_id, edificio_id, titulo, descripcion, tipo, prioridad) 
    VALUES (?, ?, ?, ?, ?, ?)
");
$stmt->bind_param("iissss", $usuario_id, $edificio_id, $titulo, $descripcion, $tipo, $prioridad);

if ($stmt->execute()) {
    $incidencia_id = $conn->insert_id;
    $stmt->close();
    $conn->close();
    
    echo json_encode([
        'success' => true,
        'message' => 'Incidencia reportada exitosamente',
        'data' => [
            'incidencia_id' => $incidencia_id
        ]
    ], JSON_UNESCAPED_UNICODE);
} else {
    $error = $stmt->error;
    $stmt->close();
    $conn->close();
    
    echo json_encode([
        'success' => false,
        'message' => 'Error al reportar incidencia: ' . $error
    ], JSON_UNESCAPED_UNICODE);
}

exit;
?>

