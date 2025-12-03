<?php
header('Content-Type: application/json; charset=UTF-8');
require_once '../config/database.php';

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

// Consultar edificios ACTIVOS ordenados por nombre
$sql = "SELECT id, nombre, direccion, ciudad FROM edificios WHERE activo = 1 ORDER BY nombre ASC";
$result = $conn->query($sql);

if (!$result) {
    $conn->close();
    echo json_encode([
        'success' => false, 
        'message' => 'Error al consultar edificios'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$edificios = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $edificios[] = [
            'id' => (int)$row['id'],
            'nombre' => $row['nombre'],
            'direccion' => $row['direccion'],
            'ciudad' => $row['ciudad']
        ];
    }
}

$conn->close();

echo json_encode([
    'success' => true,
    'edificios' => $edificios
], JSON_UNESCAPED_UNICODE);
exit;
