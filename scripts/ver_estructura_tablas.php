<?php
/**
 * Script para verificar estructura de tablas usuarios y usuario_edificios
 */

require_once __DIR__ . '/../config/database.php';

$database = new Database();
$conn = $database->getConnection();

echo "========================================\n";
echo "ESTRUCTURA DE TABLAS\n";
echo "========================================\n\n";

echo "=== TABLA usuarios ===\n";
$result = $conn->query('DESCRIBE usuarios');
while($row = $result->fetch_assoc()) {
    echo "{$row['Field']} ({$row['Type']})\n";
}

echo "\n=== TABLA usuario_edificios ===\n";
$result = $conn->query('DESCRIBE usuario_edificios');
while($row = $result->fetch_assoc()) {
    echo "{$row['Field']} ({$row['Type']})\n";
}

$conn->close();
