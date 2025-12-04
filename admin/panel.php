<?php
require_once '../includes/session.php';
require_once '../includes/permissions.php';
require_once '../config/database.php';

// Verificar que el usuario esté autenticado y tenga acceso al panel
requiereAutenticacion('../login.php');
requierePermiso('acceso_panel_admin');

$title = "Panel de Administración - Sistema de Edificios";
$pageTitle = "🏢 Panel de Administración";
$useAdminLayout = true;
include '../includes/header.php';
include '../includes/admin_layout_start.php';
?>

<!-- Main Content -->
    <main class="main-content">
        <div class="container">
            <h2 class="section-title" style="margin-bottom: 2rem;">🏢 Bienvenido al Panel de Administración</h2>
            
            <p style="font-size: 1.1rem; color: var(--color-gray-600); margin-bottom: 3rem;">
                Utiliza el menú lateral para navegar entre las diferentes secciones del sistema.
            </p>
            
            <!-- Estadísticas Rápidas -->
            <div class="cards-grid" style="margin-bottom: 3rem;">
                <?php if (tienePermiso('gestionar_usuarios', true)): ?>
                <div class="card-bordered">
                    <div class="card-bordered-icon">👥</div>
                    <h3>Gestión de Usuarios</h3>
                    <p><?php echo tienePermiso('acceso_completo') ? 'Administra todos los usuarios del sistema, incluyendo roles y permisos.' : 'Administra los usuarios asignados a tus edificios.'; ?></p>
                </div>
                <?php endif; ?>
                
                <?php if (tienePermiso('gestionar_edificios', true)): ?>
                <div class="card-bordered">
                    <div class="card-bordered-icon">🏛️</div>
                    <h3>Gestión de Edificios</h3>
                    <p><?php echo tienePermiso('acceso_completo') ? 'Administra el registro de edificios, sus propiedades y características.' : 'Visualiza la información de tus edificios asignados.'; ?></p>
                </div>
                <?php endif; ?>
                
                <?php if (tienePermiso('gestionar_inquilinos', true)): ?>
                <div class="card-bordered">
                    <div class="card-bordered-icon">🏘️</div>
                    <h3>Gestión de Inquilinos</h3>
                    <p>Gestiona la información de los inquilinos, asignación a departamentos y contratos.</p>
                </div>
                <?php endif; ?>
                
                <?php if (tienePermiso('gestionar_roles', true)): ?>
                <div class="card-bordered">
                    <div class="card-bordered-icon">🔑</div>
                    <h3>Gestión de Roles</h3>
                    <p>Administra los roles del sistema y sus niveles de acceso.</p>
                </div>
                <?php endif; ?>
                
                <?php if (tienePermiso('asignar_permisos', true)): ?>
                <div class="card-bordered">
                    <div class="card-bordered-icon">🔐</div>
                    <h3>Permisos por Rol</h3>
                    <p>Configura detalladamente qué permisos tiene cada rol en el sistema.</p>
                </div>
                <?php endif; ?>
                
                <?php if (tienePermiso('gestionar_avisos', true)): ?>
                <div class="card-bordered">
                    <div class="card-bordered-icon">📢</div>
                    <h3>Gestión de Avisos</h3>
                    <p>Crea y administra avisos importantes para comunicar a los usuarios.</p>
                </div>
                <?php endif; ?>
                
                <?php if (tienePermiso('gestionar_gastos', true)): ?>
                <div class="card-bordered">
                    <div class="card-bordered-icon">💰</div>
                    <h3>Registrar Gastos</h3>
                    <p>Registra los gastos mensuales del edificio y procesa el cierre de mes.</p>
                </div>
                <?php endif; ?>
                
                <?php if (tienePermiso('procesar_cierre_mensual', true)): ?>
                <div class="card-bordered">
                    <div class="card-bordered-icon">📅</div>
                    <h3>Procesar Cierre</h3>
                    <p>Visualiza y gestiona los ciclos de facturación y cierres mensuales.</p>
                </div>
                <?php endif; ?>
                
                <?php if (tienePermiso('ver_reportes', true)): ?>
                <div class="card-bordered">
                    <div class="card-bordered-icon">📊</div>
                    <h3>Reportes</h3>
                    <p>Accede a estadísticas detalladas y reportes financieros del sistema.</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </main>
</div>



<?php 
include '../includes/admin_layout_end.php';

