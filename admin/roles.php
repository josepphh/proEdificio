<?php
require_once '../includes/session.php';
require_once '../includes/permissions.php';
require_once '../config/database.php';

// Verificar permisos
requiereAutenticacion('../login.php');
requierePermiso('acceso_completo'); // Redirige a acceso_denegado.php automáticamente

$title = "Gestión de Roles - Sistema de Edificios";
$pageTitle = "🔑 Gestión de Roles";
$useAdminLayout = true;
include '../includes/header.php';
include '../includes/admin_layout_start.php';
?>
<link rel="stylesheet" href="../css/modal-styles.css">
<?php


// Obtener lista de TODOS los roles (activos e inactivos) con conteo de usuarios ACTIVOS
$database = new Database();
$conn = $database->getConnection();

$sql = "SELECT r.*, 
               COUNT(DISTINCT CASE WHEN u.activo = 1 THEN u.id END) as total_usuarios
        FROM roles r
        LEFT JOIN usuarios u ON r.id = u.rol_id
        GROUP BY r.id
        ORDER BY r.activo DESC, r.id ASC";

$result = $conn->query($sql);
?>

<section class="section-white">
    <div class="container">
        <h2 class="section-title">🎭 Gestión de Roles</h2>
        
        <div class="d-flex gap-2 mb-3">
            <button onclick="mostrarModalNuevoRol()" class="btn btn-primary">+ Nuevo Rol</button>
            <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                <input type="checkbox" id="mostrarInactivos" onchange="toggleInactivos()">
                <span>Mostrar roles inactivos</span>
            </label>
        </div>
    
        <div class="table-responsive">
            <table class="table">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Descripción</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result && $result->num_rows > 0): ?>
                    <?php while ($rol = $result->fetch_assoc()): ?>
                        <tr data-activo="<?php echo $rol['activo']; ?>" style="<?php echo $rol['activo'] == 0 ? 'display: none;' : ''; ?>">
                            <td data-label="Nombre"><strong><?php echo htmlspecialchars($rol['nombre']); ?></strong></td>
                            <td data-label="Descripción"><?php echo htmlspecialchars($rol['descripcion']); ?></td>
                            <td data-label="Estado">
                                <?php if ($rol['activo'] == 1): ?>
                                    <span class="badge badge-success">Activo</span>
                                <?php else: ?>
                                    <span class="badge badge-secondary">Inactivo</span>
                                <?php endif; ?>
                            </td>
                            <td data-label="Acciones">
                                <button onclick="verPermisos('<?php echo htmlspecialchars($rol['nombre'], ENT_QUOTES); ?>')" class="btn-icon btn-icon-info" title="Ver permisos del rol">🔍</button>
                                <?php if ($rol['activo'] == 1): ?>
                                    <button onclick="editarRol(<?php echo $rol['id']; ?>)" class="btn-icon btn-icon-edit" title="Editar">✏️</button>
                                    <button onclick="eliminarRol(<?php echo $rol['id']; ?>, <?php echo $rol['total_usuarios']; ?>)" class="btn-icon btn-icon-delete" title="Desactivar">🗑️</button>
                                <?php else: ?>
                                    <button onclick="restaurarRol(<?php echo $rol['id']; ?>)" class="btn-icon btn-icon-restore" title="Restaurar">♻️</button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" style="text-align: center;">No hay roles registrados</td>
                    </tr>
                <?php endif; ?>
            </tbody>
            </table>
        </div>
    </div>
</section>

<!-- Modal para Nuevo/Editar Rol -->
<div id="modalRol" class="modal">
    <div class="modal-content">
        <span class="close" onclick="cerrarModal()">&times;</span>
        <div class="modal-header">
            <h3 id="modalTitulo">Nuevo Rol</h3>
        </div>
        <form id="formRol" onsubmit="return guardarRol(event)">
        <div class="modal-body">
            <input type="hidden" id="rol_id" name="rol_id">
            
            <div class="form-group">
                <label for="nombre">Nombre del Rol:</label>
                <input type="text" id="nombre" name="nombre" required minlength="3" maxlength="50">
                <small>Ejemplo: Administrador, Inquilino, Seguridad</small>
            </div>
            
            <div class="form-group">
                <label for="descripcion">Descripción:</label>
                <textarea id="descripcion" name="descripcion" rows="4" required minlength="10" maxlength="255"></textarea>
                <small>Describe las funciones y permisos de este rol</small>
            </div>
            
        </div>
        <div class="modal-footer">
            <button type="button" onclick="cerrarModal()" class="btn btn-light">Cancelar</button>
            <button type="submit" class="btn btn-primary">Guardar</button>
        </div>
        </form>
    </div>
</div>

<!-- Modal para Ver Permisos -->
<div id="modalPermisos" class="modal">
    <div class="modal-content" style="max-width: 700px;">
        <span class="close" onclick="cerrarModalPermisos()">&times;</span>
        <div class="modal-header">
            <h3 id="modalPermisosTitle">🔐 Permisos del Rol</h3>
        </div>
        <div class="modal-body">
            <div id="permisosList" style="display: grid; gap: 12px;"></div>
            
            <div style="margin-top: 24px; padding: 16px; background: #f0f9ff; border-left: 4px solid #3b82f6; border-radius: 6px;">
                <p style="margin: 0; color: #1e40af; font-size: 14px;">
                    <strong>ℹ️ Nota:</strong> Los permisos están definidos en <code>includes/permissions.php</code>. 
                    Para modificarlos, edita ese archivo directamente.
                </p>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" onclick="cerrarModalPermisos()" class="btn btn-primary">Cerrar</button>
        </div>
    </div>
</div>

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
    
    .btn-icon-info:hover {
        background-color: #dbeafe;
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

    .modal {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        overflow: auto;
        background-color: rgba(0,0,0,0.4);
    }
    
    .modal-content {
        background-color: var(--color-white);
        margin: 5% auto;
        padding: 2rem;
        border-radius: var(--border-radius-lg);
        width: 80%;
        max-width: 600px;
        box-shadow: var(--shadow-2xl);
    }
</style>

<script>
(function() {
// Variables locales al scope
let modoEdicion = false;

// Mostrar modal para nuevo rol
window.mostrarModalNuevoRol = function() {
    modoEdicion = false;
    document.getElementById('modalTitulo').textContent = 'Nuevo Rol';
    document.getElementById('formRol').reset();
    document.getElementById('rol_id').value = '';
    document.getElementById('modalRol').style.display = 'block';
}

// Editar rol
window.editarRol = async function(id) {
    modoEdicion = true;
    document.getElementById('modalTitulo').textContent = 'Editar Rol';
    
    try {
        const response = await fetch(`../api/get_rol.php?id=${id}`);
        const data = await response.json();
        
        if (data.success) {
            const rol = data.rol;
            document.getElementById('rol_id').value = rol.id;
            document.getElementById('nombre').value = rol.nombre;
            document.getElementById('descripcion').value = rol.descripcion;
            
            document.getElementById('modalRol').style.display = 'block';
        } else {
            showToast('Error: ' + data.message, 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showToast('Error al cargar los datos del rol', 'error');
    }
}

// Guardar rol (crear o actualizar)
window.guardarRol = async function(event) {
    event.preventDefault();
    
    const formData = new FormData(document.getElementById('formRol'));
    const action = modoEdicion ? 'actualizar' : 'crear';
    formData.append('action', action);
    
    try {
        const response = await fetch('../api/gestionar_rol.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            showToast(data.message, 'success');
            cerrarModal();
            location.reload();
        } else {
            showToast('Error: ' + data.message, 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showToast('Error al guardar el rol', 'error');
    }
    
    return false;
}

// Toggle para mostrar/ocultar roles inactivos
window.toggleInactivos = function() {
    const checkbox = document.getElementById('mostrarInactivos');
    const rows = document.querySelectorAll('tr[data-activo="0"]');
    
    rows.forEach(row => {
        row.style.display = checkbox.checked ? 'table-row' : 'none';
    });
}

// Desactivar rol (soft delete)
window.eliminarRol = async function(id, totalUsuarios) {
    let mensaje = '¿Estás seguro de que deseas desactivar este rol?';
    
    if (totalUsuarios > 0) {
        mensaje += `\n\nEste rol tiene ${totalUsuarios} usuario(s) asignado(s).\nLos usuarios serán reasignados automáticamente al rol "Inquilino".`;
    }
    
    const confirmado = await showConfirm(mensaje, '⚠️ Desactivar Rol');
    if (!confirmado) return;
    
    try {
        const formData = new FormData();
        formData.append('action', 'eliminar');
        formData.append('rol_id', id);
        
        const response = await fetch('../api/gestionar_rol.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            showToast(data.message, 'success');
            location.reload();
        } else {
            showToast('Error: ' + data.message, 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showToast('Error al desactivar el rol', 'error');
    }
}

// Restaurar rol
window.restaurarRol = async function(id) {
    const confirmado = await showConfirm(
        '¿Estás seguro de que deseas restaurar este rol?',
        '✅ Restaurar Rol',
        'Restaurar',
        'btn-success'
    );
    if (!confirmado) return;
    
    try {
        const formData = new FormData();
        formData.append('action', 'restaurar');
        formData.append('rol_id', id);
        
        const response = await fetch('../api/gestionar_rol.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            showToast(data.message, 'success');
            location.reload();
        } else {
            showToast('Error: ' + data.message, 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showToast('Error al restaurar el rol', 'error');
    }
}

// Cerrar modal
window.cerrarModal = function() {
    document.getElementById('modalRol').style.display = 'none';
    document.getElementById('formRol').reset();
}

// Definir permisos por rol (sincronizado con includes/permissions.php)
const PERMISOS_POR_ROL = {
    'Administrador Total': [
        { key: 'gestionar_usuarios', label: 'Gestionar usuarios' },
        { key: 'gestionar_edificios', label: 'Gestionar edificios' },
        { key: 'gestionar_inquilinos', label: 'Gestionar inquilinos' },
        { key: 'ver_reportes_globales', label: 'Ver reportes globales' },
        { key: 'gestionar_roles', label: 'Gestionar roles' },
        { key: 'gestionar_gastos', label: 'Gestionar gastos' },
        { key: 'procesar_cierre_mensual', label: 'Procesar cierre mensual' },
        { key: 'gestionar_avisos', label: 'Gestionar avisos' },
        { key: 'ver_avisos', label: 'Ver avisos' },
        { key: 'acceso_completo', label: 'Acceso completo al sistema' },
        { key: 'acceso_panel_admin', label: 'Acceso al panel de administración' }
    ],
    'Administrador Edificio': [
        { key: 'gestionar_usuarios', label: 'Gestionar usuarios (de sus edificios)' },
        { key: 'gestionar_edificios', label: 'Gestionar edificios (sus asignados)' },
        { key: 'gestionar_inquilinos', label: 'Gestionar inquilinos' },
        { key: 'ver_reportes_edificio', label: 'Ver reportes de edificio' },
        { key: 'gestionar_mantenimiento', label: 'Gestionar mantenimiento' },
        { key: 'gestionar_seguridad_edificio', label: 'Gestionar seguridad del edificio' },
        { key: 'gestionar_gastos', label: 'Gestionar gastos' },
        { key: 'procesar_cierre_mensual', label: 'Procesar cierre mensual' },
        { key: 'gestionar_avisos', label: 'Gestionar avisos' },
        { key: 'ver_avisos', label: 'Ver avisos' },
        { key: 'acceso_panel_admin', label: 'Acceso al panel de administración' }
    ],
    'Inquilino': [
        { key: 'ver_perfil', label: 'Ver perfil' },
        { key: 'reportar_incidencias', label: 'Reportar incidencias' },
        { key: 'ver_avisos', label: 'Ver avisos' },
        { key: 'pagar_servicios', label: 'Pagar servicios' },
        { key: 'ver_mis_pagos', label: 'Ver mis pagos' },
        { key: 'registrar_pago', label: 'Registrar pago' }
    ],
    'Seguridad': [
        { key: 'registrar_visitas', label: 'Registrar visitas' },
        { key: 'ver_residentes', label: 'Ver residentes' },
        { key: 'reportar_incidentes', label: 'Reportar incidentes' },
        { key: 'ver_avisos', label: 'Ver avisos' }
    ]
};

// Ver permisos de un rol
window.verPermisos = function(rolNombre) {
    document.getElementById('modalPermisosTitle').textContent = `🔐 Permisos: ${rolNombre}`;
    
    const permisos = PERMISOS_POR_ROL[rolNombre] || [];
    const permisosList = document.getElementById('permisosList');
    
    if (permisos.length === 0) {
        permisosList.innerHTML = `
            <p style="text-align: center; color: #666; padding: 20px;">
                No hay permisos definidos para este rol.
            </p>
        `;
    } else {
        permisosList.innerHTML = permisos.map((permiso, index) => `
            <div style="display: flex; align-items: center; gap: 12px; padding: 12px; background: white; border: 1px solid #e5e7eb; border-radius: 8px;">
                <span style="font-size: 20px; color: #10b981;">✓</span>
                <div style="flex: 1;">
                    <strong style="color: #1f2937;">${permiso.label}</strong>
                    <br>
                    <code style="font-size: 12px; color: #6b7280; background: #f3f4f6; padding: 2px 6px; border-radius: 4px;">${permiso.key}</code>
                </div>
            </div>
        `).join('');
    }
    
    document.getElementById('modalPermisos').style.display = 'block';
}

// Cerrar modal de permisos
window.cerrarModalPermisos = function() {
    document.getElementById('modalPermisos').style.display = 'none';
}

// Toggle para mostrar roles inactivos
window.toggleInactivos = function() {
    const checkbox = document.getElementById('mostrarInactivos');
    const tbody = document.querySelector('tbody');
    const rows = tbody.querySelectorAll('tr[data-activo]');
    
    rows.forEach(row => {
        const activo = row.getAttribute('data-activo');
        if (activo === '0') {
            row.style.display = checkbox.checked ? '' : 'none';
        }
    });
}

// El modal ya NO se cierra al hacer clic fuera
// Solo se puede cerrar con el botón X o Cancelar

})(); // Fin del IIFE
</script>

<?php
$conn->close();
include '../includes/admin_layout_end.php';
include '../includes/admin_layout_end.php';
include '../includes/footer.php';
?>
