<?php
require_once '../includes/session.php';
require_once '../includes/permissions.php';
require_once '../config/database.php';

requiereAutenticacion('../login.php');
requierePermiso('validar_pagos');

$title = "Validar Pagos - Sistema de Edificios";
$pageTitle = "✅ Validar Pagos";
$useAdminLayout = true;
include '../includes/header.php';
include '../includes/admin_layout_start.php';


$database = new Database();
$conn = $database->getConnection();

$rol_nombre = $_SESSION['rol_nombre'] ?? '';
$usuario_id = $_SESSION['usuario_id'] ?? 0;
$es_admin_total = ($rol_nombre === 'Administrador Total');

// Obtener pagos pendientes de validación
$sql = "
    SELECT p.id, p.recibo_id, p.monto_pagado, p.metodo_pago, p.fecha_pago, 
           p.observaciones, p.voucher_path, p.estado,
           u.nombre as inquilino_nombre, u.email as inquilino_email,
           r.monto_deuda, c.fecha_periodo,
           e.nombre as edificio_nombre
    FROM pagos_inquilino p
    INNER JOIN usuarios u ON p.usuario_id = u.id
    INNER JOIN recibos_inquilino r ON p.recibo_id = r.id
    INNER JOIN ciclos_facturacion c ON r.ciclo_id = c.id
    INNER JOIN edificios e ON c.edificio_id = e.id
    WHERE p.activo = 1
";

if ($es_admin_total) {
    $sql .= " ORDER BY p.estado ASC, p.fecha_pago DESC";
    $result = $conn->query($sql);
} else {
    // Admin edificio solo ve pagos de sus edificios
    $sql .= " AND e.id IN (
        SELECT edificio_id FROM usuario_edificios 
        WHERE usuario_id = ? AND activo = 1
    ) ORDER BY p.estado ASC, p.fecha_pago DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    $result = $stmt->get_result();
}

$pagos = [];
while ($row = $result->fetch_assoc()) {
    $pagos[] = $row;
}

$conn->close();
?>

<section class="section-white">
    <div class="container">
        <h2 class="section-title">💳 Validar Pagos de Inquilinos</h2>
    
    <div class="stats-container" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
        <?php
        $pendientes = count(array_filter($pagos, fn($p) => $p['estado'] === 'PENDIENTE'));
        $verificados = count(array_filter($pagos, fn($p) => $p['estado'] === 'VERIFICADO'));
        $rechazados = count(array_filter($pagos, fn($p) => $p['estado'] === 'RECHAZADO'));
        ?>
        <div class="stat-card" style="background: #fff3cd; padding: 1.5rem; border-radius: 8px; text-align: center;">
            <h3 style="margin: 0; color: #856404; font-size: 2rem;"><?php echo $pendientes; ?></h3>
            <p style="margin: 0.5rem 0 0 0; color: #856404;">Pendientes</p>
        </div>
        <div class="stat-card" style="background: #d4edda; padding: 1.5rem; border-radius: 8px; text-align: center;">
            <h3 style="margin: 0; color: #155724; font-size: 2rem;"><?php echo $verificados; ?></h3>
            <p style="margin: 0.5rem 0 0 0; color: #155724;">Verificados</p>
        </div>
        <div class="stat-card" style="background: #f8d7da; padding: 1.5rem; border-radius: 8px; text-align: center;">
            <h3 style="margin: 0; color: #721c24; font-size: 2rem;"><?php echo $rechazados; ?></h3>
            <p style="margin: 0.5rem 0 0 0; color: #721c24;">Rechazados</p>
        </div>
    </div>

    <div class="filtros" style="background: white; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
        <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
            <input type="checkbox" id="mostrarVerificados" onchange="toggleVerificados()">
            <span>Mostrar pagos verificados y rechazados</span>
        </label>
    </div>

    <?php if (count($pagos) === 0): ?>
        <div class="alert alert-info" style="padding: 1.5rem; background: #d1ecf1; border: 1px solid #bee5eb; border-radius: 8px; text-align: center;">
            No hay pagos registrados para validar
        </div>
    <?php else: ?>
        <div class="pagos-grid">
            <?php foreach ($pagos as $pago): ?>
                <div class="pago-card" data-estado="<?php echo $pago['estado']; ?>" style="<?php echo $pago['estado'] !== 'PENDIENTE' ? 'display: none;' : ''; ?>">
                    <div class="pago-header" style="background: <?php 
                        echo $pago['estado'] === 'PENDIENTE' ? '#fff3cd' : 
                            ($pago['estado'] === 'VERIFICADO' ? '#d4edda' : '#f8d7da'); 
                    ?>; padding: 1rem; border-radius: 8px 8px 0 0;">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <h3 style="margin: 0;"><?php echo htmlspecialchars($pago['inquilino_nombre']); ?></h3>
                            <span class="badge badge-<?php echo strtolower($pago['estado']); ?>">
                                <?php echo $pago['estado']; ?>
                            </span>
                        </div>
                        <p style="margin: 0.5rem 0 0 0; font-size: 0.9rem; opacity: 0.8;">
                            <?php echo htmlspecialchars($pago['edificio_nombre']); ?>
                        </p>
                    </div>
                    
                    <div class="pago-body" style="background: white; padding: 1.5rem; border: 1px solid #ddd; border-top: none;">
                        <div class="pago-info">
                            <p><strong>📅 Periodo:</strong> <?php echo date('F Y', strtotime($pago['fecha_periodo'])); ?></p>
                            <p><strong>💰 Monto Recibo:</strong> S/ <?php echo number_format($pago['monto_deuda'], 2); ?></p>
                            <p><strong>💵 Monto Pagado:</strong> S/ <?php echo number_format($pago['monto_pagado'], 2); ?></p>
                            <p><strong>📱 Método:</strong> <?php echo htmlspecialchars($pago['metodo_pago']); ?></p>
                            <p><strong>📆 Fecha Pago:</strong> <?php echo date('d/m/Y H:i', strtotime($pago['fecha_pago'])); ?></p>
                            
                            <?php if ($pago['observaciones']): ?>
                                <p><strong>📝 Observaciones:</strong> <?php echo htmlspecialchars($pago['observaciones']); ?></p>
                            <?php endif; ?>
                            
                            <?php if ($pago['voucher_path']): ?>
                                <p style="margin-top: 1rem;">
                                    <strong>🧾 Voucher:</strong><br>
                                    <a href="../<?php echo htmlspecialchars($pago['voucher_path']); ?>" 
                                       target="_blank" 
                                       class="btn-voucher"
                                       style="display: inline-block; margin-top: 0.5rem; padding: 0.5rem 1rem; background: #007bff; color: white; text-decoration: none; border-radius: 4px;">
                                        Ver Comprobante 📄
                                    </a>
                                </p>
                            <?php endif; ?>
                        </div>
                        
                        <?php if ($pago['estado'] === 'PENDIENTE'): ?>
                            <div class="pago-actions" style="display: flex; gap: 0.5rem; margin-top: 1.5rem; padding-top: 1rem; border-top: 1px solid #ddd;">
                                <button onclick="validarPago(<?php echo $pago['id']; ?>, 'VERIFICADO')" 
                                        class="btn-aprobar" 
                                        style="flex: 1; padding: 0.75rem; background: #28a745; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: bold;">
                                    ✅ Aprobar
                                </button>
                                <button onclick="validarPago(<?php echo $pago['id']; ?>, 'RECHAZADO')" 
                                        class="btn-rechazar" 
                                        style="flex: 1; padding: 0.75rem; background: #dc3545; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: bold;">
                                    ❌ Rechazar
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    </div>
</section>

<script>
function toggleVerificados() {
    const checkbox = document.getElementById('mostrarVerificados');
    const cards = document.querySelectorAll('.pago-card[data-estado="VERIFICADO"], .pago-card[data-estado="RECHAZADO"]');
    
    cards.forEach(card => {
        card.style.display = checkbox.checked ? 'block' : 'none';
    });
}

async function validarPago(pagoId, nuevoEstado) {
    const accion = nuevoEstado === 'VERIFICADO' ? 'aprobar' : 'rechazar';
    const motivo = nuevoEstado === 'RECHAZADO' ? prompt('Motivo del rechazo (opcional):') : '';
    
    if (nuevoEstado === 'RECHAZADO' && motivo === null) {
        return; // Cancelado
    }
    
    const confirmado = await showConfirm(
        `¿Está seguro de ${accion} este pago?`,
        accion === 'aprobar' ? '✅ Aprobar Pago' : '❌ Rechazar Pago',
        accion === 'aprobar' ? 'Aprobar' : 'Rechazar',
        accion === 'aprobar' ? 'btn-success' : 'btn-danger'
    );
    if (!confirmado) return;
    
    try {
        const formData = new FormData();
        formData.append('action', 'validar_pago');
        formData.append('pago_id', pagoId);
        formData.append('nuevo_estado', nuevoEstado);
        if (motivo) {
            formData.append('motivo_rechazo', motivo);
        }
        
        const response = await fetch('../api/validar_pago.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            alert(data.message);
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Error al validar el pago');
    }
}
</script>

<?php
include '../includes/admin_layout_end.php';
include '../includes/footer.php';
?>
