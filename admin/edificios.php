<?php
require_once '../includes/session.php';
require_once '../includes/permissions.php';
require_once '../config/database.php';

requiereAutenticacion('../login.php');
requierePermiso('gestionar_edificios'); // Redirige a acceso_denegado.php automáticamente

$title = "Gestión de Edificios";
$pageTitle = "🏛️ Gestión de Edificios";
$useAdminLayout = true;
include '../includes/header.php';
include '../includes/admin_layout_start.php';
?>
<link rel="stylesheet" href="../css/modal-styles.css">
<?php

$database = new Database();
$conn = $database->getConnection();

$sql = "SELECT e.*, COUNT(DISTINCT CASE WHEN u.activo = 1 THEN ue.usuario_id END) as total_usuarios 
        FROM edificios e 
        LEFT JOIN usuario_edificios ue ON e.id = ue.edificio_id AND ue.activo = 1
        LEFT JOIN usuarios u ON ue.usuario_id = u.id
        GROUP BY e.id 
        ORDER BY e.activo DESC, e.nombre ASC";
$result = $conn->query($sql);
?>

<section class="section-white">
    <div class="container">
        <h2 class="section-title">🏢 Gestión de Edificios</h2>
        
        <div class="d-flex gap-2 mb-3">
            <button onclick="mostrarModalNuevoEdificio()" class="btn btn-primary">+ Nuevo Edificio</button>
        <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
            <input type="checkbox" id="mostrarInactivos" onchange="toggleInactivos()">
            <span>Mostrar edificios inactivos</span>
        </label>
    </div>
    
        <div class="cards-grid" id="edificios-grid">
        <?php if ($result && $result->num_rows > 0): ?>
            <?php while ($edificio = $result->fetch_assoc()): ?>
                <div class="card" data-activo="<?php echo $edificio['activo']; ?>" style="<?php echo $edificio['activo'] == 0 ? 'display: none;' : ''; ?>">
                    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                        <h3><?php echo htmlspecialchars($edificio['nombre']); ?></h3>
                        <?php if ($edificio['activo'] == 1): ?>
                            <span class="badge badge-success">Activo</span>
                        <?php else: ?>
                            <span class="badge badge-secondary">Inactivo</span>
                        <?php endif; ?>
                    </div>
                    <div class="card-body">
                        <p><strong>📍 Dirección:</strong> <?php echo htmlspecialchars($edificio['direccion']); ?></p>
                        <p><strong>🏙️ Ciudad:</strong> <?php echo htmlspecialchars($edificio['ciudad']); ?></p>
                        <p><strong>📊 Pisos:</strong> <?php echo htmlspecialchars($edificio['num_pisos']); ?></p>
                        <p><strong>🏠 Departamentos:</strong> <?php echo htmlspecialchars($edificio['num_departamentos']); ?></p>
                        <p><strong>👥 Usuarios Activos:</strong> <?php echo htmlspecialchars($edificio['total_usuarios']); ?></p>
                    </div>
                    <div class="d-flex gap-2" style="padding-top: 1rem; border-top: 1px solid #e0e0e0;">
                        <?php if ($edificio['activo'] == 1): ?>
                            <button onclick="editarEdificio(<?php echo $edificio['id']; ?>)" class="btn btn-sm btn-secondary">Editar</button>
                            <button onclick="eliminarEdificio(<?php echo $edificio['id']; ?>)" class="btn btn-sm btn-danger">Desactivar</button>
                        <?php else: ?>
                            <button onclick="restaurarEdificio(<?php echo $edificio['id']; ?>)" class="btn btn-sm btn-info">Restaurar</button>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p style="grid-column: 1 / -1; text-align: center; padding: 40px;">No hay edificios registrados.</p>
        <?php endif; ?>
        </div>
    </div>
</section>

<!-- Modal para Nuevo Edificio -->
<div id="modalNuevoEdificio" class="modal">
    <div class="modal-content">
        <span class="close" onclick="cerrarModalNuevo()">&times;</span>
        <div class="modal-header">
            <h3>➕ Nuevo Edificio</h3>
        </div>
        <form id="formNuevoEdificio" onsubmit="crearEdificio(event)">
        <div class="modal-body">
            <div class="form-group">
                <label for="nuevo_nombre">Nombre del Edificio *</label>
                <input type="text" id="nuevo_nombre" name="nombre" required maxlength="100" placeholder="Ej: Torre Miraflores">
            </div>

            <div class="form-group">
                <label for="nuevo_direccion">Dirección *</label>
                <input type="text" id="nuevo_direccion" name="direccion" required maxlength="200" placeholder="Ej: Av. Larco 1234">
            </div>

            <div class="form-group">
                <label for="nuevo_ciudad">Ciudad *</label>
                <input type="text" id="nuevo_ciudad" name="ciudad" required maxlength="100" value="Lima">
            </div>

            <div class="form-group">
                <label for="nuevo_num_pisos">Número de Pisos *</label>
                <input type="number" id="nuevo_num_pisos" name="num_pisos" required min="1" placeholder="Ej: 10">
            </div>

            <div class="form-group">
                <label for="nuevo_num_departamentos">Número de Departamentos *</label>
                <input type="number" id="nuevo_num_departamentos" name="num_departamentos" required min="1" placeholder="Ej: 30">
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" onclick="cerrarModalNuevo()" class="btn btn-light">Cancelar</button>
            <button type="submit" class="btn btn-primary">Crear Edificio</button>
        </div>
        </form>
    </div>
</div>

<!-- Modal para Editar Edificio -->
<div id="modalEditarEdificio" class="modal">
    <div class="modal-content">
        <span class="close" onclick="cerrarModalEditar()">&times;</span>
        <div class="modal-header">
            <h3 id="modalTitulo">Editar Edificio</h3>
        </div>
        <form id="formEditarEdificio" onsubmit="guardarEdificio(event)">
        <div class="modal-body">
            <input type="hidden" id="edit_edificio_id" name="edificio_id">
            
            <div class="form-group">
                <label for="edit_nombre">Nombre del Edificio *</label>
                <input type="text" id="edit_nombre" name="nombre" required maxlength="100">
            </div>

            <div class="form-group">
                <label for="edit_direccion">Dirección *</label>
                <input type="text" id="edit_direccion" name="direccion" required maxlength="200">
            </div>

            <div class="form-group">
                <label for="edit_ciudad">Ciudad *</label>
                <input type="text" id="edit_ciudad" name="ciudad" required maxlength="100">
            </div>

            <div class="form-group">
                <label for="edit_num_pisos">Número de Pisos *</label>
                <input type="number" id="edit_num_pisos" name="num_pisos" required min="1">
            </div>

            <div class="form-group">
                <label for="edit_num_departamentos">Número de Departamentos *</label>
                <input type="number" id="edit_num_departamentos" name="num_departamentos" required min="1">
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" onclick="cerrarModalEditar()" class="btn btn-light">Cancelar</button>
            <button type="submit" class="btn btn-primary">Guardar Cambios</button>
        </div>
        </form>
    </div>
</div>

<style>
.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
}

.modal-content {
    background: white;
    margin: 5% auto;
    padding: 2rem;
    border-radius: 8px;
    position: relative;
    max-height: 80vh;
    overflow-y: auto;
}

.close {
    position: absolute;
    right: 1rem;
    top: 1rem;
    font-size: 2rem;
    cursor: pointer;
    color: #aaa;
}

.close:hover {
    color: #000;
}

.form-buttons {
    display: flex;
    gap: 1rem;
    margin-top: 1.5rem;
}

.btn-save {
    flex: 1;
    background: #28a745;
    color: white;
    border: none;
    padding: 0.75rem 1.5rem;
    border-radius: 4px;
    cursor: pointer;
    font-size: 1rem;
    font-weight: 600;
}

.btn-save:hover {
    background: #218838;
}

.btn-cancel {
    flex: 1;
    background: #6c757d;
    color: white;
    border: none;
    padding: 0.75rem 1.5rem;
    border-radius: 4px;
    cursor: pointer;
    font-size: 1rem;
}

.btn-cancel:hover {
    background: #5a6268;
}

/* Estilos para notificaciones toast */
.toast-container {
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 9999;
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.toast {
    min-width: 300px;
    padding: 16px 20px;
    background: white;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    display: flex;
    align-items: center;
    gap: 12px;
    animation: slideIn 0.3s ease;
    border-left: 4px solid #007bff;
}

.toast.success {
    border-left-color: #28a745;
}

.toast.error {
    border-left-color: #dc3545;
}

.toast.warning {
    border-left-color: #ffc107;
}

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
    font-size: 14px;
}

.toast-message {
    font-size: 13px;
    color: #666;
}

.toast-close {
    cursor: pointer;
    font-size: 20px;
    color: #999;
    flex-shrink: 0;
    line-height: 1;
}

.toast-close:hover {
    color: #333;
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

.toast.hiding {
    animation: slideOut 0.3s ease forwards;
}

</style>

<!-- Contenedor para notificaciones -->
<div class="toast-container" id="toastContainer"></div>

<script>
(function() {
// Toast Notifications
window.showToast = function(message, type = 'success') {
    const container = document.getElementById('toastContainer');
    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    
    let icon = '';
    switch(type) {
        case 'success': icon = '✅'; break;
        case 'error': icon = '❌'; break;
        case 'warning': icon = '⚠️'; break;
        case 'info': icon = 'ℹ️'; break;
    }
    
    toast.innerHTML = `
        <div class="toast-icon">${icon}</div>
        <div class="toast-content">
            <div class="toast-title">${type.charAt(0).toUpperCase() + type.slice(1)}</div>
            <div class="toast-message">${message}</div>
        </div>
        <button class="toast-close" onclick="closeToast(this)">×</button>
    `;
    
    container.appendChild(toast);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        toast.style.animation = 'slideOut 0.3s ease-in forwards';
        setTimeout(() => toast.remove(), 300);
    }, 5000);
}

window.closeToast = function(button) {
    const toast = button.closest('.toast');
    toast.style.animation = 'slideOut 0.3s ease-in forwards';
    setTimeout(() => toast.remove(), 300);
}

window.toggleInactivos = function() {
    const checkbox = document.getElementById('mostrarInactivos');
    const rows = document.querySelectorAll('tr[data-activo]');
    
    rows.forEach(row => {
        if (row.getAttribute('data-activo') == '0') {
            row.style.display = checkbox.checked ? '' : 'none';
        }
    });
}

window.mostrarModalNuevoEdificio = function() {
    document.getElementById('modalNuevoEdificio').style.display = 'block';
}

window.crearEdificio = async function(event) {
    event.preventDefault();
    
    const formData = new FormData(document.getElementById('formNuevoEdificio'));
    formData.append('action', 'crear');
    
    try {
        const response = await fetch('/proyectoEdificio/api/gestionar_edificio.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            showToast('Edificio creado exitosamente');
            cerrarModalNuevo();
            setTimeout(() => location.reload(), 1000);
        } else {
            showToast(result.message, 'error');
        }
    } catch (error) {
        showToast('Error de conexión: ' + error.message, 'error');
    }
}

window.cerrarModalNuevo = function() {
    document.getElementById('modalNuevoEdificio').style.display = 'none';
    document.getElementById('formNuevoEdificio').reset();
}

window.editarEdificio = async function(id) {
    try {
        const response = await fetch(`/proyectoEdificio/api/get_edificio.php?id=${id}`);
        const result = await response.json();
        
        if (result.success) {
            const edificio = result.edificio;
            
            document.getElementById('edit_edificio_id').value = edificio.id;
            document.getElementById('edit_nombre').value = edificio.nombre;
            document.getElementById('edit_direccion').value = edificio.direccion;
            document.getElementById('edit_ciudad').value = edificio.ciudad;
            document.getElementById('edit_num_pisos').value = edificio.num_pisos;
            document.getElementById('edit_num_departamentos').value = edificio.num_departamentos;
            
            document.getElementById('modalEditarEdificio').style.display = 'block';
        } else {
            showToast('Error al cargar datos del edificio', 'error');
        }
    } catch (error) {
        showToast('Error de conexión: ' + error.message, 'error');
    }
}

window.guardarEdificio = async function(event) {
    event.preventDefault();
    
    const formData = new FormData(document.getElementById('formEditarEdificio'));
    formData.append('action', 'actualizar');
    
    try {
        const response = await fetch('/proyectoEdificio/api/gestionar_edificio.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            showToast('Edificio actualizado exitosamente');
            cerrarModalEditar();
            setTimeout(() => location.reload(), 1000);
        } else {
            showToast(result.message, 'error');
        }
    } catch (error) {
        showToast('Error de conexión: ' + error.message, 'error');
    }
}

window.cerrarModalEditar = function() {
    document.getElementById('modalEditarEdificio').style.display = 'none';
    document.getElementById('formEditarEdificio').reset();
}

window.eliminarEdificio = async function(id) {
    const confirmado = await showConfirm(
        '¿Está seguro de eliminar este edificio?',
        '🗑️ Eliminar Edificio'
    );
    if (!confirmado) return;
    
    try {
        const formData = new FormData();
        formData.append('action', 'eliminar');
        formData.append('id', id);
        
        const response = await fetch('/proyectoEdificio/api/gestionar_edificio.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            showToast('Edificio eliminado exitosamente');
            setTimeout(() => location.reload(), 1000);
        } else {
            showToast(result.message, 'error');
        }
    } catch (error) {
        showToast('Error de conexión: ' + error.message, 'error');
    }
}

window.restaurarEdificio = async function(id) {
    const confirmado = await showConfirm(
        '¿Está seguro de restaurar este edificio?',
        '✅ Restaurar Edificio',
        'Restaurar',
        'btn-success'
    );
    if (!confirmado) return;
    
    try {
        const formData = new FormData();
        formData.append('action', 'restaurar');
        formData.append('id', id);
        
        const response = await fetch('/proyectoEdificio/api/gestionar_edificio.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            showToast('Edificio restaurado exitosamente');
            setTimeout(() => location.reload(), 1000);
        } else {
            showToast(result.message, 'error');
        }
    } catch (error) {
        showToast('Error de conexión: ' + error.message, 'error');
    }
}

// Los modales ya NO se cierran al hacer clic fuera
// Solo se pueden cerrar con el botón X o Cancelar
})();
</script>

<?php 
$conn->close();
include '../includes/admin_layout_end.php';
include '../includes/footer.php'; 
?>
