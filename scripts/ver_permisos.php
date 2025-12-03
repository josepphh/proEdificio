<?php
require_once __DIR__ . '/../config/database.php';

$database = new Database();
$conn = $database->getConnection();

echo "=== PERMISOS EN LA BASE DE DATOS ===\n\n";

$result = $conn->query("SELECT id, codigo, nombre, categoria FROM permisos ORDER BY categoria, nombre");

$permisos_por_categoria = [];
while ($row = $result->fetch_assoc()) {
    $cat = $row['categoria'] ?? 'General';
    if (!isset($permisos_por_categoria[$cat])) {
        $permisos_por_categoria[$cat] = [];
    }
    $permisos_por_categoria[$cat][] = $row;
}

foreach ($permisos_por_categoria as $categoria => $permisos) {
    echo "\n[$categoria]\n";
    foreach ($permisos as $p) {
        echo "  - {$p['codigo']} (ID: {$p['id']}) - {$p['nombre']}\n";
    }
}

echo "\n\n=== PERMISOS POR ROL ===\n\n";

$sql = "SELECT r.id, r.nombre as rol, GROUP_CONCAT(p.codigo SEPARATOR ', ') as permisos
        FROM roles r
        LEFT JOIN rol_permisos rp ON r.id = rp.rol_id
        LEFT JOIN permisos p ON rp.permiso_id = p.id
        WHERE r.activo = 1
        GROUP BY r.id, r.nombre
        ORDER BY r.id";

$result = $conn->query($sql);

while ($row = $result->fetch_assoc()) {
    echo "[{$row['rol']}]\n";
    echo "Permisos: " . ($row['permisos'] ?? 'Ninguno') . "\n\n";
}

$conn->close();
