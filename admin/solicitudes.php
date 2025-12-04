<?php
require_once '../includes/session.php';
require_once '../includes/permissions.php';
require_once '../config/database.php';

requiereAutenticacion('../login.php');

// Solo administradores totales pueden ver solicitudes
if (!esRol('Administrador Total')) {
    header('Location: ../index.php');
    exit;
}

$title = "Solicitudes de Acceso - Sistema de Edificios";
$pageTitle = "📝 Solicitudes de Acceso";
$useAdminLayout = true;
include '../includes/header.php';
include '../includes/admin_layout_start.php';
require_once '../config/constants.php';


$database = new Database();
$conn = $database->getConnection();

// Obtener todas las solicitudes
$sql = "SELECT * FROM solicitudes_acceso WHERE activo = 1 ORDER BY 
        CASE estado 
            WHEN '" . SOLICITUD_PENDIENTE . "' THEN 1 
            WHEN '" . SOLICITUD_APROBADA . "' THEN 2 
            WHEN '" . SOLICITUD_RECHAZADA . "' THEN 3 
        END, fecha_solicitud DESC";
$result = $conn->query($sql);
$solicitudes = $result->fetch_all(MYSQLI_ASSOC);

$conn->close();
?>

<section class="content-section">
    <h2 class="section-title">📬 Solicitudes de Acceso al Sistema</h2>
    
    <div class="stats-container" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
        <?php
        $pendientes = count(array_filter($solicitudes, fn($s) => strtoupper($s['estado']) === SOLICITUD_PENDIENTE));
        $aprobadas = count(array_filter($solicitudes, fn($s) => strtoupper($s['estado']) === SOLICITUD_APROBADA));
        $rechazadas = count(array_filter($solicitudes, fn($s) => strtoupper($s['estado']) === SOLICITUD_RECHAZADA));
        ?>
        <div class="stat-card" style="background: #fff3cd; padding: 1.5rem; border-radius: 8px; text-align: center;">
            <h3 style="margin: 0; color: #856404; font-size: 2rem;"><?php echo $pendientes; ?></h3>
            <p style="margin: 0.5rem 0 0 0; color: #856404;"><?php echo SOLICITUD_PENDIENTE; ?></p>
        </div>
        <div class="stat-card" style="background: #d4edda; padding: 1.5rem; border-radius: 8px; text-align: center;">
            <h3 style="margin: 0; color: #155724; font-size: 2rem;"><?php echo $aprobadas; ?></h3>
            <p style="margin: 0.5rem 0 0 0; color: #155724;"><?php echo SOLICITUD_APROBADA; ?></p>
        </div>
        <div class="stat-card" style="background: #f8d7da; padding: 1.5rem; border-radius: 8px; text-align: center;">
            <h3 style="margin: 0; color: #721c24; font-size: 2rem;"><?php echo $rechazadas; ?></h3>
            <p style="margin: 0.5rem 0 0 0; color: #721c24;"><?php echo SOLICITUD_RECHAZADA; ?></p>
        </div>
    </div>

    <!-- Filtro de Estado -->
    <div style="margin-bottom: 1.5rem; display: flex; align-items: center; gap: 1rem;">
        <label for="filtroEstado" style="font-weight: 600; color: var(--color-gray-700);">
            🔍 Filtrar por estado:
        </label>
        <select id="filtroEstado" onchange="filtrarSolicitudes()" class="form-control" style="max-width: 250px;">
            <option value="TODAS">Todas las solicitudes</option>
            <option value="<?php echo SOLICITUD_PENDIENTE; ?>" selected>Solo Pendientes</option>
            <option value="<?php echo SOLICITUD_APROBADA; ?>">Solo Aprobadas</option>
            <option value="<?php echo SOLICITUD_RECHAZADA; ?>">Solo Rechazadas</option>
        </select>
    </div>

    <?php if (count($solicitudes) === 0): ?>
        <div style="text-align: center; padding: 3rem; background: white; border-radius: 12px;">
            <p style="font-size: 1.2rem; color: #666;">📭 No hay solicitudes de acceso aún</p>
        </div>
    <?php else: ?>
        <div class="solicitudes-grid" style="display: grid; gap: 1.5rem;">
            <?php foreach ($solicitudes as $solicitud): ?>
                <div class="solicitud-card" style="background: white; padding: 2rem; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); border-left: 5px solid <?php 
                    echo strtoupper($solicitud['estado']) === SOLICITUD_PENDIENTE ? '#ffc107' : 
                        (strtoupper($solicitud['estado']) === SOLICITUD_APROBADA ? '#28a745' : '#dc3545'); 
                ?>;">
                    <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
                        <div style="flex: 1;">
                            <h3 style="margin: 0 0 0.5rem 0; color: #333; font-size: 1.4rem;">
                                🏢 <?php echo htmlspecialchars($solicitud['nombre_edificio']); ?>
                            </h3>
                            <p style="margin: 0; color: #666;">
                                👤 <strong><?php echo htmlspecialchars($solicitud['nombre_completo']); ?></strong>
                            </p>
                        </div>
                        <span style="padding: 0.5rem 1rem; border-radius: 20px; font-weight: 600; font-size: 0.9rem; <?php 
                            echo strtoupper($solicitud['estado']) === SOLICITUD_PENDIENTE ? 'background: #fff3cd; color: #856404;' : 
                                (strtoupper($solicitud['estado']) === SOLICITUD_APROBADA ? 'background: #d4edda; color: #155724;' : 'background: #f8d7da; color: #721c24;'); 
                        ?>">
                            <?php echo $solicitud['estado']; ?>
                        </span>
                    </div>
                    
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 1.5rem;">
                        <div>
                            <p style="margin: 0 0 0.25rem 0; color: #999; font-size: 0.9rem;">📧 Email</p>
                            <p style="margin: 0; color: #333; font-weight: 500;"><?php echo htmlspecialchars($solicitud['email']); ?></p>
                        </div>
                        <div>
                            <p style="margin: 0 0 0.25rem 0; color: #999; font-size: 0.9rem;">📱 Teléfono</p>
                            <p style="margin: 0; color: #333; font-weight: 500;"><?php echo htmlspecialchars($solicitud['telefono']); ?></p>
                        </div>
                        <?php if ($solicitud['num_departamentos']): ?>
                        <div>
                            <p style="margin: 0 0 0.25rem 0; color: #999; font-size: 0.9rem;">🚪 Departamentos</p>
                            <p style="margin: 0; color: #333; font-weight: 500;"><?php echo $solicitud['num_departamentos']; ?></p>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <?php if ($solicitud['direccion_edificio']): ?>
                    <div style="margin-bottom: 1rem;">
                        <p style="margin: 0 0 0.25rem 0; color: #999; font-size: 0.9rem;">📍 Dirección</p>
                        <p style="margin: 0; color: #333;"><?php echo htmlspecialchars($solicitud['direccion_edificio']); ?></p>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($solicitud['mensaje']): ?>
                    <div style="margin-bottom: 1.5rem; padding: 1rem; background: #f8f9fa; border-radius: 8px;">
                        <p style="margin: 0 0 0.5rem 0; color: #999; font-size: 0.9rem;">💬 Mensaje</p>
                        <p style="margin: 0; color: #666; white-space: pre-line;"><?php echo htmlspecialchars($solicitud['mensaje']); ?></p>
                    </div>
                    <?php endif; ?>
                    
                    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; padding-top: 1rem; border-top: 1px solid #e0e0e0;">
                        <small style="color: #999;">
                            📅 Solicitado: <?php echo date('d/m/Y H:i', strtotime($solicitud['fecha_solicitud'])); ?>
                        </small>
                        
                        <?php if (strtoupper($solicitud['estado']) === SOLICITUD_PENDIENTE): ?>
                        <div style="display: flex; gap: 0.5rem;">
                            <button onclick="aprobarSolicitud(<?php echo $solicitud['id']; ?>, '<?php echo htmlspecialchars($solicitud['nombre_completo']); ?>', '<?php echo htmlspecialchars($solicitud['email']); ?>')" 
                                    class="btn-action btn-edit" style="background: #28a745; color: white; border: none; padding: 0.5rem 1rem; border-radius: 6px; cursor: pointer; font-weight: 600;">
                                ✅ Aprobar
                            </button>
                            <button onclick="rechazarSolicitud(<?php echo $solicitud['id']; ?>, '<?php echo htmlspecialchars($solicitud['nombre_completo']); ?>')" 
                                    class="btn-action btn-delete" style="background: #dc3545; color: white; border: none; padding: 0.5rem 1rem; border-radius: 6px; cursor: pointer; font-weight: 600;">
                                ❌ Rechazar
                            </button>
                        </div>
                        <?php elseif ($solicitud['fecha_respuesta']): ?>
                        <small style="color: #666;">
                            Respondido: <?php echo date('d/m/Y H:i', strtotime($solicitud['fecha_respuesta'])); ?>
                        </small>
                        <?php endif; ?>
                    </div>
                    
                    <?php if (strtoupper($solicitud['estado']) === SOLICITUD_RECHAZADA && !empty($solicitud['respuesta_admin'])): ?>
                    <div style="margin-top: 1rem; padding: 1rem; background: #fff3cd; border-left: 4px solid #ffc107; border-radius: 6px;">
                        <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem;">
                            <span style="font-size: 1.2rem;">💬</span>
                            <strong style="color: #856404;">Motivo del rechazo:</strong>
                        </div>
                        <p style="margin: 0; color: #856404; line-height: 1.5; white-space: pre-line;">
                            <?php echo htmlspecialchars($solicitud['respuesta_admin']); ?>
                        </p>
                    </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

<!-- Modal para Rechazar Solicitud -->
<div id="modalRechazar" class="modal">
    <div class="modal-content modal-sm">
        <span class="close" onclick="cerrarModalRechazar()">&times;</span>
        <div class="modal-header">
            <h3>❌ Rechazar Solicitud</h3>
        </div>
        <form id="formRechazar" onsubmit="return procesarRechazo(event);">
            <div class="modal-body">
                <p id="modalRechazarNombre" style="margin-bottom: 1.5rem; font-weight: 600; color: var(--color-gray-700);"></p>
                <div class="form-group">
                    <label for="motivoRechazo">Motivo del rechazo (opcional)</label>
                    <textarea id="motivoRechazo" 
                              name="motivo" 
                              rows="4" 
                              placeholder="Explica brevemente por qué se rechaza esta solicitud..."></textarea>
                    <small>Describe el motivo del rechazo para mantener un registro</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" onclick="cerrarModalRechazar()" class="btn btn-light">Cancelar</button>
                <button type="submit" class="btn btn-primary">Rechazar Solicitud</button>
            </div>
        </form>
    </div>
</div>
</section>

<script>
(function() {
// Función para filtrar solicitudes por estado
window.filtrarSolicitudes = function() {
    const filtro = document.getElementById('filtroEstado').value;
    const solicitudes = document.querySelectorAll('.solicitud-card');
    
    solicitudes.forEach(card => {
        const estadoBadge = card.querySelector('span[style*="border-radius: 20px"]');
        if (!estadoBadge) return;
        
        const estado = estadoBadge.textContent.trim().toUpperCase();
        
        if (filtro === 'TODAS') {
            card.parentElement.style.display = 'block';
        } else {
            if (estado === filtro) {
                card.parentElement.style.display = 'block';
            } else {
                card.parentElement.style.display = 'none';
            }
        }
    });
}

// Aplicar filtro al cargar la página (mostrar solo pendientes por defecto)
document.addEventListener('DOMContentLoaded', function() {
    filtrarSolicitudes();
});

window.aprobarSolicitud = async function(id, nombre, email) {
    const confirmado = await showConfirm(
        `¿Aprobar solicitud de ${nombre}?\n\nEsto te redirigirá a crear un nuevo usuario con estos datos.`,
        '✅ Aprobar Solicitud',
        'Aprobar',
        'btn-success'
    );
    if (confirmado) {
        window.location.href = `/proyectoEdificio/admin/usuarios.php?crear_desde_solicitud=${id}&nombre=${encodeURIComponent(nombre)}&email=${encodeURIComponent(email)}`;
    }
}

let solicitudActualId = null;

window.rechazarSolicitud = function(id, nombre) {
    solicitudActualId = id;
    document.getElementById('modalRechazarNombre').textContent = `¿Estás seguro de rechazar la solicitud de ${nombre}?`;
    document.getElementById('motivoRechazo').value = '';
    document.getElementById('modalRechazar').classList.add('active');
}

window.cerrarModalRechazar = function() {
    document.getElementById('modalRechazar').classList.remove('active');
    solicitudActualId = null;
}

window.procesarRechazo = async function(event) {
    event.preventDefault();
    
    const motivo = document.getElementById('motivoRechazo').value.trim();
    const id = solicitudActualId;
    
    if (!id) return false;
    
    try {
        const formData = new FormData();
        formData.append('id', id);
        formData.append('accion', 'rechazar');
        formData.append('motivo', motivo);
        
        const response = await fetch('/proyectoEdificio/api/gestionar_solicitud.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            cerrarModalRechazar();
            location.reload();
        } else {
            console.error('Error:', data.message);
            cerrarModalRechazar();
        }
    } catch (error) {
        console.error('Error al procesar la solicitud:', error);
        cerrarModalRechazar();
    }
    
    return false;
}
})();
</script>

<?php include '../includes/admin_layout_end.php';
include '../includes/admin_layout_end.php';
include '../includes/footer.php'; ?>
