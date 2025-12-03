<?php
require_once '../includes/session.php';
require_once '../includes/permissions.php';
require_once '../config/database.php';

// Verificar permisos
requiereAutenticacion('../login.php');
requierePermiso('gestionar_usuarios'); // Redirige a acceso_denegado.php automáticamente

$title = "Gestión de Usuarios - Sistema de Edificios";
$pageTitle = "👥 Gestión de Usuarios";
$useAdminLayout = true;
include '../includes/header.php';
include '../includes/admin_layout_start.php';

// Obtener lista de usuarios, roles y edificios
$database = new Database();
$conn = $database->getConnection();

// Obtener el rol del usuario actual
$usuario_actual_id = $_SESSION['usuario_id'] ?? null;
$rol_actual_nombre = $_SESSION['rol_nombre'] ?? '';

// Obtener roles para el selector (filtrado según el rol actual)
if ($rol_actual_nombre === 'Administrador Edificio') {
    // Administrador Edificio solo puede asignar roles de Inquilino y Seguridad
    $roles_query = "SELECT id, nombre FROM roles WHERE nombre IN ('Inquilino', 'Seguridad') ORDER BY nombre";
} else {
    // Administrador Total puede ver todos los roles
    $roles_query = "SELECT id, nombre FROM roles ORDER BY nombre";
}
$roles_result = $conn->query($roles_query);
$roles = [];
while ($rol = $roles_result->fetch_assoc()) {
    $roles[] = $rol;
}

// Obtener edificios ACTIVOS para el selector (filtrado según el rol actual)
if ($rol_actual_nombre === 'Administrador Edificio') {
    // Administrador Edificio solo ve sus edificios asignados
    $edificios_query = "SELECT DISTINCT e.id, e.nombre 
                        FROM edificios e
                        INNER JOIN usuario_edificios ue ON e.id = ue.edificio_id
                        WHERE e.activo = 1 
                        AND ue.usuario_id = ?
                        AND ue.activo = 1
                        ORDER BY e.nombre";
    $stmt = $conn->prepare($edificios_query);
    $stmt->bind_param("i", $usuario_actual_id);
    $stmt->execute();
    $edificios_result = $stmt->get_result();
} else {
    // Administrador Total ve todos los edificios activos
    $edificios_query = "SELECT id, nombre FROM edificios WHERE activo = 1 ORDER BY nombre";
    $edificios_result = $conn->query($edificios_query);
}
$edificios = [];
while ($edificio = $edificios_result->fetch_assoc()) {
    $edificios[] = $edificio;
}

// Obtener usuarios según el rol del administrador actual
if ($rol_actual_nombre === 'Administrador Edificio') {
    // Administrador Edificio: Solo ve usuarios de SUS edificios
    $sql = "SELECT DISTINCT u.id, u.nombre, u.email, u.username, u.fecha_registro, u.activo,
                   r.nombre as rol_nombre, r.id as rol_id,
                   -- Total de edificios asignados
                   COUNT(DISTINCT ue_all.edificio_id) as total_edificios,
                   -- Lista de edificios asignados
                   GROUP_CONCAT(DISTINCT ed_all.nombre ORDER BY ed_all.nombre SEPARATOR ', ') as edificios_asignados
            FROM usuarios u
            LEFT JOIN roles r ON u.rol_id = r.id
            -- JOIN para filtrar: usuarios que tienen al menos un edificio en común con el admin
            INNER JOIN usuario_edificios ue_filter ON u.id = ue_filter.usuario_id 
                AND ue_filter.activo = 1
                AND ue_filter.edificio_id IN (
                    SELECT edificio_id 
                    FROM usuario_edificios 
                    WHERE usuario_id = ? AND activo = 1
                )
            -- JOIN para mostrar TODOS los edificios del usuario (no solo los comunes)
            LEFT JOIN usuario_edificios ue_all ON u.id = ue_all.usuario_id AND ue_all.activo = 1
            LEFT JOIN edificios ed_all ON ue_all.edificio_id = ed_all.id
            GROUP BY u.id, u.nombre, u.email, u.username, u.fecha_registro, u.activo, 
                     r.nombre, r.id
            ORDER BY u.activo DESC, u.fecha_registro DESC";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $usuario_actual_id);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    // Administrador Total: Ve TODOS los usuarios
    $sql = "SELECT u.id, u.nombre, u.email, u.username, u.fecha_registro, u.activo,
                   r.nombre as rol_nombre, r.id as rol_id,
                   -- Total de edificios asignados
                   COUNT(DISTINCT ue.edificio_id) as total_edificios,
                   -- Lista de edificios asignados
                   GROUP_CONCAT(DISTINCT ed.nombre ORDER BY ed.nombre SEPARATOR ', ') as edificios_asignados
            FROM usuarios u
            LEFT JOIN roles r ON u.rol_id = r.id
            -- Todos los edificios asignados
            LEFT JOIN usuario_edificios ue ON u.id = ue.usuario_id AND ue.activo = 1
            LEFT JOIN edificios ed ON ue.edificio_id = ed.id
            GROUP BY u.id, u.nombre, u.email, u.username, u.fecha_registro, u.activo, 
                     r.nombre, r.id
            ORDER BY u.activo DESC, u.fecha_registro DESC";
    
    $result = $conn->query($sql);
}
?>

<section class="section-white">
    <div class="container">
        <h2 class="section-title">👥 Gestión de Usuarios</h2>
        
        <?php if ($rol_actual_nombre === 'Administrador Edificio'): ?>
            <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 12px 20px; border-radius: 8px; margin-bottom: 20px; display: flex; align-items: center; gap: 12px;">
                <span style="font-size: 24px;">ℹ️</span>
                <div>
                    <strong>Vista filtrada:</strong> Solo se muestran usuarios asignados a tus edificios.
                </div>
            </div>
        <?php endif; ?>
        
        <div class="d-flex gap-2 mb-3" style="flex-wrap: wrap; align-items: center;">
            <button onclick="mostrarModalNuevoUsuario()" class="btn btn-primary">+ Nuevo Usuario</button>
            
            <label class="d-flex align-center gap-1" style="cursor: pointer;">
                <input type="checkbox" id="mostrarInactivos" onchange="toggleInactivos()">
                <span>Mostrar usuarios inactivos</span>
            </label>
            
            <div class="d-flex align-center gap-2">
                <label for="filtroEdificio" style="font-weight: 600; white-space: nowrap;">🏢 Filtrar por edificio:</label>
                <select id="filtroEdificio" onchange="filtrarPorEdificio()" style="padding: 8px 12px; border: 2px solid #e5e7eb; border-radius: 6px; font-size: 14px; min-width: 200px;">
                    <option value="">Todos los edificios</option>
                    <?php foreach ($edificios as $edificio): ?>
                        <option value="<?php echo htmlspecialchars($edificio['nombre']); ?>">
                            <?php echo htmlspecialchars($edificio['nombre']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <span id="contadorResultados" style="color: #667eea; font-weight: 600; white-space: nowrap;"></span>
            </div>
        </div>
        
        <div class="table-responsive">
            <table class="table">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Rol</th>
                    <th>Edificios Asignados</th>
                    <th>Estado</th>
                    <th style="text-align: center;">Acciones</th>
                </tr>
            </thead>
            <tbody id="tbody-usuarios">
                <?php if ($result && $result->num_rows > 0): ?>
                    <?php while ($user = $result->fetch_assoc()): ?>
                        <tr data-activo="<?php echo $user['activo']; ?>" style="<?php echo $user['activo'] == 0 ? 'display: none;' : ''; ?>">
                            <td data-label="Nombre">
                                <strong><?php echo htmlspecialchars($user['nombre']); ?></strong>
                            </td>
                            <td data-label="Rol"><?php echo htmlspecialchars($user['rol_nombre'] ?? 'Sin rol'); ?></td>
                            <td data-label="Edificios">
                                <?php if ($user['total_edificios'] > 0): ?>
                                    <span class="badge badge-info" style="cursor: help;" 
                                          title="<?php echo htmlspecialchars($user['edificios_asignados']); ?>">
                                        <?php echo $user['total_edificios']; ?> edificio<?php echo $user['total_edificios'] > 1 ? 's' : ''; ?>: <?php echo htmlspecialchars($user['edificios_asignados']); ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-muted">Sin edificios</span>
                                <?php endif; ?>
                            </td>
                            <td data-label="Estado">
                                <?php if ($user['activo'] == 1): ?>
                                    <span class="badge badge-success">Activo</span>
                                <?php else: ?>
                                    <span class="badge badge-secondary">Inactivo</span>
                                <?php endif; ?>
                            </td>
                            <td data-label="Acciones" style="text-align: center;">
                                <?php if ($user['activo'] == 1): ?>
                                    <button onclick="editarUsuario(<?php echo $user['id']; ?>)" class="btn-icon btn-icon-edit" title="Editar usuario">✏️</button>
                                    <button onclick="eliminarUsuario(<?php echo $user['id']; ?>)" class="btn-icon btn-icon-delete" title="Desactivar usuario">🗑️</button>
                                <?php else: ?>
                                    <button onclick="restaurarUsuario(<?php echo $user['id']; ?>)" class="btn-icon btn-icon-restore" title="Restaurar usuario">♻️</button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" style="text-align: center;">No hay usuarios registrados</td>
                    </tr>
                <?php endif; ?>
            </tbody>
            </table>
        </div>
    </div>
</section>

<?php include '../includes/admin_layout_end.php'; ?>

<!-- Estilos específicos de modal inline (temporal) -->
<style>
    /* Botones con iconos */
    .btn-icon {
        background: none;
        border: none;
        font-size: 20px;
        cursor: pointer;
        padding: 6px 10px;
        border-radius: 6px;
        transition: all 0.2s;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
    
    .btn-icon:hover {
        transform: scale(1.15);
    }
    
    .btn-icon-edit:hover {
        background-color: #e0e7ff;
    }
    
    .btn-icon-delete:hover {
        background-color: #fee2e2;
    }
    
    .btn-icon-restore:hover {
        background-color: #d1fae5;
    }

    /* Toast Notifications */
    .toast-container {
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 10000;
        display: flex;
        flex-direction: column;
        gap: 10px;
    }
    
    .toast {
        background: white;
        padding: 16px 20px;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        display: flex;
        align-items: center;
        gap: 12px;
        min-width: 300px;
        max-width: 400px;
        animation: slideIn 0.3s ease-out;
        border-left: 4px solid #ccc;
    }
    
    .toast.success { border-left-color: #22c55e; }
    .toast.error { border-left-color: #ef4444; }
    .toast.warning { border-left-color: #f59e0b; }
    .toast.info { border-left-color: #3b82f6; }
    
    .toast-icon {
        font-size: 24px;
        flex-shrink: 0;
    }
    
    .toast-content {
        flex: 1;
    }
    
    .toast-title {
        font-weight: 600;
        margin-bottom: 4px;
        color: #1f2937;
    }
    
    .toast-message {
        color: #6b7280;
        font-size: 14px;
    }
    
    .toast-close {
        background: none;
        border: none;
        font-size: 20px;
        cursor: pointer;
        color: #9ca3af;
        padding: 0;
        width: 24px;
        height: 24px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    
    .toast-close:hover {
        color: #4b5563;
    }
    
    @keyframes slideIn {
        from {
            transform: translateX(400px);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    @keyframes slideOut {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(400px);
            opacity: 0;
        }
    }
    
    .toast.removing {
        animation: slideOut 0.3s ease-in forwards;
    }

    .modal {
        display: none;
        position: fixed;
        z-index: var(--z-modal);
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        overflow: auto;
        background-color: rgba(0,0,0,0.5);
        backdrop-filter: blur(4px);
    }
    
    .modal-content {
        background-color: var(--color-white);
        margin: 3% auto;
        padding: 0;
        border-radius: 12px;
        width: 90%;
        max-width: 650px;
        box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        animation: modalFadeIn 0.3s ease-out;
    }
    
    @keyframes modalFadeIn {
        from {
            opacity: 0;
            transform: translateY(-50px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .modal-header {
        padding: 24px 30px;
        border-bottom: 2px solid #e5e7eb;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 12px 12px 0 0;
        position: relative;
    }
    
    .modal-header h3 {
        margin: 0;
        font-size: 24px;
        font-weight: 600;
    }
    
    .modal-body {
        padding: 30px;
        max-height: 60vh;
        overflow-y: auto;
    }
    
    .modal-footer {
        padding: 20px 30px;
        border-top: 1px solid #e5e7eb;
        display: flex;
        justify-content: flex-end;
        gap: 12px;
        background-color: #f9fafb;
        border-radius: 0 0 12px 12px;
    }
    
    .close {
        position: absolute;
        right: 20px;
        top: 50%;
        transform: translateY(-50%);
        color: white;
        font-size: 32px;
        font-weight: bold;
        cursor: pointer;
        transition: all 0.2s;
        width: 36px;
        height: 36px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
    }
    
    .close:hover {
        background-color: rgba(255,255,255,0.2);
        transform: translateY(-50%) rotate(90deg);
    }
    
    .form-group {
        margin-bottom: 20px;
    }
    
    .form-group label {
        display: block;
        margin-bottom: 8px;
        font-weight: 600;
        color: #374151;
        font-size: 14px;
    }
    
    .form-group input,
    .form-group select {
        width: 100%;
        padding: 12px 14px;
        border: 2px solid #e5e7eb;
        border-radius: 8px;
        font-size: 15px;
        transition: all 0.2s;
        box-sizing: border-box;
    }
    
    .form-group input:focus,
    .form-group select:focus {
        outline: none;
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }
    
    .form-group small {
        display: block;
        margin-top: 6px;
        color: #6b7280;
        font-size: 13px;
    }
    
    .password-wrapper {
        position: relative;
    }
    
    .password-wrapper input {
        padding-right: 45px;
    }
    
    .toggle-password {
        position: absolute;
        right: 12px;
        top: 50%;
        transform: translateY(-50%);
        background: none;
        border: none;
        cursor: pointer;
        font-size: 20px;
        color: #6b7280;
        padding: 4px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: color 0.2s;
    }
    
    .toggle-password:hover {
        color: #374151;
    }
    
    .checkbox-label {
        display: flex !important;
        align-items: center;
        gap: 10px;
        padding: 8px 12px !important;
        border-radius: 6px;
        cursor: pointer;
        transition: background-color 0.2s;
    }
    
    .checkbox-label:hover {
        background-color: #f3f4f6;
    }
    
    .checkbox-label input[type="checkbox"] {
        width: 18px;
        height: 18px;
        cursor: pointer;
        flex-shrink: 0;
    }
    
    .edificios-checkboxes {
        max-height: 200px;
        overflow-y: auto;
        border: 2px solid #e5e7eb;
        border-radius: 8px;
        padding: 12px;
        background-color: #fafafa;
    }
</style>

<!-- Toast Container -->
<div class="toast-container" id="toastContainer"></div>

<!-- Modal para Nuevo/Editar Usuario -->
<div id="modalUsuario" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modalTitulo">Nuevo Usuario</h3>
            <span class="close" onclick="cerrarModal()">&times;</span>
        </div>
        <form id="formUsuario" onsubmit="return guardarUsuario(event)">
        <div class="modal-body">
            <input type="hidden" id="usuario_id" name="usuario_id">
            
            <div class="form-group">
                <label for="nombre">Nombre Completo: <span style="color: red;">*</span></label>
                <input type="text" id="nombre" name="nombre" required minlength="3" maxlength="100" placeholder="Ej: Juan Pérez García">
            </div>
            
            <div class="form-group">
                <label for="email">Email: <span style="color: red;">*</span></label>
                <input type="email" id="email" name="email" required maxlength="100" placeholder="correo@ejemplo.com">
            </div>
            
            <div class="form-group">
                <label for="username">Usuario: <span style="color: red;">*</span></label>
                <input type="text" id="username" name="username" required minlength="3" maxlength="50" pattern="[a-zA-Z0-9_]+" placeholder="usuario123">
                <small>Solo letras, números y guión bajo</small>
            </div>
            
            <div class="form-group">
                <label for="password">Contraseña: <span style="color: red;" id="password-required">*</span></label>
                <div class="password-wrapper">
                    <input type="password" id="password" name="password" minlength="8" placeholder="Mínimo 8 caracteres">
                    <button type="button" class="toggle-password" onclick="togglePasswordVisibility('password')" title="Mostrar/Ocultar contraseña">
                        👁️
                    </button>
                </div>
                <small id="password-help">Mínimo 8 caracteres</small>
            </div>
            
            <div class="form-group" id="confirm-password-group">
                <label for="confirm_password">Confirmar Contraseña: <span style="color: red;" id="confirm-required">*</span></label>
                <div class="password-wrapper">
                    <input type="password" id="confirm_password" name="confirm_password" minlength="8" placeholder="Repite la contraseña">
                    <button type="button" class="toggle-password" onclick="togglePasswordVisibility('confirm_password')" title="Mostrar/Ocultar contraseña">
                        👁️
                    </button>
                </div>
            </div>
            
            <div class="form-group">
                <label for="rol_id">Rol: <span style="color: red;">*</span></label>
                <?php if ($rol_actual_nombre === 'Administrador Edificio'): ?>
                    <small style="color: #667eea; display: block; margin-bottom: 8px;">
                        ℹ️ Como Administrador de Edificio, solo puedes crear usuarios con rol de Inquilino o Seguridad
                    </small>
                <?php endif; ?>
                <select id="rol_id" name="rol_id" required>
                    <option value="">Seleccione un rol</option>
                    <?php foreach ($roles as $rol): ?>
                        <option value="<?php echo $rol['id']; ?>"><?php echo htmlspecialchars($rol['nombre']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <!-- Selector de edificios para cualquier rol que lo necesite -->
            <div class="form-group" id="edificios-multiple-group" style="display: none;">
                <label>Edificios Asignados:</label>
                <?php if ($rol_actual_nombre === 'Administrador Edificio'): ?>
                    <p style="color: #667eea; font-size: 0.9rem; margin: 5px 0 10px 0;">
                        ℹ️ Como Administrador de Edificio, solo puedes asignar usuarios a tus edificios asignados
                    </p>
                <?php else: ?>
                    <p style="color: #666; font-size: 0.9rem; margin: 5px 0 10px 0;">
                        Seleccione todos los edificios que correspondan. <strong>Todos tienen igual importancia.</strong>
                    </p>
                <?php endif; ?>
                <div class="edificios-checkboxes" id="edificios-checkboxes">
                    <?php if (count($edificios) > 0): ?>
                        <?php foreach ($edificios as $edificio): ?>
                            <label class="checkbox-label" style="display: block; padding: 5px 0;">
                                <input type="checkbox" name="edificios[]" value="<?php echo $edificio['id']; ?>" class="edificio-checkbox">
                                <?php echo htmlspecialchars($edificio['nombre']); ?>
                            </label>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p style="color: #ef4444; padding: 10px; text-align: center;">
                            ⚠️ No tienes edificios asignados. Contacta al Administrador Total para que te asigne edificios.
                        </p>
                    <?php endif; ?>
                </div>
            </div>
            
        </div>
        <div class="modal-footer">
            <button type="button" onclick="cerrarModal()" class="btn btn-light">Cancelar</button>
            <button type="submit" class="btn btn-success">Guardar Usuario</button>
        </div>
        </form>
    </div>
</div>

<script>
// Variables globales
if (typeof modoEdicion === 'undefined') {
    var modoEdicion = false;
}

// Toast Notification System
function showToast(message, type = 'info', title = '') {
    const container = document.getElementById('toastContainer');
    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    
    const icons = {
        success: '✅',
        error: '❌',
        warning: '⚠️',
        info: 'ℹ️'
    };
    
    const titles = {
        success: title || 'Éxito',
        error: title || 'Error',
        warning: title || 'Advertencia',
        info: title || 'Información'
    };
    
    toast.innerHTML = `
        <div class="toast-icon">${icons[type]}</div>
        <div class="toast-content">
            <div class="toast-title">${titles[type]}</div>
            <div class="toast-message">${message}</div>
        </div>
        <button class="toast-close" onclick="this.parentElement.remove()">×</button>
    `;
    
    container.appendChild(toast);
    
    // Auto-remove after 4 seconds
    setTimeout(() => {
        toast.classList.add('removing');
        setTimeout(() => toast.remove(), 300);
    }, 4000);
}

// Toggle password visibility
function togglePasswordVisibility(fieldId) {
    const field = document.getElementById(fieldId);
    const button = field.nextElementSibling;
    
    if (field.type === 'password') {
        field.type = 'text';
        button.textContent = '🙈';
    } else {
        field.type = 'password';
        button.textContent = '👁️';
    }
}

// Configurar listener para cambio de rol
document.getElementById('rol_id').addEventListener('change', function() {
    const rolSelect = this;
    const rolTexto = rolSelect.options[rolSelect.selectedIndex].text;
    
    // Mostrar selector de edificios para roles que lo necesitan
    const necesitaEdificios = ['Administrador Edificio', 'Inquilino', 'Seguridad'].includes(rolTexto);
    document.getElementById('edificios-multiple-group').style.display = necesitaEdificios ? 'block' : 'none';
});

// Mostrar modal para nuevo usuario
function mostrarModalNuevoUsuario() {
    modoEdicion = false;
    document.getElementById('modalTitulo').textContent = '👤 Nuevo Usuario';
    document.getElementById('formUsuario').reset();
    document.getElementById('usuario_id').value = '';
    document.getElementById('password').required = true;
    document.getElementById('confirm_password').required = true;
    document.getElementById('password-required').style.display = 'inline';
    document.getElementById('confirm-required').style.display = 'inline';
    document.getElementById('password-help').textContent = 'Mínimo 8 caracteres';
    document.getElementById('confirm-password-group').style.display = 'block';
    
    // Resetear vista de edificios
    document.getElementById('edificios-multiple-group').style.display = 'none';
    
    // Limpiar todos los checkboxes
    document.querySelectorAll('.edificio-checkbox').forEach(cb => cb.checked = false);
    
    document.getElementById('modalUsuario').style.display = 'block';
}

// Editar usuario
async function editarUsuario(id) {
    modoEdicion = true;
    document.getElementById('modalTitulo').textContent = '✏️ Editar Usuario';
    document.getElementById('password').required = false;
    document.getElementById('confirm_password').required = false;
    document.getElementById('password-required').style.display = 'none';
    document.getElementById('confirm-required').style.display = 'none';
    document.getElementById('password-help').textContent = 'Dejar en blanco para mantener la contraseña actual';
    document.getElementById('confirm-password-group').style.display = 'none';
    
    try {
        const response = await fetch(`../api/get_usuario.php?id=${id}`);
        const data = await response.json();
        
        if (data.success) {
            const usuario = data.usuario;
            document.getElementById('usuario_id').value = usuario.id;
            document.getElementById('nombre').value = usuario.nombre;
            document.getElementById('email').value = usuario.email;
            document.getElementById('username').value = usuario.username;
            document.getElementById('rol_id').value = usuario.rol_id;
            document.getElementById('password').value = '';
            document.getElementById('confirm_password').value = '';
            
            // Determinar si necesita selector de edificios
            const rolSelect = document.getElementById('rol_id');
            const rolTexto = rolSelect.options[rolSelect.selectedIndex].text;
            const necesitaEdificios = ['Administrador Edificio', 'Inquilino', 'Seguridad'].includes(rolTexto);
            
            // Mostrar/ocultar selector de edificios
            document.getElementById('edificios-multiple-group').style.display = necesitaEdificios ? 'block' : 'none';
            
            // Marcar edificios asignados
            if (necesitaEdificios && data.edificios_asignados) {
                // Desmarcar todos primero
                document.querySelectorAll('.edificio-checkbox').forEach(cb => cb.checked = false);
                
                // Marcar los asignados
                data.edificios_asignados.forEach(edificio_id => {
                    const checkbox = document.querySelector(`.edificio-checkbox[value="${edificio_id}"]`);
                    if (checkbox) checkbox.checked = true;
                });
            }
            
            document.getElementById('modalUsuario').style.display = 'block';
        } else {
            showToast(data.message, 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showToast('Error al cargar los datos del usuario', 'error');
    }
}

// Cargar edificios asignados a un usuario
async function cargarEdificiosAsignados(usuario_id) {
    try {
        const response = await fetch(`/proyectoEdificio/api/asignar_edificios.php?accion=obtener_edificios_usuario&usuario_id=${usuario_id}`);
        const data = await response.json();
        
        if (data.success) {
            // Desmarcar todos los checkboxes primero
            document.querySelectorAll('.edificio-checkbox').forEach(cb => cb.checked = false);
            
            // Marcar los edificios asignados
            data.edificios_asignados.forEach(edificio_id => {
                const checkbox = document.querySelector(`.edificio-checkbox[value="${edificio_id}"]`);
                if (checkbox) checkbox.checked = true;
            });
        }
    } catch (error) {
        console.error('Error al cargar edificios asignados:', error);
    }
}

// Guardar usuario (crear o actualizar)
async function guardarUsuario(event) {
    event.preventDefault();
    
    // Validar contraseñas si es modo crear o si se ingresó nueva contraseña
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirm_password').value;
    
    if (!modoEdicion) {
        // Modo crear: contraseñas son obligatorias
        if (!password || !confirmPassword) {
            showToast('Por favor, ingresa y confirma la contraseña', 'warning');
            return false;
        }
        
        if (password !== confirmPassword) {
            showToast('Las contraseñas no coinciden', 'error');
            return false;
        }
    } else {
        // Modo editar: solo validar si se ingresó nueva contraseña
        if (password && password !== confirmPassword) {
            showToast('Las contraseñas no coinciden', 'error');
            return false;
        }
    }
    
    const formData = new FormData(document.getElementById('formUsuario'));
    
    // Remover confirm_password del FormData (solo es para validación frontend)
    formData.delete('confirm_password');
    
    const action = modoEdicion ? 'actualizar' : 'crear';
    formData.append('action', action);
    
    // Verificar si necesita asignación de edificios
    const rolSelect = document.getElementById('rol_id');
    const rolTexto = rolSelect.options[rolSelect.selectedIndex].text;
    const necesitaEdificios = ['Administrador Edificio', 'Inquilino', 'Seguridad'].includes(rolTexto);
    
    // Validar que si necesita edificios, al menos uno esté seleccionado
    if (necesitaEdificios) {
        const edificiosSeleccionados = document.querySelectorAll('.edificio-checkbox:checked');
        if (edificiosSeleccionados.length === 0) {
            showToast('Debes seleccionar al menos un edificio para este rol', 'warning');
            return false;
        }
    }
    
    try {
        const response = await fetch('../api/gestionar_usuario.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            // Si tiene asignación de edificios, actualizar (crear o editar)
            if (necesitaEdificios) {
                const usuario_id = modoEdicion ? document.getElementById('usuario_id').value : data.usuario_id;
                await asignarEdificios(usuario_id);
            }
            
            showToast(data.message, 'success');
            cerrarModal();
            setTimeout(() => location.reload(), 1000);
        } else {
            showToast(data.message, 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showToast('Error al guardar el usuario', 'error');
    }
    
    return false;
}

// Desactivar usuario (soft delete)
async function eliminarUsuario(id) {
    const confirmado = await showConfirm(
        '¿Estás seguro de que deseas desactivar este usuario?',
        '⚠️ Desactivar Usuario'
    );
    if (!confirmado) return;
    
    try {
        const formData = new FormData();
        formData.append('action', 'eliminar');
        formData.append('usuario_id', id);
        
        const response = await fetch('../api/gestionar_usuario.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            showToast(data.message, 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            showToast(data.message, 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showToast('Error al desactivar el usuario', 'error');
    }
}

// Restaurar usuario
async function restaurarUsuario(id) {
    const confirmado = await showConfirm(
        '¿Estás seguro de que deseas restaurar este usuario?',
        '✅ Restaurar Usuario',
        'Restaurar',
        'btn-success'
    );
    if (!confirmado) return;
    
    try {
        const formData = new FormData();
        formData.append('action', 'restaurar');
        formData.append('usuario_id', id);
        
        const response = await fetch('../api/gestionar_usuario.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            showToast(data.message, 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            showToast(data.message, 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showToast('Error al restaurar el usuario', 'error');
    }
}

// Asignar edificios (simplificado - todos iguales)
async function asignarEdificios(usuario_id) {
    const checkboxes = document.querySelectorAll('.edificio-checkbox:checked');
    const edificios = Array.from(checkboxes).map(cb => cb.value);
    
    const formData = new FormData();
    formData.append('accion', 'asignar_edificios');
    formData.append('usuario_id', usuario_id);
    edificios.forEach(id => formData.append('edificios[]', id));
    
    try {
        const response = await fetch('/proyectoEdificio/api/asignar_edificios.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (!data.success) {
            console.error('Error al asignar edificios:', data.message);
            showToast('Error al asignar edificios: ' + data.message, 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showToast('Error de conexión al asignar edificios', 'error');
    }
}

// Filtrar por edificio
function filtrarPorEdificio() {
    const filtroEdificio = document.getElementById('filtroEdificio').value.toLowerCase();
    const tbody = document.getElementById('tbody-usuarios');
    const rows = tbody.querySelectorAll('tr[data-activo]');
    let contadorVisibles = 0;
    
    rows.forEach(row => {
        const edificiosCell = row.querySelector('td[data-label="Edificios"]');
        if (!edificiosCell) return;
        
        const edificiosTexto = edificiosCell.textContent.toLowerCase();
        
        // Si no hay filtro seleccionado, aplicar regla de inactivos
        if (filtroEdificio === '') {
            const activo = row.getAttribute('data-activo');
            const mostrarInactivos = document.getElementById('mostrarInactivos').checked;
            const visible = (activo === '1' || mostrarInactivos);
            row.style.display = visible ? '' : 'none';
            if (visible) contadorVisibles++;
        } else {
            // Verificar si el edificio filtrado está en la lista de edificios del usuario
            const coincide = edificiosTexto.includes(filtroEdificio);
            const activo = row.getAttribute('data-activo');
            const mostrarInactivos = document.getElementById('mostrarInactivos').checked;
            
            // Mostrar si coincide el edificio Y cumple la regla de activo/inactivo
            if (coincide && (activo === '1' || mostrarInactivos)) {
                row.style.display = '';
                contadorVisibles++;
            } else {
                row.style.display = 'none';
            }
        }
    });
    
    // Actualizar contador
    const contadorSpan = document.getElementById('contadorResultados');
    if (filtroEdificio === '') {
        contadorSpan.textContent = '';
    } else {
        contadorSpan.textContent = `(${contadorVisibles} usuario${contadorVisibles !== 1 ? 's' : ''})`;
    }
}

// Toggle para mostrar usuarios inactivos
function toggleInactivos() {
    // Al cambiar el estado de inactivos, re-aplicar el filtro de edificio
    filtrarPorEdificio();
}

// Cerrar modal
function cerrarModal() {
    document.getElementById('modalUsuario').style.display = 'none';
    document.getElementById('formUsuario').reset();
}

// El modal ya NO se cierra al hacer clic fuera de él
// Solo se puede cerrar con el botón X o el botón Cancelar
</script>

<?php
$conn->close();
include '../includes/footer.php';
?>
