<?php
require_once 'config/database.php';
session_start();

// Establecer codificación UTF-8 para los datos
header('Content-Type: application/json; charset=UTF-8');

// Verificar método POST
if ($_SERVER["REQUEST_METHOD"] != "POST") {
    echo json_encode([
        'success' => false,
        'message' => 'Metodo no permitido'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Verificar campos requeridos
if (!isset($_POST['username']) || !isset($_POST['password'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Por favor, completa todos los campos'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Validar que los campos no estén vacíos
if (empty(trim($_POST['username'])) || empty(trim($_POST['password']))) {
    echo json_encode([
        'success' => false,
        'message' => 'Usuario y contrasena son requeridos'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Crear conexión a la base de datos
$database = new Database();
$conn = $database->getConnection();

if (!$conn) {
    echo json_encode([
        'success' => false,
        'message' => 'Error de conexion a la base de datos'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Limpiar datos
$username = trim($_POST['username']);
$password = $_POST['password'];

// Validar longitud mínima
if (strlen($username) < 3) {
    $conn->close();
    echo json_encode([
        'success' => false,
        'message' => 'Usuario invalido'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Buscar usuario ACTIVO con información de rol (sin edificio_id ya que fue eliminado)
$stmt = $conn->prepare("
    SELECT u.id, u.nombre, u.email, u.username, u.password, u.rol_id, 
           r.nombre as rol_nombre
    FROM usuarios u
    LEFT JOIN roles r ON u.rol_id = r.id
    WHERE (u.username = ? OR u.email = ?) AND u.activo = 1
");

if (!$stmt) {
    $conn->close();
    echo json_encode([
        'success' => false,
        'message' => 'Error al procesar la solicitud'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$stmt->bind_param("ss", $username, $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $stmt->close();
    $conn->close();
    echo json_encode([
        'success' => false,
        'message' => 'Usuario o contrasena incorrectos'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$user = $result->fetch_assoc();
$stmt->close();

// Verificar contraseña
if (!password_verify($password, $user['password'])) {
    $conn->close();
    echo json_encode([
        'success' => false,
        'message' => 'Usuario o contrasena incorrectos'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Login exitoso - crear sesión (sin edificio_id ya que fue eliminado)
$_SESSION['usuario_id'] = $user['id'];
$_SESSION['usuario_nombre'] = $user['nombre'];
$_SESSION['usuario_username'] = $user['username'];
$_SESSION['usuario_email'] = $user['email'];
$_SESSION['rol_id'] = $user['rol_id'];
$_SESSION['rol_nombre'] = $user['rol_nombre'];

$conn->close();

echo json_encode([
    'success' => true,
    'message' => 'Inicio de sesion exitoso',
    'user' => [
        'nombre' => $user['nombre'],
        'rol' => $user['rol_nombre']
    ]
], JSON_UNESCAPED_UNICODE);
exit;
?>
