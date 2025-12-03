<?php
require_once '../includes/session.php';
require_once '../includes/permissions.php';
require_once '../config/database.php';

// Verificar permisos
requiereAutenticacion('../login.php');
requierePermiso('gestionar_inquilinos'); // Redirige a acceso_denegado.php automáticamente

$title = "Gestión de Inquilinos - Sistema de Edificios";
$pageTitle = "🏘️ Gestión de Inquilinos";
$useAdminLayout = true;
include '../includes/header.php';
include '../includes/admin_layout_start.php';


$database = new Database();
$conn = $database->getConnection();

// Obtener edificios según el rol del usuario
$rol_nombre = $_SESSION['rol_nombre'] ?? '';
$usuario_id = $_SESSION['usuario_id'] ?? 0;

if ($rol_nombre === 'Administrador Total') {
    // Administrador Total ve todos los edificios activos
    $edificios_query = "SELECT id, nombre FROM edificios WHERE activo = 1 ORDER BY nombre";
    $edificios_result = $conn->query($edificios_query);
} else if ($rol_nombre === 'Administrador Edificio') {
    // Administrador Edificio solo ve sus edificios asignados
    $stmt = $conn->prepare("
        SELECT e.id, e.nombre 
        FROM edificios e
        INNER JOIN usuario_edificios ue ON e.id = ue.edificio_id
        WHERE ue.usuario_id = ? AND ue.activo = 1 AND e.activo = 1
        ORDER BY e.nombre
    ");
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    $edificios_result = $stmt->get_result();
} else {
    // Otros roles no tienen acceso (no deberían llegar aquí por permisos)
    $edificios_result = null;
}

$edificios = [];
if ($edificios_result) {
    while ($edificio = $edificios_result->fetch_assoc()) {
        $edificios[] = $edificio;
    }
}

// Obtener ID del rol "Inquilino"
$rol_query = "SELECT id FROM roles WHERE nombre = 'Inquilino' LIMIT 1";
$rol_result = $conn->query($rol_query);
$rol_inquilino = $rol_result->fetch_assoc();
$rol_inquilino_id = $rol_inquilino['id'] ?? 3; // Default a 3 si no existe

// Si hay un edificio seleccionado, obtener sus inquilinos
$edificio_seleccionado = isset($_GET['edificio_id']) ? intval($_GET['edificio_id']) : 0;
$inquilinos = [];

if ($edificio_seleccionado > 0) {
    $stmt = $conn->prepare("
        SELECT u.id, u.nombre, u.email, u.username, u.fecha_registro, u.activo,
               ue.fecha_asignacion,
               COUNT(DISTINCT r.id) as total_recibos,
               COUNT(DISTINCT CASE WHEN r.estado = 'PENDIENTE' THEN r.id END) as recibos_pendientes,
               COALESCE(SUM(CASE WHEN r.estado = 'PENDIENTE' THEN r.monto_deuda ELSE 0 END), 0) as monto_pendiente
        FROM usuarios u
        INNER JOIN usuario_edificios ue ON u.id = ue.usuario_id
        LEFT JOIN recibos_inquilino r ON u.id = r.usuario_id AND r.activo = 1
        WHERE ue.edificio_id = ? 
        AND ue.activo = 1
        AND u.rol_id = ?
        GROUP BY u.id
        ORDER BY u.activo DESC, u.nombre ASC
    ");
    $stmt->bind_param("ii", $edificio_seleccionado, $rol_inquilino_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $inquilinos = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

// Obtener nombre del edificio seleccionado
$edificio_nombre = '';
if ($edificio_seleccionado > 0) {
    $stmt = $conn->prepare("SELECT nombre FROM edificios WHERE id = ?");
    $stmt->bind_param("i", $edificio_seleccionado);
    $stmt->execute();
    $result = $stmt->get_result();
    $edificio_data = $result->fetch_assoc();
    $edificio_nombre = $edificio_data['nombre'] ?? '';
    $stmt->close();
}

$conn->close();
?>

<section class="content-section">
    <h2 class="section-title">Gestión de Inquilinos por Edificio</h2>
    
    <div class="filter-section">
        <div class="form-group">
            <label for="edificio_id">Seleccionar Edificio:</label>
            <select id="edificio_id" name="edificio_id" onchange="cargarInquilinos()" style="padding: 0.75rem; border: 1px solid #ddd; border-radius: 5px; font-size: 1rem; min-width: 300px;">
                <option value="">-- Seleccione un edificio --</option>
                <?php foreach ($edificios as $edificio): ?>
                    <option value="<?php echo htmlspecialchars($edificio['id']); ?>" 
                            <?php echo $edificio_seleccionado == $edificio['id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($edificio['nombre']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>
    
    <?php if ($edificio_seleccionado > 0): ?>
        <div class="edificio-header" style="background: white; padding: 1.5rem; border-radius: 8px; margin-bottom: 1.5rem; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
            <h3>📌 Edificio: <?php echo htmlspecialchars($edificio_nombre); ?></h3>
            <p>Total de inquilinos: <strong><?php echo count($inquilinos); ?></strong></p>
        </div>
        
        <?php if (empty($inquilinos)): ?>
            <div class="no-data" style="background: white; padding: 3rem; border-radius: 8px; text-align: center; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                <p>No hay inquilinos registrados en este edificio.</p>
            </div>
        <?php else: ?>
            <div class="inquilinos-container">
                <div style="display: flex; gap: 10px; margin-bottom: 20px; align-items: center;">
                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                        <input type="checkbox" id="mostrarInactivos" onchange="toggleInactivos()">
                        <span>Mostrar inquilinos inactivos</span>
                    </label>
                </div>
                
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Email</th>
                            <th>Usuario</th>
                            <th>Fecha Ingreso</th>
                            <th>Recibos</th>
                            <th>Pendientes</th>
                            <th>Monto Pendiente</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($inquilinos as $inquilino): ?>
                            <tr data-activo="<?php echo $inquilino['activo']; ?>" 
                                style="<?php echo $inquilino['activo'] == 0 ? 'display: none;' : ''; ?>">
                                <td data-label="ID"><?php echo htmlspecialchars($inquilino['id']); ?></td>
                                <td data-label="Nombre"><?php echo htmlspecialchars($inquilino['nombre']); ?></td>
                                <td data-label="Email"><?php echo htmlspecialchars($inquilino['email']); ?></td>
                                <td data-label="Usuario"><?php echo htmlspecialchars($inquilino['username']); ?></td>
                                <td data-label="Fecha Asignación">
                                    <?php 
                                    if (isset($inquilino['fecha_asignacion']) && $inquilino['fecha_asignacion']) {
                                        echo date('d/m/Y', strtotime($inquilino['fecha_asignacion']));
                                    } else {
                                        echo date('d/m/Y', strtotime($inquilino['fecha_registro']));
                                    }
                                    ?>
                                </td>
                                <td data-label="Recibos"><?php echo $inquilino['total_recibos']; ?></td>
                                <td data-label="Pendientes">
                                    <?php if ($inquilino['recibos_pendientes'] > 0): ?>
                                        <span style="color: #dc3545; font-weight: bold;">
                                            <?php echo $inquilino['recibos_pendientes']; ?>
                                        </span>
                                    <?php else: ?>
                                        <span style="color: #28a745;">0</span>
                                    <?php endif; ?>
                                </td>
                                <td data-label="Monto Pendiente">
                                    <?php if ($inquilino['monto_pendiente'] > 0): ?>
                                        <span style="color: #dc3545; font-weight: bold;">
                                            S/ <?php echo number_format($inquilino['monto_pendiente'], 2); ?>
                                        </span>
                                    <?php else: ?>
                                        <span style="color: #28a745;">S/ 0.00</span>
                                    <?php endif; ?>
                                </td>
                                <td data-label="Estado">
                                    <?php if ($inquilino['activo'] == 1): ?>
                                        <span class="badge badge-activo">Activo</span>
                                    <?php else: ?>
                                        <span class="badge badge-inactivo">Inactivo</span>
                                    <?php endif; ?>
                                </td>
                                <td data-label="Acciones">
                                    <div style="display: flex; gap: 5px;">
                                        <button onclick="verDetalleInquilino(<?php echo $inquilino['id']; ?>)" 
                                                class="btn-action btn-view" title="Ver Detalle">
                                            👁️
                                        </button>
                                        <button onclick="editarInquilino(<?php echo $inquilino['id']; ?>)" 
                                                class="btn-action btn-edit" title="Editar">
                                            ✏️
                                        </button>
                                        <?php if ($inquilino['activo'] == 1): ?>
                                            <button onclick="desactivarInquilino(<?php echo $inquilino['id']; ?>)" 
                                                    class="btn-action btn-delete" title="Desactivar">
                                                🗑️
                                            </button>
                                        <?php else: ?>
                                            <button onclick="activarInquilino(<?php echo $inquilino['id']; ?>)" 
                                                    class="btn-action btn-restore" title="Activar">
                                                ✅
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    <?php else: ?>
        <div class="no-selection" style="background: white; padding: 3rem; border-radius: 8px; text-align: center; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
            <p>👆 Selecciona un edificio para ver sus inquilinos</p>
        </div>
    <?php endif; ?>
</section>

<!-- Modal para ver detalle del inquilino -->
<div id="modalDetalle" class="modal" style="display: none;">
    <div class="modal-content" style="max-width: 800px;">
        <span class="close" onclick="cerrarModalDetalle()">&times;</span>
        <h3 id="modalTitulo">Detalle del Inquilino</h3>
        <div id="modalBody"></div>
    </div>
</div>

<!-- Modal para editar inquilino -->
<div id="modalEditar" class="modal" style="display: none;">
    <div class="modal-content" style="max-width: 600px;">
        <span class="close" onclick="cerrarModalEditar()">&times;</span>
        <h3>Editar Inquilino</h3>
        <form id="formEditarInquilino" onsubmit="guardarInquilino(event)">
            <input type="hidden" id="edit_usuario_id" name="usuario_id">
            
            <div class="form-group">
                <label for="edit_nombre">Nombre Completo *</label>
                <input type="text" id="edit_nombre" name="nombre" required maxlength="100">
            </div>
            
            <div class="form-group">
                <label for="edit_email">Email *</label>
                <input type="email" id="edit_email" name="email" required maxlength="100">
            </div>
            
            <div class="form-group">
                <label for="edit_username">Usuario *</label>
                <input type="text" id="edit_username" name="username" required maxlength="50" readonly style="background: #f0f0f0;">
                <small>El nombre de usuario no se puede modificar</small>
            </div>
            
            <div class="form-buttons" style="display: flex; gap: 1rem; margin-top: 1.5rem;">
                <button type="submit" class="btn-save">Guardar Cambios</button>
                <button type="button" onclick="cerrarModalEditar()" class="btn-cancel">Cancelar</button>
            </div>
        </form>
    </div>
</div>

<style>
    .filter-section {
        background: white;
        padding: 1.5rem;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        margin-bottom: 1.5rem;
    }
    
    .edificio-header h3 {
        color: #007bff;
        margin: 0 0 0.5rem 0;
    }
    
    .data-table {
        width: 100%;
        border-collapse: collapse;
        background: white;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }
    
    .data-table th,
    .data-table td {
        padding: 0.75rem;
        text-align: left;
        border-bottom: 1px solid #ddd;
    }
    
    .data-table th {
        background: #f8f9fa;
        font-weight: 600;
        color: #333;
    }
    
    .data-table tr:hover {
        background: #f8f9fa;
    }
    
    .badge {
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        font-size: 0.875rem;
        font-weight: 600;
    }
    
    .badge-activo {
        background: #d4edda;
        color: #155724;
    }
    
    .badge-inactivo {
        background: #f8d7da;
        color: #721c24;
    }
    
    .btn-action {
        border: none;
        padding: 0.4rem 0.6rem;
        border-radius: 4px;
        cursor: pointer;
        font-size: 1rem;
        transition: all 0.3s ease;
    }
    
    .btn-view { background: #17a2b8; color: white; }
    .btn-edit { background: #ffc107; color: white; }
    .btn-delete { background: #dc3545; color: white; }
    .btn-restore { background: #28a745; color: white; }
    
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
    background: var(--color-white);
    margin: 5% auto;
    padding: 2rem;
    border-radius: var(--border-radius-lg);
    position: relative;
    max-height: 80vh;
    overflow-y: auto;
    box-shadow: var(--shadow-2xl);
}
</style>

<script>
function cargarInquilinos() {
    const edificio_id = document.getElementById('edificio_id').value;
    if (edificio_id) {
        window.location.href = '/proyectoEdificio/admin/inquilinos.php?edificio_id=' + edificio_id;
    } else {
        window.location.href = '/proyectoEdificio/admin/inquilinos.php';
    }
}

function toggleInactivos() {
    const checkbox = document.getElementById('mostrarInactivos');
    const rows = document.querySelectorAll('tr[data-activo]');
    
    rows.forEach(row => {
        if (row.getAttribute('data-activo') == '0') {
            row.style.display = checkbox.checked ? '' : 'none';
        }
    });
}

async function verDetalleInquilino(usuario_id) {
    try {
        const response = await fetch(`/proyectoEdificio/api/get_inquilino.php?id=${usuario_id}`);
        const result = await response.json();
        
        if (result.success) {
            const inquilino = result.data;
            const modalBody = document.getElementById('modalBody');
            
            modalBody.innerHTML = `
                <div style="display: grid; gap: 1rem;">
                    <div><strong>Nombre:</strong> ${inquilino.nombre}</div>
                    <div><strong>Email:</strong> ${inquilino.email}</div>
                    <div><strong>Usuario:</strong> ${inquilino.username}</div>
                    <div><strong>Edificio:</strong> ${inquilino.edificio_nombre || 'N/A'}</div>
                    <div><strong>Fecha Asignación:</strong> ${inquilino.fecha_asignacion ? new Date(inquilino.fecha_asignacion).toLocaleDateString('es-PE') : inquilino.fecha_registro ? new Date(inquilino.fecha_registro).toLocaleDateString('es-PE') : 'N/A'}</div>
                    <hr>
                    <h4>Recibos</h4>
                    <div><strong>Total:</strong> ${inquilino.total_recibos || 0}</div>
                    <div><strong>Pendientes:</strong> ${inquilino.recibos_pendientes || 0}</div>
                    <div><strong>Pagados:</strong> ${inquilino.recibos_pagados || 0}</div>
                    <div><strong>Monto Pendiente:</strong> S/ ${parseFloat(inquilino.monto_pendiente || 0).toFixed(2)}</div>
                </div>
            `;
            
            document.getElementById('modalDetalle').style.display = 'block';
        } else {
            alert('Error al cargar datos del inquilino');
        }
    } catch (error) {
        alert('Error de conexión: ' + error.message);
    }
}

async function editarInquilino(usuario_id) {
    try {
        const response = await fetch(`/proyectoEdificio/api/get_inquilino.php?id=${usuario_id}`);
        const result = await response.json();
        
        if (result.success) {
            const inquilino = result.data;
            
            // Llenar formulario
            document.getElementById('edit_usuario_id').value = inquilino.id;
            document.getElementById('edit_nombre').value = inquilino.nombre;
            document.getElementById('edit_email').value = inquilino.email;
            document.getElementById('edit_username').value = inquilino.username;
            
            // Mostrar modal
            document.getElementById('modalEditar').style.display = 'block';
        } else {
            alert('Error al cargar datos del inquilino');
        }
    } catch (error) {
        alert('Error de conexión: ' + error.message);
    }
}

async function guardarInquilino(event) {
    event.preventDefault();
    
    const formData = new FormData(document.getElementById('formEditarInquilino'));
    formData.append('action', 'actualizar');
    formData.append('rol_id', '3'); // Mantener como inquilino
    
    try {
        const response = await fetch('/proyectoEdificio/api/gestionar_usuario.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            alert('Inquilino actualizado exitosamente');
            cerrarModalEditar();
            location.reload();
        } else {
            alert('Error: ' + result.message);
        }
    } catch (error) {
        alert('Error de conexión: ' + error.message);
    }
}

async function desactivarInquilino(usuario_id) {
    const confirmado = await showConfirm(
        '¿Está seguro de desactivar este inquilino?',
        '⚠️ Desactivar Inquilino'
    );
    if (!confirmado) return;
    
    try {
        const formData = new FormData();
        formData.append('action', 'eliminar');
        formData.append('usuario_id', usuario_id);
        
        const response = await fetch('/proyectoEdificio/api/gestionar_usuario.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            alert('Inquilino desactivado exitosamente');
            location.reload();
        } else {
            alert('Error: ' + result.message);
        }
    } catch (error) {
        alert('Error de conexión: ' + error.message);
    }
}

async function activarInquilino(usuario_id) {
    const confirmado = await showConfirm(
        '¿Está seguro de activar este inquilino?',
        '✅ Activar Inquilino',
        'Activar',
        'btn-success'
    );
    if (!confirmado) return;
    
    try {
        const formData = new FormData();
        formData.append('action', 'restaurar');
        formData.append('usuario_id', usuario_id);
        
        const response = await fetch('/proyectoEdificio/api/gestionar_usuario.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            alert('Inquilino activado exitosamente');
            location.reload();
        } else {
            alert('Error: ' + result.message);
        }
    } catch (error) {
        alert('Error de conexión: ' + error.message);
    }
}

function cerrarModalDetalle() {
    document.getElementById('modalDetalle').style.display = 'none';
}

function cerrarModalEditar() {
    document.getElementById('modalEditar').style.display = 'none';
    document.getElementById('formEditarInquilino').reset();
}

// Los modales ya NO se cierran al hacer clic fuera
// Solo se pueden cerrar con el botón X o Cancelar
</script>

<?php include '../includes/admin_layout_end.php';
include '../includes/footer.php'; ?>

