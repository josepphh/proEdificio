<?php
/**
 * Sidebar Navigation Component
 * Sistema de Gestión de Edificios
 * 
 * Este componente muestra la navegación lateral para el panel de administración.
 * Solo se muestra para usuarios autenticados con permisos de administración.
 */

// Asegurarse de que session.php y permissions.php estén cargados
if (!isset($_SESSION)) {
    require_once __DIR__ . '/session.php';
}
if (!function_exists('tienePermiso')) {
    require_once __DIR__ . '/permissions.php';
}

// Solo mostrar sidebar si el usuario está autenticado
if (!estaAutenticado()) {
    return;
}
?>

<!-- Sidebar Navigation -->
<aside class="sidebar" id="sidebar">
    <!-- Header del Sidebar -->
    <div class="sidebar-header">
        <h2>
            <span class="sidebar-logo">🏢</span>
            EMISIÓN
        </h2>
    </div>
    
    <!-- Navegación -->
    <nav class="sidebar-nav">
        <!-- Sección: GESTIÓN -->
        <div class="sidebar-section">
            <h3 class="sidebar-section-title">GESTIÓN</h3>
            
            <?php if (tienePermiso('gestionar_usuarios', true)): ?>
            <a href="/proyectoEdificio/admin/usuarios.php" 
               onclick="event.preventDefault(); loadContent(this.href);" 
               class="sidebar-item" 
               data-page="usuarios">
                <span class="sidebar-icon">👥</span>
                <span class="sidebar-label">Usuarios</span>
            </a>
            <?php endif; ?>
            
            <?php if (tienePermiso('gestionar_edificios', true)): ?>
            <a href="/proyectoEdificio/admin/edificios.php" 
               onclick="event.preventDefault(); loadContent(this.href);" 
               class="sidebar-item" 
               data-page="edificios">
                <span class="sidebar-icon">🏛️</span>
                <span class="sidebar-label">Edificios</span>
            </a>
            <?php endif; ?>
            
            <?php if (tienePermiso('gestionar_inquilinos', true)): ?>
            <a href="/proyectoEdificio/admin/inquilinos.php" 
               onclick="event.preventDefault(); loadContent(this.href);" 
               class="sidebar-item" 
               data-page="inquilinos">
                <span class="sidebar-icon">🏘️</span>
                <span class="sidebar-label">Inquilinos</span>
            </a>
            <?php endif; ?>
            
            <?php if (tienePermiso('gestionar_roles', true)): ?>
            <a href="/proyectoEdificio/admin/roles.php" 
               onclick="event.preventDefault(); loadContent(this.href);" 
               class="sidebar-item" 
               data-page="roles">
                <span class="sidebar-icon">🔑</span>
                <span class="sidebar-label">Roles</span>
            </a>
            <?php endif; ?>
            
            <?php if (tienePermiso('asignar_permisos', true)): ?>
            <a href="/proyectoEdificio/admin/permisos.php" 
               onclick="event.preventDefault(); loadContent(this.href);" 
               class="sidebar-item" 
               data-page="permisos">
                <span class="sidebar-icon">🔐</span>
                <span class="sidebar-label">Permisos</span>
            </a>
            <?php endif; ?>
        </div>
        
        <!-- Sección: OPERACIONES -->
        <div class="sidebar-section">
            <h3 class="sidebar-section-title">OPERACIONES</h3>
            
            <?php if (tienePermiso('gestionar_avisos', true)): ?>
            <a href="/proyectoEdificio/admin/avisos.php" 
               onclick="event.preventDefault(); loadContent(this.href);" 
               class="sidebar-item" 
               data-page="avisos">
                <span class="sidebar-icon">📢</span>
                <span class="sidebar-label">Avisos</span>
            </a>
            <?php endif; ?>
            
            <?php if (tienePermiso('gestionar_incidencias', true)): ?>
            <a href="/proyectoEdificio/admin/incidencias.php" 
               onclick="event.preventDefault(); loadContent(this.href);" 
               class="sidebar-item" 
               data-page="incidencias">
                <span class="sidebar-icon">🔧</span>
                <span class="sidebar-label">Incidencias</span>
            </a>
            <?php endif; ?>
            
            <?php if (tienePermiso('ver_solicitudes', true)): ?>
            <a href="/proyectoEdificio/admin/solicitudes.php" 
               onclick="event.preventDefault(); loadContent(this.href);" 
               class="sidebar-item" 
               data-page="solicitudes">
                <span class="sidebar-icon">📝</span>
                <span class="sidebar-label">Solicitudes</span>
            </a>
            <?php endif; ?>
        </div>
        
        <!-- Sección: FINANZAS -->
        <div class="sidebar-section">
            <h3 class="sidebar-section-title">FINANZAS</h3>
            
            <?php if (tienePermiso('gestionar_gastos', true)): ?>
            <a href="/proyectoEdificio/admin/registrar_gastos.php" 
               onclick="event.preventDefault(); loadContent(this.href);" 
               class="sidebar-item" 
               data-page="registrar_gastos">
                <span class="sidebar-icon">💰</span>
                <span class="sidebar-label">Registrar Gastos</span>
            </a>
            <?php endif; ?>
            
            <?php if (tienePermiso('validar_pagos', true)): ?>
            <a href="/proyectoEdificio/admin/validar_pagos.php" 
               onclick="event.preventDefault(); loadContent(this.href);" 
               class="sidebar-item" 
               data-page="validar_pagos">
                <span class="sidebar-icon">✅</span>
                <span class="sidebar-label">Validar Pagos</span>
            </a>
            <?php endif; ?>
            
            <?php if (tienePermiso('procesar_cierre_mensual', true)): ?>
            <a href="/proyectoEdificio/admin/procesar_cierre.php" 
               onclick="event.preventDefault(); loadContent(this.href);" 
               class="sidebar-item" 
               data-page="procesar_cierre">
                <span class="sidebar-icon">📅</span>
                <span class="sidebar-label">Procesar Cierre</span>
            </a>
            <?php endif; ?>
            
            <?php if (tienePermiso('ver_reportes', true)): ?>
            <a href="/proyectoEdificio/admin/reportes.php" 
               onclick="event.preventDefault(); loadContent(this.href);" 
               class="sidebar-item" 
               data-page="reportes">
                <span class="sidebar-icon">📊</span>
                <span class="sidebar-label">Reportes</span>
            </a>
            <?php endif; ?>
        </div>
        
        <!-- Sección: SISTEMA -->
        <div class="sidebar-section">
            <h3 class="sidebar-section-title">SISTEMA</h3>
            
            <a href="/proyectoEdificio/admin/panel.php" 
               onclick="event.preventDefault(); loadContent(this.href);" 
               class="sidebar-item" 
               data-page="panel">
                <span class="sidebar-icon">🏢</span>
                <span class="sidebar-label">Panel Principal</span>
            </a>
            
            <a href="/proyectoEdificio/index.php" 
               class="sidebar-item" 
               data-page="index">
                <span class="sidebar-icon">🏠</span>
                <span class="sidebar-label">Ir a Inicio</span>
            </a>
            
            <a href="/proyectoEdificio/cambiar_password.php" 
               onclick="event.preventDefault(); loadContent(this.href);" 
               class="sidebar-item" 
               data-page="cambiar_password">
                <span class="sidebar-icon">🔒</span>
                <span class="sidebar-label">Cambiar Contraseña</span>
            </a>
            
            <a href="/proyectoEdificio/cerrar_sesion.php" 
               onclick="cerrarSesion(); return false;" 
               class="sidebar-item">
                <span class="sidebar-icon">🚪</span>
                <span class="sidebar-label">Cerrar Sesión</span>
            </a>
        </div>
    </nav>
</aside>

<!-- Overlay para cerrar sidebar en móvil -->
<div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>
