<?php
require_once __DIR__ . '/../config/database.php';

echo "========================================\n";
echo "MIGRACIÓN: Añadir campo 'activo' a roles\n";
echo "========================================\n\n";

$database = new Database();
$conn = $database->getConnection();

// Verificar si ya existe la columna
$check = $conn->query("SHOW COLUMNS FROM roles LIKE 'activo'");
if ($check->num_rows > 0) {
    echo "⚠️  La columna 'activo' ya existe en la tabla roles.\n";
    $conn->close();
    exit;
}

// Añadir columna activo
echo "1. Añadiendo columna 'activo' a tabla 'roles'...\n";
$sql = "ALTER TABLE roles ADD COLUMN activo TINYINT(1) NOT NULL DEFAULT 1 AFTER descripcion";

if ($conn->query($sql)) {
    echo "   ✅ Columna 'activo' añadida exitosamente\n\n";
} else {
    echo "   ❌ Error: " . $conn->error . "\n";
    $conn->close();
    exit;
}

// Asegurarse de que todos los roles existentes estén activos
echo "2. Configurando todos los roles existentes como activos...\n";
$sql_update = "UPDATE roles SET activo = 1";
if ($conn->query($sql_update)) {
    echo "   ✅ Todos los roles marcados como activos\n\n";
} else {
    echo "   ❌ Error: " . $conn->error . "\n";
}

// Verificar estructura final
echo "3. Verificando estructura actualizada:\n";
$result = $conn->query("DESCRIBE roles");
while ($row = $result->fetch_assoc()) {
    echo "   - {$row['Field']} ({$row['Type']})\n";
}

echo "\n========================================\n";
echo "✅ MIGRACIÓN COMPLETADA EXITOSAMENTE\n";
echo "========================================\n";

$conn->close();
?>
