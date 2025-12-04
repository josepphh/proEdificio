<?php
require_once __DIR__ . '/../config/database.php';

$database = new Database();
$conn = $database->getConnection();

// Actualizar todas las solicitudes activas sin respuesta a PENDIENTE
$sql = "UPDATE solicitudes_acceso 
        SET estado = 'PENDIENTE' 
        WHERE activo = 1 
        AND fecha_respuesta IS NULL";

if ($conn->query($sql)) {
    echo "✅ Solicitudes actualizadas correctamente a estado PENDIENTE<br>";
    echo "Filas afectadas: " . $conn->affected_rows . "<br><br>";
    
    // Mostrar solicitudes actualizadas
    $result = $conn->query("SELECT id, nombre_completo, estado FROM solicitudes_acceso WHERE activo = 1");
    echo "<h3>Estado actual de solicitudes:</h3>";
    while ($row = $result->fetch_assoc()) {
        echo "ID: " . $row['id'] . " - " . $row['nombre_completo'] . " - Estado: <strong>" . $row['estado'] . "</strong><br>";
    }
} else {
    echo "❌ Error: " . $conn->error;
}

$conn->close();
?>
