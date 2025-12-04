<?php
require_once '../includes/session.php';
require_once '../includes/permissions.php';
require_once '../config/database.php';

requiereAutenticacion('../login.php');
requierePermiso('gestionar_incidencias');

$title = "Gestión de Incidencias - Sistema de Edificios";
$pageTitle = "🔧 Gestión de Incidencias";
$useAdminLayout = true;
include '../includes/header.php';
include '../includes/admin_layout_start.php';
?>
<link rel="stylesheet" href="../css/modal-styles.css">
<?php


$database = new Database();
$conn = $database->getConnection();

$rol_nombre = $_SESSION['rol_nombre'] ?? '';
$usuario_id = $_SESSION['usuario_id'] ?? 0;
$es_admin_total = ($rol_nombre === 'Administrador Total');

// Obtener filtros
$filtro_edificio = $_GET['edificio'] ?? '';
$filtro_estado = $_GET['estado'] ?? '';
$filtro_prioridad = $_GET['prioridad'] ?? '';
$filtro_tipo = $_GET['tipo'] ?? '';

// Query base
$sql = "
    SELECT i.*, 
           u.nombre as usuario_nombre, u.email as usuario_email,
           e.nombre as edificio_nombre,
           (SELECT COUNT(*) FROM comentarios_incidencia WHERE incidencia_id = i.id) as num_comentarios
    FROM incidencias i
    INNER JOIN usuarios u ON i.usuario_id = u.id
    INNER JOIN edificios e ON i.edificio_id = e.id
    WHERE i.activo = 1
";

$params = [];
$types = '';

// Filtros para admin edificio
if (!$es_admin_total) {
    $sql .= " AND e.id IN (
        SELECT edificio_id FROM usuario_edificios 
        WHERE usuario_id = ? AND activo = 1
    )";
    $params[] = $usuario_id;
    $types .= 'i';
}

// Aplicar filtros
if ($filtro_edificio) {
    $sql .= " AND i.edificio_id = ?";
    $params[] = $filtro_edificio;
    $types .= 'i';
}

if ($filtro_estado) {
    $sql .= " AND i.estado = ?";
    $params[] = $filtro_estado;
    $types .= 's';
}

if ($filtro_prioridad) {
    $sql .= " AND i.prioridad = ?";
    $params[] = $filtro_prioridad;
    $types .= 's';
}

if ($filtro_tipo) {
    $sql .= " AND i.tipo_incidencia = ?";
    $params[] = $filtro_tipo;
    $types .= 's';
}

$sql .= " ORDER BY 
    CASE i.prioridad 
        WHEN 'ALTA' THEN 1 
        WHEN 'MEDIA' THEN 2 
        WHEN 'BAJA' THEN 3 
    END,
    CASE i.estado
        WHEN 'PENDIENTE' THEN 1
        WHEN 'EN_PROCESO' THEN 2
        WHEN 'RESUELTA' THEN 3
    END,
    i.fecha_creacion DESC
";

if (count($params) > 0) {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query($sql);
}

$incidencias = [];
while ($row = $result->fetch_assoc()) {
    $incidencias[] = $row;
}

// Obtener edificios para filtro
if ($es_admin_total) {
    $edificios_result = $conn->query("SELECT id, nombre FROM edificios WHERE activo = 1 ORDER BY nombre");
} else {
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
}

$edificios = [];
while ($row = $edificios_result->fetch_assoc()) {
    $edificios[] = $row;
}

$conn->close();

// Estadísticas
$stats_pendientes = count(array_filter($incidencias, fn($i) => $i['estado'] === 'PENDIENTE'));
$stats_proceso = count(array_filter($incidencias, fn($i) => $i['estado'] === 'EN_PROCESO'));
$stats_resueltas = count(array_filter($incidencias, fn($i) => $i['estado'] === 'RESUELTA'));
$stats_alta_prioridad = count(array_filter($incidencias, fn($i) => $i['prioridad'] === 'ALTA' && $i['estado'] !== 'RESUELTA'));
?>

<section class="section-white">
    <div class="container">
        <h2 class="section-title">🛠️ Gestión de Incidencias</h2>
    
    <!-- Estadísticas -->
    <div class="stats-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
        <div class="stat-card" style="background: #fff3cd; padding: 1.5rem; border-radius: 8px; text-align: center;">
            <h3 style="margin: 0; color: #856404; font-size: 2rem;"><?php echo $stats_pendientes; ?></h3>
            <p style="margin: 0.5rem 0 0 0; color: #856404;">Pendientes</p>
        </div>
        <div class="stat-card" style="background: #cce5ff; padding: 1.5rem; border-radius: 8px; text-align: center;">
            <h3 style="margin: 0; color: #004085; font-size: 2rem;"><?php echo $stats_proceso; ?></h3>
            <p style="margin: 0.5rem 0 0 0; color: #004085;">En Proceso</p>
        </div>
        <div class="stat-card" style="background: #d4edda; padding: 1.5rem; border-radius: 8px; text-align: center;">
            <h3 style="margin: 0; color: #155724; font-size: 2rem;"><?php echo $stats_resueltas; ?></h3>
            <p style="margin: 0.5rem 0 0 0; color: #155724;">Resueltas</p>
        </div>
        <div class="stat-card" style="background: #f8d7da; padding: 1.5rem; border-radius: 8px; text-align: center;">
            <h3 style="margin: 0; color: #721c24; font-size: 2rem;"><?php echo $stats_alta_prioridad; ?></h3>
            <p style="margin: 0.5rem 0 0 0; color: #721c24;">Alta Prioridad</p>
        </div>
    </div>
    
    <!-- Filtros -->
    <div class="filtros-box" style="background: white; padding: 1.5rem; border-radius: 8px; margin-bottom: 2rem; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
        <h3 style="margin: 0 0 1rem 0;">🔍 Filtros</h3>
        <form method="GET" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
            <div>
                <label style="display: block; margin-bottom: 0.5rem; font-weight: bold;">Edificio:</label>
                <select name="edificio" style="width: 100%; padding: 0.5rem; border: 1px solid #ddd; border-radius: 4px;">
                    <option value="">Todos</option>
                    <?php foreach ($edificios as $edif): ?>
                        <option value="<?php echo $edif['id']; ?>" <?php echo $filtro_edificio == $edif['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($edif['nombre']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div>
                <label style="display: block; margin-bottom: 0.5rem; font-weight: bold;">Estado:</label>
                <select name="estado" style="width: 100%; padding: 0.5rem; border: 1px solid #ddd; border-radius: 4px;">
                    <option value="">Todos</option>
                    <option value="PENDIENTE" <?php echo $filtro_estado === 'PENDIENTE' ? 'selected' : ''; ?>>Pendiente</option>
                    <option value="EN_PROCESO" <?php echo $filtro_estado === 'EN_PROCESO' ? 'selected' : ''; ?>>En Proceso</option>
                    <option value="RESUELTA" <?php echo $filtro_estado === 'RESUELTA' ? 'selected' : ''; ?>>Resuelta</option>
                </select>
            </div>
            
            <div>
                <label style="display: block; margin-bottom: 0.5rem; font-weight: bold;">Prioridad:</label>
                <select name="prioridad" style="width: 100%; padding: 0.5rem; border: 1px solid #ddd; border-radius: 4px;">
                    <option value="">Todas</option>
                    <option value="BAJA" <?php echo $filtro_prioridad === 'BAJA' ? 'selected' : ''; ?>>Baja</option>
                    <option value="MEDIA" <?php echo $filtro_prioridad === 'MEDIA' ? 'selected' : ''; ?>>Media</option>
                    <option value="ALTA" <?php echo $filtro_prioridad === 'ALTA' ? 'selected' : ''; ?>>Alta</option>
                </select>
            </div>
            
            <div>
                <label style="display: block; margin-bottom: 0.5rem; font-weight: bold;">Tipo:</label>
                <select name="tipo" style="width: 100%; padding: 0.5rem; border: 1px solid #ddd; border-radius: 4px;">
                    <option value="">Todos</option>
                    <option value="MANTENIMIENTO" <?php echo $filtro_tipo === 'MANTENIMIENTO' ? 'selected' : ''; ?>>Mantenimiento</option>
                    <option value="LIMPIEZA" <?php echo $filtro_tipo === 'LIMPIEZA' ? 'selected' : ''; ?>>Limpieza</option>
                    <option value="SEGURIDAD" <?php echo $filtro_tipo === 'SEGURIDAD' ? 'selected' : ''; ?>>Seguridad</option>
                    <option value="RUIDO" <?php echo $filtro_tipo === 'RUIDO' ? 'selected' : ''; ?>>Ruido</option>
                    <option value="OTROS" <?php echo $filtro_tipo === 'OTROS' ? 'selected' : ''; ?>>Otros</option>
                </select>
            </div>
            
            <div style="display: flex; align-items: flex-end; gap: 0.5rem;">
                <button type="submit" style="flex: 1; padding: 0.5rem; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: bold;">
                    Aplicar
                </button>
                <a href="incidencias.php" style="flex: 1; padding: 0.5rem; background: #6c757d; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: bold; text-align: center; text-decoration: none; display: block;">
                    Limpiar
                </a>
            </div>
        </form>
    </div>
    
    <!-- Lista de incidencias -->
    <?php if (count($incidencias) === 0): ?>
        <div class="alert alert-info" style="padding: 2rem; background: #d1ecf1; border: 1px solid #bee5eb; border-radius: 8px; text-align: center;">
            No se encontraron incidencias con los filtros seleccionados
        </div>
    <?php else: ?>
        <div class="incidencias-grid" style="display: grid; gap: 1.5rem;">
            <?php foreach ($incidencias as $inc): ?>
                <?php
                $estado_colores = [
                    'PENDIENTE' => ['bg' => '#fff3cd', 'border' => '#ffc107', 'text' => '#856404'],
                    'EN_PROCESO' => ['bg' => '#cce5ff', 'border' => '#007bff', 'text' => '#004085'],
                    'RESUELTA' => ['bg' => '#d4edda', 'border' => '#28a745', 'text' => '#155724']
                ];
                $prioridad_colores = [
                    'BAJA' => '#28a745',
                    'MEDIA' => '#ffc107',
                    'ALTA' => '#dc3545'
                ];
                $colores = $estado_colores[$inc['estado']] ?? ['bg' => '#f8f9fa', 'border' => '#6c757d', 'text' => '#495057'];
                $color_prioridad = $prioridad_colores[$inc['prioridad']] ?? '#6c757d';
                ?>
                
                <div class="incidencia-card" style="background: white; border-left: 4px solid <?php echo $colores['border']; ?>; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); overflow: hidden; cursor: pointer; transition: transform 0.2s;" onclick="verDetalleIncidencia(<?php echo $inc['id']; ?>)">
                    <div class="inc-header" style="background: <?php echo $colores['bg']; ?>; padding: 1rem; display: flex; justify-content: space-between; align-items: start;">
                        <div style="flex: 1;">
                            <h3 style="margin: 0; color: <?php echo $colores['text']; ?>;">#<?php echo $inc['id']; ?> - <?php echo htmlspecialchars($inc['tipo_incidencia']); ?></h3>
                            <p style="margin: 0.5rem 0 0 0; font-size: 0.9rem; opacity: 0.8;">
                                <?php echo htmlspecialchars($inc['edificio_nombre']); ?>
                            </p>
                        </div>
                        <div style="display: flex; gap: 0.5rem; flex-direction: column; align-items: flex-end;">
                            <span style="background: <?php echo $colores['border']; ?>; color: white; padding: 0.25rem 0.75rem; border-radius: 12px; font-size: 0.85rem; font-weight: bold;">
                                <?php echo $inc['estado']; ?>
                            </span>
                            <span style="background: <?php echo $color_prioridad; ?>; color: white; padding: 0.25rem 0.75rem; border-radius: 12px; font-size: 0.85rem; font-weight: bold;">
                                <?php echo $inc['prioridad']; ?>
                            </span>
                        </div>
                    </div>
                    
                    <div class="inc-body" style="padding: 1.5rem;">
                        <p style="margin: 0 0 1rem 0; line-height: 1.6;">
                            <?php echo nl2br(htmlspecialchars(substr($inc['descripcion'], 0, 150))); ?>
                            <?php echo strlen($inc['descripcion']) > 150 ? '...' : ''; ?>
                        </p>
                        
                        <div style="display: flex; justify-content: space-between; align-items: center; padding-top: 1rem; border-top: 1px solid #ddd;">
                            <div>
                                <p style="margin: 0; font-size: 0.9rem; color: #666;">
                                    👤 <strong><?php echo htmlspecialchars($inc['usuario_nombre']); ?></strong>
                                </p>
                                <p style="margin: 0.25rem 0 0 0; font-size: 0.85rem; color: #999;">
                                    📅 <?php echo date('d/m/Y H:i', strtotime($inc['fecha_creacion'])); ?>
                                </p>
                            </div>
                            <div style="text-align: right;">
                                <?php if ($inc['num_comentarios'] > 0): ?>
                                    <span style="background: #007bff; color: white; padding: 0.25rem 0.5rem; border-radius: 8px; font-size: 0.85rem;">
                                        💬 <?php echo $inc['num_comentarios']; ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<!-- Modal Detalle de Incidencia -->
<div id="modalIncidencia" class="modal" style="display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.5);">
    <div class="modal-content" style="background-color: #fefefe; margin: 2% auto; padding: 0; border-radius: 8px; width: 90%; max-width: 800px; max-height: 90vh; overflow-y: auto;">
        <div class="modal-header" style="padding: 1.5rem; background: #007bff; color: white; border-radius: 8px 8px 0 0; display: flex; justify-content: space-between; align-items: center;">
            <h2 style="margin: 0;">Detalle de Incidencia</h2>
            <span class="close" onclick="cerrarModalIncidencia()" style="font-size: 2rem; font-weight: bold; cursor: pointer;">&times;</span>
        </div>
        
        <div id="contenidoModalIncidencia" style="padding: 2rem;">
            <div style="text-align: center; padding: 2rem;">
                <p>Cargando...</p>
            </div>
        </div>
    </div>
</div>



<script>
(function() {
window.verDetalleIncidencia = async function(incidenciaId) {
    const modal = document.getElementById('modalIncidencia');
    const contenido = document.getElementById('contenidoModalIncidencia');
    
    modal.style.display = 'block';
    contenido.innerHTML = '<div style="text-align: center; padding: 2rem;"><p>Cargando...</p></div>';
    
    try {
        const response = await fetch(`../api/get_incidencia.php?id=${incidenciaId}`);
        const data = await response.json();
        
        if (data.success) {
            mostrarDetalleIncidencia(data.incidencia, data.comentarios);
        } else {
            contenido.innerHTML = '<div class="alert alert-danger">Error: ' + data.message + '</div>';
        }
    } catch (error) {
        console.error('Error:', error);
        contenido.innerHTML = '<div class="alert alert-danger">Error al cargar la incidencia</div>';
    }
}

function mostrarDetalleIncidencia(inc, comentarios) {
    const estado_colores = {
        'PENDIENTE': '#ffc107',
        'EN_PROCESO': '#007bff',
        'RESUELTA': '#28a745'
    };
    
    const prioridad_colores = {
        'BAJA': '#28a745',
        'MEDIA': '#ffc107',
        'ALTA': '#dc3545'
    };
    
    let html = `
        <div class="detalle-incidencia">
            <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 1.5rem; padding-bottom: 1rem; border-bottom: 2px solid #ddd;">
                <div>
                    <h3 style="margin: 0;">Incidencia #${inc.id}</h3>
                    <p style="margin: 0.5rem 0 0 0; color: #666;">${inc.edificio_nombre}</p>
                </div>
                <div style="display: flex; gap: 0.5rem; flex-direction: column; align-items: flex-end;">
                    <span style="background: ${estado_colores[inc.estado]}; color: white; padding: 0.5rem 1rem; border-radius: 12px; font-weight: bold;">
                        ${inc.estado}
                    </span>
                    <span style="background: ${prioridad_colores[inc.prioridad]}; color: white; padding: 0.5rem 1rem; border-radius: 12px; font-weight: bold;">
                        Prioridad: ${inc.prioridad}
                    </span>
                </div>
            </div>
            
            <div style="margin-bottom: 1.5rem;">
                <p><strong>Tipo:</strong> ${inc.tipo_incidencia}</p>
                <p><strong>Reportado por:</strong> ${inc.usuario_nombre} (${inc.usuario_email})</p>
                <p><strong>Fecha:</strong> ${new Date(inc.fecha_creacion).toLocaleString('es-ES')}</p>
                
                ${inc.ubicacion ? `<p><strong>Ubicación:</strong> ${inc.ubicacion}</p>` : ''}
            </div>
            
            <div style="background: #f8f9fa; padding: 1rem; border-radius: 4px; margin-bottom: 1.5rem;">
                <strong>Descripción:</strong>
                <p style="margin: 0.5rem 0 0 0; line-height: 1.6;">${inc.descripcion.replace(/\n/g, '<br>')}</p>
            </div>
            
            ${inc.imagen_url ? `
                <div style="margin-bottom: 1.5rem;">
                    <strong>Imagen adjunta:</strong><br>
                    <img src="../${inc.imagen_url}" style="max-width: 100%; height: auto; margin-top: 0.5rem; border-radius: 4px;" alt="Imagen de incidencia">
                </div>
            ` : ''}
            
            ${inc.estado !== 'RESUELTA' ? `
                <div style="margin-bottom: 1.5rem; padding: 1rem; background: #fff3cd; border-radius: 4px;">
                    <strong>Cambiar Estado:</strong>
                    <div style="display: flex; gap: 0.5rem; margin-top: 0.5rem;">
                        ${inc.estado === 'PENDIENTE' ? `
                            <button onclick="cambiarEstado(${inc.id}, 'EN_PROCESO')" style="flex: 1; padding: 0.75rem; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: bold;">
                                ▶️ Iniciar Proceso
                            </button>
                        ` : ''}
                        ${inc.estado === 'EN_PROCESO' ? `
                            <button onclick="cambiarEstado(${inc.id}, 'RESUELTA')" style="flex: 1; padding: 0.75rem; background: #28a745; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: bold;">
                                ✅ Marcar como Resuelta
                            </button>
                        ` : ''}
                    </div>
                </div>
            ` : ''}
            
            <div style="margin-top: 2rem; padding-top: 1.5rem; border-top: 2px solid #ddd;">
                <h4>💬 Comentarios (${comentarios.length})</h4>
                
                <div id="listaComentarios" style="margin-top: 1rem; max-height: 300px; overflow-y: auto;">
                    ${comentarios.length === 0 ? '<p style="text-align: center; color: #999;">No hay comentarios</p>' : comentarios.map(c => `
                        <div style="background: #f8f9fa; padding: 1rem; border-radius: 4px; margin-bottom: 1rem;">
                            <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                                <strong>${c.usuario_nombre}</strong>
                                <small style="color: #666;">${new Date(c.fecha_creacion).toLocaleString('es-ES')}</small>
                            </div>
                            <p style="margin: 0;">${c.comentario.replace(/\n/g, '<br>')}</p>
                        </div>
                    `).join('')}
                </div>
                
                <div style="margin-top: 1rem;">
                    <textarea id="nuevoComentario" rows="3" placeholder="Escribe un comentario..." style="width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 4px; resize: vertical;"></textarea>
                    <button onclick="agregarComentario(${inc.id})" style="margin-top: 0.5rem; padding: 0.75rem 1.5rem; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: bold;">
                        Agregar Comentario
                    </button>
                </div>
            </div>
        </div>
    `;
    
    document.getElementById('contenidoModalIncidencia').innerHTML = html;
}

window.cambiarEstado = async function(incidenciaId, nuevoEstado) {
    const confirmado = await showConfirm(
        '¿Cambiar el estado de esta incidencia?',
        '🔄 Cambiar Estado',
        'Cambiar',
        'btn-primary'
    );
    if (!confirmado) return;
    
    try {
        const formData = new FormData();
        formData.append('action', 'cambiar_estado');
        formData.append('incidencia_id', incidenciaId);
        formData.append('nuevo_estado', nuevoEstado);
        
        const response = await fetch('../api/gestionar_incidencia.php', {
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
        showToast('Error al cambiar el estado', 'error');
    }
}

window.agregarComentario = async function(incidenciaId) {
    const textarea = document.getElementById('nuevoComentario');
    const comentario = textarea.value.trim();
    
    if (!comentario) {
        showToast('Por favor escribe un comentario', 'warning');
        return;
    }
    
    try {
        const formData = new FormData();
        formData.append('action', 'agregar_comentario');
        formData.append('incidencia_id', incidenciaId);
        formData.append('comentario', comentario);
        
        const response = await fetch('../api/gestionar_incidencia.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            // Recargar detalles
            verDetalleIncidencia(incidenciaId);
        } else {
            showToast('Error: ' + data.message, 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showToast('Error al agregar comentario', 'error');
    }
}

window.cerrarModalIncidencia = function() {
    document.getElementById('modalIncidencia').style.display = 'none';
}

// Los modales ya NO se cierran al hacer clic fuera
// Solo se puede cerrar con el botón X o Cancelar
})();
</script>

<?php
include '../includes/admin_layout_end.php';
include '../includes/footer.php';
?>
