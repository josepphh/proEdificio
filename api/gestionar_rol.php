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

if (!tienePermiso('acceso_completo')) {
    echo json_encode([
        'success' => false,
        'message' => 'No tienes permisos para gestionar roles'
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

// CREAR ROL
if ($action === 'crear') {
    // Validar campos requeridos
    if (empty($_POST['nombre']) || empty($_POST['descripcion'])) {
        $conn->close();
        echo json_encode([
            'success' => false,
            'message' => 'Todos los campos son requeridos'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    $nombre = trim($_POST['nombre']);
    $descripcion = trim($_POST['descripcion']);
    
    // Validaciones
    if (strlen($nombre) < 3 || strlen($nombre) > 50) {
        $conn->close();
        echo json_encode([
            'success' => false,
            'message' => 'El nombre debe tener entre 3 y 50 caracteres'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    if (strlen($descripcion) < 10 || strlen($descripcion) > 255) {
        $conn->close();
        echo json_encode([
            'success' => false,
            'message' => 'La descripcion debe tener entre 10 y 255 caracteres'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // Verificar si el nombre ya existe
    $stmt_check = $conn->prepare("SELECT id FROM roles WHERE nombre = ?");
    $stmt_check->bind_param("s", $nombre);
    $stmt_check->execute();
    if ($stmt_check->get_result()->num_rows > 0) {
        $stmt_check->close();
        $conn->close();
        echo json_encode([
            'success' => false,
            'message' => 'Ya existe un rol con este nombre'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    $stmt_check->close();
    
    // Insertar rol
    $sql = "INSERT INTO roles (nombre, descripcion) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $nombre, $descripcion);
    
    if ($stmt->execute()) {
        $stmt->close();
        $conn->close();
        echo json_encode([
            'success' => true,
            'message' => 'Rol creado exitosamente'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    } else {
        error_log("Error al crear rol: " . $stmt->error);
        $stmt->close();
        $conn->close();
        echo json_encode([
            'success' => false,
            'message' => 'Error al crear el rol'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
}

// ACTUALIZAR ROL
elseif ($action === 'actualizar') {
    // Validar campos requeridos
    if (empty($_POST['rol_id']) || empty($_POST['nombre']) || empty($_POST['descripcion'])) {
        $conn->close();
        echo json_encode([
            'success' => false,
            'message' => 'Todos los campos son requeridos'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    $rol_id = intval($_POST['rol_id']);
    $nombre = trim($_POST['nombre']);
    $descripcion = trim($_POST['descripcion']);
    
    // Validaciones
    if (strlen($nombre) < 3 || strlen($nombre) > 50) {
        $conn->close();
        echo json_encode([
            'success' => false,
            'message' => 'El nombre debe tener entre 3 y 50 caracteres'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    if (strlen($descripcion) < 10 || strlen($descripcion) > 255) {
        $conn->close();
        echo json_encode([
            'success' => false,
            'message' => 'La descripcion debe tener entre 10 y 255 caracteres'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // Verificar si el nombre ya existe (excepto el rol actual)
    $stmt_check = $conn->prepare("SELECT id FROM roles WHERE nombre = ? AND id != ?");
    $stmt_check->bind_param("si", $nombre, $rol_id);
    $stmt_check->execute();
    if ($stmt_check->get_result()->num_rows > 0) {
        $stmt_check->close();
        $conn->close();
        echo json_encode([
            'success' => false,
            'message' => 'Ya existe otro rol con este nombre'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    $stmt_check->close();
    
    // Actualizar rol
    $sql = "UPDATE roles SET nombre = ?, descripcion = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssi", $nombre, $descripcion, $rol_id);
    
    if ($stmt->execute()) {
        $stmt->close();
        $conn->close();
        echo json_encode([
            'success' => true,
            'message' => 'Rol actualizado exitosamente'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    } else {
        error_log("Error al actualizar rol: " . $stmt->error);
        $stmt->close();
        $conn->close();
        echo json_encode([
            'success' => false,
            'message' => 'Error al actualizar el rol'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
}

// ELIMINAR ROL
elseif ($action === 'eliminar') {
    if (empty($_POST['rol_id'])) {
        $conn->close();
        echo json_encode([
            'success' => false,
            'message' => 'ID de rol no especificado'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    $rol_id = intval($_POST['rol_id']);
    
    // Verificar si es uno de los roles del sistema que no se debe desactivar
    $roles_sistema = [1, 2, 3]; // Administrador Total, Administrador Edificio, Inquilino
    if (in_array($rol_id, $roles_sistema)) {
        $conn->close();
        echo json_encode([
            'success' => false,
            'message' => 'No se pueden desactivar los roles principales del sistema'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // Verificar si hay usuarios ACTIVOS con este rol
    $stmt_check = $conn->prepare("SELECT COUNT(*) as total FROM usuarios WHERE rol_id = ? AND activo = 1");
    $stmt_check->bind_param("i", $rol_id);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();
    $row = $result_check->fetch_assoc();
    $usuarios_afectados = $row['total'];
    $stmt_check->close();
    
    // Si hay usuarios con este rol, reasignarlos al rol "Inquilino" (ID 3)
    if ($usuarios_afectados > 0) {
        $rol_default = 3; // Inquilino
        $stmt_update = $conn->prepare("UPDATE usuarios SET rol_id = ? WHERE rol_id = ? AND activo = 1");
        $stmt_update->bind_param("ii", $rol_default, $rol_id);
        $stmt_update->execute();
        $stmt_update->close();
    }
    
    // Soft delete: Marcar rol como inactivo
    $sql = "UPDATE roles SET activo = 0 WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $rol_id);
    
    if ($stmt->execute()) {
        $stmt->close();
        $conn->close();
        
        $mensaje = 'Rol desactivado exitosamente';
        if ($usuarios_afectados > 0) {
            $mensaje .= ". {$usuarios_afectados} usuario(s) fueron reasignados al rol Inquilino";
        }
        
        echo json_encode([
            'success' => true,
            'message' => $mensaje
        ], JSON_UNESCAPED_UNICODE);
        exit;
    } else {
        error_log("Error al desactivar rol: " . $stmt->error);
        $stmt->close();
        $conn->close();
        echo json_encode([
            'success' => false,
            'message' => 'Error al desactivar el rol'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
}

// RESTAURAR ROL
elseif ($action === 'restaurar') {
    if (empty($_POST['rol_id'])) {
        $conn->close();
        echo json_encode([
            'success' => false,
            'message' => 'ID de rol no especificado'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    $rol_id = intval($_POST['rol_id']);
    
    // Restaurar rol: Marcar como activo
    $sql = "UPDATE roles SET activo = 1 WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $rol_id);
    
    if ($stmt->execute()) {
        $stmt->close();
        $conn->close();
        echo json_encode([
            'success' => true,
            'message' => 'Rol restaurado exitosamente'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    } else {
        error_log("Error al restaurar rol: " . $stmt->error);
        $stmt->close();
        $conn->close();
        echo json_encode([
            'success' => false,
            'message' => 'Error al restaurar el rol'
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
