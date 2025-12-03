<?php
require_once '../includes/session.php';
require_once '../includes/permissions.php';
require_once '../config/database.php';

header('Content-Type: application/json; charset=UTF-8');

requiereAutenticacion();

if (!tienePermiso('gestionar_usuarios')) {
    echo json_encode([
        'success' => false,
        'message' => 'No tiene permisos para gestionar incidencias'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] != "POST") {
    echo json_encode([
        'success' => false,
        'message' => 'Método no permitido'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$action = $_POST['action'] ?? '';
$database = new Database();
$conn = $database->getConnection();

if ($action === 'cambiar_estado') {
    $incidencia_id = intval($_POST['incidencia_id'] ?? 0);
    $nuevo_estado = $_POST['nuevo_estado'] ?? '';
    
    if ($incidencia_id <= 0 || !in_array($nuevo_estado, ['PENDIENTE', 'EN_PROCESO', 'RESUELTA'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Datos inválidos'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // Actualizar estado
    $stmt = $conn->prepare("
        UPDATE incidencias 
        SET estado = ?, fecha_resolucion = IF(? = 'RESUELTA', NOW(), NULL)
        WHERE id = ?
    ");
    $stmt->bind_param("ssi", $nuevo_estado, $nuevo_estado, $incidencia_id);
    
    if ($stmt->execute()) {
        $stmt->close();
        
        // Registrar cambio como comentario del sistema
        $usuario_id = $_SESSION['usuario_id'];
        $comentario = "Estado cambiado a: " . $nuevo_estado;
        
        $stmt = $conn->prepare("
            INSERT INTO comentarios_incidencia (incidencia_id, usuario_id, comentario)
            VALUES (?, ?, ?)
        ");
        $stmt->bind_param("iis", $incidencia_id, $usuario_id, $comentario);
        $stmt->execute();
        $stmt->close();
        
        $conn->close();
        
        echo json_encode([
            'success' => true,
            'message' => 'Estado actualizado exitosamente'
        ], JSON_UNESCAPED_UNICODE);
    } else {
        $conn->close();
        echo json_encode([
            'success' => false,
            'message' => 'Error al actualizar el estado'
        ], JSON_UNESCAPED_UNICODE);
    }
    
} elseif ($action === 'agregar_comentario') {
    $incidencia_id = intval($_POST['incidencia_id'] ?? 0);
    $comentario = trim($_POST['comentario'] ?? '');
    $usuario_id = $_SESSION['usuario_id'];
    
    if ($incidencia_id <= 0 || empty($comentario)) {
        echo json_encode([
            'success' => false,
            'message' => 'Datos inválidos'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // Insertar comentario
    $stmt = $conn->prepare("
        INSERT INTO comentarios_incidencia (incidencia_id, usuario_id, comentario)
        VALUES (?, ?, ?)
    ");
    $stmt->bind_param("iis", $incidencia_id, $usuario_id, $comentario);
    
    if ($stmt->execute()) {
        $stmt->close();
        $conn->close();
        
        echo json_encode([
            'success' => true,
            'message' => 'Comentario agregado exitosamente'
        ], JSON_UNESCAPED_UNICODE);
    } else {
        $stmt->close();
        $conn->close();
        
        echo json_encode([
            'success' => false,
            'message' => 'Error al agregar comentario'
        ], JSON_UNESCAPED_UNICODE);
    }
    
} else {
    $conn->close();
    echo json_encode([
        'success' => false,
        'message' => 'Acción no válida'
    ], JSON_UNESCAPED_UNICODE);
}
?>
