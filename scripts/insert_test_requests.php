<?php
require_once __DIR__ . '/../config/database.php';

$database = new Database();
$conn = $database->getConnection();

if (!$conn) {
    die("Error: No se pudo conectar a la base de datos");
}

$requests = [
    [
        'nombre_completo' => 'Juan Pérez',
        'email' => 'juan.perez@example.com',
        'telefono' => '555-0101',
        'nombre_edificio' => 'Edificio Central',
        'direccion_edificio' => 'Av. Principal 123',
        'num_departamentos' => 20,
        'mensaje' => 'Solicito acceso para gestionar mi edificio.'
    ],
    [
        'nombre_completo' => 'María García',
        'email' => 'maria.garcia@example.com',
        'telefono' => '555-0102',
        'nombre_edificio' => 'Residencial Los Pinos',
        'direccion_edificio' => 'Calle Los Pinos 456',
        'num_departamentos' => 15,
        'mensaje' => 'Me gustaría probar el sistema para mi comunidad.'
    ],
    [
        'nombre_completo' => 'Carlos López',
        'email' => 'carlos.lopez@example.com',
        'telefono' => '555-0103',
        'nombre_edificio' => 'Torre Vista',
        'direccion_edificio' => 'Blvd. Vista Hermosa 789',
        'num_departamentos' => 50,
        'mensaje' => 'Administro un edificio grande y necesito una solución integral.'
    ]
];

$stmt = $conn->prepare("INSERT INTO solicitudes_acceso (nombre_completo, email, telefono, nombre_edificio, direccion_edificio, num_departamentos, mensaje) VALUES (?, ?, ?, ?, ?, ?, ?)");

if (!$stmt) {
    die("Error en la preparación: " . $conn->error);
}

foreach ($requests as $req) {
    $stmt->bind_param("sssssis", 
        $req['nombre_completo'], 
        $req['email'], 
        $req['telefono'], 
        $req['nombre_edificio'], 
        $req['direccion_edificio'], 
        $req['num_departamentos'], 
        $req['mensaje']
    );
    
    if ($stmt->execute()) {
        echo "✅ Solicitud creada para: " . $req['nombre_completo'] . "\n";
    } else {
        echo "❌ Error al crear solicitud para " . $req['nombre_completo'] . ": " . $stmt->error . "\n";
    }
}

$stmt->close();
$conn->close();
?>
