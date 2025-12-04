<?php
require_once '../includes/session.php';
require_once '../includes/permissions.php';
require_once '../config/database.php';

requiereAutenticacion('../login.php');
requierePermiso('procesar_cierre_mensual'); // Redirige a acceso_denegado.php automáticamente

$title = "Procesar Cierre Mensual - Sistema de Edificios";
$pageTitle = "📅 Procesar Cierre";
$useAdminLayout = true;
include '../includes/header.php';
include '../includes/admin_layout_start.php';
?>
<link rel="stylesheet" href="../css/modal-styles.css">
<link rel="stylesheet" href="../css/modal-dark-mode.css">
<link rel="stylesheet" href="../css/admin-dark-mode.css">
<?php


$database = new Database();
$conn = $database->getConnection();

// Obtener edificios
$edificios_query = "SELECT id, nombre FROM edificios WHERE activo = 1 ORDER BY nombre";
$edificios_result = $conn->query($edificios_query);
$edificios = [];
while ($edificio = $edificios_result->fetch_assoc()) {
    $edificios[] = $edificio;
}

// Obtener ciclos existentes
$ciclos_query = "
    SELECT c.id, c.fecha_periodo, c.estado, e.nombre as edificio_nombre,
           COUNT(g.id) as total_gastos,
           COALESCE(SUM(g.monto_total), 0) as monto_total
    FROM ciclos_facturacion c
    INNER JOIN edificios e ON c.edificio_id = e.id
    LEFT JOIN gastos_edificio g ON c.id = g.ciclo_id AND g.activo = 1
    WHERE c.activo = 1
    GROUP BY c.id
    ORDER BY c.fecha_periodo DESC
    LIMIT 20
";
$ciclos_result = $conn->query($ciclos_query);
$ciclos = [];
while ($ciclo = $ciclos_result->fetch_assoc()) {
    $ciclos[] = $ciclo;
}

$conn->close();
?>

<section class="section-white">
    <div class="container">
        <h2 class="section-title">📋 Procesar Cierre Mensual</h2>
        
        <div class="alert alert-warning" style="margin-bottom: 2rem;">
        <strong>ℹ️ Información:</strong> Esta función procesa el cierre mensual de un edificio. 
        Asegúrate de haber registrado todos los gastos del mes antes de procesar el cierre.
    </div>
    
    <div class="form-container">
        <h3>Ver Ciclos Existentes</h3>
        
        <?php if (empty($ciclos)): ?>
            <p>No hay ciclos de facturación registrados aún.</p>
        <?php else: ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Edificio</th>
                        <th>Período</th>
                        <th>Estado</th>
                        <th>Gastos</th>
                        <th>Monto Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($ciclos as $ciclo): ?>
                        <tr>
                            <td data-label="Edificio"><?php echo htmlspecialchars($ciclo['edificio_nombre']); ?></td>
                            <td data-label="Período"><?php echo date('F Y', strtotime($ciclo['fecha_periodo'])); ?></td>
                            <td data-label="Estado">
                                <span class="badge badge-<?php echo strtolower($ciclo['estado']); ?>">
                                    <?php echo $ciclo['estado']; ?>
                                </span>
                            </td>
                            <td data-label="Gastos"><?php echo $ciclo['total_gastos']; ?></td>
                            <td data-label="Monto Total"><?php echo "S/ " . number_format($ciclo['monto_total'], 2); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
    
    <div style="margin-top: 2rem; text-align: center;">
        <a href="registrar_gastos.php" class="btn btn-primary" style="text-decoration: none; display: inline-block;">
            Registrar Nuevos Gastos
        </a>
        </div>
    </div>
</section>

<?php include '../includes/admin_layout_end.php';
include '../includes/admin_layout_end.php';


