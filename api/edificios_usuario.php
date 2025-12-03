<?php
/**
 * API para obtener edificios asignados a un usuario
 * Devuelve la lista de edificios asignados a un Administrador de Edificio
 */

header('Content-Type: application/json');
require_once '../config/database.php';
require_once '../includes/session.php';

// Verificar autenticación
if (!isset($_SESSION['usuario_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'No autenticado']);
    exit;
}

$database = new Database();
$conn = $database->getConnection();

// Obtener usuario_id del parámetro GET
$usuario_id = isset($_GET['usuario_id']) ? intval($_GET['usuario_id']) : 0;

if ($usuario_id <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID de usuario inválido']);
    exit;
}

try {
    // Obtener información del usuario
    $stmt = $conn->prepare("
        SELECT u.id, u.nombre, u.email, r.nombre as rol_nombre, r.id as rol_id
        FROM usuarios u
        INNER JOIN roles r ON u.rol_id = r.id
        WHERE u.id = ? AND u.activo = 1
    ");
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    $usuario = $stmt->get_result()->fetch_assoc();
    
    if (!$usuario) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Usuario no encontrado']);
        exit;
    }
    
    // Obtener edificios asignados
    $stmt = $conn->prepare("
        SELECT e.id, e.nombre, e.direccion, 
               ue.fecha_creacion as fecha_asignacion
        FROM usuario_edificios ue
        INNER JOIN edificios e ON ue.edificio_id = e.id
        WHERE ue.usuario_id = ? AND ue.activo = 1
        ORDER BY e.nombre
    ");
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $edificios = [];
    while ($row = $result->fetch_assoc()) {
        $edificios[] = $row;
    }
    
    // Preparar respuesta
    $response = [
        'success' => true,
        'usuario' => [
            'id' => $usuario['id'],
            'nombre' => $usuario['nombre'],
            'email' => $usuario['email'],
            'rol' => $usuario['rol_nombre']
        ],
        'total_edificios' => count($edificios),
        'edificios' => $edificios
    ];
    
    echo json_encode($response);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => 'Error al obtener edificios: ' . $e->getMessage()
    ]);
}

$conn->close();
?>
