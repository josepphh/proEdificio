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

if (!tienePermiso('gestionar_usuarios')) {
    echo json_encode([
        'success' => false,
        'message' => 'No tienes permisos para gestionar usuarios'
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

// CREAR USUARIO
if ($action === 'crear') {
    // Validar campos requeridos
    if (empty($_POST['nombre']) || empty($_POST['email']) || empty($_POST['username']) || empty($_POST['password']) || empty($_POST['rol_id'])) {
        $conn->close();
        echo json_encode([
            'success' => false,
            'message' => 'Todos los campos son requeridos'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    $nombre = trim($_POST['nombre']);
    $email = trim($_POST['email']);
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $rol_id = intval($_POST['rol_id']);
    
    // Validaciones
    if (strlen($nombre) < 3 || strlen($nombre) > 100) {
        $conn->close();
        echo json_encode([
            'success' => false,
            'message' => 'El nombre debe tener entre 3 y 100 caracteres'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $conn->close();
        echo json_encode([
            'success' => false,
            'message' => 'Email invalido'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    if (strlen($username) < 3 || strlen($username) > 50) {
        $conn->close();
        echo json_encode([
            'success' => false,
            'message' => 'El username debe tener entre 3 y 50 caracteres'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        $conn->close();
        echo json_encode([
            'success' => false,
            'message' => 'El username solo puede contener letras, numeros y guion bajo'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    if (strlen($password) < 8) {
        $conn->close();
        echo json_encode([
            'success' => false,
            'message' => 'La contrasena debe tener minimo 8 caracteres'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // Verificar si el email ya existe
    $stmt_check = $conn->prepare("SELECT id FROM usuarios WHERE email = ?");
    $stmt_check->bind_param("s", $email);
    $stmt_check->execute();
    if ($stmt_check->get_result()->num_rows > 0) {
        $stmt_check->close();
        $conn->close();
        echo json_encode([
            'success' => false,
            'message' => 'Este email ya esta registrado'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    $stmt_check->close();
    
    // Verificar si el username ya existe
    $stmt_check = $conn->prepare("SELECT id FROM usuarios WHERE username = ?");
    $stmt_check->bind_param("s", $username);
    $stmt_check->execute();
    if ($stmt_check->get_result()->num_rows > 0) {
        $stmt_check->close();
        $conn->close();
        echo json_encode([
            'success' => false,
            'message' => 'Este username ya esta en uso'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    $stmt_check->close();
    
    // Hashear contraseña
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // Insertar usuario (edificios se asignan por usuario_edificios)
    $sql = "INSERT INTO usuarios (nombre, email, username, password, rol_id) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssi", $nombre, $email, $username, $hashed_password, $rol_id);
    
    if ($stmt->execute()) {
        $nuevo_usuario_id = $conn->insert_id;
        $stmt->close();
        
        // Obtener nombre del rol
        $stmt_rol = $conn->prepare("SELECT nombre FROM roles WHERE id = ?");
        $stmt_rol->bind_param("i", $rol_id);
        $stmt_rol->execute();
        $rol_result = $stmt_rol->get_result();
        $rol_data = $rol_result->fetch_assoc();
        $rol_nombre = $rol_data['nombre'] ?? 'Usuario';
        $stmt_rol->close();
        
        // Intentar enviar email de bienvenida
        $email_enviado = false;
        $mensaje_respuesta = 'Usuario creado exitosamente.';
        
        try {
            // Verificar si la clase Mailer existe (PHPMailer instalado)
            if (file_exists(__DIR__ . '/../includes/mail.php')) {
                require_once __DIR__ . '/../includes/mail.php';
                
                if (class_exists('Mailer')) {
                    $mailer = new Mailer();
                    $datos_usuario = [
                        'username' => $username,
                        'password' => $password, // Contraseña sin hashear (solo para este email)
                        'rol' => $rol_nombre,
                        'link_sistema' => 'http://localhost:8012/proyectoEdificio/login.php'
                    ];
                    
                    if ($mailer->enviarBienvenidaUsuario($email, $nombre, $datos_usuario)) {
                        $email_enviado = true;
                        $mensaje_respuesta = 'Usuario creado exitosamente. Email de bienvenida enviado.';
                    } else {
                        $mensaje_respuesta = 'Usuario creado exitosamente. No se pudo enviar el correo de bienvenida.';
                    }
                } else {
                    $mensaje_respuesta = 'Usuario creado exitosamente. No se pudo enviar el correo de bienvenida.';
                }
            } else {
                $mensaje_respuesta = 'Usuario creado exitosamente. No se pudo enviar el correo de bienvenida.';
            }
        } catch (Exception $e) {
            // Si hay error al enviar email, continuar pero notificar
            error_log("Error al enviar email de bienvenida: " . $e->getMessage());
            $mensaje_respuesta = 'Usuario creado exitosamente. No se pudo enviar el correo de bienvenida.';
        }
        
        $conn->close();
        echo json_encode([
            'success' => true,
            'message' => $mensaje_respuesta,
            'usuario_id' => $nuevo_usuario_id,
            'email_enviado' => $email_enviado
        ], JSON_UNESCAPED_UNICODE);
        exit;
    } else {
        error_log("Error al crear usuario: " . $stmt->error);
        $stmt->close();
        $conn->close();
        echo json_encode([
            'success' => false,
            'message' => 'Error al crear el usuario'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
}

// ACTUALIZAR USUARIO
elseif ($action === 'actualizar') {
    // Validar campos requeridos
    if (empty($_POST['usuario_id']) || empty($_POST['nombre']) || empty($_POST['email']) || empty($_POST['username']) || empty($_POST['rol_id'])) {
        $conn->close();
        echo json_encode([
            'success' => false,
            'message' => 'Todos los campos son requeridos'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    $usuario_id = intval($_POST['usuario_id']);
    $nombre = trim($_POST['nombre']);
    $email = trim($_POST['email']);
    $username = trim($_POST['username']);
    $password = !empty($_POST['password']) ? $_POST['password'] : null;
    $rol_id = intval($_POST['rol_id']);
    
    // Validaciones
    if (strlen($nombre) < 3 || strlen($nombre) > 100) {
        $conn->close();
        echo json_encode([
            'success' => false,
            'message' => 'El nombre debe tener entre 3 y 100 caracteres'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $conn->close();
        echo json_encode([
            'success' => false,
            'message' => 'Email invalido'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    if (strlen($username) < 3 || strlen($username) > 50) {
        $conn->close();
        echo json_encode([
            'success' => false,
            'message' => 'El username debe tener entre 3 y 50 caracteres'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        $conn->close();
        echo json_encode([
            'success' => false,
            'message' => 'El username solo puede contener letras, numeros y guion bajo'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    if ($password && strlen($password) < 8) {
        $conn->close();
        echo json_encode([
            'success' => false,
            'message' => 'La contrasena debe tener minimo 8 caracteres'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // Verificar si el email ya existe (excepto el usuario actual)
    $stmt_check = $conn->prepare("SELECT id FROM usuarios WHERE email = ? AND id != ?");
    $stmt_check->bind_param("si", $email, $usuario_id);
    $stmt_check->execute();
    if ($stmt_check->get_result()->num_rows > 0) {
        $stmt_check->close();
        $conn->close();
        echo json_encode([
            'success' => false,
            'message' => 'Este email ya esta registrado'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    $stmt_check->close();
    
    // Verificar si el username ya existe (excepto el usuario actual)
    $stmt_check = $conn->prepare("SELECT id FROM usuarios WHERE username = ? AND id != ?");
    $stmt_check->bind_param("si", $username, $usuario_id);
    $stmt_check->execute();
    if ($stmt_check->get_result()->num_rows > 0) {
        $stmt_check->close();
        $conn->close();
        echo json_encode([
            'success' => false,
            'message' => 'Este username ya esta en uso'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    $stmt_check->close();
    
    // Actualizar usuario
    if ($password) {
        // Si se proporciona nueva contraseña
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $sql = "UPDATE usuarios SET nombre = ?, email = ?, username = ?, password = ?, rol_id = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssii", $nombre, $email, $username, $hashed_password, $rol_id, $usuario_id);
    } else {
        // Sin cambiar contraseña
        $sql = "UPDATE usuarios SET nombre = ?, email = ?, username = ?, rol_id = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssii", $nombre, $email, $username, $rol_id, $usuario_id);
    }
    
    if ($stmt->execute()) {
        $stmt->close();
        $conn->close();
        echo json_encode([
            'success' => true,
            'message' => 'Usuario actualizado exitosamente'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    } else {
        error_log("Error al actualizar usuario: " . $stmt->error);
        $stmt->close();
        $conn->close();
        echo json_encode([
            'success' => false,
            'message' => 'Error al actualizar el usuario'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
}

// ELIMINAR USUARIO
elseif ($action === 'eliminar') {
    if (empty($_POST['usuario_id'])) {
        $conn->close();
        echo json_encode([
            'success' => false,
            'message' => 'ID de usuario no especificado'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    $usuario_id = intval($_POST['usuario_id']);
    
    // No permitir eliminar al usuario actual
    if ($usuario_id == $_SESSION['usuario_id']) {
        $conn->close();
        echo json_encode([
            'success' => false,
            'message' => 'No puedes eliminar tu propio usuario'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // Soft delete: Marcar usuario como inactivo
    $sql = "UPDATE usuarios SET activo = 0 WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $usuario_id);
    
    if ($stmt->execute()) {
        $stmt->close();
        $conn->close();
        echo json_encode([
            'success' => true,
            'message' => 'Usuario desactivado exitosamente'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    } else {
        error_log("Error al desactivar usuario: " . $stmt->error);
        $stmt->close();
        $conn->close();
        echo json_encode([
            'success' => false,
            'message' => 'Error al desactivar el usuario'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
}

// RESTAURAR USUARIO
elseif ($action === 'restaurar') {
    if (empty($_POST['usuario_id'])) {
        $conn->close();
        echo json_encode([
            'success' => false,
            'message' => 'ID de usuario no especificado'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    $usuario_id = intval($_POST['usuario_id']);
    
    // Restaurar usuario: Marcar como activo
    $sql = "UPDATE usuarios SET activo = 1 WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $usuario_id);
    
    if ($stmt->execute()) {
        $stmt->close();
        $conn->close();
        echo json_encode([
            'success' => true,
            'message' => 'Usuario restaurado exitosamente'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    } else {
        error_log("Error al restaurar usuario: " . $stmt->error);
        $stmt->close();
        $conn->close();
        echo json_encode([
            'success' => false,
            'message' => 'Error al restaurar el usuario'
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
