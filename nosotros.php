<?php
require_once 'includes/session.php';
$title = "Sobre Nosotros - Gestión Profesional de Edificios";
include 'includes/header.php';
include 'includes/nav.php';
?>

<!-- Hero Section -->
<section class="hero-section">
    <div class="hero-content">
        <h1>👥 Sobre Nosotros</h1>
        <p>
            Somos el aliado estratégico que transforma la administración de edificios en Perú
        </p>
    </div>
</section>

<!-- Nuestra Historia -->
<section class="section-white">
    <div class="container">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 3rem; align-items: center;">
            <div>
                <h2 class="section-title" style="text-align: left;">📖 Nuestra Historia</h2>
                <p class="text-muted" style="font-size: 1.1rem; line-height: 1.8; margin-bottom: 1rem;">
                    Fundada en <strong>2015</strong> en Lima, Perú, nacimos de una visión clara: modernizar la gestión de edificios residenciales y comerciales mediante tecnología de vanguardia.
                </p>
                <p style="color: #666; font-size: 1.1rem; line-height: 1.8; margin-bottom: 1rem;">
                    Lo que comenzó como un pequeño proyecto para ayudar a 5 edificios, hoy se ha convertido en la plataforma líder que gestiona más de <strong>150 edificios</strong> en todo el país, con presencia en Lima, Arequipa, Cusco y Trujillo.
                </p>
                <p style="color: #666; font-size: 1.1rem; line-height: 1.8;">
                    Nuestra pasión por la innovación y el servicio al cliente nos ha llevado a ser reconocidos como líderes en transformación digital inmobiliaria.
                </p>
            </div>
            <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 3rem; border-radius: 20px; color: white; text-align: center; box-shadow: 0 10px 40px rgba(102, 126, 234, 0.3);">
                <div style="font-size: 4rem; margin-bottom: 1rem;">🏆</div>
                <h3 style="font-size: 2rem; margin-bottom: 1rem;">10 Años</h3>
                <p style="font-size: 1.2rem; opacity: 0.95;">De experiencia transformando la administración de edificios en Perú</p>
            </div>
        </div>
    </div>
</section>

<!-- Misión, Visión y Valores -->
<section class="section-gray">
    <div class="container">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 2rem;">
            <!-- Misión -->
            <div style="background: white; padding: 2.5rem; border-radius: 16px; box-shadow: 0 8px 30px rgba(0,0,0,0.08); border-top: 5px solid #667eea;">
                <div style="font-size: 3rem; margin-bottom: 1rem;">🎯</div>
                <h3 style="color: #667eea; font-size: 1.8rem; margin-bottom: 1rem;">Nuestra Misión</h3>
                <p style="color: #666; font-size: 1.05rem; line-height: 1.8;">
                    Simplificar y digitalizar la gestión de edificios mediante tecnología innovadora, brindando transparencia, eficiencia y tranquilidad a administradores e inquilinos por igual.
                </p>
            </div>
            
            <!-- Visión -->
            <div style="background: white; padding: 2.5rem; border-radius: 16px; box-shadow: 0 8px 30px rgba(0,0,0,0.08); border-top: 5px solid #f5576c;">
                <div style="font-size: 3rem; margin-bottom: 1rem;">🔮</div>
                <h3 style="color: #f5576c; font-size: 1.8rem; margin-bottom: 1rem;">Nuestra Visión</h3>
                <p style="color: #666; font-size: 1.05rem; line-height: 1.8;">
                    Ser la plataforma de gestión inmobiliaria #1 en Latinoamérica, reconocida por nuestra innovación continua y el impacto positivo en la calidad de vida de las comunidades que servimos.
                </p>
            </div>
            
            <!-- Valores -->
            <div style="background: white; padding: 2.5rem; border-radius: 16px; box-shadow: 0 8px 30px rgba(0,0,0,0.08); border-top: 5px solid #00f2fe;">
                <div style="font-size: 3rem; margin-bottom: 1rem;">💎</div>
                <h3 style="color: #00a8cc; font-size: 1.8rem; margin-bottom: 1rem;">Nuestros Valores</h3>
                <ul style="color: #666; font-size: 1.05rem; line-height: 2; padding-left: 1.5rem;">
                    <li><strong>Innovación</strong> constante</li>
                    <li><strong>Transparencia</strong> total</li>
                    <li><strong>Excelencia</strong> en servicio</li>
                    <li><strong>Compromiso</strong> con clientes</li>
                    <li><strong>Integridad</strong> profesional</li>
                </ul>
            </div>
        </div>
    </div>
</section>

<!-- Estadísticas -->
<section style="padding: 4rem 2rem; background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white;">
    <div style="max-width: 1200px; margin: 0 auto; text-align: center;">
        <h2 style="font-size: clamp(1.8rem, 4vw, 2.5rem); margin-bottom: 3rem;">📊 Nuestra Trayectoria en Números</h2>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 2rem;">
            <div style="padding: 2rem;">
                <div style="font-size: 3.5rem; font-weight: bold; margin-bottom: 0.5rem;">150+</div>
                <p style="opacity: 0.95; font-size: 1.1rem;">Edificios Gestionados</p>
            </div>
            <div style="padding: 2rem;">
                <div style="font-size: 3.5rem; font-weight: bold; margin-bottom: 0.5rem;">8,500+</div>
                <p style="opacity: 0.95; font-size: 1.1rem;">Departamentos Administrados</p>
            </div>
            <div style="padding: 2rem;">
                <div style="font-size: 3.5rem; font-weight: bold; margin-bottom: 0.5rem;">12,000+</div>
                <p style="opacity: 0.95; font-size: 1.1rem;">Usuarios Activos</p>
            </div>
            <div style="padding: 2rem;">
                <div style="font-size: 3.5rem; font-weight: bold; margin-bottom: 0.5rem;">98%</div>
                <p style="opacity: 0.95; font-size: 1.1rem;">Satisfacción de Clientes</p>
            </div>
        </div>
    </div>
</section>

<!-- Nuestro Equipo -->
<section style="padding: 4rem 2rem; background: white;">
    <div style="max-width: 1200px; margin: 0 auto;">
        <h2 style="text-align: center; color: #333; font-size: clamp(1.8rem, 4vw, 2.5rem); margin-bottom: 1rem;">👨‍💼 Nuestro Equipo</h2>
        <p style="text-align: center; color: #666; font-size: 1.1rem; max-width: 700px; margin: 0 auto 3rem;">
            Un equipo multidisciplinario de profesionales apasionados por la tecnología y el servicio
        </p>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 2rem;">
            <!-- Miembro 1 -->
            <div style="text-align: center; padding: 2rem; background: #f8f9fa; border-radius: 16px; transition: transform 0.3s;" class="team-member">
                <div style="width: 120px; height: 120px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 50%; margin: 0 auto 1.5rem; display: flex; align-items: center; justify-content: center; font-size: 3rem; color: white; box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);">
                    👨‍💼
                </div>
                <h3 style="color: #333; margin-bottom: 0.5rem; font-size: 1.3rem;">Carlos Mendoza</h3>
                <p style="color: #667eea; font-weight: 600; margin-bottom: 1rem;">CEO & Fundador</p>
                <p style="color: #666; font-size: 0.95rem; line-height: 1.6;">15 años de experiencia en gestión inmobiliaria y transformación digital.</p>
            </div>
            
            <!-- Miembro 2 -->
            <div style="text-align: center; padding: 2rem; background: #f8f9fa; border-radius: 16px; transition: transform 0.3s;" class="team-member">
                <div style="width: 120px; height: 120px; background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); border-radius: 50%; margin: 0 auto 1.5rem; display: flex; align-items: center; justify-content: center; font-size: 3rem; color: white; box-shadow: 0 8px 25px rgba(245, 87, 108, 0.3);">
                    👩‍💻
                </div>
                <h3 style="color: #333; margin-bottom: 0.5rem; font-size: 1.3rem;">Ana García</h3>
                <p style="color: #f5576c; font-weight: 600; margin-bottom: 1rem;">CTO & Co-fundadora</p>
                <p style="color: #666; font-size: 0.95rem; line-height: 1.6;">Ingeniera de software con especialización en arquitectura cloud y sistemas escalables.</p>
            </div>
            
            <!-- Miembro 3 -->
            <div style="text-align: center; padding: 2rem; background: #f8f9fa; border-radius: 16px; transition: transform 0.3s;" class="team-member">
                <div style="width: 120px; height: 120px; background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); border-radius: 50%; margin: 0 auto 1.5rem; display: flex; align-items: center; justify-content: center; font-size: 3rem; color: white; box-shadow: 0 8px 25px rgba(79, 172, 254, 0.3);">
                    👨‍🔧
                </div>
                <h3 style="color: #333; margin-bottom: 0.5rem; font-size: 1.3rem;">Roberto Silva</h3>
                <p style="color: #00a8cc; font-weight: 600; margin-bottom: 1rem;">Director de Operaciones</p>
                <p style="color: #666; font-size: 0.95rem; line-height: 1.6;">Experto en optimización de procesos y gestión de mantenimiento preventivo.</p>
            </div>
            
            <!-- Miembro 4 -->
            <div style="text-align: center; padding: 2rem; background: #f8f9fa; border-radius: 16px; transition: transform 0.3s;" class="team-member">
                <div style="width: 120px; height: 120px; background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); border-radius: 50%; margin: 0 auto 1.5rem; display: flex; align-items: center; justify-content: center; font-size: 3rem; color: white; box-shadow: 0 8px 25px rgba(67, 233, 123, 0.3);">
                    👩‍💼
                </div>
                <h3 style="color: #333; margin-bottom: 0.5rem; font-size: 1.3rem;">María Torres</h3>
                <p style="color: #28a745; font-weight: 600; margin-bottom: 1rem;">Directora de Atención al Cliente</p>
                <p style="color: #666; font-size: 0.95rem; line-height: 1.6;">Especialista en experiencia del usuario y satisfacción del cliente.</p>
            </div>
        </div>
    </div>
</section>

<!-- Por qué elegirnos -->
<section style="padding: 4rem 2rem; background: #f8f9fa;">
    <div style="max-width: 1000px; margin: 0 auto;">
        <h2 style="text-align: center; color: #333; font-size: clamp(1.8rem, 4vw, 2.5rem); margin-bottom: 3rem;">🌟 ¿Por Qué Elegirnos?</h2>
        
        <div style="display: grid; gap: 2rem;">
            <div style="display: flex; gap: 2rem; align-items: start; background: white; padding: 2rem; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.06);">
                <div style="min-width: 60px; height: 60px; background: linear-gradient(135deg, #667eea, #764ba2); border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 2rem;">💡</div>
                <div>
                    <h3 style="color: #667eea; margin-bottom: 0.5rem;">Tecnología de Vanguardia</h3>
                    <p style="color: #666; line-height: 1.8;">Plataforma desarrollada con las últimas tecnologías, constantemente actualizada y mejorada según las necesidades del mercado.</p>
                </div>
            </div>
            
            <div style="display: flex; gap: 2rem; align-items: start; background: white; padding: 2rem; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.06);">
                <div style="min-width: 60px; height: 60px; background: linear-gradient(135deg, #f093fb, #f5576c); border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 2rem;">🤝</div>
                <div>
                    <h3 style="color: #f5576c; margin-bottom: 0.5rem;">Atención Personalizada</h3>
                    <p style="color: #666; line-height: 1.8;">Cada edificio es único. Adaptamos nuestra solución a tus necesidades específicas con un equipo dedicado de soporte.</p>
                </div>
            </div>
            
            <div style="display: flex; gap: 2rem; align-items: start; background: white; padding: 2rem; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.06);">
                <div style="min-width: 60px; height: 60px; background: linear-gradient(135deg, #4facfe, #00f2fe); border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 2rem;">🔒</div>
                <div>
                    <h3 style="color: #00a8cc; margin-bottom: 0.5rem;">Seguridad Garantizada</h3>
                    <p style="color: #666; line-height: 1.8;">Tus datos y los de tus inquilinos están protegidos con encriptación de nivel bancario y backups automáticos diarios.</p>
                </div>
            </div>
            
            <div style="display: flex; gap: 2rem; align-items: start; background: white; padding: 2rem; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.06);">
                <div style="min-width: 60px; height: 60px; background: linear-gradient(135deg, #43e97b, #38f9d7); border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 2rem;">📈</div>
                <div>
                    <h3 style="color: #28a745; margin-bottom: 0.5rem;">ROI Comprobado</h3>
                    <p style="color: #666; line-height: 1.8;">Reducción promedio del 85% en tiempo administrativo y 95% de mejora en tasa de cobranza. Los números hablan por sí solos.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Certificaciones y Reconocimientos -->
<section style="padding: 4rem 2rem; background: white;">
    <div style="max-width: 1000px; margin: 0 auto; text-align: center;">
        <h2 style="color: #333; font-size: clamp(1.8rem, 4vw, 2.5rem); margin-bottom: 1rem;">🏅 Certificaciones y Reconocimientos</h2>
        <p style="color: #666; font-size: 1.1rem; margin-bottom: 3rem;">Respaldados por instituciones líderes en tecnología y gestión</p>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 2rem;">
            <div style="padding: 2rem; background: #f8f9fa; border-radius: 12px;">
                <div style="font-size: 3rem; margin-bottom: 1rem;">🏆</div>
                <h4 style="color: #333; margin-bottom: 0.5rem;">Premio Innovación Digital</h4>
                <p style="color: #666; font-size: 0.9rem;">Cámara de Comercio de Lima 2024</p>
            </div>
            <div style="padding: 2rem; background: #f8f9fa; border-radius: 12px;">
                <div style="font-size: 3rem; margin-bottom: 1rem;">✅</div>
                <h4 style="color: #333; margin-bottom: 0.5rem;">ISO 27001</h4>
                <p style="color: #666; font-size: 0.9rem;">Seguridad de la Información</p>
            </div>
            <div style="padding: 2rem; background: #f8f9fa; border-radius: 12px;">
                <div style="font-size: 3rem; margin-bottom: 1rem;">🔐</div>
                <h4 style="color: #333; margin-bottom: 0.5rem;">Cumplimiento GDPR</h4>
                <p style="color: #666; font-size: 0.9rem;">Protección de Datos Personales</p>
            </div>
            <div style="padding: 2rem; background: #f8f9fa; border-radius: 12px;">
                <div style="font-size: 3rem; margin-bottom: 1rem;">⭐</div>
                <h4 style="color: #333; margin-bottom: 0.5rem;">Top 10 PropTech</h4>
                <p style="color: #666; font-size: 0.9rem;">Perú 2023-2024</p>
            </div>
        </div>
    </div>
</section>

<!-- Call to Action Final -->
<section style="padding: 4rem 2rem; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; text-align: center;">
    <div style="max-width: 800px; margin: 0 auto;">
        <h2 style="font-size: clamp(1.8rem, 4vw, 2.5rem); margin-bottom: 1rem;">¿Quieres conocernos mejor?</h2>
        <p style="font-size: 1.2rem; opacity: 0.95; margin-bottom: 2rem;">Agenda una reunión con nuestro equipo y descubre cómo podemos ayudarte</p>
        
        <?php if(!isset($_SESSION['usuario_nombre'])): ?>
        <button onclick="abrirModalSolicitud()" style="background: white; color: #667eea; padding: 1.2rem 3rem; border: none; border-radius: 50px; font-weight: 600; font-size: 1.2rem; cursor: pointer; box-shadow: 0 4px 20px rgba(0,0,0,0.2); transition: all 0.3s;">
            📞 Agendar una Reunión
        </button>
        <?php else: ?>
        <a href="<?php echo esRol('Administrador Total') ? 'admin/panel.php' : (esRol('Inquilino') ? 'inquilino/dashboard.php' : 'mi_perfil.php'); ?>" 
           style="background: white; color: #667eea; padding: 1.2rem 3rem; border-radius: 50px; text-decoration: none; font-weight: 600; font-size: 1.2rem; box-shadow: 0 4px 20px rgba(0,0,0,0.2); display: inline-block; transition: all 0.3s;">
            🏠 Ir a Mi Panel
        </a>
        <?php endif; ?>
    </div>
</section>

<style>
    .team-member:hover {
        transform: translateY(-10px);
    }
    
    @media (max-width: 768px) {
        .team-member {
            padding: 1.5rem !important;
        }
    }
</style>

<?php include 'includes/footer.php'; ?>
