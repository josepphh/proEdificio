<?php
/**
 * Script para verificar y crear el permiso 'asignar_permisos' si no existe
 */

require_once __DIR__ . '/../config/database.php';

echo "========================================\n";
echo "VERIFICAR PERMISO ASIGNAR_PERMISOS\n";
echo "========================================\n\n";

$database = new Database();
$conn = $database->getConnection();

// 1. Verificar si existe el permiso
echo "1. Verificando permiso 'asignar_permisos'...\n";
$sql = "SELECT id, codigo, nombre, activo FROM permisos WHERE codigo = 'asignar_permisos'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $permiso = $result->fetch_assoc();
    echo "   ✓ Permiso encontrado (ID: {$permiso['id']})\n";
    echo "   - Nombre: {$permiso['nombre']}\n";
    echo "   - Activo: " . ($permiso['activo'] ? 'Sí' : 'No') . "\n\n";
    $permiso_id = $permiso['id'];
    
    // Si está inactivo, activarlo
    if (!$permiso['activo']) {
        echo "2. Activando permiso...\n";
        $conn->query("UPDATE permisos SET activo = 1 WHERE id = $permiso_id");
        echo "   ✓ Permiso activado\n\n";
    }
} else {
    echo "   ✗ Permiso NO encontrado\n\n";
    echo "2. Creando permiso 'asignar_permisos'...\n";
    $sql = "INSERT INTO permisos (codigo, nombre, descripcion, categoria, activo) 
            VALUES ('asignar_permisos', 'Asignar Permisos a Roles', 'Permite gestionar y asignar permisos a los diferentes roles del sistema', 'Sistema', 1)";
    if ($conn->query($sql)) {
        $permiso_id = $conn->insert_id;
        echo "   ✓ Permiso creado correctamente (ID: $permiso_id)\n\n";
    } else {
        die("   ✗ Error al crear permiso: " . $conn->error . "\n");
    }
}

// 3. Verificar si el Administrador Total tiene este permiso
echo "3. Verificando asignación al Administrador Total...\n";
$sql = "SELECT r.id, r.nombre 
        FROM roles r
        WHERE r.nombre = 'Administrador Total'";
$result = $conn->query($sql);

if ($result->num_rows === 0) {
    die("   ✗ Rol 'Administrador Total' no encontrado\n");
}

$rol = $result->fetch_assoc();
$rol_id = $rol['id'];
echo "   - Rol ID: $rol_id\n";

// Verificar si ya tiene el permiso
$sql = "SELECT * FROM rol_permisos WHERE rol_id = $rol_id AND permiso_id = $permiso_id";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    echo "   ✓ El Administrador Total YA tiene el permiso asignado\n";
} else {
    echo "   - El Administrador Total NO tiene el permiso\n";
    echo "   - Asignando permiso...\n";
    $sql = "INSERT INTO rol_permisos (rol_id, permiso_id) VALUES ($rol_id, $permiso_id)";
    if ($conn->query($sql)) {
        echo "   ✓ Permiso asignado correctamente\n";
    } else {
        echo "   ✗ Error al asignar: " . $conn->error . "\n";
    }
}

echo "\n========================================\n";
echo "✅ VERIFICACIÓN COMPLETADA\n";
echo "========================================\n";

$conn->close();
