<?php
require_once 'includes/session.php';
$title = "Iniciar Sesión - Sistema de Gestión de Edificios";
include 'includes/header.php';
include 'includes/nav.php';
?>

<style>
    /* Reset para esta página */
    .content-section {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
        min-height: calc(100vh - 180px);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 2rem 1rem;
        position: relative;
        overflow: hidden;
    }
    
    /* Patrón de fondo decorativo */
    .content-section::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-image: 
            radial-gradient(circle at 20% 50%, rgba(255, 255, 255, 0.1) 0%, transparent 50%),
            radial-gradient(circle at 80% 80%, rgba(255, 255, 255, 0.1) 0%, transparent 50%),
            radial-gradient(circle at 40% 20%, rgba(255, 255, 255, 0.05) 0%, transparent 50%);
        animation: moveBackground 20s ease-in-out infinite;
        pointer-events: none;
    }
    
    @keyframes moveBackground {
        0%, 100% { transform: translate(0, 0); }
        50% { transform: translate(20px, 20px); }
    }
    
    .login-wrapper {
        width: 100%;
        max-width: 480px;
        animation: slideInUp 0.6s ease-out;
    }
    
    @keyframes slideInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .login-card {
        background: white;
        border-radius: 24px;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        overflow: hidden;
    }
    
    .login-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 3rem 2rem 2.5rem;
        text-align: center;
        position: relative;
    }
    
    .login-header::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: url('data:image/svg+xml,<svg width="100" height="100" xmlns="http://www.w3.org/2000/svg"><circle cx="50" cy="50" r="40" fill="rgba(255,255,255,0.1)"/></svg>');
        opacity: 0.1;
    }
    
    .login-icon {
        font-size: 4rem;
        margin-bottom: 1rem;
        filter: drop-shadow(0 4px 8px rgba(0,0,0,0.2));
    }
    
    .login-header h2 {
        margin: 0 0 0.5rem 0;
        font-size: clamp(1.8rem, 4vw, 2.2rem);
        font-weight: 700;
        position: relative;
        z-index: 1;
    }
    
    .login-header p {
        margin: 0;
        opacity: 0.95;
        font-size: 1.05rem;
        position: relative;
        z-index: 1;
    }
    
    .login-body {
        padding: 2.5rem;
    }
    
    #mensaje-respuesta {
        margin-bottom: 1.5rem;
        padding: 1rem 1.25rem;
        border-radius: 12px;
        font-weight: 500;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        animation: slideDown 0.3s ease-out;
    }
    
    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    #mensaje-respuesta::before {
        font-size: 1.5rem;
    }
    
    .mensaje-exito {
        background: #d4edda;
        color: #155724;
        border: 2px solid #c3e6cb;
    }
    
    .mensaje-exito::before {
        content: '✅';
    }
    
    .mensaje-error {
        background: #f8d7da;
        color: #721c24;
        border: 2px solid #f5c6cb;
    }
    
    .mensaje-error::before {
        content: '❌';
    }
    
    .form-group-modern {
        margin-bottom: 1.75rem;
        position: relative;
    }
    
    .form-group-modern label {
        display: block;
        margin-bottom: 0.5rem;
        color: #333;
        font-weight: 600;
        font-size: 0.95rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .form-group-modern label::before {
        font-size: 1.2rem;
    }
    
    .label-username::before {
        content: '👤';
    }
    
    .label-password::before {
        content: '🔒';
    }
    
    .input-modern {
        width: 100%;
        padding: 1rem 1.25rem;
        border: 2px solid #e9ecef;
        border-radius: 12px;
        font-size: 1rem;
        transition: all 0.3s ease;
        background: #f8f9fa;
        box-sizing: border-box;
    }
    
    .input-modern:focus {
        outline: none;
        border-color: #667eea;
        background: white;
        box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
        transform: translateY(-2px);
    }
    
    .input-modern:hover {
        border-color: #dee2e6;
        background: white;
    }
    
    .btn-login-modern {
        width: 100%;
        padding: 1.1rem;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border: none;
        border-radius: 12px;
        font-size: 1.1rem;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.3s ease;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.75rem;
        margin-top: 2rem;
    }
    
    .btn-login-modern:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(102, 126, 234, 0.5);
    }
    
    .btn-login-modern:active {
        transform: translateY(-1px);
    }
    
    .btn-login-modern:disabled {
        opacity: 0.7;
        cursor: not-allowed;
        transform: none;
    }
    
    .btn-login-modern .btn-icon {
        font-size: 1.3rem;
    }
    
    .loading-spinner {
        width: 20px;
        height: 20px;
        border: 3px solid rgba(255, 255, 255, 0.3);
        border-top-color: white;
        border-radius: 50%;
        animation: spin 0.8s linear infinite;
    }
    
    @keyframes spin {
        to { transform: rotate(360deg); }
    }
    
    .login-footer {
        padding: 1.5rem 2.5rem;
        background: #f8f9fa;
        text-align: center;
        border-top: 1px solid #e9ecef;
    }
    
    .login-footer p {
        margin: 0;
        color: #666;
        font-size: 0.95rem;
    }
    
    .login-footer a {
        color: #667eea;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.2s;
    }
    
    .login-footer a:hover {
        color: #764ba2;
        text-decoration: underline;
    }
    
    /* Responsive */
    @media (max-width: 480px) {
        .login-header {
            padding: 2rem 1.5rem 1.75rem;
        }
        
        .login-icon {
            font-size: 3rem;
        }
        
        .login-body {
            padding: 2rem 1.5rem;
        }
        
        .login-footer {
            padding: 1.25rem 1.5rem;
        }
        
        .form-group-modern {
            margin-bottom: 1.5rem;
        }
        
        .btn-login-modern {
            padding: 1rem;
            font-size: 1rem;
        }
    }
</style>

<section class="content-section">
    <div class="login-wrapper">
        <div class="login-card">
            <!-- Header -->
            <div class="login-header">
                <div class="login-icon">🏢</div>
                <h2>Bienvenido de vuelta</h2>
                <p>Ingresa tus credenciales para continuar</p>
            </div>
            
            <!-- Body -->
            <div class="login-body">
                <div id="mensaje-respuesta" style="display: none;" class="mensaje-alerta"></div>
                
                <form id="loginForm">
                    <div class="form-group-modern">
                        <label for="username" class="label-username">Usuario</label>
                        <input 
                            type="text" 
                            id="username" 
                            name="username" 
                            class="input-modern"
                            placeholder="Ingresa tu nombre de usuario"
                            required
                            autocomplete="username"
                        >
                    </div>
                    
                    <div class="form-group-modern">
                        <label for="password" class="label-password">Contraseña</label>
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            class="input-modern"
                            placeholder="Ingresa tu contraseña"
                            required
                            autocomplete="current-password"
                        >
                    </div>
                    
                    <button type="submit" class="btn-login-modern">
                        <span class="btn-icon">🚀</span>
                        <span class="btn-text">Iniciar Sesión</span>
                    </button>
                </form>
            </div>
            
            <!-- Footer -->
            <div class="login-footer">
                <p>¿No tienes acceso? <a href="javascript:void(0);" onclick="solicitarAccesoDesdeLogin()">Solicita acceso aquí</a></p>
            </div>
        </div>
    </div>
</section>

<script>
// Inicializar formulario de login
function inicializarLogin() {
    const form = document.getElementById('loginForm');
    const submitBtn = form.querySelector('.btn-login-modern');
    
    if (!form) {
        console.error('❌ Formulario de login no encontrado');
        return;
    }
    
    if (!submitBtn) {
        console.error('❌ Botón de login no encontrado');
        return;
    }
    
    console.log('✓ Formulario de login inicializado');
    
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        console.log('=== INICIANDO LOGIN ===');
        
        const formData = new FormData(this);
        const username = formData.get('username');
        const password = formData.get('password');
        
        // Validación básica
        if (!username || !password) {
            mostrarMensaje('Por favor, completa todos los campos', false);
            return;
        }
        
        // Deshabilitar botón y mostrar loading
        submitBtn.disabled = true;
        const btnText = submitBtn.querySelector('.btn-text');
        const btnIcon = submitBtn.querySelector('.btn-icon');
        btnIcon.style.display = 'none';
        btnText.textContent = 'Verificando...';
        submitBtn.innerHTML = '<div class="loading-spinner"></div><span>Verificando credenciales...</span>';
        
        console.log('Enviando credenciales...');
        
        fetch('procesar_login.php', {
            method: 'POST',
            body: formData
        })
        .then(async response => {
            console.log('Respuesta recibida:', response.status);
            
            const textoRaw = await response.text();
            console.log('Respuesta RAW:', textoRaw);
            
            // Intentar parsear JSON
            try {
                // Limpiar respuesta (extraer solo JSON)
                let textoLimpio = textoRaw.trim();
                const primerLlave = textoLimpio.indexOf('{');
                const ultimoLlave = textoLimpio.lastIndexOf('}');
                
                if (primerLlave !== -1 && ultimoLlave !== -1) {
                    textoLimpio = textoLimpio.substring(primerLlave, ultimoLlave + 1);
                }
                
                return JSON.parse(textoLimpio);
            } catch (e) {
                console.error('Error parseando JSON:', e);
                throw new Error('Respuesta invalida del servidor');
            }
        })
        .then(data => {
            console.log('✓ Respuesta procesada:', data);
            
            mostrarMensaje(data.message || 'Operacion completada', data.success);
            
            if (data.success) {
                console.log('✅ Login exitoso');
                console.log('Usuario:', data.user);
                
                // Limpiar formulario
                form.reset();
                
                // Redirigir al index en 1 segundo
                setTimeout(() => {
                    console.log('Redirigiendo a index.php...');
                    window.location.href = 'index.php';
                }, 1000);
            } else {
                console.log('❌ Login fallido:', data.message);
                // Rehabilitar botón
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<span class="btn-icon">🚀</span><span class="btn-text">Iniciar Sesión</span>';
            }
        })
        .catch(error => {
            console.error('❌ Error en login:', error);
            mostrarMensaje('Error en el inicio de sesion. Por favor, intenta nuevamente.', false);
            
            // Rehabilitar botón
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<span class="btn-icon">🚀</span><span class="btn-text">Iniciar Sesión</span>';
        });
    });
}

function mostrarMensaje(mensaje, esExito) {
    const mensajeDiv = document.getElementById('mensaje-respuesta');
    mensajeDiv.textContent = mensaje;
    mensajeDiv.style.display = 'block';
    mensajeDiv.className = 'mensaje-alerta ' + (esExito ? 'mensaje-exito' : 'mensaje-error');
    mensajeDiv.scrollIntoView({ behavior: 'smooth', block: 'center' });
    
    if (!esExito) {
        setTimeout(() => {
            mensajeDiv.style.display = 'none';
        }, 5000);
    }
}

// Función para redirigir a index y abrir modal de solicitud
function solicitarAccesoDesdeLogin() {
    // Guardar en sessionStorage que debe abrir el modal
    sessionStorage.setItem('abrirModalSolicitud', 'true');
    // Redirigir a index
    window.location.href = 'index.php';
}

// Ejecutar inicialización
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', inicializarLogin);
} else {
    inicializarLogin();
}
</script>

<?php include 'includes/footer.php'; ?>
