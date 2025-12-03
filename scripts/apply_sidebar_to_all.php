<?php
/**
 * Script para aplicar sidebar layout a todos los módulos admin
 * Ejecutar desde línea de comandos: php apply_sidebar_to_all.php
 */

$modulos = [
    ['archivo' => 'inquilinos.php', 'titulo' => 'Gestión de Inquilinos', 'icono' => '🏘️'],
    ['archivo' => 'roles.php', 'titulo' => 'Gestión de Roles', 'icono' => '🔑'],
    ['archivo' => 'permisos.php', 'titulo' => 'Permisos por Rol', 'icono' => '🔐'],
    ['archivo' => 'avisos.php', 'titulo' => 'Gestión de Avisos', 'icono' => '📢'],
    ['archivo' => 'incidencias.php', 'titulo' => 'Gestión de Incidencias', 'icono' => '🔧'],
    ['archivo' => 'solicitudes.php', 'titulo' => 'Solicitudes de Acceso', 'icono' => '📝'],
    ['archivo' => 'registrar_gastos.php', 'titulo' => 'Registrar Gastos', 'icono' => '💰'],
    ['archivo' => 'validar_pagos.php', 'titulo' => 'Validar Pagos', 'icono' => '✅'],
    ['archivo' => 'procesar_cierre.php', 'titulo' => 'Procesar Cierre', 'icono' => '📅'],
    ['archivo' => 'reportes.php', 'titulo' => 'Reportes', 'icono' => '📊'],
];

echo "=== Aplicando Sidebar Layout a Módulos Admin ===\n\n";

foreach ($modulos as $modulo) {
    $archivo = __DIR__ . '/../admin/' . $modulo['archivo'];
    
    if (!file_exists($archivo)) {
        echo "❌ No encontrado: {$modulo['archivo']}\n";
        continue;
    }
    
    $contenido = file_get_contents($archivo);
    
    // 1. Buscar y reemplazar el header section
    $patron_header = '/(\$title\s*=\s*["\'][^"\']*["\'];)\s*\n\s*include\s+["\']\.\.\/includes\/header\.php["\'];/';
    $reemplazo_header = "$1\n\$pageTitle = \"{$modulo['icono']} {$modulo['titulo']}\";\n\$useAdminLayout = true;\ninclude '../includes/header.php';\ninclude '../includes/admin_layout_start.php';";
    
    $contenido = preg_replace($patron_header, $reemplazo_header, $contenido);
    
    // 2. Remover include nav.php si existe
    $contenido = str_replace("include '../includes/nav.php';", "", $contenido);
    $contenido = str_replace("include '../includes/nav.php';\n", "", $contenido);
    $contenido = str_replace("include '../includes/nav.php';\r\n", "", $contenido);
    
    // 3. Agregar admin_layout_end.php antes del footer
    $patron_footer = '/(include\s+["\']\.\.\/includes\/footer\.php["\'];)/';
    $reemplazo_footer = "include '../includes/admin_layout_end.php';\n$1";
    
    $contenido = preg_replace($patron_footer, $reemplazo_footer, $contenido, 1);
    
    // Guardar archivo
    file_put_contents($archivo, $contenido);
    
    echo "✅ Actualizado: {$modulo['archivo']}\n";
}

echo "\n=== Proceso Completado ===\n";
echo "Total módulos actualizados: " . count($modulos) . "\n";
