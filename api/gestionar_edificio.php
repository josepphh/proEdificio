<?php
require_once '../includes/session.php';
require_once '../includes/permissions.php';
require_once '../config/database.php';

header('Content-Type: application/json; charset=UTF-8');

// Verificar método POST
if ($_SERVER["REQUEST_METHOD"] != "POST") {
    echo json_encode([
        'success' => false,
        'message' => 'Metodo no permitido'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

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

// Verificar acción
if (!isset($_POST['action'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Accion no especificada'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$action = $_POST['action'];

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

// CREAR EDIFICIO
if ($action === 'crear') {
    // Validar campos requeridos
    if (empty($_POST['nombre']) || empty($_POST['direccion']) || empty($_POST['ciudad']) || 
        empty($_POST['num_pisos']) || empty($_POST['num_departamentos'])) {
        $conn->close();
        echo json_encode([
            'success' => false,
            'message' => 'Todos los campos son requeridos'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    $nombre = trim($_POST['nombre']);
    $direccion = trim($_POST['direccion']);
    $ciudad = trim($_POST['ciudad']);
    $num_pisos = intval($_POST['num_pisos']);
    $num_departamentos = intval($_POST['num_departamentos']);
    
    // Validaciones
    if (strlen($nombre) < 3 || strlen($nombre) > 100) {
        $conn->close();
        echo json_encode([
            'success' => false,
            'message' => 'El nombre debe tener entre 3 y 100 caracteres'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    if (strlen($direccion) < 5 || strlen($direccion) > 200) {
        $conn->close();
        echo json_encode([
            'success' => false,
            'message' => 'La direccion debe tener entre 5 y 200 caracteres'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    if (strlen($ciudad) < 3 || strlen($ciudad) > 100) {
        $conn->close();
        echo json_encode([
            'success' => false,
            'message' => 'La ciudad debe tener entre 3 y 100 caracteres'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    if ($num_pisos < 1 || $num_pisos > 200) {
        $conn->close();
        echo json_encode([
            'success' => false,
            'message' => 'El numero de pisos debe estar entre 1 y 200'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    if ($num_departamentos < 1 || $num_departamentos > 1000) {
        $conn->close();
        echo json_encode([
            'success' => false,
            'message' => 'El numero de departamentos debe estar entre 1 y 1000'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // Verificar si el nombre ya existe
    $stmt_check = $conn->prepare("SELECT id FROM edificios WHERE nombre = ?");
    $stmt_check->bind_param("s", $nombre);
    $stmt_check->execute();
    if ($stmt_check->get_result()->num_rows > 0) {
        $stmt_check->close();
        $conn->close();
        echo json_encode([
            'success' => false,
            'message' => 'Ya existe un edificio con este nombre'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    $stmt_check->close();
    
    // Insertar edificio
    $sql = "INSERT INTO edificios (nombre, direccion, ciudad, num_pisos, num_departamentos) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssii", $nombre, $direccion, $ciudad, $num_pisos, $num_departamentos);
    
    if ($stmt->execute()) {
        $stmt->close();
        $conn->close();
        echo json_encode([
            'success' => true,
            'message' => 'Edificio creado exitosamente'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    } else {
        error_log("Error al crear edificio: " . $stmt->error);
        $stmt->close();
        $conn->close();
        echo json_encode([
            'success' => false,
            'message' => 'Error al crear el edificio'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
}

// ACTUALIZAR EDIFICIO
elseif ($action === 'actualizar') {
    // Validar campos requeridos
    if (empty($_POST['edificio_id']) || empty($_POST['nombre']) || empty($_POST['direccion']) || 
        empty($_POST['ciudad']) || empty($_POST['num_pisos']) || empty($_POST['num_departamentos'])) {
        $conn->close();
        echo json_encode([
            'success' => false,
            'message' => 'Todos los campos son requeridos'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    $edificio_id = intval($_POST['edificio_id']);
    $nombre = trim($_POST['nombre']);
    $direccion = trim($_POST['direccion']);
    $ciudad = trim($_POST['ciudad']);
    $num_pisos = intval($_POST['num_pisos']);
    $num_departamentos = intval($_POST['num_departamentos']);
    
    // Validaciones
    if (strlen($nombre) < 3 || strlen($nombre) > 100) {
        $conn->close();
        echo json_encode([
            'success' => false,
            'message' => 'El nombre debe tener entre 3 y 100 caracteres'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    if (strlen($direccion) < 5 || strlen($direccion) > 200) {
        $conn->close();
        echo json_encode([
            'success' => false,
            'message' => 'La direccion debe tener entre 5 y 200 caracteres'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    if (strlen($ciudad) < 3 || strlen($ciudad) > 100) {
        $conn->close();
        echo json_encode([
            'success' => false,
            'message' => 'La ciudad debe tener entre 3 y 100 caracteres'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    if ($num_pisos < 1 || $num_pisos > 200) {
        $conn->close();
        echo json_encode([
            'success' => false,
            'message' => 'El numero de pisos debe estar entre 1 y 200'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    if ($num_departamentos < 1 || $num_departamentos > 1000) {
        $conn->close();
        echo json_encode([
            'success' => false,
            'message' => 'El numero de departamentos debe estar entre 1 y 1000'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // Verificar si el nombre ya existe (excepto el edificio actual)
    $stmt_check = $conn->prepare("SELECT id FROM edificios WHERE nombre = ? AND id != ?");
    $stmt_check->bind_param("si", $nombre, $edificio_id);
    $stmt_check->execute();
    if ($stmt_check->get_result()->num_rows > 0) {
        $stmt_check->close();
        $conn->close();
        echo json_encode([
            'success' => false,
            'message' => 'Ya existe otro edificio con este nombre'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    $stmt_check->close();
    
    // Actualizar edificio
    $sql = "UPDATE edificios SET nombre = ?, direccion = ?, ciudad = ?, num_pisos = ?, num_departamentos = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssiii", $nombre, $direccion, $ciudad, $num_pisos, $num_departamentos, $edificio_id);
    
    if ($stmt->execute()) {
        $stmt->close();
        $conn->close();
        echo json_encode([
            'success' => true,
            'message' => 'Edificio actualizado exitosamente'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    } else {
        error_log("Error al actualizar edificio: " . $stmt->error);
        $stmt->close();
        $conn->close();
        echo json_encode([
            'success' => false,
            'message' => 'Error al actualizar el edificio'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
}

// ELIMINAR EDIFICIO
elseif ($action === 'eliminar') {
    if (empty($_POST['edificio_id'])) {
        $conn->close();
        echo json_encode([
            'success' => false,
            'message' => 'ID de edificio no especificado'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    $edificio_id = intval($_POST['edificio_id']);
    
    // Desactivar asignaciones de usuarios a este edificio en la tabla usuario_edificios
    $sql_update = "UPDATE usuario_edificios SET activo = 0 WHERE edificio_id = ?";
    $stmt_update = $conn->prepare($sql_update);
    $stmt_update->bind_param("i", $edificio_id);
    $stmt_update->execute();
    $stmt_update->close();
    
    // Soft delete: Marcar edificio como inactivo
    $sql = "UPDATE edificios SET activo = 0 WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $edificio_id);
    
    if ($stmt->execute()) {
        $stmt->close();
        $conn->close();
        echo json_encode([
            'success' => true,
            'message' => 'Edificio desactivado exitosamente'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    } else {
        error_log("Error al desactivar edificio: " . $stmt->error);
        $stmt->close();
        $conn->close();
        echo json_encode([
            'success' => false,
            'message' => 'Error al desactivar el edificio'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
}

// RESTAURAR EDIFICIO
elseif ($action === 'restaurar') {
    if (empty($_POST['edificio_id'])) {
        $conn->close();
        echo json_encode([
            'success' => false,
            'message' => 'ID de edificio no especificado'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    $edificio_id = intval($_POST['edificio_id']);
    
    // Restaurar asignaciones de usuarios a este edificio en la tabla usuario_edificios
    $sql_update = "UPDATE usuario_edificios SET activo = 1 WHERE edificio_id = ?";
    $stmt_update = $conn->prepare($sql_update);
    $stmt_update->bind_param("i", $edificio_id);
    $stmt_update->execute();
    $stmt_update->close();
    
    // Restaurar edificio: Marcar como activo
    $sql = "UPDATE edificios SET activo = 1 WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $edificio_id);
    
    if ($stmt->execute()) {
        $stmt->close();
        $conn->close();
        echo json_encode([
            'success' => true,
            'message' => 'Edificio restaurado exitosamente'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    } else {
        error_log("Error al restaurar edificio: " . $stmt->error);
        $stmt->close();
        $conn->close();
        echo json_encode([
            'success' => false,
            'message' => 'Error al restaurar el edificio'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
}

// Acción no válida
else {
    $conn->close();
    echo json_encode([
        'success' => false,
        'message' => 'Accion no valida'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}
?>
