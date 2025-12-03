<?php
require_once 'includes/session.php';
require_once 'includes/permissions.php';
require_once 'config/database.php';

requiereAutenticacion('login.php');
requierePermiso('ver_perfil'); // Redirige a acceso_denegado.php automáticamente

$title = "Mi Perfil - Sistema de Edificios";
include 'includes/header.php';
include 'includes/nav.php';

$database = new Database();
$conn = $database->getConnection();

$usuario_id = $_SESSION['usuario_id'];

// Obtener datos del usuario
$stmt = $conn->prepare("
    SELECT u.id, u.nombre, u.email, u.username, u.fecha_registro, u.fecha_ingreso, u.fecha_salida,
           r.nombre as rol_nombre
    FROM usuarios u
    LEFT JOIN roles r ON u.rol_id = r.id
    WHERE u.id = ?
");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();
$usuario = $result->fetch_assoc();
$stmt->close();

// Obtener edificios asignados
$stmt = $conn->prepare("
    SELECT e.nombre, e.direccion 
    FROM usuario_edificios ue
    INNER JOIN edificios e ON ue.edificio_id = e.id
    WHERE ue.usuario_id = ? AND ue.activo = 1
    ORDER BY e.nombre
");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();
$edificios_asignados = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Obtener estadísticas del usuario
$stats = [];

// Total de recibos
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM recibos_inquilino WHERE usuario_id = ? AND activo = 1");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();
$stats['total_recibos'] = $result->fetch_assoc()['total'];
$stmt->close();

// Recibos pendientes
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM recibos_inquilino WHERE usuario_id = ? AND estado = 'PENDIENTE' AND activo = 1");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();
$stats['recibos_pendientes'] = $result->fetch_assoc()['total'];
$stmt->close();

// Monto pendiente
$stmt = $conn->prepare("SELECT COALESCE(SUM(monto_deuda), 0) as total FROM recibos_inquilino WHERE usuario_id = ? AND estado = 'PENDIENTE' AND activo = 1");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();
$stats['monto_pendiente'] = $result->fetch_assoc()['total'];
$stmt->close();

$conn->close();
?>

<section class="content-section">
    <h2 class="section-title">Mi Perfil</h2>
    
    <div class="profile-container">
        <div class="profile-card">
            <h3>Información Personal</h3>
            <div class="profile-info">
                <div class="info-row">
                    <label>Nombre Completo:</label>
                    <span><?php echo htmlspecialchars($usuario['nombre']); ?></span>
                </div>
                <div class="info-row">
                    <label>Email:</label>
                    <span><?php echo htmlspecialchars($usuario['email']); ?></span>
                </div>
                <div class="info-row">
                    <label>Usuario:</label>
                    <span><?php echo htmlspecialchars($usuario['username']); ?></span>
                </div>
                <div class="info-row">
                    <label>Rol:</label>
                    <span><?php echo htmlspecialchars($usuario['rol_nombre']); ?></span>
                </div>
                <div class="info-row">
                    <label>Edificios Asignados:</label>
                    <span>
                        <?php 
                        if (!empty($edificios_asignados)) {
                            echo htmlspecialchars(count($edificios_asignados)) . ' edificio(s)';
                        } else {
                            echo 'Ninguno';
                        }
                        ?>
                    </span>
                </div>
                <?php if (!empty($edificios_asignados)): ?>
                <div class="info-row">
                    <label>Detalles:</label>
                    <div class="edificios-lista">
                        <?php foreach ($edificios_asignados as $edif): ?>
                            <div class="edificio-item">
                                <strong><?php echo htmlspecialchars($edif['nombre']); ?></strong><br>
                                <small><?php echo htmlspecialchars($edif['direccion']); ?></small>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
                <div class="info-row">
                    <label>Fecha de Registro:</label>
                    <span><?php echo date('d/m/Y', strtotime($usuario['fecha_registro'])); ?></span>
                </div>
                <?php if ($usuario['fecha_ingreso']): ?>
                <div class="info-row">
                    <label>Fecha de Ingreso:</label>
                    <span><?php echo date('d/m/Y', strtotime($usuario['fecha_ingreso'])); ?></span>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="stats-card">
            <h3>Mis Estadísticas</h3>
            <div class="stats-grid">
                <div class="stat-item">
                    <label>Total Recibos</label>
                    <span class="stat-value"><?php echo $stats['total_recibos']; ?></span>
                </div>
                <div class="stat-item">
                    <label>Recibos Pendientes</label>
                    <span class="stat-value"><?php echo $stats['recibos_pendientes']; ?></span>
                </div>
                <div class="stat-item">
                    <label>Monto Pendiente</label>
                    <span class="stat-value">S/ <?php echo number_format($stats['monto_pendiente'], 2); ?></span>
                </div>
            </div>
        </div>
    </div>
    
    <div class="quick-actions">
        <h3>Acciones Rápidas</h3>
        <div class="actions-grid">
            <a href="mis_pagos.php" class="action-btn">
                <span class="action-icon">💰</span>
                <span>Mis Pagos</span>
            </a>
            <a href="reportar_incidencia.php" class="action-btn">
                <span class="action-icon">📋</span>
                <span>Reportar Incidencia</span>
            </a>
            <a href="avisos.php" class="action-btn">
                <span class="action-icon">📢</span>
                <span>Ver Avisos</span>
            </a>
        </div>
    </div>
</section>

<style>
    .profile-container {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 2rem;
        margin-bottom: 2rem;
    }
    
    .profile-card,
    .stats-card {
        background: white;
        padding: 2rem;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }
    
    .profile-card h3,
    .stats-card h3 {
        color: #007bff;
        margin-bottom: 1.5rem;
        border-bottom: 2px solid #007bff;
        padding-bottom: 0.5rem;
    }
    
    .profile-info {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }
    
    .info-row {
        display: grid;
        grid-template-columns: 200px 1fr;
        gap: 1rem;
        padding: 0.75rem 0;
        border-bottom: 1px solid #eee;
    }
    
    .info-row label {
        font-weight: 600;
        color: #666;
    }
    
    .info-row span {
        color: #333;
    }
    
    .stats-grid {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }
    
    .stat-item {
        padding: 1rem;
        background: #f8f9fa;
        border-radius: 5px;
    }
    
    .stat-item label {
        display: block;
        font-size: 0.875rem;
        color: #666;
        margin-bottom: 0.5rem;
    }
    
    .stat-value {
        font-size: 1.5rem;
        font-weight: bold;
        color: #007bff;
    }
    
    .edificios-lista {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
        margin-top: 0.5rem;
    }
    
    .edificio-item {
        padding: 0.75rem;
        background: #f8f9fa;
        border-left: 3px solid #007bff;
        border-radius: 4px;
    }
    
    .edificio-item strong {
        color: #007bff;
    }
    
    .edificio-item small {
        color: #666;
    }
    
    .quick-actions {
        background: white;
        padding: 2rem;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }
    
    .quick-actions h3 {
        color: #007bff;
        margin-bottom: 1.5rem;
    }
    
    .actions-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
    }
    
    .action-btn {
        display: flex;
        flex-direction: column;
        align-items: center;
        padding: 1.5rem;
        background: #f8f9fa;
        border: 2px solid #007bff;
        border-radius: 8px;
        text-decoration: none;
        color: #007bff;
        transition: all 0.3s ease;
    }
    
    .action-btn:hover {
        background: #007bff;
        color: white;
        transform: translateY(-3px);
    }
    
    .action-icon {
        font-size: 2rem;
        margin-bottom: 0.5rem;
    }
</style>

<?php include 'includes/footer.php'; ?>

