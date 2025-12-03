<?php
require_once '../includes/session.php';
require_once '../includes/permissions.php';
require_once '../config/database.php';

requiereAutenticacion();

// Solo administradores totales pueden gestionar solicitudes
if (!esRol('Administrador Total')) {
    echo json_encode(['success' => false, 'message' => 'No tienes permisos para esta acción']);
    exit;
}

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

$accion = $_POST['accion'] ?? '';
$id = intval($_POST['id'] ?? 0);

if (!$id || !$accion) {
    echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
    exit;
}

$database = new Database();
$conn = $database->getConnection();

if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'Error de conexión']);
    exit;
}

try {
    if ($accion === 'rechazar') {
        $motivo = trim($_POST['motivo'] ?? '');
        
        $stmt = $conn->prepare("
            UPDATE solicitudes_acceso 
            SET estado = 'RECHAZADA', 
                fecha_respuesta = NOW(), 
                respuesta_admin = ? 
            WHERE id = ? AND estado = 'PENDIENTE'
        ");
        $stmt->bind_param("si", $motivo, $id);
        
        if ($stmt->execute() && $stmt->affected_rows > 0) {
            echo json_encode(['success' => true, 'message' => 'Solicitud rechazada']);
        } else {
            echo json_encode(['success' => false, 'message' => 'No se pudo actualizar la solicitud']);
        }
        
        $stmt->close();
    } 
    elseif ($accion === 'aprobar') {
        $usuario_id = intval($_POST['usuario_id'] ?? 0);
        
        if (!$usuario_id) {
            echo json_encode(['success' => false, 'message' => 'ID de usuario requerido']);
            exit;
        }
        
        $stmt = $conn->prepare("
            UPDATE solicitudes_acceso 
            SET estado = 'APROBADA', 
                fecha_respuesta = NOW(), 
                usuario_creado_id = ? 
            WHERE id = ? AND estado = 'PENDIENTE'
        ");
        $stmt->bind_param("ii", $usuario_id, $id);
        
        if ($stmt->execute() && $stmt->affected_rows > 0) {
            echo json_encode(['success' => true, 'message' => 'Solicitud aprobada']);
        } else {
            echo json_encode(['success' => false, 'message' => 'No se pudo actualizar la solicitud']);
        }
        
        $stmt->close();
    }
    else {
        echo json_encode(['success' => false, 'message' => 'Acción no válida']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}

$conn->close();
?>
