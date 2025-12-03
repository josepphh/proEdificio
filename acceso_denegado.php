<?php
require_once 'includes/session.php';

$error_permiso = $_SESSION['error_permiso'] ?? 'No tienes permiso para acceder a esta sección.';
$rol_actual = $_SESSION['rol_actual'] ?? 'Sin rol asignado';

// Limpiar mensajes de sesión
unset($_SESSION['error_permiso']);
unset($_SESSION['rol_actual']);

$title = "Acceso Denegado - Sistema de Edificios";
include 'includes/header.php';
include 'includes/nav.php';
?>

<section class="section-white">
    <div class="container" style="max-width: 800px; margin: 3rem auto;">
        
        <!-- Mensaje de Error -->
        <div style="background: linear-gradient(135deg, #ff6b6b 0%, #ee5a6f 100%); 
                    color: white; 
                    padding: 3rem; 
                    border-radius: 16px; 
                    box-shadow: 0 8px 32px rgba(255, 107, 107, 0.3);
                    text-align: center;">
            
            <!-- Icono -->
            <div style="font-size: 80px; margin-bottom: 1rem;">
                🚫
            </div>
            
            <!-- Título -->
            <h1 style="color: white; margin: 0 0 1rem 0; font-size: 2.5rem;">
                Acceso Denegado
            </h1>
            
            <!-- Mensaje -->
            <p style="font-size: 1.1rem; margin: 0 0 2rem 0; line-height: 1.6;">
                <?php echo htmlspecialchars($error_permiso); ?>
            </p>
            
            <!-- Rol actual -->
            <div style="background: rgba(255,255,255,0.2); 
                        padding: 1rem; 
                        border-radius: 8px; 
                        margin-bottom: 2rem;">
                <p style="margin: 0; font-size: 0.95rem;">
                    <strong>Tu rol actual:</strong> <?php echo htmlspecialchars($rol_actual); ?>
                </p>
            </div>
            
            <!-- Botones de acción -->
            <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
                <button onclick="window.history.back();" class="btn btn-light" style="min-width: 150px;">
                    ⬅️ Volver Atrás
                </button>
                <a href="/proyectoEdificio/index.php" onclick="event.preventDefault(); loadContent('/proyectoEdificio/index.php');" class="btn btn-light" style="min-width: 150px;">
                    🏠 Ir al Inicio
                </a>
                <?php if (isset($_SESSION['usuario_id'])): ?>
                    <?php if ($_SESSION['rol_nombre'] === 'Administrador Total'): ?>
                        <a href="/proyectoEdificio/admin/panel.php" onclick="event.preventDefault(); loadContent('/proyectoEdificio/admin/panel.php');" class="btn btn-success" style="min-width: 150px;">
                            📊 Mi Panel
                        </a>
                    <?php elseif ($_SESSION['rol_nombre'] === 'Administrador Edificio'): ?>
                        <a href="/proyectoEdificio/admin/panel.php" onclick="event.preventDefault(); loadContent('/proyectoEdificio/admin/panel.php');" class="btn btn-success" style="min-width: 150px;">
                            📊 Mi Panel
                        </a>
                    <?php elseif ($_SESSION['rol_nombre'] === 'Inquilino'): ?>
                        <a href="/proyectoEdificio/mi_perfil.php" onclick="event.preventDefault(); loadContent('/proyectoEdificio/mi_perfil.php');" class="btn btn-success" style="min-width: 150px;">
                            👤 Mi Perfil
                        </a>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Información adicional -->
        <div style="margin-top: 2rem; padding: 1.5rem; background: #f8f9fa; border-radius: 8px; border-left: 4px solid #667eea;">
            <h3 style="color: #667eea; margin: 0 0 1rem 0;">💡 ¿Por qué veo este mensaje?</h3>
            <ul style="margin: 0; padding-left: 1.5rem; line-height: 1.8;">
                <li>No tienes los permisos necesarios para acceder a esta sección</li>
                <li>Tu rol actual no tiene habilitada esta funcionalidad</li>
                <li>Esta página está restringida a ciertos tipos de usuarios</li>
            </ul>
            
            <h3 style="color: #667eea; margin: 2rem 0 1rem 0;">🔧 ¿Qué puedo hacer?</h3>
            <ul style="margin: 0; padding-left: 1.5rem; line-height: 1.8;">
                <li>Contacta al administrador del sistema si crees que deberías tener acceso</li>
                <li>Verifica que hayas iniciado sesión con la cuenta correcta</li>
                <li>Revisa las funcionalidades disponibles para tu rol en el panel principal</li>
            </ul>
        </div>
        
    </div>
</section>

<?php include 'includes/footer.php'; ?>
