<?php
header('Content-Type: application/json; charset=UTF-8');
require_once '../includes/session.php';
require_once '../includes/permissions.php';
require_once '../config/database.php';

// Verificar autenticación y que sea Administrador Total
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol_nombre'] !== 'Administrador Total') {
    echo json_encode(['success' => false, 'message' => 'Sin permisos'], JSON_UNESCAPED_UNICODE);
    exit;
}

$database = new Database();
$conn = $database->getConnection();

$accion = $_POST['accion'] ?? $_GET['accion'] ?? '';
$usuario_id = $_POST['usuario_id'] ?? $_GET['usuario_id'] ?? 0;

try {
    switch ($accion) {
        case 'obtener_edificios_usuario':
            obtenerEdificiosUsuario($conn, $usuario_id);
            break;
        
        case 'asignar_edificios':
            asignarEdificios($conn);
            break;
        
        default:
            echo json_encode(['success' => false, 'message' => 'Acción no válida'], JSON_UNESCAPED_UNICODE);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()], JSON_UNESCAPED_UNICODE);
}

$conn->close();

function obtenerEdificiosUsuario($conn, $usuario_id) {
    if (empty($usuario_id)) {
        echo json_encode(['success' => false, 'message' => 'ID de usuario requerido'], JSON_UNESCAPED_UNICODE);
        return;
    }
    
    // Obtener edificios asignados al usuario (sin concepto de principal)
    $stmt = $conn->prepare("
        SELECT edificio_id 
        FROM usuario_edificios 
        WHERE usuario_id = ? AND activo = 1
        ORDER BY edificio_id ASC
    ");
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $edificios_asignados = [];
    
    while ($row = $result->fetch_assoc()) {
        $edificios_asignados[] = (int)$row['edificio_id'];
    }
    $stmt->close();
    
    echo json_encode([
        'success' => true, 
        'edificios_asignados' => $edificios_asignados
    ], JSON_UNESCAPED_UNICODE);
}

function asignarEdificios($conn) {
    $usuario_id = $_POST['usuario_id'] ?? 0;
    $edificios = $_POST['edificios'] ?? []; // Array de IDs de edificios
    
    if (empty($usuario_id)) {
        echo json_encode(['success' => false, 'message' => 'ID de usuario requerido'], JSON_UNESCAPED_UNICODE);
        return;
    }
    
    // Verificar que el usuario exista
    $check = $conn->prepare("SELECT u.id, r.nombre as rol_nombre FROM usuarios u JOIN roles r ON u.rol_id = r.id WHERE u.id = ?");
    $check->bind_param("i", $usuario_id);
    $check->execute();
    $result = $check->get_result();
    $usuario = $result->fetch_assoc();
    $check->close();
    
    if (!$usuario) {
        echo json_encode(['success' => false, 'message' => 'Usuario no encontrado'], JSON_UNESCAPED_UNICODE);
        return;
    }
    
    // Iniciar transacción
    $conn->begin_transaction();
    
    try {
        // Desactivar todas las asignaciones existentes
        $stmt = $conn->prepare("UPDATE usuario_edificios SET activo = 0 WHERE usuario_id = ?");
        $stmt->bind_param("i", $usuario_id);
        $stmt->execute();
        $stmt->close();
        
        // Insertar o reactivar las nuevas asignaciones (todos los edificios son iguales)
        if (!empty($edificios)) {
            $stmt = $conn->prepare("
                INSERT INTO usuario_edificios (usuario_id, edificio_id, activo)
                VALUES (?, ?, 1)
                ON DUPLICATE KEY UPDATE activo = 1, fecha_asignacion = CURRENT_TIMESTAMP
            ");
            
            foreach ($edificios as $edificio_id) {
                $edificio_id = (int)$edificio_id;
                if ($edificio_id > 0) {
                    $stmt->bind_param("ii", $usuario_id, $edificio_id);
                    $stmt->execute();
                }
            }
            $stmt->close();
        }
        
        $conn->commit();
        
        $total = count($edificios);
        echo json_encode([
            'success' => true, 
            'message' => "Edificios asignados exitosamente ($total)"
        ], JSON_UNESCAPED_UNICODE);
        
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => 'Error al asignar edificios: ' . $e->getMessage()], JSON_UNESCAPED_UNICODE);
    }
}
?>
