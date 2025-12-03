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
                    <p><?php echo tienePermiso('acceso_completo') ? 'Administra todos los usuarios del sistema' : 'Administra usuarios de tus edificios'; ?></p>
                    <a href="/proyectoEdificio/admin/usuarios.php" onclick="event.preventDefault(); loadContent('/proyectoEdificio/admin/usuarios.php');" class="btn btn-primary btn-block mt-2">Ver Usuarios</a>
                </div>
                <?php endif; ?>
                
                <?php if (tienePermiso('gestionar_edificios', true)): ?>
                <div class="card-bordered">
                    <div class="card-bordered-icon">🏛️</div>
                    <h3>Gestión de Edificios</h3>
                    <p><?php echo tienePermiso('acceso_completo') ? 'Administra edificios y sus propiedades' : 'Visualiza tus edificios asignados'; ?></p>
                    <a href="/proyectoEdificio/admin/edificios.php" onclick="event.preventDefault(); loadContent('/proyectoEdificio/admin/edificios.php');" class="btn btn-primary btn-block mt-2">Ver Edificios</a>
                </div>
                <?php endif; ?>
                
                <?php if (tienePermiso('gestionar_inquilinos', true)): ?>
                <div class="card-bordered">
                    <div class="card-bordered-icon">🏘️</div>
                    <h3>Gestión de Inquilinos</h3>
                    <p>Gestiona inquilinos por edificio</p>
                    <a href="/proyectoEdificio/admin/inquilinos.php" onclick="event.preventDefault(); loadContent(this.href);" class="btn btn-primary btn-block mt-2">Ver Inquilinos</a>
                </div>
                <?php endif; ?>
                
                <?php if (tienePermiso('gestionar_roles', true)): ?>
                <div class="card-bordered">
                    <div class="card-bordered-icon">🔑</div>
                    <h3>Gestión de Roles</h3>
                    <p>Administra roles del sistema</p>
                    <a href="/proyectoEdificio/admin/roles.php" onclick="event.preventDefault(); loadContent('/proyectoEdificio/admin/roles.php');" class="btn btn-primary btn-block mt-2">Ver Roles</a>
                </div>
                <?php endif; ?>
                
                <?php if (tienePermiso('asignar_permisos', true)): ?>
                <div class="card-bordered">
                    <div class="card-bordered-icon">🔐</div>
                    <h3>Permisos por Rol</h3>
                    <p>Gestiona qué permisos tiene cada rol</p>
                    <a href="/proyectoEdificio/admin/permisos.php" onclick="event.preventDefault(); loadContent('/proyectoEdificio/admin/permisos.php');" class="btn btn-primary btn-block mt-2">Gestionar Permisos</a>
                </div>
                <?php endif; ?>
                
                <?php if (tienePermiso('gestionar_avisos', true)): ?>
                <div class="card-bordered">
                    <div class="card-bordered-icon">📢</div>
                    <h3>Gestión de Avisos</h3>
                    <p>Crea y administra avisos para los usuarios</p>
                    <a href="/proyectoEdificio/admin/avisos.php" onclick="event.preventDefault(); loadContent('/proyectoEdificio/admin/avisos.php');" class="btn btn-primary btn-block mt-2">Ver Avisos</a>
                </div>
                <?php endif; ?>
                
                <?php if (tienePermiso('gestionar_gastos', true)): ?>
                <div class="card-bordered">
                    <div class="card-bordered-icon">💰</div>
                    <h3>Registrar Gastos</h3>
                    <p>Registra gastos del mes y procesa cierre</p>
                    <a href="/proyectoEdificio/admin/registrar_gastos.php" onclick="event.preventDefault(); loadContent(this.href);" class="btn btn-primary btn-block mt-2">Registrar Gastos</a>
                </div>
                <?php endif; ?>
                
                <?php if (tienePermiso('procesar_cierre_mensual', true)): ?>
                <div class="card-bordered">
                    <div class="card-bordered-icon">📅</div>
                    <h3>Procesar Cierre</h3>
                    <p>Visualiza ciclos de facturación</p>
                    <a href="/proyectoEdificio/admin/procesar_cierre.php" onclick="event.preventDefault(); loadContent(this.href);" class="btn btn-primary btn-block mt-2">Ver Ciclos</a>
                </div>
                <?php endif; ?>
                
                <?php if (tienePermiso('ver_reportes', true)): ?>
                <div class="card-bordered">
                    <div class="card-bordered-icon">📊</div>
                    <h3>Reportes</h3>
                    <p>Visualiza estadísticas y reportes del sistema</p>
                    <a href="/proyectoEdificio/admin/reportes.php" onclick="event.preventDefault(); loadContent(this.href);" class="btn btn-primary btn-block mt-2">Ver Reportes</a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </main>
</div>

<style>
    .card-bordered {
        display: flex;
        flex-direction: column;
        height: 100%;
    }
    
    .card-bordered h3 {
        flex-shrink: 0;
    }
    
    .card-bordered p {
        flex-grow: 1;
        margin-bottom: 1rem;
    }
    
    .card-bordered .btn {
        margin-top: auto;
    }
</style>

<?php 
include '../includes/admin_layout_end.php';
include '../includes/footer.php'; 
?>
