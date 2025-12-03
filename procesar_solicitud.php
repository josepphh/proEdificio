<?php
require_once 'config/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

// Validar campos requeridos
$required = ['nombre_completo', 'email', 'telefono', 'nombre_edificio'];
foreach ($required as $field) {
    if (empty($_POST[$field])) {
        echo json_encode(['success' => false, 'message' => 'Todos los campos son obligatorios']);
        exit;
    }
}

// Validar email
if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Email inválido']);
    exit;
}

// Sanitizar datos
$nombre_completo = trim($_POST['nombre_completo']);
$email = trim(strtolower($_POST['email']));
$telefono = trim($_POST['telefono']);
$nombre_edificio = trim($_POST['nombre_edificio']);
$direccion_edificio = trim($_POST['direccion_edificio'] ?? '');
$num_departamentos = intval($_POST['num_departamentos'] ?? 0);
$mensaje = trim($_POST['mensaje'] ?? '');

// Conectar a la base de datos
$database = new Database();
$conn = $database->getConnection();

if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'Error de conexión a la base de datos']);
    exit;
}

try {
    // Verificar si ya existe una solicitud pendiente con ese email
    $stmt = $conn->prepare("SELECT id FROM solicitudes_acceso WHERE email = ? AND estado = 'PENDIENTE' AND activo = 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        echo json_encode([
            'success' => false, 
            'message' => 'Ya tienes una solicitud pendiente. Por favor espera la respuesta del administrador.'
        ]);
        $stmt->close();
        $conn->close();
        exit;
    }
    $stmt->close();
    
    // Insertar solicitud
    $stmt = $conn->prepare("
        INSERT INTO solicitudes_acceso 
        (nombre_completo, email, telefono, nombre_edificio, direccion_edificio, num_departamentos, mensaje, estado, activo) 
        VALUES (?, ?, ?, ?, ?, ?, ?, 'PENDIENTE', 1)
    ");
    
    $stmt->bind_param("sssssis", 
        $nombre_completo, 
        $email, 
        $telefono, 
        $nombre_edificio, 
        $direccion_edificio, 
        $num_departamentos, 
        $mensaje
    );
    
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true, 
            'message' => '¡Solicitud enviada exitosamente! Nos pondremos en contacto contigo pronto.'
        ]);
    } else {
        echo json_encode([
            'success' => false, 
            'message' => 'Error al guardar la solicitud: ' . $stmt->error
        ]);
    }
    
    $stmt->close();
} catch (Exception $e) {
    echo json_encode([
        'success' => false, 
        'message' => 'Error: ' . $e->getMessage()
    ]);
}

$conn->close();
?>
