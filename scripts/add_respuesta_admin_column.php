<?php
/**
 * Script para agregar la columna respuesta_admin a la tabla solicitudes_acceso
 * Ejecutar si la columna no existe
 */

require_once '../config/database.php';

$database = new Database();
$conn = $database->getConnection();

if (!$conn) {
    die("❌ Error: No se pudo conectar a la base de datos\n");
}

// Verificar si la columna existe
$result = $conn->query("SHOW COLUMNS FROM solicitudes_acceso LIKE 'respuesta_admin'");

if ($result->num_rows == 0) {
    // La columna no existe, agregarla
    $sql = "ALTER TABLE solicitudes_acceso 
            ADD COLUMN respuesta_admin TEXT NULL 
            AFTER fecha_respuesta";
    
    if ($conn->query($sql)) {
        echo "✅ Columna 'respuesta_admin' agregada exitosamente\n";
    } else {
        echo "❌ Error al agregar la columna: " . $conn->error . "\n";
    }
} else {
    echo "ℹ️ La columna 'respuesta_admin' ya existe\n";
}

// Mostrar estructura actualizada
echo "\n📋 Estructura actual de la tabla:\n";
echo str_repeat("-", 80) . "\n";

$result = $conn->query("DESCRIBE solicitudes_acceso");
while ($row = $result->fetch_assoc()) {
    printf("%-25s %-20s\n", $row['Field'], $row['Type']);
}

$conn->close();
?>
