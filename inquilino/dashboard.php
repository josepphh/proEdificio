<?php
require_once '../includes/session.php';
require_once '../includes/permissions.php';
require_once '../config/database.php';

requiereAutenticacion('../login.php');

// Solo inquilinos pueden ver este dashboard
if (!esRol('Inquilino')) {
    header('Location: ../index.php');
    exit;
}

$title = "Mi Dashboard - Sistema de Edificios";
include '../includes/header.php';
include '../includes/nav.php';

$database = new Database();
$conn = $database->getConnection();

$usuario_id = $_SESSION['usuario_id'];

// Obtener estadísticas del inquilino
// 1. Recibos pendientes
$stmt = $conn->prepare("
    SELECT COUNT(*) as total, COALESCE(SUM(monto_deuda), 0) as monto_total
    FROM recibos_inquilino
    WHERE usuario_id = ? AND estado = 'PENDIENTE' AND activo = 1
");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();
$recibos_pendientes = $result->fetch_assoc();
$stmt->close();

// 2. Recibo más próximo a vencer
$stmt = $conn->prepare("
    SELECT r.id, r.monto_deuda, r.fecha_vencimiento, c.fecha_periodo, e.nombre as edificio_nombre
    FROM recibos_inquilino r
    INNER JOIN ciclos_facturacion c ON r.ciclo_id = c.id
    INNER JOIN edificios e ON c.edificio_id = e.id
    WHERE r.usuario_id = ? AND r.estado = 'PENDIENTE' AND r.activo = 1
    ORDER BY r.fecha_vencimiento ASC
    LIMIT 1
");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();
$proximo_recibo = $result->fetch_assoc();
$stmt->close();

// 3. Últimos 6 meses de gastos para gráfica
$stmt = $conn->prepare("
    SELECT DATE_FORMAT(c.fecha_periodo, '%Y-%m') as mes, 
           DATE_FORMAT(c.fecha_periodo, '%b %Y') as mes_nombre,
           COALESCE(SUM(r.monto_deuda), 0) as total
    FROM ciclos_facturacion c
    INNER JOIN edificios e ON c.edificio_id = e.id
    INNER JOIN usuario_edificios ue ON e.id = ue.edificio_id
    LEFT JOIN recibos_inquilino r ON c.id = r.ciclo_id AND r.usuario_id = ?
    WHERE ue.usuario_id = ? AND ue.activo = 1
      AND c.fecha_periodo >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
    GROUP BY mes, mes_nombre
    ORDER BY mes ASC
");
$stmt->bind_param("ii", $usuario_id, $usuario_id);
$stmt->execute();
$result = $stmt->get_result();
$gastos_mensuales = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// 4. Incidencias recientes
$stmt = $conn->prepare("
    SELECT i.*, e.nombre as edificio_nombre
    FROM incidencias i
    INNER JOIN edificios e ON i.edificio_id = e.id
    WHERE i.usuario_id = ? AND i.activo = 1
    ORDER BY i.fecha_creacion DESC
    LIMIT 3
");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();
$incidencias = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// 5. Avisos recientes
$stmt = $conn->prepare("
    SELECT a.*, e.nombre as edificio_nombre
    FROM avisos a
    INNER JOIN edificios e ON a.edificio_id = e.id
    INNER JOIN usuario_edificios ue ON e.id = ue.edificio_id
    WHERE ue.usuario_id = ? AND ue.activo = 1 AND a.activo = 1
    ORDER BY a.fecha_creacion DESC
    LIMIT 3
");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();
$avisos = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// 6. Pagos recientes
$stmt = $conn->prepare("
    SELECT p.*, c.fecha_periodo, e.nombre as edificio_nombre
    FROM pagos_inquilino p
    INNER JOIN recibos_inquilino r ON p.recibo_id = r.id
    INNER JOIN ciclos_facturacion c ON r.ciclo_id = c.id
    INNER JOIN edificios e ON c.edificio_id = e.id
    WHERE p.usuario_id = ? AND p.activo = 1
    ORDER BY p.fecha_pago DESC
    LIMIT 5
");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();
$pagos_recientes = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$conn->close();

// Verificar si hay recibos vencidos
$tiene_vencidos = false;
$dias_vencido = 0;
if ($proximo_recibo) {
    $fecha_venc = strtotime($proximo_recibo['fecha_vencimiento']);
    $hoy = strtotime(date('Y-m-d'));
    if ($fecha_venc < $hoy) {
        $tiene_vencidos = true;
        $dias_vencido = floor(($hoy - $fecha_venc) / (60 * 60 * 24));
    }
}
?>

<section class="content-section">
    <div class="dashboard-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <div>
            <h2 class="section-title" style="margin: 0;">¡Hola, <?php echo htmlspecialchars($_SESSION['nombre']); ?>!</h2>
            <p style="color: #666; margin: 0.5rem 0 0 0;">Bienvenido a tu dashboard</p>
        </div>
        <div style="color: #666;">
            <?php echo date('l, d \d\e F \d\e Y'); ?>
        </div>
    </div>

    <!-- Alertas -->
    <?php if ($tiene_vencidos): ?>
        <div class="alert alert-danger" style="background: #f8d7da; border: 2px solid #dc3545; border-radius: 8px; padding: 1.5rem; margin-bottom: 2rem; display: flex; align-items: center; gap: 1rem;">
            <span style="font-size: 2rem;">⚠️</span>
            <div style="flex: 1;">
                <strong>¡Atención! Tienes un recibo vencido hace <?php echo $dias_vencido; ?> día(s)</strong>
                <p style="margin: 0.5rem 0 0 0;">
                    Recibo de <?php echo date('F Y', strtotime($proximo_recibo['fecha_periodo'])); ?> 
                    por S/ <?php echo number_format($proximo_recibo['monto_deuda'], 2); ?>
                </p>
            </div>
            <a href="../mis_pagos.php" class="btn-pagar" style="background: #dc3545; color: white; padding: 0.75rem 1.5rem; border-radius: 4px; text-decoration: none; font-weight: bold;">
                Pagar Ahora
            </a>
        </div>
    <?php endif; ?>

    <!-- Cards de resumen -->
    <div class="stats-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
        <div class="stat-card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 2rem; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
            <div style="display: flex; justify-content: space-between; align-items: start;">
                <div>
                    <p style="margin: 0; opacity: 0.9; font-size: 0.9rem;">Recibos Pendientes</p>
                    <h3 style="margin: 0.5rem 0; font-size: 2.5rem;"><?php echo $recibos_pendientes['total']; ?></h3>
                </div>
                <span style="font-size: 2.5rem;">📋</span>
            </div>
        </div>

        <div class="stat-card" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white; padding: 2rem; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
            <div style="display: flex; justify-content: space-between; align-items: start;">
                <div>
                    <p style="margin: 0; opacity: 0.9; font-size: 0.9rem;">Total Adeudado</p>
                    <h3 style="margin: 0.5rem 0; font-size: 2rem;">S/ <?php echo number_format($recibos_pendientes['monto_total'], 2); ?></h3>
                </div>
                <span style="font-size: 2.5rem;">💰</span>
            </div>
        </div>

        <div class="stat-card" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); color: white; padding: 2rem; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
            <div style="display: flex; justify-content: space-between; align-items: start;">
                <div>
                    <p style="margin: 0; opacity: 0.9; font-size: 0.9rem;">Próximo Vencimiento</p>
                    <h3 style="margin: 0.5rem 0; font-size: 1.3rem;">
                        <?php echo $proximo_recibo ? date('d/m/Y', strtotime($proximo_recibo['fecha_vencimiento'])) : 'N/A'; ?>
                    </h3>
                </div>
                <span style="font-size: 2.5rem;">📅</span>
            </div>
        </div>

        <div class="stat-card" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); color: white; padding: 2rem; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
            <div style="display: flex; justify-content: space-between; align-items: start;">
                <div>
                    <p style="margin: 0; opacity: 0.9; font-size: 0.9rem;">Incidencias Activas</p>
                    <h3 style="margin: 0.5rem 0; font-size: 2.5rem;"><?php echo count($incidencias); ?></h3>
                </div>
                <span style="font-size: 2.5rem;">🔧</span>
            </div>
        </div>
    </div>

    <!-- Gráfica de gastos mensuales -->
    <div style="background: white; padding: 2rem; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); margin-bottom: 2rem;">
        <h3 style="margin: 0 0 1.5rem 0; color: #333;">📊 Evolución de Gastos (Últimos 6 meses)</h3>
        <canvas id="chartGastos" style="max-height: 300px;"></canvas>
    </div>

    <!-- Grid de secciones -->
    <div class="dashboard-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem;">
        
        <!-- Avisos recientes -->
        <div style="background: white; padding: 1.5rem; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
            <h3 style="margin: 0 0 1rem 0; color: #333; display: flex; align-items: center; gap: 0.5rem;">
                <span>📢</span> Avisos Recientes
            </h3>
            <?php if (count($avisos) > 0): ?>
                <div style="display: flex; flex-direction: column; gap: 1rem;">
                    <?php foreach ($avisos as $aviso): ?>
                        <div style="border-left: 4px solid #007bff; padding: 0.75rem; background: #f8f9fa; border-radius: 4px;">
                            <strong style="color: #007bff;"><?php echo htmlspecialchars($aviso['titulo']); ?></strong>
                            <p style="margin: 0.5rem 0 0 0; font-size: 0.9rem; color: #666;">
                                <?php echo nl2br(htmlspecialchars(substr($aviso['contenido'], 0, 100))); ?>...
                            </p>
                            <small style="color: #999;">
                                <?php echo date('d/m/Y', strtotime($aviso['fecha_creacion'])); ?> - 
                                <?php echo htmlspecialchars($aviso['edificio_nombre']); ?>
                            </small>
                        </div>
                    <?php endforeach; ?>
                </div>
                <a href="../avisos.php" style="display: block; text-align: center; margin-top: 1rem; color: #007bff; text-decoration: none; font-weight: bold;">
                    Ver todos los avisos →
                </a>
            <?php else: ?>
                <p style="text-align: center; color: #999; padding: 2rem;">No hay avisos recientes</p>
            <?php endif; ?>
        </div>

        <!-- Incidencias recientes -->
        <div style="background: white; padding: 1.5rem; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
            <h3 style="margin: 0 0 1rem 0; color: #333; display: flex; align-items: center; gap: 0.5rem;">
                <span>🔧</span> Mis Incidencias
            </h3>
            <?php if (count($incidencias) > 0): ?>
                <div style="display: flex; flex-direction: column; gap: 1rem;">
                    <?php foreach ($incidencias as $incidencia): ?>
                        <?php 
                        $estado_color = ['PENDIENTE' => '#ffc107', 'EN_PROCESO' => '#17a2b8', 'RESUELTA' => '#28a745'];
                        $color = $estado_color[$incidencia['estado']] ?? '#6c757d';
                        ?>
                        <div style="border-left: 4px solid <?php echo $color; ?>; padding: 0.75rem; background: #f8f9fa; border-radius: 4px;">
                            <strong style="color: <?php echo $color; ?>;"><?php echo htmlspecialchars($incidencia['tipo_incidencia']); ?></strong>
                            <p style="margin: 0.5rem 0; font-size: 0.9rem;">
                                <?php echo nl2br(htmlspecialchars(substr($incidencia['descripcion'], 0, 80))); ?>...
                            </p>
                            <small style="color: #999;">
                                Estado: <strong><?php echo $incidencia['estado']; ?></strong> - 
                                <?php echo date('d/m/Y', strtotime($incidencia['fecha_creacion'])); ?>
                            </small>
                        </div>
                    <?php endforeach; ?>
                </div>
                <a href="../reportar_incidencia.php" style="display: block; text-align: center; margin-top: 1rem; color: #007bff; text-decoration: none; font-weight: bold;">
                    Ver todas mis incidencias →
                </a>
            <?php else: ?>
                <p style="text-align: center; color: #999; padding: 2rem;">No has reportado incidencias</p>
                <a href="../reportar_incidencia.php" class="btn-primary" style="display: block; text-align: center; background: #007bff; color: white; padding: 0.75rem; border-radius: 4px; text-decoration: none; font-weight: bold;">
                    Reportar Incidencia
                </a>
            <?php endif; ?>
        </div>

        <!-- Pagos recientes -->
        <div style="background: white; padding: 1.5rem; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
            <h3 style="margin: 0 0 1rem 0; color: #333; display: flex; align-items: center; gap: 0.5rem;">
                <span>💳</span> Pagos Recientes
            </h3>
            <?php if (count($pagos_recientes) > 0): ?>
                <div style="display: flex; flex-direction: column; gap: 1rem;">
                    <?php foreach ($pagos_recientes as $pago): ?>
                        <?php 
                        $estado_color = ['PENDIENTE' => '#ffc107', 'VERIFICADO' => '#28a745', 'RECHAZADO' => '#dc3545'];
                        $color = $estado_color[$pago['estado']] ?? '#6c757d';
                        ?>
                        <div style="border-left: 4px solid <?php echo $color; ?>; padding: 0.75rem; background: #f8f9fa; border-radius: 4px;">
                            <strong><?php echo date('F Y', strtotime($pago['fecha_periodo'])); ?></strong>
                            <p style="margin: 0.5rem 0; font-size: 0.9rem;">
                                Monto: S/ <?php echo number_format($pago['monto_pagado'], 2); ?> - 
                                <span style="color: <?php echo $color; ?>; font-weight: bold;"><?php echo $pago['estado']; ?></span>
                            </p>
                            <small style="color: #999;">
                                <?php echo date('d/m/Y H:i', strtotime($pago['fecha_pago'])); ?>
                            </small>
                        </div>
                    <?php endforeach; ?>
                </div>
                <a href="../mis_pagos.php" style="display: block; text-align: center; margin-top: 1rem; color: #007bff; text-decoration: none; font-weight: bold;">
                    Ver historial completo →
                </a>
            <?php else: ?>
                <p style="text-align: center; color: #999; padding: 2rem;">No hay pagos registrados</p>
            <?php endif; ?>
        </div>

        <!-- Accesos rápidos -->
        <div style="background: white; padding: 1.5rem; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
            <h3 style="margin: 0 0 1rem 0; color: #333; display: flex; align-items: center; gap: 0.5rem;">
                <span>⚡</span> Accesos Rápidos
            </h3>
            <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                <a href="../mis_pagos.php" style="display: flex; align-items: center; gap: 1rem; padding: 1rem; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; text-decoration: none; border-radius: 8px; font-weight: bold;">
                    <span style="font-size: 1.5rem;">💳</span>
                    <span>Mis Pagos</span>
                </a>
                <a href="../reportar_incidencia.php" style="display: flex; align-items: center; gap: 1rem; padding: 1rem; background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white; text-decoration: none; border-radius: 8px; font-weight: bold;">
                    <span style="font-size: 1.5rem;">🔧</span>
                    <span>Reportar Incidencia</span>
                </a>
                <a href="../avisos.php" style="display: flex; align-items: center; gap: 1rem; padding: 1rem; background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); color: white; text-decoration: none; border-radius: 8px; font-weight: bold;">
                    <span style="font-size: 1.5rem;">📢</span>
                    <span>Ver Avisos</span>
                </a>
                <a href="../mi_perfil.php" style="display: flex; align-items: center; gap: 1rem; padding: 1rem; background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); color: white; text-decoration: none; border-radius: 8px; font-weight: bold;">
                    <span style="font-size: 1.5rem;">👤</span>
                    <span>Mi Perfil</span>
                </a>
            </div>
        </div>
    </div>
</section>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Gráfica de gastos
const ctx = document.getElementById('chartGastos').getContext('2d');
const chartGastos = new Chart(ctx, {
    type: 'line',
    data: {
        labels: <?php echo json_encode(array_column($gastos_mensuales, 'mes_nombre')); ?>,
        datasets: [{
            label: 'Gastos Mensuales (S/)',
            data: <?php echo json_encode(array_column($gastos_mensuales, 'total')); ?>,
            borderColor: 'rgb(102, 126, 234)',
            backgroundColor: 'rgba(102, 126, 234, 0.1)',
            borderWidth: 3,
            fill: true,
            tension: 0.4,
            pointRadius: 5,
            pointBackgroundColor: 'rgb(102, 126, 234)',
            pointBorderColor: '#fff',
            pointBorderWidth: 2,
            pointHoverRadius: 7
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                display: true,
                position: 'top'
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        return 'S/ ' + context.parsed.y.toFixed(2);
                    }
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return 'S/ ' + value.toFixed(2);
                    }
                }
            }
        }
    }
});
</script>

<?php
include '../includes/footer.php';
?>
