<?php
require_once '../includes/session.php';
require_once '../includes/permissions.php';
require_once '../config/database.php';

requiereAutenticacion('../login.php');
requierePermiso('gestionar_avisos'); // Redirige a acceso_denegado.php automáticamente

$title = "Gestión de Avisos - Sistema de Edificios";
$pageTitle = "📢 Gestión de Avisos";
$useAdminLayout = true;
include '../includes/header.php';
include '../includes/admin_layout_start.php';


// Obtener edificios según el rol del usuario
$database = new Database();
$conn = $database->getConnection();

$rol_nombre = $_SESSION['rol_nombre'] ?? '';
$usuario_id = $_SESSION['usuario_id'] ?? 0;
$es_admin_total = ($rol_nombre === 'Administrador Total');

$edificios = [];
if ($es_admin_total) {
    // Administrador Total ve todos los edificios
    $result = $conn->query("SELECT id, nombre FROM edificios WHERE activo = 1 ORDER BY nombre");
    while ($row = $result->fetch_assoc()) {
        $edificios[] = $row;
    }
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
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $edificios[] = $row;
    }
    $stmt->close();
}

$conn->close();
?>

<section class="section-white">
    <div class="container">
        <h2 class="section-title">📢 Gestión de Avisos</h2>
        
        <div class="avisos-header">
            <button onclick="nuevoAviso()" class="btn btn-success">+ Nuevo Aviso</button>
            <div class="d-flex gap-2">
                <select id="filtroTipo" onchange="filtrarAvisos()" class="form-select">
                    <option value="">Todos los tipos</option>
                    <option value="INFORMATIVO">Informativo</option>
                    <option value="URGENTE">Urgente</option>
                    <option value="MANTENIMIENTO">Mantenimiento</option>
                    <option value="EVENTO">Evento</option>
                </select>
                <select id="filtroEdificio" onchange="filtrarAvisos()" class="form-select">
                    <option value="">Todos los edificios</option>
                    <option value="null">Avisos generales</option>
                    <?php foreach ($edificios as $edificio): ?>
                        <option value="<?php echo $edificio['id']; ?>"><?php echo htmlspecialchars($edificio['nombre']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        
        <div class="table-responsive">
            <table id="tablaAvisos" class="table">
                <thead>
                    <tr>
                        <th>Edificio</th>
                        <th>Título</th>
                        <th>Tipo</th>
                        <th>Fecha Publicación</th>
                        <th>Vencimiento</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody id="avisosList">
                    <tr><td colspan="7" style="text-align: center; padding: 2rem;">Cargando avisos...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</section>

<!-- Modal para crear/editar aviso -->
<div id="modalAviso" class="modal">
    <div class="modal-content">
        <span class="close" onclick="cerrarModal()">&times;</span>
        <div class="modal-header">
            <h3 id="modalTitulo">Nuevo Aviso</h3>
        </div>
        <form id="formAviso" onsubmit="guardarAviso(event)">
        <div class="modal-body">
            <input type="hidden" id="aviso_id" name="id">
            
            <div class="form-group">
                <label for="edificio_id">Edificio</label>
                <select id="edificio_id" name="edificio_id">
                    <option value="">Todos los edificios (General)</option>
                    <?php foreach ($edificios as $edificio): ?>
                        <option value="<?php echo $edificio['id']; ?>"><?php echo htmlspecialchars($edificio['nombre']); ?></option>
                    <?php endforeach; ?>
                </select>
                <small class="form-hint">Selecciona un edificio específico o déjalo vacío para un aviso general</small>
            </div>
            
            <div class="form-group">
                <label for="titulo">Título *</label>
                <input type="text" id="titulo" name="titulo" required maxlength="255">
            </div>
            
            <div class="form-group">
                <label for="contenido">Contenido *</label>
                <textarea id="contenido" name="contenido" required rows="5"></textarea>
            </div>
            
            <div class="form-group">
                <label for="tipo">Tipo de Aviso *</label>
                <select id="tipo" name="tipo" required>
                    <option value="INFORMATIVO">Informativo</option>
                    <option value="URGENTE">Urgente</option>
                    <option value="MANTENIMIENTO">Mantenimiento</option>
                    <option value="EVENTO">Evento</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="fecha_vencimiento">Fecha de Vencimiento (Opcional)</label>
                <input type="date" id="fecha_vencimiento" name="fecha_vencimiento">
                <small class="form-hint">Después de esta fecha el aviso no se mostrará a los usuarios</small>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" onclick="cerrarModal()" class="btn btn-light">Cancelar</button>
            <button type="submit" class="btn btn-success">Guardar</button>
        </div>
        </form>
    </div>
</div>

<style>
.tipo-badge {
    display: inline-block;
    padding: 0.25rem 0.75rem;
    border-radius: 12px;
    font-size: 0.85rem;
    font-weight: 600;
}

.tipo-INFORMATIVO {
    background: var(--color-info-light);
    color: var(--color-info-dark);
}

.tipo-URGENTE {
    background: var(--color-danger-light);
    color: var(--color-danger-dark);
}

.tipo-MANTENIMIENTO {
    background: var(--color-warning-light);
    color: var(--color-warning-dark);
}

.tipo-EVENTO {
    background: var(--color-success-light);
    color: var(--color-success-dark);
}

.estado-activo {
    color: var(--color-success);
    font-weight: 600;
}

.estado-inactivo {
    color: var(--color-danger);
    font-weight: 600;
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
    background-color: rgba(0, 0, 0, 0.5);
}

.modal-content {
    background-color: var(--color-white);
    margin: 5% auto;
    padding: 2rem;
    border-radius: var(--border-radius-lg);
    width: 90%;
    max-width: 600px;
    box-shadow: var(--shadow-2xl);
}
</style>

<script>
(function() {
    // Scope aislado para evitar conflictos al cargar dinámicamente
    let avisosData = [];

    // Cargar avisos al iniciar
    cargarAvisos();

    async function cargarAvisos() {
    try {
        const response = await fetch('/proyectoEdificio/api/gestionar_avisos.php?accion=listar');
        const data = await response.json();
        
        if (data.success) {
            avisosData = data.avisos;
            mostrarAvisos(avisosData);
        } else {
            alert('Error al cargar avisos: ' + data.message);
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Error al cargar avisos');
    }
    }

    function mostrarAvisos(avisos) {
        const tbody = document.getElementById('avisosList');
    
    if (avisos.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" style="text-align: center; padding: 2rem; color: #666;">No hay avisos para mostrar</td></tr>';
        return;
    }
    
    tbody.innerHTML = avisos.map(aviso => `
        <tr>
            <td data-label="Edificio">${aviso.edificio_nombre || 'Todos'}</td>
            <td data-label="Título"><strong>${aviso.titulo}</strong></td>
            <td data-label="Tipo"><span class="tipo-badge tipo-${aviso.tipo}">${aviso.tipo}</span></td>
            <td data-label="Fecha Publicación">${formatearFecha(aviso.fecha_publicacion)}</td>
            <td data-label="Vencimiento">${aviso.fecha_vencimiento ? formatearFecha(aviso.fecha_vencimiento) : '-'}</td>
            <td data-label="Estado" class="${aviso.activo ? 'estado-activo' : 'estado-inactivo'}">${aviso.activo ? 'Activo' : 'Inactivo'}</td>
            <td data-label="Acciones">
                <button onclick="editarAviso(${aviso.id})" class="btn-action btn-edit">Editar</button>
                ${aviso.activo ? `<button onclick="eliminarAviso(${aviso.id})" class="btn-action btn-delete">Eliminar</button>` : ''}
            </td>
        </tr>
    `).join('');
    }

    function filtrarAvisos() {
        const filtroTipo = document.getElementById('filtroTipo').value;
        const filtroEdificio = document.getElementById('filtroEdificio').value;
        
        let avisosFiltrados = avisosData;
    
    if (filtroTipo) {
        avisosFiltrados = avisosFiltrados.filter(a => a.tipo === filtroTipo);
    }
    
    if (filtroEdificio) {
        if (filtroEdificio === 'null') {
            avisosFiltrados = avisosFiltrados.filter(a => a.edificio_id === null);
        } else {
            avisosFiltrados = avisosFiltrados.filter(a => a.edificio_id == filtroEdificio);
        }
    }
    
        mostrarAvisos(avisosFiltrados);
    }

    function nuevoAviso() {
        document.getElementById('modalTitulo').textContent = 'Nuevo Aviso';
        document.getElementById('formAviso').reset();
        document.getElementById('aviso_id').value = '';
        document.getElementById('modalAviso').style.display = 'block';
    }

    async function editarAviso(id) {
    try {
        const response = await fetch(`/proyectoEdificio/api/gestionar_avisos.php?accion=obtener&id=${id}`);
        const data = await response.json();
        
        if (data.success) {
            const aviso = data.aviso;
            document.getElementById('modalTitulo').textContent = 'Editar Aviso';
            document.getElementById('aviso_id').value = aviso.id;
            document.getElementById('edificio_id').value = aviso.edificio_id || '';
            document.getElementById('titulo').value = aviso.titulo;
            document.getElementById('contenido').value = aviso.contenido;
            document.getElementById('tipo').value = aviso.tipo;
            document.getElementById('fecha_vencimiento').value = aviso.fecha_vencimiento || '';
            document.getElementById('modalAviso').style.display = 'block';
        } else {
            alert('Error al cargar aviso: ' + data.message);
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Error al cargar aviso');
        }
    }

    async function guardarAviso(event) {
        event.preventDefault();
        
        const formData = new FormData(document.getElementById('formAviso'));
        const id = formData.get('id');
        formData.append('accion', id ? 'editar' : 'crear');
    
    try {
        const response = await fetch('/proyectoEdificio/api/gestionar_avisos.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            alert(data.message);
            cerrarModal();
            cargarAvisos();
        } else {
            alert('Error: ' + data.message);
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Error al guardar aviso');
        }
    }

    async function eliminarAviso(id) {
        const confirmado = await showConfirm(
            '¿Estás seguro de que deseas eliminar este aviso?',
            '🗑️ Eliminar Aviso'
        );
        if (!confirmado) return;
    
    try {
        const formData = new FormData();
        formData.append('accion', 'eliminar');
        formData.append('id', id);
        
        const response = await fetch('/proyectoEdificio/api/gestionar_avisos.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            alert(data.message);
            cargarAvisos();
        } else {
            alert('Error: ' + data.message);
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Error al eliminar aviso');
        }
    }

    function cerrarModal() {
        document.getElementById('modalAviso').style.display = 'none';
        document.getElementById('formAviso').reset();
    }

    function formatearFecha(fecha) {
        if (!fecha) return '-';
        const d = new Date(fecha);
        return d.toLocaleDateString('es-ES', { year: 'numeric', month: '2-digit', day: '2-digit' });
    }

    // El modal ya NO se cierra al hacer clic fuera
    // Solo se puede cerrar con el botón X o Cancelar

    // Exponer funciones necesarias al scope global para onclick en HTML
    window.nuevoAviso = nuevoAviso;
    window.editarAviso = editarAviso;
    window.guardarAviso = guardarAviso;
    window.eliminarAviso = eliminarAviso;
    window.cerrarModal = cerrarModal;
    window.filtrarAvisos = filtrarAvisos;
})();
</script>

<?php include '../includes/admin_layout_end.php';
include '../includes/admin_layout_end.php';
include '../includes/footer.php'; ?>
