<?php
/**
 * Script para comparar los permisos de acceso_completo y acceso_panel_admin
 */

require_once __DIR__ . '/../config/database.php';

echo "========================================\n";
echo "COMPARACIÓN DE PERMISOS ADMINISTRATIVOS\n";
echo "========================================\n\n";

$database = new Database();
$conn = $database->getConnection();

// Obtener ambos permisos
$sql = "SELECT codigo, nombre, descripcion, categoria FROM permisos 
        WHERE codigo IN ('acceso_completo', 'acceso_panel_admin') 
        ORDER BY codigo";
$result = $conn->query($sql);

$permisos = [];
while ($row = $result->fetch_assoc()) {
    $permisos[] = $row;
}

foreach ($permisos as $permiso) {
    echo "📋 {$permiso['nombre']}\n";
    echo "   Código: {$permiso['codigo']}\n";
    echo "   Descripción: {$permiso['descripcion']}\n";
    echo "   Categoría: {$permiso['categoria']}\n\n";
}

echo "========================================\n";
echo "USO EN EL CÓDIGO\n";
echo "========================================\n\n";

echo "1. ACCESO_COMPLETO:\n";
echo "   - Se usa como FLAG para el bypass en tienePermiso()\n";
echo "   - Cuando está presente Y \$bypass_admin_total = true:\n";
echo "     → Permite ver todas las opciones del Panel Admin\n";
echo "   - NO da acceso automático a las páginas\n";
echo "   - Es un permiso VISUAL, no funcional\n\n";

echo "2. ACCESO_PANEL_ADMIN:\n";
echo "   - Es un permiso FUNCIONAL estándar\n";
echo "   - Se verifica normalmente como cualquier otro permiso\n";
echo "   - NO tiene comportamiento especial de bypass\n";
echo "   - Controla el acceso real a la página panel.php\n\n";

echo "========================================\n";
echo "EJEMPLO PRÁCTICO\n";
echo "========================================\n\n";

echo "En includes/permissions.php:\n\n";
echo "function tienePermiso(\$permiso, \$bypass_admin_total = false) {\n";
echo "    if (\$bypass_admin_total && in_array('acceso_completo', \$permisos)) {\n";
echo "        return true;  // ← BYPASS VISUAL\n";
echo "    }\n";
echo "    return in_array(\$permiso, \$permisos);  // ← VERIFICACIÓN NORMAL\n";
echo "}\n\n";

echo "En admin/panel.php (línea ~8):\n";
echo "requierePermiso('acceso_panel_admin');  // ← Acceso REAL a la página\n\n";

echo "En admin/panel.php (línea ~70+):\n";
echo "if (tienePermiso('gestionar_usuarios', true)) {  // ← Mostrar tarjeta\n";
echo "    // Muestra la tarjeta de Usuarios en el panel\n";
echo "}\n\n";

echo "========================================\n";
echo "RESUMEN\n";
echo "========================================\n\n";

echo "┌─────────────────────┬──────────────────┬─────────────────────┐\n";
echo "│ Aspecto             │ acceso_completo  │ acceso_panel_admin  │\n";
echo "├─────────────────────┼──────────────────┼─────────────────────┤\n";
echo "│ Tipo                │ Visual/Flag      │ Funcional/Normal    │\n";
echo "│ Bypass              │ Sí (condicional) │ No                  │\n";
echo "│ Uso                 │ Mostrar opciones │ Acceso a página     │\n";
echo "│ Verificación        │ Especial         │ Estándar            │\n";
echo "│ Quién lo necesita   │ Admin Total      │ Todos los admins    │\n";
echo "└─────────────────────┴──────────────────┴─────────────────────┘\n\n";

$conn->close();
