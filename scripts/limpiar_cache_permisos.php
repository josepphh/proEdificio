<?php
/**
 * Script para limpiar la caché de permisos en sesión
 */

require_once __DIR__ . '/../config/database.php';

echo "========================================\n";
echo "LIMPIAR CACHÉ DE PERMISOS\n";
echo "========================================\n\n";

$database = new Database();
$conn = $database->getConnection();

// Obtener todos los roles
$sql = "SELECT id, nombre FROM roles";
$result = $conn->query($sql);

echo "Limpiando caché de permisos para todos los roles:\n\n";

// Simular limpieza (en realidad la sesión se limpia cuando el usuario cierra sesión y vuelve a entrar)
while ($rol = $result->fetch_assoc()) {
    echo "- Rol: {$rol['nombre']} (ID: {$rol['id']})\n";
    echo "  Variable de sesión: permisos_rol_{$rol['id']}\n";
}

echo "\n✅ Para que los cambios surtan efecto:\n";
echo "   1. Cierra sesión en el navegador\n";
echo "   2. Vuelve a iniciar sesión\n";
echo "   3. Los nuevos permisos estarán disponibles\n\n";

// Mostrar permisos actuales del Administrador Total
echo "========================================\n";
echo "PERMISOS DEL ADMINISTRADOR TOTAL\n";
echo "========================================\n\n";

$sql = "SELECT p.codigo, p.nombre 
        FROM permisos p
        INNER JOIN rol_permisos rp ON p.id = rp.permiso_id
        INNER JOIN roles r ON rp.rol_id = r.id
        WHERE r.nombre = 'Administrador Total' AND p.activo = 1
        ORDER BY p.nombre";
$result = $conn->query($sql);

$count = 0;
while ($permiso = $result->fetch_assoc()) {
    $count++;
    echo "$count. [{$permiso['codigo']}] {$permiso['nombre']}\n";
}

echo "\nTotal de permisos: $count\n";

$conn->close();
