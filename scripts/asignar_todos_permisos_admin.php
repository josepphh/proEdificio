<?php
/**
 * Script para asignar TODOS los permisos activos al Administrador Total
 */

require_once __DIR__ . '/../config/database.php';

echo "========================================\n";
echo "ASIGNAR TODOS LOS PERMISOS AL ADMINISTRADOR TOTAL\n";
echo "========================================\n\n";

$database = new Database();
$conn = $database->getConnection();

// 1. Obtener ID del rol Administrador Total
echo "1. Buscando rol Administrador Total...\n";
$sql = "SELECT id, nombre FROM roles WHERE nombre = 'Administrador Total'";
$result = $conn->query($sql);

if ($result->num_rows === 0) {
    die("   ✗ Rol 'Administrador Total' no encontrado\n");
}

$rol = $result->fetch_assoc();
$rol_id = $rol['id'];
echo "   ✓ Rol encontrado (ID: $rol_id)\n\n";

// 2. Obtener TODOS los permisos activos
echo "2. Obteniendo todos los permisos activos...\n";
$sql = "SELECT id, codigo, nombre, categoria FROM permisos WHERE activo = 1 ORDER BY categoria, nombre";
$result = $conn->query($sql);

$permisos_sistema = [];
while ($row = $result->fetch_assoc()) {
    $permisos_sistema[] = $row;
}
echo "   ✓ Total de permisos activos: " . count($permisos_sistema) . "\n\n";

// 3. Obtener permisos actuales del Administrador Total
echo "3. Verificando permisos actuales del Administrador Total...\n";
$sql = "SELECT p.id, p.codigo 
        FROM permisos p
        INNER JOIN rol_permisos rp ON p.id = rp.permiso_id
        WHERE rp.rol_id = $rol_id AND p.activo = 1";
$result = $conn->query($sql);

$permisos_actuales = [];
while ($row = $result->fetch_assoc()) {
    $permisos_actuales[$row['codigo']] = $row['id'];
}
echo "   - Permisos actuales: " . count($permisos_actuales) . "\n\n";

// 4. Asignar permisos faltantes
echo "4. Asignando permisos faltantes...\n";
$stmt = $conn->prepare("INSERT IGNORE INTO rol_permisos (rol_id, permiso_id) VALUES (?, ?)");

$permisos_agregados = 0;
$permisos_existentes = 0;

foreach ($permisos_sistema as $permiso) {
    if (!isset($permisos_actuales[$permiso['codigo']])) {
        $stmt->bind_param("ii", $rol_id, $permiso['id']);
        if ($stmt->execute()) {
            if ($conn->affected_rows > 0) {
                echo "   ✓ Asignado: {$permiso['codigo']} ({$permiso['nombre']})\n";
                $permisos_agregados++;
            } else {
                $permisos_existentes++;
            }
        } else {
            echo "   ✗ Error al asignar {$permiso['codigo']}: " . $stmt->error . "\n";
        }
    } else {
        $permisos_existentes++;
    }
}
$stmt->close();

echo "\n========================================\n";
echo "✅ ASIGNACIÓN COMPLETADA\n";
echo "========================================\n\n";

// 5. Resumen final
echo "Resumen:\n";
echo "- Permisos totales en el sistema: " . count($permisos_sistema) . "\n";
echo "- Permisos que ya tenía: " . count($permisos_actuales) . "\n";
echo "- Permisos nuevos asignados: $permisos_agregados\n";
echo "- Total final: " . (count($permisos_actuales) + $permisos_agregados) . "\n\n";

// 6. Mostrar todos los permisos del Administrador Total agrupados por categoría
echo "Permisos del Administrador Total por categoría:\n";
$sql = "SELECT p.categoria, p.codigo, p.nombre 
        FROM permisos p
        INNER JOIN rol_permisos rp ON p.id = rp.permiso_id
        WHERE rp.rol_id = $rol_id AND p.activo = 1
        ORDER BY p.categoria, p.nombre";
$result = $conn->query($sql);

$permisos_por_categoria = [];
while ($row = $result->fetch_assoc()) {
    $cat = $row['categoria'] ?? 'General';
    if (!isset($permisos_por_categoria[$cat])) {
        $permisos_por_categoria[$cat] = [];
    }
    $permisos_por_categoria[$cat][] = $row;
}

foreach ($permisos_por_categoria as $categoria => $permisos) {
    echo "\n[$categoria] (" . count($permisos) . " permisos)\n";
    foreach ($permisos as $p) {
        echo "  - {$p['codigo']}\n";
    }
}

echo "\n✅ El Administrador Total ahora tiene acceso completo a TODO el sistema\n";

$conn->close();
