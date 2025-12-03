<?php
require_once 'includes/session.php';
$title = "Inicio - Mi Sitio Web";
include 'includes/header.php';
include 'includes/nav.php';
?>

<!-- Hero Section -->
<section class="hero-section">
    <div class="hero-content">
        <h1 style="font-size: clamp(2rem, 5vw, 3.5rem);">Sistema de Gestión de Edificios</h1>
        <p style="font-size: clamp(1.1rem, 3vw, 1.5rem); margin: 0 0 2rem 0;">Administra tu edificio de manera inteligente, eficiente y transparente</p>
        
        <?php if(!isset($_SESSION['usuario_nombre'])): ?>
        <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
            <a href="login.php" style="background: white; color: #667eea; padding: 1.2rem 3rem; border-radius: 50px; text-decoration: none; font-weight: 600; font-size: 1.2rem; box-shadow: 0 4px 15px rgba(0,0,0,0.2); transition: transform 0.3s; display: inline-block;">
                🔐 Iniciar Sesión
            </a>
            <button onclick="abrirModalSolicitud()" style="background: rgba(255,255,255,0.2); color: white; padding: 1.2rem 3rem; border-radius: 50px; border: 2px solid white; font-weight: 600; font-size: 1.2rem; cursor: pointer; transition: all 0.3s;">
                🏢 Solicitar Acceso
            </button>
        </div>
        <p style="margin-top: 1.5rem; opacity: 0.85; font-size: 0.95rem;">¿Administras un edificio? Solicita acceso a nuestro sistema</p>
        <?php else: ?>
        <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
            <?php
            // Redirigir según el rol
            $dashboardUrl = 'mi_perfil.php';
            $dashboardText = '📊 Mi Panel';
            
            if(isset($_SESSION['rol_nombre'])) {
                if($_SESSION['rol_nombre'] === 'Administrador Total') {
                    $dashboardUrl = 'admin/panel.php';
                    $dashboardText = '🏢 Panel de Administración';
                } elseif($_SESSION['rol_nombre'] === 'Inquilino') {
                    $dashboardUrl = 'inquilino/dashboard.php';
                    $dashboardText = '🏠 Mi Dashboard';
                }
            }
            ?>
            <a href="<?php echo $dashboardUrl; ?>" style="background: white; color: #667eea; padding: 1.2rem 3rem; border-radius: 50px; text-decoration: none; font-weight: 600; font-size: 1.2rem; box-shadow: 0 4px 15px rgba(0,0,0,0.2); transition: transform 0.3s; display: inline-block;">
                <?php echo $dashboardText; ?>
            </a>
        </div>
        <p style="margin-top: 1.5rem; opacity: 0.85; font-size: 0.95rem;">Bienvenido de vuelta, <?php echo htmlspecialchars($_SESSION['usuario_nombre']); ?>! 👋</p>
        <?php endif; ?>
    </div>
</section>

<!-- Características Principales -->
<section class="section-gray">
    <div class="container">
        <h2 class="section-title">¿Qué puedes hacer con nuestro sistema?</h2>
        
        <div class="cards-grid">
            <!-- Feature 1 -->
            <div class="card-bordered">
                <div class="card-bordered-icon">💰</div>
                <h3>Gestión de Pagos</h3>
                <ul style="color: #666; line-height: 1.8; padding-left: 1.2rem;">
                    <li>Registro de pagos de mantenimiento</li>
                    <li>Múltiples métodos: Yape, Plin, Transferencia</li>
                    <li>Historial completo de transacciones</li>
                    <li>Notificaciones automáticas por email</li>
                    <li>Descarga de recibos en PDF</li>
                </ul>
            </div>

            <!-- Feature 2 -->
            <div class="card-bordered">
                <div class="card-bordered-icon">📊</div>
                <h3>Dashboard Inteligente</h3>
                <ul style="color: #666; line-height: 1.8; padding-left: 1.2rem;">
                    <li>Visualización de recibos pendientes</li>
                    <li>Gráficos de evolución de gastos</li>
                    <li>Alertas de vencimientos próximos</li>
                    <li>Estadísticas mensuales y anuales</li>
                    <li>Reportes descargables</li>
                </ul>
            </div>

            <!-- Feature 3 -->
            <div class="card-bordered">
                <div class="card-bordered-icon">🔧</div>
                <h3>Incidencias y Mantenimiento</h3>
                <ul style="color: #666; line-height: 1.8; padding-left: 1.2rem;">
                    <li>Reporta problemas en tiempo real</li>
                    <li>Seguimiento del estado de reparaciones</li>
                    <li>Sistema de comentarios y actualizaciones</li>
                    <li>Clasificación por prioridad</li>
                    <li>Historial completo de incidencias</li>
                </ul>
            </div>

            <!-- Feature 4 -->
            <div class="card-bordered">
                <div class="card-bordered-icon">📢</div>
                <h3>Comunicación Efectiva</h3>
                <ul style="color: #666; line-height: 1.8; padding-left: 1.2rem;">
                    <li>Avisos generales y por edificio</li>
                    <li>Notificaciones urgentes automáticas</li>
                    <li>Recordatorios de vencimiento</li>
                    <li>Comunicación administrador-inquilinos</li>
                    <li>Sistema de emails integrado</li>
                </ul>
            </div>

            <!-- Feature 5 -->
            <div class="card-bordered">
                <div class="card-bordered-icon">🏢</div>
                <h3>Multi-Edificio</h3>
                <ul style="color: #666; line-height: 1.8; padding-left: 1.2rem;">
                    <li>Gestiona múltiples edificios</li>
                    <li>Administradores por edificio</li>
                    <li>Roles y permisos personalizados</li>
                    <li>Reportes independientes por edificio</li>
                    <li>Control de accesos y seguridad</li>
                </ul>
            </div>

            <!-- Feature 6 -->
            <div class="card-bordered">
                <div class="card-bordered-icon">📱</div>
                <h3>100% Responsive</h3>
                <ul style="color: #666; line-height: 1.8; padding-left: 1.2rem;">
                    <li>Acceso desde cualquier dispositivo</li>
                    <li>Diseño optimizado para móviles</li>
                    <li>Interfaz táctil intuitiva</li>
                    <li>Funciona en tablets y smartphones</li>
                    <li>Sin necesidad de apps nativas</li>
                </ul>
            </div>
        </div>
    </div>
</section>

<!-- Beneficios -->
<section class="section-white">
    <div class="container" style="text-align: center;">
        <h2 class="section-title">¿Por qué elegirnos?</h2>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 2rem; margin-top: 3rem;">
            <div>
                <div style="font-size: 3rem; margin-bottom: 0.5rem;">⚡</div>
                <h3 style="font-size: 1.2rem; margin-bottom: 0.5rem; color: var(--color-primary);">Rápido y Eficiente</h3>
                <p class="text-muted">Procesos automatizados que ahorran tiempo</p>
            </div>
            <div>
                <div style="font-size: 3rem; margin-bottom: 0.5rem;">🔒</div>
                <h3 style="font-size: 1.2rem; margin-bottom: 0.5rem; color: var(--color-primary);">Seguro</h3>
                <p class="text-muted">Datos protegidos con encriptación</p>
            </div>
            <div>
                <div style="font-size: 3rem; margin-bottom: 0.5rem;">💡</div>
                <h3 style="font-size: 1.2rem; margin-bottom: 0.5rem; color: var(--color-primary);">Intuitivo</h3>
                <p class="text-muted">Fácil de usar para todos</p>
            </div>
            <div>
                <div style="font-size: 3rem; margin-bottom: 0.5rem;">📈</div>
                <h3 style="font-size: 1.2rem; margin-bottom: 0.5rem; color: var(--color-primary);">Transparente</h3>
                <p class="text-muted">Información clara en tiempo real</p>
            </div>
        </div>
    </div>
</section>

<!-- Llamado a la Acción -->
<?php if(!isset($_SESSION['usuario_nombre'])): ?>
<section class="section-gray" style="text-align: center;">
    <div class="container container-sm">
        <h2 class="section-title">¿Listo para gestionar tu edificio de forma profesional?</h2>
        <p class="text-muted" style="font-size: 1.2rem; margin-bottom: 2rem;">Accede a tu cuenta para comenzar</p>
        <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
            <a href="login.php" class="btn btn-primary btn-lg" style="border-radius: 50px; padding: 1.2rem 3rem; font-size: 1.2rem;">
                🔐 Acceder al Sistema
            </a>
        </div>
        <p style="margin-top: 1.5rem; color: #999; font-size: 0.95rem;">* Los usuarios son creados por el administrador del edificio</p>
    </div>
</section>
<?php else: ?>
<section class="section-gray" style="text-align: center;">
    <div class="container container-sm">
        <h2 class="section-title">Tu edificio bajo control total</h2>
        <p class="text-muted" style="font-size: 1.2rem; margin-bottom: 2rem;">Todas las herramientas que necesitas en un solo lugar</p>
        <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
            <?php
            $dashboardUrl = 'mi_perfil.php';
            $dashboardText = '📊 Ir a Mi Panel';
            
            if(isset($_SESSION['rol_nombre'])) {
                if($_SESSION['rol_nombre'] === 'Administrador Total') {
                    $dashboardUrl = 'admin/panel.php';
                    $dashboardText = '🏢 Ir al Panel de Administración';
                } elseif($_SESSION['rol_nombre'] === 'Inquilino') {
                    $dashboardUrl = 'inquilino/dashboard.php';
                    $dashboardText = '🏠 Ir a Mi Dashboard';
                }
            }
            ?>
            <a href="<?php echo $dashboardUrl; ?>" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 1.2rem 3rem; border-radius: 50px; text-decoration: none; font-weight: 600; font-size: 1.2rem; box-shadow: 0 4px 20px rgba(102, 126, 234, 0.4); transition: all 0.3s; display: inline-block;">
                <?php echo $dashboardText; ?>
            </a>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Footer con info -->


<!-- Modal de Solicitud de Acceso -->
<div id="modalSolicitud" class="modal-solicitud" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.7); z-index: 9999; overflow-y: auto;">
    <div style="min-height: 100%; display: flex; align-items: center; justify-content: center; padding: 2rem;">
        <div style="background: white; border-radius: 16px; max-width: 600px; width: 100%; margin: auto; box-shadow: 0 10px 40px rgba(0,0,0,0.3); position: relative;">
            <button onclick="cerrarModalSolicitud()" style="position: absolute; top: 1rem; right: 1rem; background: none; border: none; font-size: 2rem; cursor: pointer; color: #999; line-height: 1; padding: 0; width: 40px; height: 40px;">&times;</button>
            
            <div style="padding: 2.5rem;">
                <h2 style="color: #667eea; margin: 0 0 0.5rem 0; font-size: 2rem;">🏢 Solicitar Acceso</h2>
                <p style="color: #666; margin: 0 0 2rem 0;">Completa el formulario y nos contactaremos contigo pronto</p>
                
                <form id="formSolicitud" onsubmit="enviarSolicitud(event)">
                    <div style="margin-bottom: 1.5rem;">
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #333;">Nombre Completo *</label>
                        <input type="text" name="nombre_completo" required 
                               style="width: 100%; padding: 0.75rem; border: 2px solid #e0e0e0; border-radius: 8px; font-size: 1rem; transition: border-color 0.3s;"
                               placeholder="Ej: Juan Pérez García">
                    </div>
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.5rem;">
                        <div>
                            <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #333;">Email *</label>
                            <input type="email" name="email" required 
                                   style="width: 100%; padding: 0.75rem; border: 2px solid #e0e0e0; border-radius: 8px; font-size: 1rem; transition: border-color 0.3s;"
                                   placeholder="correo@ejemplo.com">
                        </div>
                        <div>
                            <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #333;">Teléfono *</label>
                            <input type="tel" name="telefono" required 
                                   style="width: 100%; padding: 0.75rem; border: 2px solid #e0e0e0; border-radius: 8px; font-size: 1rem; transition: border-color 0.3s;"
                                   placeholder="+51 999 999 999">
                        </div>
                    </div>
                    
                    <div style="margin-bottom: 1.5rem;">
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #333;">Nombre del Edificio *</label>
                        <input type="text" name="nombre_edificio" required 
                               style="width: 100%; padding: 0.75rem; border: 2px solid #e0e0e0; border-radius: 8px; font-size: 1rem; transition: border-color 0.3s;"
                               placeholder="Ej: Torre Azul">
                    </div>
                    
                    <div style="margin-bottom: 1.5rem;">
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #333;">Dirección del Edificio</label>
                        <input type="text" name="direccion_edificio" 
                               style="width: 100%; padding: 0.75rem; border: 2px solid #e0e0e0; border-radius: 8px; font-size: 1rem; transition: border-color 0.3s;"
                               placeholder="Ej: Av. Principal 123, San Isidro">
                    </div>
                    
                    <div style="margin-bottom: 1.5rem;">
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #333;">Número de Departamentos (aprox.)</label>
                        <input type="number" name="num_departamentos" min="1" 
                               style="width: 100%; padding: 0.75rem; border: 2px solid #e0e0e0; border-radius: 8px; font-size: 1rem; transition: border-color 0.3s;"
                               placeholder="Ej: 24">
                    </div>
                    
                    <div style="margin-bottom: 1.5rem;">
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #333;">Mensaje Adicional</label>
                        <textarea name="mensaje" rows="4" 
                                  style="width: 100%; padding: 0.75rem; border: 2px solid #e0e0e0; border-radius: 8px; font-size: 1rem; transition: border-color 0.3s; resize: vertical;"
                                  placeholder="Cuéntanos más sobre tu edificio y tus necesidades..."></textarea>
                    </div>
                    
                    <div id="mensajeSolicitud" style="margin-bottom: 1rem; padding: 1rem; border-radius: 8px; display: none;"></div>
                    
                    <div style="display: flex; gap: 1rem;">
                        <button type="submit" id="btnEnviar" 
                                style="flex: 1; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 1rem 2rem; border: none; border-radius: 8px; font-weight: 600; font-size: 1.1rem; cursor: pointer; transition: transform 0.3s;">
                            📤 Enviar Solicitud
                        </button>
                        <button type="button" onclick="cerrarModalSolicitud()" 
                                style="background: #f0f0f0; color: #666; padding: 1rem 2rem; border: none; border-radius: 8px; font-weight: 600; font-size: 1.1rem; cursor: pointer; transition: background 0.3s;">
                            Cancelar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
    .feature-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 8px 30px rgba(0,0,0,0.15) !important;
    }
    
    .hero-section a:hover,
    .hero-section button:hover {
        transform: translateY(-3px);
    }
    
    #formSolicitud input:focus,
    #formSolicitud textarea:focus {
        outline: none;
        border-color: #667eea;
    }
    
    #btnEnviar:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
    }
    
    @media (max-width: 480px) {
        .modal-solicitud > div {
            padding: 1rem;
        }
        
        .modal-solicitud > div > div {
            padding: 1.5rem !important;
        }
        
        #formSolicitud > div[style*="grid-template-columns"] {
            grid-template-columns: 1fr !important;
        }
    }
</style>

<script>
function abrirModalSolicitud() {
    document.getElementById('modalSolicitud').style.display = 'block';
    document.body.style.overflow = 'hidden';
}

function cerrarModalSolicitud() {
    document.getElementById('modalSolicitud').style.display = 'none';
    document.body.style.overflow = '';
    document.getElementById('formSolicitud').reset();
    document.getElementById('mensajeSolicitud').style.display = 'none';
}

async function enviarSolicitud(event) {
    event.preventDefault();
    
    const btnEnviar = document.getElementById('btnEnviar');
    const mensajeDiv = document.getElementById('mensajeSolicitud');
    
    btnEnviar.disabled = true;
    btnEnviar.textContent = '⏳ Enviando...';
    
    const formData = new FormData(document.getElementById('formSolicitud'));
    
    try {
        const response = await fetch('/proyectoEdificio/procesar_solicitud.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        mensajeDiv.style.display = 'block';
        
        if (data.success) {
            mensajeDiv.style.background = '#d4edda';
            mensajeDiv.style.color = '#155724';
            mensajeDiv.style.border = '2px solid #c3e6cb';
            mensajeDiv.innerHTML = '✅ ' + data.message;
            
            setTimeout(() => {
                cerrarModalSolicitud();
            }, 3000);
        } else {
            mensajeDiv.style.background = '#f8d7da';
            mensajeDiv.style.color = '#721c24';
            mensajeDiv.style.border = '2px solid #f5c6cb';
            mensajeDiv.innerHTML = '❌ ' + data.message;
            
            btnEnviar.disabled = false;
            btnEnviar.textContent = '📤 Enviar Solicitud';
        }
    } catch (error) {
        mensajeDiv.style.display = 'block';
        mensajeDiv.style.background = '#f8d7da';
        mensajeDiv.style.color = '#721c24';
        mensajeDiv.style.border = '2px solid #f5c6cb';
        mensajeDiv.innerHTML = '❌ Error al enviar la solicitud. Por favor intenta nuevamente.';
        
        btnEnviar.disabled = false;
        btnEnviar.textContent = '📤 Enviar Solicitud';
    }
}

// El modal ya NO se cierra al hacer clic fuera
// Solo se puede cerrar con el botón X o Cancelar

// Verificar si debe abrir el modal automáticamente (desde login)
window.addEventListener('DOMContentLoaded', function() {
    if (sessionStorage.getItem('abrirModalSolicitud') === 'true') {
        sessionStorage.removeItem('abrirModalSolicitud');
        abrirModalSolicitud();
    }
});
</script>

<?php include 'includes/footer.php'; ?>
