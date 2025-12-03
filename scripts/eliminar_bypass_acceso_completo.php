<?php
/**
 * Script para actualizar el permiso acceso_completo
 * Cambia su descripción y verifica que Administrador Total tenga todos los permisos
 */

require_once __DIR__ . '/../config/database.php';

echo "========================================\n";
echo "ACTUALIZACIÓN: Eliminar bypass de acceso_completo\n";
echo "========================================\n\n";

$database = new Database();
$conn = $database->getConnection();

// 1. Actualizar descripción del permiso acceso_completo
echo "1. Actualizando descripción del permiso 'acceso_completo'...\n";
$sql = "UPDATE permisos 
        SET nombre = 'Acceso Completo (Legacy)', 
            descripcion = 'Permiso legacy - Ya no otorga bypass automático. Asignar permisos específicos en su lugar.',
            activo = 0
        WHERE codigo = 'acceso_completo'";

if ($conn->query($sql)) {
    echo "   ✓ Permiso 'acceso_completo' actualizado y desactivado\n\n";
} else {
    echo "   ⚠ No se pudo actualizar: " . $conn->error . "\n\n";
}

// 2. Obtener ID del rol Administrador Total
echo "2. Verificando permisos del Administrador Total...\n";
$sql = "SELECT id FROM roles WHERE nombre = 'Administrador Total'";
$result = $conn->query($sql);

if ($result->num_rows === 0) {
    die("   ✗ Rol 'Administrador Total' no encontrado\n");
}

$rol_admin = $result->fetch_assoc();
$rol_id = $rol_admin['id'];
echo "   ✓ Rol 'Administrador Total' encontrado (ID: $rol_id)\n\n";

// 3. Obtener todos los permisos activos (excepto acceso_completo)
echo "3. Obteniendo permisos activos del sistema...\n";
$sql = "SELECT id, codigo, nombre FROM permisos WHERE activo = 1 AND codigo != 'acceso_completo' ORDER BY categoria, nombre";
$result = $conn->query($sql);

$permisos_sistema = [];
while ($row = $result->fetch_assoc()) {
    $permisos_sistema[] = $row;
}
echo "   ✓ Total de permisos activos: " . count($permisos_sistema) . "\n\n";

// 4. Obtener permisos actuales del Administrador Total
echo "4. Verificando permisos actuales del Administrador Total...\n";
$sql = "SELECT p.id, p.codigo, p.nombre 
        FROM permisos p
        INNER JOIN rol_permisos rp ON p.id = rp.permiso_id
        WHERE rp.rol_id = $rol_id AND p.activo = 1
        ORDER BY p.categoria, p.nombre";
$result = $conn->query($sql);

$permisos_actuales = [];
while ($row = $result->fetch_assoc()) {
    $permisos_actuales[$row['codigo']] = $row;
}
echo "   - Permisos actuales: " . count($permisos_actuales) . "\n";

// 5. Identificar permisos faltantes
$permisos_faltantes = [];
foreach ($permisos_sistema as $permiso) {
    if (!isset($permisos_actuales[$permiso['codigo']])) {
        $permisos_faltantes[] = $permiso;
    }
}

if (count($permisos_faltantes) > 0) {
    echo "   - Permisos faltantes: " . count($permisos_faltantes) . "\n\n";
    
    echo "5. Asignando permisos faltantes al Administrador Total...\n";
    $stmt = $conn->prepare("INSERT INTO rol_permisos (rol_id, permiso_id) VALUES (?, ?)");
    
    foreach ($permisos_faltantes as $permiso) {
        $stmt->bind_param("ii", $rol_id, $permiso['id']);
        if ($stmt->execute()) {
            echo "   ✓ Asignado: {$permiso['codigo']}\n";
        } else {
            echo "   ✗ Error al asignar {$permiso['codigo']}: " . $stmt->error . "\n";
        }
    }
    $stmt->close();
} else {
    echo "   ✓ El Administrador Total ya tiene todos los permisos activos\n";
}

echo "\n========================================\n";
echo "✅ ACTUALIZACIÓN COMPLETADA\n";
echo "========================================\n\n";

// 6. Mostrar resumen final
echo "Resumen Final:\n";
echo "- Bypass de 'acceso_completo' eliminado del código\n";
echo "- Permiso 'acceso_completo' marcado como legacy y desactivado\n";
echo "- Administrador Total tiene " . (count($permisos_actuales) + count($permisos_faltantes)) . " permisos asignados\n";
echo "- Permisos agregados: " . count($permisos_faltantes) . "\n\n";

echo "IMPORTANTE:\n";
echo "- Ahora TODOS los permisos se verifican correctamente\n";
echo "- Para dar acceso a una funcionalidad, asigna el permiso específico\n";
echo "- El Administrador Total debe tener los permisos explícitamente asignados\n";

$conn->close();
