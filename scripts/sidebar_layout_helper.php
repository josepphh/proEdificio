/**
 * Helper Script: Aplicar Sidebar Layout a Módulos Admin
 * 
 * Este script contiene la función para convertir módulos admin
 * del layout tradicional al nuevo layout con sidebar
 */

// Patrón de reemplazo para archivos admin
function convertirASidebarLayout($archivo) {
    $contenido = file_get_contents($archivo);
    
    // 1. Agregar $useAdminLayout = true; después de $title
    $patron1 = '/(\$title\s*=\s*["\'].*?["\'];)/';
    $reemplazo1 = "$1\n\$useAdminLayout = true;";
    $contenido = preg_replace($patron1, $reemplazo1, $contenido, 1);
    
    // 2. Reemplazar include nav.php con sidebar.php
    $contenido = str_replace(
        "include '../includes/nav.php';",
        "// Sidebar incluido automáticamente por el layout",
        $contenido
    );
    
    // 3. Agregar estructura de layout después del header
    // Buscar la primera sección/div después del header
    $patron3 = '/(include\s+[\'"]\.\.\/includes\/header\.php[\'"];.*?\n)/s';
    $reemplazo3 = "$1\n<!-- Sidebar -->\n<?php include '../includes/sidebar.php'; ?>\n\n<!-- Main Wrapper -->\n<div class=\"main-wrapper\">\n    <!-- Top Header -->\n    <header class=\"top-header\">\n        <div class=\"top-header-left\">\n            <button class=\"sidebar-toggle\" onclick=\"toggleSidebar()\" aria-label=\"Toggle Sidebar\">☰</button>\n            <h1 class=\"top-header-title\" id=\"pageTitle\">Panel de Administración</h1>\n        </div>\n        <div class=\"top-header-right\">\n            <div class=\"user-info-sidebar\">\n                <div class=\"user-avatar\">\n                    <?php echo strtoupper(substr(\$_SESSION['usuario_nombre'], 0, 1)); ?>\n                </div>\n                <div class=\"user-details\">\n                    <span class=\"user-name\"><?php echo htmlspecialchars(\$_SESSION['usuario_nombre']); ?></span>\n                    <span class=\"user-role-badge\"><?php echo htmlspecialchars(\$_SESSION['rol_nombre'] ?? 'Usuario'); ?></span>\n                </div>\n            </div>\n        </div>\n    </header>\n    \n    <!-- Main Content -->\n    <main class=\"main-content\">\n";
    
    $contenido = preg_replace($patron3, $reemplazo3, $contenido, 1);
    
    // 4. Cerrar divs antes del footer
    $patron4 = '/(include\s+[\'"]\.\.\/includes\/footer\.php[\'"];)/';
    $reemplazo4 = "    </main>\n</div>\n\n$1";
    $contenido = preg_replace($patron4, $reemplazo4, $contenido, 1);
    
    return $contenido;
}

// Nota: Este es un helper de referencia.
// La conversión se hará manualmente para mayor precisión.
