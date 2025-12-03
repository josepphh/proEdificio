<?php
require_once 'includes/session.php';
require_once 'includes/permissions.php';
require_once 'config/database.php';

requiereAutenticacion('login.php');

$title = "Cambiar Contraseña - Sistema de Edificios";
include 'includes/header.php';
include 'includes/nav.php';

$database = new Database();
$conn = $database->getConnection();
$usuario_id = $_SESSION['usuario_id'];
?>

<style>
    .cambio-password-section {
        max-width: 600px;
        margin: 3rem auto;
        padding: 0 1rem;
    }
    
    .password-card {
        background: white;
        border-radius: 16px;
        box-shadow: 0 8px 30px rgba(0,0,0,0.1);
        overflow: hidden;
    }
    
    .password-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 2rem;
        text-align: center;
    }
    
    .password-header h2 {
        margin: 0 0 0.5rem 0;
        font-size: 1.8rem;
    }
    
    .password-body {
        padding: 2rem;
    }
    
    .form-group-password {
        margin-bottom: 1.5rem;
    }
    
    .form-group-password label {
        display: block;
        margin-bottom: 0.5rem;
        color: #333;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .input-password-wrapper {
        position: relative;
    }
    
    .input-password {
        width: 100%;
        padding: 1rem;
        padding-right: 3rem;
        border: 2px solid #e9ecef;
        border-radius: 12px;
        font-size: 1rem;
        transition: all 0.3s;
        box-sizing: border-box;
    }
    
    .input-password:focus {
        outline: none;
        border-color: #667eea;
        box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
    }
    
    .toggle-password {
        position: absolute;
        right: 1rem;
        top: 50%;
        transform: translateY(-50%);
        background: none;
        border: none;
        cursor: pointer;
        font-size: 1.2rem;
        color: #666;
        padding: 0.5rem;
    }
    
    .toggle-password:hover {
        color: #667eea;
    }
    
    .password-requirements {
        background: #f8f9fa;
        padding: 1rem;
        border-radius: 8px;
        margin-top: 1rem;
        font-size: 0.9rem;
    }
    
    .password-requirements h4 {
        margin: 0 0 0.5rem 0;
        color: #333;
        font-size: 0.95rem;
    }
    
    .password-requirements ul {
        margin: 0;
        padding-left: 1.5rem;
        color: #666;
    }
    
    .password-requirements li {
        margin: 0.25rem 0;
    }
    
    .btn-cambiar-password {
        width: 100%;
        padding: 1rem;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border: none;
        border-radius: 12px;
        font-size: 1.1rem;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.3s;
        margin-top: 1rem;
    }
    
    .btn-cambiar-password:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(102, 126, 234, 0.5);
    }
    
    .btn-cambiar-password:disabled {
        opacity: 0.6;
        cursor: not-allowed;
        transform: none;
    }
    
    .mensaje-password {
        padding: 1rem;
        border-radius: 8px;
        margin-bottom: 1rem;
        display: none;
    }
    
    .mensaje-success {
        background: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }
    
    .mensaje-error {
        background: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }
    
    @media (max-width: 768px) {
        .password-header {
            padding: 1.5rem;
        }
        
        .password-body {
            padding: 1.5rem;
        }
    }
</style>

<section class="cambio-password-section">
    <div class="password-card">
        <div class="password-header">
            <div style="font-size: 3rem; margin-bottom: 0.5rem;">🔒</div>
            <h2>Cambiar Contraseña</h2>
            <p style="margin: 0; opacity: 0.9;">Actualiza tu contraseña de acceso</p>
        </div>
        
        <div class="password-body">
            <div id="mensajePassword" class="mensaje-password"></div>
            
            <form id="formCambiarPassword">
                <div class="form-group-password">
                    <label for="passwordActual">
                        🔑 Contraseña Actual
                    </label>
                    <div class="input-password-wrapper">
                        <input 
                            type="password" 
                            id="passwordActual" 
                            name="password_actual" 
                            class="input-password"
                            placeholder="Ingresa tu contraseña actual"
                            required
                        >
                        <button type="button" class="toggle-password" onclick="togglePassword('passwordActual')">
                            👁️
                        </button>
                    </div>
                </div>
                
                <div class="form-group-password">
                    <label for="passwordNueva">
                        🆕 Nueva Contraseña
                    </label>
                    <div class="input-password-wrapper">
                        <input 
                            type="password" 
                            id="passwordNueva" 
                            name="password_nueva" 
                            class="input-password"
                            placeholder="Ingresa tu nueva contraseña"
                            required
                        >
                        <button type="button" class="toggle-password" onclick="togglePassword('passwordNueva')">
                            👁️
                        </button>
                    </div>
                </div>
                
                <div class="form-group-password">
                    <label for="passwordConfirmar">
                        ✅ Confirmar Nueva Contraseña
                    </label>
                    <div class="input-password-wrapper">
                        <input 
                            type="password" 
                            id="passwordConfirmar" 
                            name="password_confirmar" 
                            class="input-password"
                            placeholder="Confirma tu nueva contraseña"
                            required
                        >
                        <button type="button" class="toggle-password" onclick="togglePassword('passwordConfirmar')">
                            👁️
                        </button>
                    </div>
                </div>
                
                <div class="password-requirements">
                    <h4>📋 Requisitos de la contraseña:</h4>
                    <ul>
                        <li>Mínimo 6 caracteres</li>
                        <li>Se recomienda usar mayúsculas y minúsculas</li>
                        <li>Se recomienda incluir números</li>
                        <li>Se recomienda incluir caracteres especiales (!@#$%)</li>
                    </ul>
                </div>
                
                <button type="submit" class="btn-cambiar-password" id="btnCambiar">
                    🔄 Cambiar Contraseña
                </button>
            </form>
        </div>
    </div>
</section>

<script>
// Toggle mostrar/ocultar contraseña
function togglePassword(inputId) {
    const input = document.getElementById(inputId);
    const button = input.parentElement.querySelector('.toggle-password');
    
    if (input.type === 'password') {
        input.type = 'text';
        button.textContent = '🙈';
    } else {
        input.type = 'password';
        button.textContent = '👁️';
    }
}

// Manejar envío del formulario
document.getElementById('formCambiarPassword').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const passwordActual = document.getElementById('passwordActual').value;
    const passwordNueva = document.getElementById('passwordNueva').value;
    const passwordConfirmar = document.getElementById('passwordConfirmar').value;
    const btnCambiar = document.getElementById('btnCambiar');
    const mensajeDiv = document.getElementById('mensajePassword');
    
    // Validaciones
    if (passwordNueva.length < 6) {
        mostrarMensaje('La nueva contraseña debe tener al menos 6 caracteres', 'error');
        return;
    }
    
    if (passwordNueva !== passwordConfirmar) {
        mostrarMensaje('Las contraseñas no coinciden', 'error');
        return;
    }
    
    if (passwordActual === passwordNueva) {
        mostrarMensaje('La nueva contraseña debe ser diferente a la actual', 'error');
        return;
    }
    
    // Deshabilitar botón
    btnCambiar.disabled = true;
    btnCambiar.textContent = '⏳ Cambiando contraseña...';
    
    try {
        const formData = new FormData(this);
        
        const response = await fetch('api/cambiar_password.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            mostrarMensaje(data.message, 'success');
            
            // Limpiar formulario
            this.reset();
            
            // Redirigir después de 2 segundos
            setTimeout(() => {
                window.location.href = 'mi_perfil.php';
            }, 2000);
        } else {
            mostrarMensaje(data.message, 'error');
            btnCambiar.disabled = false;
            btnCambiar.textContent = '🔄 Cambiar Contraseña';
        }
    } catch (error) {
        console.error('Error:', error);
        mostrarMensaje('Error al cambiar la contraseña. Intenta nuevamente.', 'error');
        btnCambiar.disabled = false;
        btnCambiar.textContent = '🔄 Cambiar Contraseña';
    }
});

function mostrarMensaje(mensaje, tipo) {
    const mensajeDiv = document.getElementById('mensajePassword');
    mensajeDiv.textContent = mensaje;
    mensajeDiv.className = 'mensaje-password mensaje-' + tipo;
    mensajeDiv.style.display = 'block';
    
    mensajeDiv.scrollIntoView({ behavior: 'smooth', block: 'center' });
    
    if (tipo === 'error') {
        setTimeout(() => {
            mensajeDiv.style.display = 'none';
        }, 5000);
    }
}
</script>

<?php
$conn->close();
include 'includes/footer.php';
?>
