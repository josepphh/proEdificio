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
?>
<link rel="stylesheet" href="../css/modal-styles.css">
<?php
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
        <select id="filtroEstado" onchange="filtrarSolicitudes()" style="width: 300px;">
            <option value="TODAS">Todas las solicitudes</option>
            <option value="<?php echo SOLICITUD_PENDIENTE; ?>" selected>Solo Pendientes</option>
            <option value="<?php echo SOLICITUD_APROBADA; ?>">Solo Aprobadas</option>
            <option value="<?php echo SOLICITUD_RECHAZADA; ?>">Solo Rechazadas</option>
        </select>
    </div>

    <?php if (count($solicitudes) === 0): ?>
        <div class="empty-state">
            <div style="font-size: 4rem; margin-bottom: 1rem;">📭</div>
            <h3 style="margin: 0 0 0.5rem 0;">No hay solicitudes de acceso</h3>
            <p style="margin: 0; color: var(--color-gray-500);">Las nuevas solicitudes aparecerán aquí</p>
        </div>
    <?php else: ?>
        <div class="solicitudes-grid">
            <?php foreach ($solicitudes as $solicitud): ?>
                <?php
                $isPendiente = strtoupper($solicitud['estado']) === SOLICITUD_PENDIENTE;
                $isAprobada = strtoupper($solicitud['estado']) === SOLICITUD_APROBADA;
                $isRechazada = strtoupper($solicitud['estado']) === SOLICITUD_RECHAZADA;
                
                $statusIcon = $isPendiente ? '⏳' : ($isAprobada ? '✅' : '❌');
                $statusClass = $isPendiente ? 'status-pendiente' : ($isAprobada ? 'status-aprobada' : 'status-rechazada');
                ?>
                
                <div class="solicitud-card-v2" data-estado="<?php echo $solicitud['estado']; ?>">
                    <!-- Header con estado y edificio -->
                    <div class="card-header-v2">
                        <div class="status-badge-v2 <?php echo $statusClass; ?>">
                            <span class="status-icon-v2"><?php echo $statusIcon; ?></span>
                            <span class="status-text-v2"><?php echo $solicitud['estado']; ?></span>
                        </div>
                        <div class="edificio-badge-v2">
                            <span class="edificio-icon-v2">🏢</span>
                            <span class="edificio-nombre-v2"><?php echo htmlspecialchars($solicitud['nombre_edificio']); ?></span>
                        </div>
                    </div>
                    
                    <!-- Solicitante destacado -->
                    <div class="solicitante-section-v2">
                        <div class="solicitante-label-v2">
                            <span class="solicitante-icon-v2">👤</span>
                            <span>SOLICITANTE</span>
                        </div>
                        <div class="solicitante-nombre-v2">
                            <?php echo htmlspecialchars($solicitud['nombre_completo']); ?>
                        </div>
                    </div>
                    
                    <!-- Grid de información -->
                    <div class="info-grid-v2">
                        <div class="info-item-v2">
                            <div class="info-icon-box-v2">📧</div>
                            <div class="info-content-v2">
                                <div class="info-label-v2">EMAIL</div>
                                <div class="info-value-v2"><?php echo htmlspecialchars($solicitud['email']); ?></div>
                            </div>
                        </div>
                        
                        <div class="info-item-v2">
                            <div class="info-icon-box-v2">📱</div>
                            <div class="info-content-v2">
                                <div class="info-label-v2">TELÉFONO</div>
                                <div class="info-value-v2"><?php echo htmlspecialchars($solicitud['telefono']); ?></div>
                            </div>
                        </div>
                        
                        <?php if ($solicitud['num_departamentos']): ?>
                        <div class="info-item-v2">
                            <div class="info-icon-box-v2">🚪</div>
                            <div class="info-content-v2">
                                <div class="info-label-v2">DEPARTAMENTOS</div>
                                <div class="info-value-v2"><?php echo $solicitud['num_departamentos']; ?></div>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <div class="info-item-v2">
                            <div class="info-icon-box-v2">📅</div>
                            <div class="info-content-v2">
                                <div class="info-label-v2">FECHA SOLICITUD</div>
                                <div class="info-value-v2"><?php echo date('d/m/Y H:i', strtotime($solicitud['fecha_solicitud'])); ?></div>
                            </div>
                        </div>
                    </div>
                    
                    <?php if ($solicitud['direccion_edificio']): ?>
                    <div class="direccion-v2">
                        <div class="info-icon-box-v2">📍</div>
                        <div class="info-content-v2">
                            <div class="info-label-v2">DIRECCIÓN</div>
                            <div class="info-value-v2"><?php echo htmlspecialchars($solicitud['direccion_edificio']); ?></div>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($solicitud['mensaje']): ?>
                    <div class="mensaje-v2">
                        <div class="mensaje-header-v2">
                            <span class="mensaje-icon-v2">💬</span>
                            <span>MENSAJE DEL SOLICITANTE</span>
                        </div>
                        <div class="mensaje-content-v2">
                            <?php echo nl2br(htmlspecialchars($solicitud['mensaje'])); ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Botones de acción -->
                    <?php if ($isPendiente): ?>
                    <div class="actions-v2">
                        <button onclick="aprobarSolicitud(<?php echo $solicitud['id']; ?>, '<?php echo htmlspecialchars($solicitud['nombre_completo']); ?>', '<?php echo htmlspecialchars($solicitud['email']); ?>')" 
                                class="btn-action btn-edit btn-aprobar-v2">
                            ✅ Aprobar
                        </button>
                        <button onclick="rechazarSolicitud(<?php echo $solicitud['id']; ?>, '<?php echo htmlspecialchars($solicitud['nombre_completo']); ?>')" 
                                class="btn-action btn-delete btn-rechazar-v2">
                            ❌ Rechazar
                        </button>
                    </div>
                    <?php elseif ($solicitud['fecha_respuesta']): ?>
                    <div class="fecha-respuesta-v2">
                        <span class="respuesta-icon-v2">📅</span>
                        Respondido: <?php echo date('d/m/Y H:i', strtotime($solicitud['fecha_respuesta'])); ?>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Motivo de rechazo -->
                    <?php if ($isRechazada && !empty($solicitud['respuesta_admin'])): ?>
                    <div class="motivo-rechazo-v2">
                        <div class="motivo-header-v2">
                            <span class="motivo-icon-v2">💬</span>
                            <span>MOTIVO DEL RECHAZO</span>
                        </div>
                        <div class="motivo-content-v2">
                            <?php echo nl2br(htmlspecialchars($solicitud['respuesta_admin'])); ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

<style>
/* Empty State */
.empty-state {
    text-align: center;
    padding: 4rem 2rem;
    background: var(--color-surface);
    border-radius: 16px;
}

/* Solicitudes Grid */
.solicitudes-grid {
    display: grid;
    gap: 2.5rem;
}

/* Card V2 */
.solicitud-card-v2 {
    background: var(--color-surface);
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 4px 12px rgba(0,0,0,0.12);
    border: 1px solid var(--color-border);
    transition: transform 0.2s, box-shadow 0.2s;
}

.solicitud-card-v2:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 24px rgba(0,0,0,0.18);
}

/* Header */
.card-header-v2 {
    padding: 1.25rem 1.5rem;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    flex-wrap: wrap;
}

/* Status Badge */
.status-badge-v2 {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    border-radius: 8px;
    font-weight: 700;
    font-size: 0.75rem;
    letter-spacing: 0.5px;
}

.status-pendiente {
    background: #f59e0b;
    color: #1f2937;
}

.status-aprobada {
    background: #10b981;
    color: white;
}

.status-rechazada {
    background: #ef4444;
    color: white;
}

.status-icon-v2 {
    font-size: 1rem;
}

/* Edificio Badge */
.edificio-badge-v2 {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: white;
    font-weight: 600;
    font-size: 1.1rem;
}

.edificio-icon-v2 {
    font-size: 1.2rem;
}

/* Solicitante Section */
.solicitante-section-v2 {
    padding: 1.25rem 1.5rem;
    background: linear-gradient(135deg, rgba(99, 102, 241, 0.15) 0%, rgba(139, 92, 246, 0.15) 100%);
}

.solicitante-label-v2 {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.7rem;
    font-weight: 700;
    letter-spacing: 1px;
    color: var(--color-text-secondary);
    margin-bottom: 0.5rem;
}

.solicitante-icon-v2 {
    font-size: 1rem;
}

.solicitante-nombre-v2 {
    font-size: 1.25rem;
    font-weight: 700;
    color: var(--color-text);
}

/* Info Grid */
.info-grid-v2 {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 1rem;
    padding: 1.5rem;
}

.info-item-v2 {
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
    background: rgba(99, 102, 241, 0.05);
    padding: 1rem;
    border-radius: 10px;
}

.info-icon-box-v2 {
    font-size: 1.5rem;
    flex-shrink: 0;
}

.info-content-v2 {
    flex: 1;
    min-width: 0;
}

.info-label-v2 {
    font-size: 0.65rem;
    font-weight: 700;
    letter-spacing: 0.5px;
    color: var(--color-text-secondary);
    margin-bottom: 0.25rem;
}

.info-value-v2 {
    font-size: 0.95rem;
    font-weight: 500;
    color: var(--color-text);
    word-break: break-word;
}

/* Dirección */
.direccion-v2 {
    padding: 1rem 1.5rem;
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
    background: rgba(99, 102, 241, 0.05);
    margin: 0 1.5rem;
    border-radius: 10px;
}

/* Mensaje */
.mensaje-v2 {
    padding: 1.5rem;
}

.mensaje-header-v2 {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.7rem;
    font-weight: 700;
    letter-spacing: 1px;
    color: var(--color-text-secondary);
    margin-bottom: 0.75rem;
}

.mensaje-icon-v2 {
    font-size: 1rem;
}

.mensaje-content-v2 {
    background: rgba(139, 92, 246, 0.08);
    padding: 1rem;
    border-left: 3px solid #8b5cf6;
    border-radius: 8px;
    line-height: 1.6;
    color: var(--color-text);
}

/* Actions */
.actions-v2 {
    padding: 1.5rem;
    display: flex;
    gap: 0.75rem;
    justify-content: flex-end;
}

.btn-aprobar-v2,
.btn-rechazar-v2 {
    padding: 0.75rem 1.5rem;
    border-radius: 8px;
    border: none;
    font-weight: 600;
    font-size: 0.95rem;
    cursor: pointer;
    transition: all 0.2s;
    min-width: 140px;
}

.btn-aprobar-v2 {
    background: #10b981;
    color: white;
}

.btn-aprobar-v2:hover {
    background: #059669;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
}

.btn-rechazar-v2 {
    background: #ef4444;
    color: white;
}

.btn-rechazar-v2:hover {
    background: #dc2626;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
}

/* Fecha Respuesta */
.fecha-respuesta-v2 {
    padding: 1rem 1.5rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: var(--color-text-secondary);
    font-size: 0.875rem;
    background: rgba(0,0,0,0.02);
}

.respuesta-icon-v2 {
    font-size: 1rem;
}

/* Motivo Rechazo */
.motivo-rechazo-v2 {
    padding: 1.5rem;
    background: rgba(239, 68, 68, 0.08);
}

.motivo-header-v2 {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.7rem;
    font-weight: 700;
    letter-spacing: 1px;
    color: #dc2626;
    margin-bottom: 0.75rem;
}

.motivo-icon-v2 {
    font-size: 1rem;
}

.motivo-content-v2 {
    background: rgba(255,255,255,0.5);
    padding: 1rem;
    border-left: 3px solid #ef4444;
    border-radius: 8px;
    line-height: 1.6;
    color: #991b1b;
}

/* Dark Mode */
[data-theme="dark"] .card-header-v2 {
    background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
}

[data-theme="dark"] .solicitante-section-v2 {
    background: linear-gradient(135deg, rgba(99, 102, 241, 0.2) 0%, rgba(139, 92, 246, 0.2) 100%);
}

[data-theme="dark"] .info-item-v2 {
    background: rgba(99, 102, 241, 0.1);
}

[data-theme="dark"] .direccion-v2 {
    background: rgba(99, 102, 241, 0.1);
}

[data-theme="dark"] .mensaje-content-v2 {
    background: rgba(139, 92, 246, 0.15);
    border-left-color: #a78bfa;
}

[data-theme="dark"] .fecha-respuesta-v2 {
    background: rgba(255,255,255,0.03);
}

[data-theme="dark"] .motivo-rechazo-v2 {
    background: rgba(239, 68, 68, 0.15);
}

[data-theme="dark"] .motivo-content-v2 {
    background: rgba(0,0,0,0.3);
    color: #fca5a5;
}

[data-theme="dark"] .empty-state {
    background: #1e293b;
}

/* Light Theme - Header con color diferente */
[data-theme="light"] .card-header-v2 {
    background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
}
</style>

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

<?php include '../includes/admin_layout_end.php'; ?>
