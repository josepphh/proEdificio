<?php
/**
 * Script para unificar permisos de reportes
 * Convierte ver_reportes_globales y ver_reportes_edificio en un solo permiso: ver_reportes
 */

require_once __DIR__ . '/../config/database.php';

echo "========================================\n";
echo "UNIFICACIÓN DE PERMISOS DE REPORTES\n";
echo "========================================\n\n";

$database = new Database();
$conn = $database->getConnection();

// 1. Verificar permisos actuales
echo "1. Verificando permisos actuales...\n";
$sql = "SELECT id, codigo, nombre FROM permisos WHERE codigo IN ('ver_reportes_globales', 'ver_reportes_edificio', 'ver_reportes')";
$result = $conn->query($sql);

$permisos_existentes = [];
while ($row = $result->fetch_assoc()) {
    $permisos_existentes[$row['codigo']] = $row['id'];
    echo "   - {$row['codigo']} (ID: {$row['id']})\n";
}
echo "\n";

// 2. Crear el nuevo permiso unificado si no existe
echo "2. Creando permiso unificado 'ver_reportes'...\n";
$permiso_unificado_id = null;

if (isset($permisos_existentes['ver_reportes'])) {
    $permiso_unificado_id = $permisos_existentes['ver_reportes'];
    echo "   ✓ El permiso 'ver_reportes' ya existe (ID: $permiso_unificado_id)\n\n";
} else {
    $sql = "INSERT INTO permisos (codigo, nombre, descripcion, categoria, activo) 
            VALUES ('ver_reportes', 'Ver Reportes', 'Acceso a reportes y estadísticas (el alcance se determina automáticamente según edificios asignados)', 'Reportes', 1)";
    
    if ($conn->query($sql)) {
        $permiso_unificado_id = $conn->insert_id;
        echo "   ✓ Permiso 'ver_reportes' creado (ID: $permiso_unificado_id)\n\n";
    } else {
        die("   ✗ Error al crear permiso: " . $conn->error . "\n");
    }
}

// 3. Migrar asignaciones de roles
echo "3. Migrando asignaciones de roles...\n";

if (isset($permisos_existentes['ver_reportes_globales']) || isset($permisos_existentes['ver_reportes_edificio'])) {
    
    // Obtener roles que tienen alguno de los permisos antiguos
    $permisos_antiguos = [];
    if (isset($permisos_existentes['ver_reportes_globales'])) {
        $permisos_antiguos[] = $permisos_existentes['ver_reportes_globales'];
    }
    if (isset($permisos_existentes['ver_reportes_edificio'])) {
        $permisos_antiguos[] = $permisos_existentes['ver_reportes_edificio'];
    }
    
    $ids_string = implode(',', $permisos_antiguos);
    
    $sql = "SELECT DISTINCT r.id, r.nombre 
            FROM roles r
            INNER JOIN rol_permisos rp ON r.id = rp.rol_id
            WHERE rp.permiso_id IN ($ids_string)";
    
    $result = $conn->query($sql);
    $roles_afectados = [];
    
    while ($row = $result->fetch_assoc()) {
        $roles_afectados[] = $row;
    }
    
    if (count($roles_afectados) > 0) {
        echo "   Roles con permisos antiguos:\n";
        foreach ($roles_afectados as $rol) {
            echo "   - {$rol['nombre']} (ID: {$rol['id']})\n";
        }
        echo "\n";
        
        // Asignar el nuevo permiso a estos roles
        echo "   Asignando permiso unificado a roles...\n";
        $stmt = $conn->prepare("INSERT IGNORE INTO rol_permisos (rol_id, permiso_id) VALUES (?, ?)");
        
        foreach ($roles_afectados as $rol) {
            $stmt->bind_param("ii", $rol['id'], $permiso_unificado_id);
            if ($stmt->execute()) {
                echo "   ✓ Permiso asignado a '{$rol['nombre']}'\n";
            }
        }
        $stmt->close();
        echo "\n";
        
        // Eliminar asignaciones de permisos antiguos
        echo "   Eliminando asignaciones de permisos antiguos...\n";
        $sql = "DELETE FROM rol_permisos WHERE permiso_id IN ($ids_string)";
        if ($conn->query($sql)) {
            $eliminados = $conn->affected_rows;
            echo "   ✓ $eliminados asignaciones eliminadas\n\n";
        }
        
    } else {
        echo "   ℹ No hay roles con los permisos antiguos\n\n";
    }
}

// 4. Desactivar permisos antiguos (no eliminar para mantener historial)
echo "4. Desactivando permisos antiguos...\n";
if (isset($permisos_existentes['ver_reportes_globales']) || isset($permisos_existentes['ver_reportes_edificio'])) {
    
    $codigos_antiguos = [];
    if (isset($permisos_existentes['ver_reportes_globales'])) $codigos_antiguos[] = 'ver_reportes_globales';
    if (isset($permisos_existentes['ver_reportes_edificio'])) $codigos_antiguos[] = 'ver_reportes_edificio';
    
    foreach ($codigos_antiguos as $codigo) {
        $sql = "UPDATE permisos SET activo = 0 WHERE codigo = '$codigo'";
        if ($conn->query($sql)) {
            echo "   ✓ Permiso '$codigo' desactivado\n";
        }
    }
    echo "\n";
}

// 5. Verificar resultado final
echo "5. Verificación final...\n";
$sql = "SELECT r.nombre as rol, GROUP_CONCAT(p.codigo SEPARATOR ', ') as permisos
        FROM roles r
        LEFT JOIN rol_permisos rp ON r.id = rp.rol_id
        LEFT JOIN permisos p ON rp.permiso_id = p.id
        WHERE p.codigo LIKE '%reporte%' AND p.activo = 1
        GROUP BY r.id, r.nombre";

$result = $conn->query($sql);

if ($result->num_rows > 0) {
    echo "   Permisos de reportes por rol:\n";
    while ($row = $result->fetch_assoc()) {
        echo "   - {$row['rol']}: {$row['permisos']}\n";
    }
} else {
    echo "   ℹ Ningún rol tiene permisos de reportes activos\n";
}

echo "\n========================================\n";
echo "✅ MIGRACIÓN COMPLETADA EXITOSAMENTE\n";
echo "========================================\n\n";

echo "Resumen:\n";
echo "- Permiso unificado 'ver_reportes' (ID: $permiso_unificado_id)\n";
echo "- Roles migrados: " . count($roles_afectados) . "\n";
echo "- Permisos antiguos desactivados\n";
echo "\nPróximo paso: Actualizar código en reportes.php y panel.php\n";

$conn->close();
