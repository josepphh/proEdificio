<?php
// Script simple para agregar columna respuesta_admin
require_once '../config/database.php';

$database = new Database();
$conn = $database->getConnection();

// Agregar columna si no existe
$sql = "ALTER TABLE solicitudes_acceso 
        ADD COLUMN IF NOT EXISTS respuesta_admin TEXT NULL 
        AFTER fecha_respuesta";

if ($conn->query($sql)) {
    echo "✅ Columna agregada o ya existe<br>";
} else {
    echo "Error: " . $conn->error . "<br>";
}

// Mostrar estructura
$result = $conn->query("DESCRIBE solicitudes_acceso");
echo "<h3>Estructura de la tabla:</h3>";
echo "<table border='1'>";
echo "<tr><th>Campo</th><th>Tipo</th></tr>";
while ($row = $result->fetch_assoc()) {
    echo "<tr><td>{$row['Field']}</td><td>{$row['Type']}</td></tr>";
}
echo "</table>";

$conn->close();
?>
