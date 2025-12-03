<?php
require_once 'includes/session.php';
$title = "Nuestro Negocio - Gestión Profesional de Edificios";
include 'includes/header.php';
include 'includes/nav.php';
?>

<!-- Hero Section -->
<section class="hero-section">
    <div class="hero-content">
        <h1>💼 Gestión Profesional de Edificios</h1>
        <p>
            Más de 10 años transformando la administración de edificios en Lima y todo el Perú con tecnología de vanguardia
        </p>
    </div>
</section>

<!-- Nuestra Propuesta de Valor -->
<section class="section-white">
    <div class="container">
        <h2 class="section-title">🎯 Nuestra Propuesta de Valor</h2>
        <p class="text-center" style="font-size: 1.2rem; font-weight: 500; max-width: 800px; margin: 0 auto 3rem; line-height: 1.8;">
            Revolucionamos la forma en que se administran los edificios, combinando tecnología avanzada con servicio personalizado
        </p>
        
        <div class="cards-grid">
            <div class="value-card">
                <div class="value-card-icon">🚀</div>
                <h3>Digitalización Total</h3>
                <p>Eliminamos el papeleo. Todo digitalizado: pagos, incidencias, avisos y reportes en tiempo real desde cualquier dispositivo.</p>
            </div>
            
            <div class="value-card">
                <div class="value-card-icon">💰</div>
                <h3>Transparencia Financiera</h3>
                <p>Cada gasto documentado, cada pago rastreado. Reportes financieros automáticos con total trazabilidad.</p>
            </div>
            
            <div class="value-card">
                <div class="value-card-icon">⚡</div>
                <h3>Respuesta Inmediata</h3>
                <p>Sistema de incidencias con seguimiento en tiempo real. Notificaciones automáticas y gestión eficiente de mantenimientos.</p>
            </div>
        </div>
    </div>
</section>

<!-- Nuestros Servicios -->
<section class="section-gray">
    <div class="container">
        <h2 class="section-title">🏢 Servicios Integrales</h2>
        <p class="text-center text-muted" style="font-size: 1.1rem; max-width: 800px; margin: 0 auto 3rem;">
            Soluciones completas para la administración moderna de edificios
        </p>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 2rem;">
            <!-- Servicio 1 -->
            <div style="background: white; padding: 2rem; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); border-left: 5px solid #667eea;">
                <h3 style="color: #667eea; margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem;">
                    <span style="font-size: 2rem;">💳</span> Gestión de Cobranza
                </h3>
                <ul style="color: #666; line-height: 2; padding-left: 1.5rem;">
                    <li>Facturación automática mensual</li>
                    <li>Múltiples métodos de pago (Yape, Plin, Transferencia)</li>
                    <li>Recordatorios automáticos pre-vencimiento</li>
                    <li>Recibos digitales en PDF</li>
                    <li>Seguimiento de morosidad</li>
                    <li>Reportes de cobranza detallados</li>
                </ul>
            </div>
            
            <!-- Servicio 2 -->
            <div style="background: white; padding: 2rem; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); border-left: 5px solid #f5576c;">
                <h3 style="color: #f5576c; margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem;">
                    <span style="font-size: 2rem;">🔧</span> Mantenimiento Preventivo
                </h3>
                <ul style="color: #666; line-height: 2; padding-left: 1.5rem;">
                    <li>Calendario de mantenimientos programados</li>
                    <li>Sistema de tickets de incidencias 24/7</li>
                    <li>Seguimiento en tiempo real de reparaciones</li>
                    <li>Base de datos de proveedores verificados</li>
                    <li>Histórico completo de mantenimientos</li>
                    <li>Notificaciones automáticas a inquilinos</li>
                </ul>
            </div>
            
            <!-- Servicio 3 -->
            <div style="background: white; padding: 2rem; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); border-left: 5px solid #00f2fe;">
                <h3 style="color: #00a8cc; margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem;">
                    <span style="font-size: 2rem;">📊</span> Reportería Inteligente
                </h3>
                <ul style="color: #666; line-height: 2; padding-left: 1.5rem;">
                    <li>Dashboard con métricas en tiempo real</li>
                    <li>Gráficos de evolución de gastos</li>
                    <li>Reportes financieros mensuales</li>
                    <li>Análisis de tendencias y proyecciones</li>
                    <li>Exportación a Excel y PDF</li>
                    <li>Histórico completo de transacciones</li>
                </ul>
            </div>
            
            <!-- Servicio 4 -->
            <div style="background: white; padding: 2rem; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); border-left: 5px solid #ffc107;">
                <h3 style="color: #f57c00; margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem;">
                    <span style="font-size: 2rem;">📢</span> Comunicación Efectiva
                </h3>
                <ul style="color: #666; line-height: 2; padding-left: 1.5rem;">
                    <li>Sistema de avisos general y por edificio</li>
                    <li>Notificaciones por email automáticas</li>
                    <li>Alertas de emergencia inmediatas</li>
                    <li>Canal directo inquilino-administrador</li>
                    <li>Registro de todas las comunicaciones</li>
                    <li>Portal de noticias y actualizaciones</li>
                </ul>
            </div>
            
            <!-- Servicio 5 -->
            <div style="background: white; padding: 2rem; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); border-left: 5px solid #28a745;">
                <h3 style="color: #28a745; margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem;">
                    <span style="font-size: 2rem;">🏗️</span> Gestión Multi-Edificio
                </h3>
                <ul style="color: #666; line-height: 2; padding-left: 1.5rem;">
                    <li>Administra múltiples edificios desde un panel</li>
                    <li>Roles y permisos personalizados</li>
                    <li>Administradores dedicados por edificio</li>
                    <li>Reportes consolidados y por edificio</li>
                    <li>Gestión independiente de cada propiedad</li>
                    <li>Escalabilidad ilimitada</li>
                </ul>
            </div>
            
            <!-- Servicio 6 -->
            <div style="background: white; padding: 2rem; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); border-left: 5px solid #dc3545;">
                <h3 style="color: #dc3545; margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem;">
                    <span style="font-size: 2rem;">🔐</span> Seguridad y Respaldo
                </h3>
                <ul style="color: #666; line-height: 2; padding-left: 1.5rem;">
                    <li>Encriptación de datos sensibles</li>
                    <li>Backups automáticos diarios</li>
                    <li>Control de accesos por roles</li>
                    <li>Auditoría completa de operaciones</li>
                    <li>Cumplimiento de normativas de protección de datos</li>
                    <li>Servidores con alta disponibilidad</li>
                </ul>
            </div>
        </div>
    </div>
</section>

<!-- Proceso de Trabajo -->
<section style="padding: 4rem 2rem; background: white;">
    <div style="max-width: 1000px; margin: 0 auto;">
        <h2 style="text-align: center; color: #333; font-size: clamp(1.8rem, 4vw, 2.5rem); margin-bottom: 3rem;">📋 Nuestro Proceso</h2>
        
        <div style="position: relative;">
            <!-- Línea conectora -->
            <div style="position: absolute; left: 30px; top: 40px; bottom: 40px; width: 3px; background: linear-gradient(to bottom, #667eea, #764ba2); display: none;" class="timeline-line"></div>
            
            <div style="display: flex; flex-direction: column; gap: 2rem;">
                <!-- Paso 1 -->
                <div style="display: flex; gap: 2rem; align-items: start;">
                    <div style="min-width: 60px; height: 60px; background: linear-gradient(135deg, #667eea, #764ba2); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 1.5rem; font-weight: bold; box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);">1</div>
                    <div style="flex: 1; background: #f8f9fa; padding: 1.5rem; border-radius: 12px;">
                        <h3 style="color: #667eea; margin-bottom: 0.5rem;">Análisis Inicial</h3>
                        <p style="color: #666; margin: 0;">Evaluamos las necesidades específicas de tu edificio y diseñamos una solución personalizada.</p>
                    </div>
                </div>
                
                <!-- Paso 2 -->
                <div style="display: flex; gap: 2rem; align-items: start;">
                    <div style="min-width: 60px; height: 60px; background: linear-gradient(135deg, #f093fb, #f5576c); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 1.5rem; font-weight: bold; box-shadow: 0 4px 15px rgba(245, 87, 108, 0.3);">2</div>
                    <div style="flex: 1; background: #f8f9fa; padding: 1.5rem; border-radius: 12px;">
                        <h3 style="color: #f5576c; margin-bottom: 0.5rem;">Configuración Personalizada</h3>
                        <p style="color: #666; margin: 0;">Creamos tu cuenta, configuramos edificios, usuarios y personalizamos el sistema según tus requerimientos.</p>
                    </div>
                </div>
                
                <!-- Paso 3 -->
                <div style="display: flex; gap: 2rem; align-items: start;">
                    <div style="min-width: 60px; height: 60px; background: linear-gradient(135deg, #4facfe, #00f2fe); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 1.5rem; font-weight: bold; box-shadow: 0 4px 15px rgba(79, 172, 254, 0.3);">3</div>
                    <div style="flex: 1; background: #f8f9fa; padding: 1.5rem; border-radius: 12px;">
                        <h3 style="color: #00a8cc; margin-bottom: 0.5rem;">Capacitación</h3>
                        <p style="color: #666; margin: 0;">Entrenamos a administradores e inquilinos en el uso del sistema con sesiones prácticas y material de apoyo.</p>
                    </div>
                </div>
                
                <!-- Paso 4 -->
                <div style="display: flex; gap: 2rem; align-items: start;">
                    <div style="min-width: 60px; height: 60px; background: linear-gradient(135deg, #43e97b, #38f9d7); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 1.5rem; font-weight: bold; box-shadow: 0 4px 15px rgba(67, 233, 123, 0.3);">4</div>
                    <div style="flex: 1; background: #f8f9fa; padding: 1.5rem; border-radius: 12px;">
                        <h3 style="color: #28a745; margin-bottom: 0.5rem;">Puesta en Marcha</h3>
                        <p style="color: #666; margin: 0;">Lanzamiento oficial con soporte dedicado durante la transición y migración de datos históricos.</p>
                    </div>
                </div>
                
                <!-- Paso 5 -->
                <div style="display: flex; gap: 2rem; align-items: start;">
                    <div style="min-width: 60px; height: 60px; background: linear-gradient(135deg, #fa709a, #fee140); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 1.5rem; font-weight: bold; box-shadow: 0 4px 15px rgba(250, 112, 154, 0.3);">5</div>
                    <div style="flex: 1; background: #f8f9fa; padding: 1.5rem; border-radius: 12px;">
                        <h3 style="color: #f57c00; margin-bottom: 0.5rem;">Soporte Continuo</h3>
                        <p style="color: #666; margin: 0;">Asistencia técnica 24/7, actualizaciones constantes y mejoras basadas en tus necesidades.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Beneficios Tangibles -->
<section style="padding: 4rem 2rem; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
    <div style="max-width: 1200px; margin: 0 auto; text-align: center;">
        <h2 style="font-size: clamp(1.8rem, 4vw, 2.5rem); margin-bottom: 1rem;">💎 Resultados Comprobados</h2>
        <p style="font-size: 1.1rem; opacity: 0.95; margin-bottom: 3rem;">Lo que nuestros clientes logran con nuestra plataforma</p>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 2rem;">
            <div style="padding: 2rem;">
                <div style="font-size: 3rem; font-weight: bold; margin-bottom: 0.5rem;">85%</div>
                <p style="opacity: 0.9; font-size: 1.1rem;">Reducción en tiempo de gestión administrativa</p>
            </div>
            <div style="padding: 2rem;">
                <div style="font-size: 3rem; font-weight: bold; margin-bottom: 0.5rem;">95%</div>
                <p style="opacity: 0.9; font-size: 1.1rem;">Mejora en tasa de cobranza mensual</p>
            </div>
            <div style="padding: 2rem;">
                <div style="font-size: 3rem; font-weight: bold; margin-bottom: 0.5rem;">70%</div>
                <p style="opacity: 0.9; font-size: 1.1rem;">Más rápida resolución de incidencias</p>
            </div>
            <div style="padding: 2rem;">
                <div style="font-size: 3rem; font-weight: bold; margin-bottom: 0.5rem;">100%</div>
                <p style="opacity: 0.9; font-size: 1.1rem;">Transparencia en gestión financiera</p>
            </div>
        </div>
    </div>
</section>

<!-- Call to Action -->
<section style="padding: 4rem 2rem; background: white; text-align: center;">
    <div style="max-width: 800px; margin: 0 auto;">
        <h2 style="color: #333; font-size: clamp(1.8rem, 4vw, 2.5rem); margin-bottom: 1rem;">¿Listo para modernizar tu edificio?</h2>
        <p style="color: #666; font-size: 1.2rem; margin-bottom: 2rem;">Únete a los edificios que ya confían en nuestra plataforma</p>
        
        <?php if(!isset($_SESSION['usuario_nombre'])): ?>
        <button onclick="abrirModalSolicitud()" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 1.2rem 3rem; border: none; border-radius: 50px; font-weight: 600; font-size: 1.2rem; cursor: pointer; box-shadow: 0 4px 20px rgba(102, 126, 234, 0.4); transition: all 0.3s;">
            🏢 Solicitar una Demostración
        </button>
        <?php else: ?>
        <a href="<?php echo esRol('Administrador Total') ? 'admin/panel.php' : (esRol('Inquilino') ? 'inquilino/dashboard.php' : 'mi_perfil.php'); ?>" 
           style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 1.2rem 3rem; border-radius: 50px; text-decoration: none; font-weight: 600; font-size: 1.2rem; box-shadow: 0 4px 20px rgba(102, 126, 234, 0.4); display: inline-block; transition: all 0.3s;">
            📊 Ir a Mi Dashboard
        </a>
        <?php endif; ?>
    </div>
</section>

<style>
    .value-card:hover {
        transform: translateY(-10px);
    }
    
    @media (min-width: 768px) {
        .timeline-line {
            display: block !important;
        }
    }
    
    @media (max-width: 480px) {
        .value-card {
            padding: 2rem 1.5rem !important;
        }
    }
</style>

<?php include 'includes/footer.php'; ?>
