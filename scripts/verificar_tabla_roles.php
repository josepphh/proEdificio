<?php
require_once __DIR__ . '/../config/database.php';

$database = new Database();
$conn = $database->getConnection();

echo "Estructura de la tabla 'roles':\n";
echo "==================================\n\n";

$result = $conn->query("DESCRIBE roles");

while ($row = $result->fetch_assoc()) {
    echo $row['Field'] . " - " . $row['Type'] . " - " . ($row['Null'] == 'YES' ? 'NULL' : 'NOT NULL') . "\n";
}

$conn->close();
?>
