<?php
require_once '../includes/session.php';
require_once '../includes/permissions.php';
require_once '../config/database.php';

// Verificar permiso unificado
requiereAutenticacion('../login.php');
requierePermiso('ver_reportes');

$title = "Reportes - Sistema de Edificios";
$pageTitle = "📊 Reportes";
$useAdminLayout = true;
include '../includes/header.php';
include '../includes/admin_layout_start.php';
?>
<link rel="stylesheet" href="../css/modal-styles.css">
<?php


$database = new Database();
$conn = $database->getConnection();

// Obtener edificios asignados al usuario
$usuario_id = $_SESSION['usuario_id'];
$sql_edificios = "SELECT e.id, e.nombre 
                  FROM edificios e
                  INNER JOIN usuario_edificios ue ON e.id = ue.edificio_id
                  WHERE ue.usuario_id = ? AND e.activo = 1
                  ORDER BY e.nombre";
$stmt = $conn->prepare($sql_edificios);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();

$edificios_usuario = [];
while ($row = $result->fetch_assoc()) {
    $edificios_usuario[] = $row;
}
$stmt->close();

// Determinar alcance automáticamente
// Si no tiene edificios asignados = Administrador Total (ve todo)
// Si tiene edificios asignados = Administrador Edificio (ve solo los suyos)
$es_reporte_global = empty($edificios_usuario);

// Si es reporte global, obtener todos los edificios para el dropdown
if ($es_reporte_global) {
    $sql_edificios = "SELECT id, nombre FROM edificios WHERE activo = 1 ORDER BY nombre";
    $result = $conn->query($sql_edificios);
    while ($row = $result->fetch_assoc()) {
        $edificios_usuario[] = $row;
    }
}

// Obtener edificio seleccionado del filtro (si existe)
$edificio_filtro = isset($_GET['edificio_id']) ? intval($_GET['edificio_id']) : 0;

// Validar que el edificio filtrado pertenezca al usuario (seguridad)
if ($edificio_filtro > 0 && !$es_reporte_global) {
    $edificio_valido = false;
    foreach ($edificios_usuario as $ed) {
        if ($ed['id'] == $edificio_filtro) {
            $edificio_valido = true;
            break;
        }
    }
    if (!$edificio_valido) {
        $edificio_filtro = 0; // Reset si intenta acceder a un edificio no asignado
    }
}

// Construir condiciones SQL según el filtro
$where_edificio = "";
$where_edificio_usuarios = "";
$where_edificio_ciclos = "";
$where_edificio_recibos = "";

if (!$es_reporte_global) {
    // Administrador Edificio: filtrar por sus edificios
    $edificios_ids = array_column($edificios_usuario, 'id');
    if (!empty($edificios_ids)) {
        $ids_string = implode(',', $edificios_ids);
        $where_edificio_usuarios = " AND ue.edificio_id IN ($ids_string)";
        $where_edificio_ciclos = " AND c.edificio_id IN ($ids_string)";
        $where_edificio_recibos = " AND ri.ciclo_id IN (SELECT id FROM ciclos_facturacion WHERE edificio_id IN ($ids_string))";
    }
}

if ($edificio_filtro > 0) {
    // Filtro específico por edificio seleccionado
    $where_edificio_usuarios = " AND ue.edificio_id = $edificio_filtro";
    $where_edificio_ciclos = " AND c.edificio_id = $edificio_filtro";
    $where_edificio_recibos = " AND ri.ciclo_id IN (SELECT id FROM ciclos_facturacion WHERE edificio_id = $edificio_filtro)";
}

// Estadísticas generales
$stats = [];

// Total de usuarios (filtrado por edificios)
if ($edificio_filtro > 0 || !$es_reporte_global) {
    $sql = "SELECT COUNT(DISTINCT u.id) as total 
            FROM usuarios u
            INNER JOIN usuario_edificios ue ON u.id = ue.usuario_id
            WHERE u.activo = 1 $where_edificio_usuarios";
    $result = $conn->query($sql);
} else {
    $result = $conn->query("SELECT COUNT(*) as total FROM usuarios WHERE activo = 1");
}
$stats['total_usuarios'] = $result->fetch_assoc()['total'];

// Total de edificios
if ($edificio_filtro > 0) {
    $stats['total_edificios'] = 1;
} else if (!$es_reporte_global) {
    $stats['total_edificios'] = count($edificios_usuario);
} else {
    $result = $conn->query("SELECT COUNT(*) as total FROM edificios WHERE activo = 1");
    $stats['total_edificios'] = $result->fetch_assoc()['total'];
}

// Total de ciclos de facturación
$sql = "SELECT COUNT(*) as total FROM ciclos_facturacion c WHERE c.activo = 1 $where_edificio_ciclos";
$result = $conn->query($sql);
$stats['total_ciclos'] = $result->fetch_assoc()['total'];

// Total de recibos pendientes
$sql = "SELECT COUNT(*) as total FROM recibos_inquilino ri WHERE ri.estado = 'PENDIENTE' AND ri.activo = 1 $where_edificio_recibos";
$result = $conn->query($sql);
$stats['recibos_pendientes'] = $result->fetch_assoc()['total'];

// Total de recibos pagados
$sql = "SELECT COUNT(*) as total FROM recibos_inquilino ri WHERE ri.estado = 'PAGADO' AND ri.activo = 1 $where_edificio_recibos";
$result = $conn->query($sql);
$stats['recibos_pagados'] = $result->fetch_assoc()['total'];

// Monto total pendiente
$sql = "SELECT COALESCE(SUM(ri.monto_deuda), 0) as total FROM recibos_inquilino ri WHERE ri.estado = 'PENDIENTE' AND ri.activo = 1 $where_edificio_recibos";
$result = $conn->query($sql);
$stats['monto_pendiente'] = $result->fetch_assoc()['total'];

// Monto total pagado
$sql = "SELECT COALESCE(SUM(pi.monto_pagado), 0) as total 
        FROM pagos_inquilino pi
        INNER JOIN recibos_inquilino ri ON pi.recibo_id = ri.id
        WHERE pi.estado = 'VERIFICADO' AND pi.activo = 1 $where_edificio_recibos";
$result = $conn->query($sql);
$stats['monto_pagado'] = $result->fetch_assoc()['total'];

// Usuarios por rol (con filtro de edificio si aplica)
if ($edificio_filtro > 0 || !$es_reporte_global) {
    $sql = "SELECT r.nombre, COUNT(DISTINCT u.id) as total 
            FROM roles r 
            LEFT JOIN usuarios u ON r.id = u.rol_id AND u.activo = 1 
            LEFT JOIN usuario_edificios ue ON u.id = ue.usuario_id
            WHERE 1=1 $where_edificio_usuarios
            GROUP BY r.id, r.nombre 
            ORDER BY total DESC";
} else {
    $sql = "SELECT r.nombre, COUNT(u.id) as total 
            FROM roles r 
            LEFT JOIN usuarios u ON r.id = u.rol_id AND u.activo = 1 
            GROUP BY r.id, r.nombre 
            ORDER BY total DESC";
}
$result = $conn->query($sql);
$usuarios_por_rol = [];
while ($row = $result->fetch_assoc()) {
    $usuarios_por_rol[] = $row;
}

// Ciclos recientes (con filtro de edificio si aplica)
$sql = "SELECT c.id, c.fecha_periodo, c.estado, e.nombre as edificio_nombre,
           COUNT(DISTINCT ri.id) as total_recibos,
           COALESCE(SUM(g.monto_total), 0) as monto_total
    FROM ciclos_facturacion c
    INNER JOIN edificios e ON c.edificio_id = e.id
    LEFT JOIN recibos_inquilino ri ON c.id = ri.ciclo_id AND ri.activo = 1
    LEFT JOIN gastos_edificio g ON c.id = g.ciclo_id AND g.activo = 1
    WHERE c.activo = 1 $where_edificio_ciclos
    GROUP BY c.id
    ORDER BY c.fecha_periodo DESC
    LIMIT 10";
$result = $conn->query($sql);
$ciclos_recientes = [];
while ($row = $result->fetch_assoc()) {
    $ciclos_recientes[] = $row;
}

$conn->close();
?>

<section class="section-white">
    <div class="container">
        <h2 class="section-title">
            📊 Reportes y Estadísticas
            <?php if ($edificio_filtro > 0): ?>
                <?php 
                $nombre_edificio_filtrado = '';
                foreach ($edificios_usuario as $ed) {
                    if ($ed['id'] == $edificio_filtro) {
                        $nombre_edificio_filtrado = $ed['nombre'];
                        break;
                    }
                }
                ?>
                <span style="background: #4CAF50; color: white; padding: 6px 15px; border-radius: 20px; font-size: 14px; margin-left: 15px; font-weight: normal;">
                    🏢 <?php echo htmlspecialchars($nombre_edificio_filtrado); ?>
                </span>
            <?php endif; ?>
        </h2>
        
        <?php if (!$es_reporte_global): ?>
        <!-- Banner informativo para usuarios con edificios asignados -->
        <div style="background: #e3f2fd; border-left: 4px solid #2196f3; padding: 15px 20px; margin-bottom: 25px; border-radius: 6px;">
            <div style="display: flex; align-items: center; gap: 12px;">
                <span style="font-size: 24px;">ℹ️</span>
                <div>
                    <strong style="color: #1976d2;">Vista Limitada a tus Edificios Asignados</strong>
                    <p style="margin: 5px 0 0 0; color: #555; font-size: 14px;">
                        Estás viendo reportes de tus <?php echo count($edificios_usuario); ?> edificio<?php echo count($edificios_usuario) != 1 ? 's' : ''; ?> asignado<?php echo count($edificios_usuario) != 1 ? 's' : ''; ?>. 
                        Los datos mostrados (usuarios, ciclos, pagos) corresponden únicamente a estos edificios.
                    </p>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Filtro de edificios -->
        <?php if (count($edificios_usuario) > 1): ?>
        <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 25px;">
            <label for="edificioFiltro" style="display: block; margin-bottom: 10px; font-weight: 600; font-size: 16px;">
                🏢 Filtrar por Edificio:
            </label>
            <select id="edificioFiltro" onchange="filtrarPorEdificio(this.value)" style="padding: 10px 15px; font-size: 15px; border-radius: 6px; border: 1px solid #ddd; width: 100%; max-width: 400px; cursor: pointer;">
                <option value="">📊 Todos los edificios (<?php echo count($edificios_usuario); ?>)</option>
                <?php foreach ($edificios_usuario as $edificio): ?>
                    <option value="<?php echo $edificio['id']; ?>" <?php echo ($edificio_filtro == $edificio['id']) ? 'selected' : ''; ?>>
                        🏢 <?php echo htmlspecialchars($edificio['nombre']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            
            <?php if ($edificio_filtro > 0): ?>
                <div style="margin-top: 15px; display: inline-block;">
                    <button onclick="limpiarFiltro()" style="padding: 8px 16px; background: #6c757d; color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 14px;">
                        🔄 Limpiar Filtro
                    </button>
                </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>
        
        <!-- Tarjetas de estadísticas principales -->
        <div class="cards-grid" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); margin-bottom: 3rem;">
            <div class="value-card">
                <div class="value-card-icon">👥</div>
                <div class="value-card-content">
                    <h3>Total Usuarios</h3>
                    <p class="value-card-number"><?php echo $stats['total_usuarios']; ?></p>
                </div>
            </div>
            
            <div class="value-card">
                <div class="value-card-icon">🏢</div>
                <div class="value-card-content">
                    <h3>Total Edificios</h3>
                    <p class="value-card-number"><?php echo $stats['total_edificios']; ?></p>
                </div>
            </div>
            
            <div class="value-card">
                <div class="value-card-icon">📅</div>
                <div class="value-card-content">
                    <h3>Ciclos Facturación</h3>
                    <p class="value-card-number"><?php echo $stats['total_ciclos']; ?></p>
                </div>
            </div>
            
            <div class="card-bordered" style="background: #fff3cd;">
                <div style="text-align: center;">
                    <div style="font-size: 2rem; margin-bottom: 0.5rem;">⏳</div>
                    <h3 style="color: #856404; font-size: 0.9rem; margin-bottom: 0.5rem;">Recibos Pendientes</h3>
                    <p style="font-size: 2rem; font-weight: bold; color: #856404; margin: 0;"><?php echo $stats['recibos_pendientes']; ?></p>
                </div>
            </div>
            
            <div class="card-bordered" style="background: #d4edda;">
                <div style="text-align: center;">
                    <div style="font-size: 2rem; margin-bottom: 0.5rem;">✅</div>
                    <h3 style="color: #155724; font-size: 0.9rem; margin-bottom: 0.5rem;">Recibos Pagados</h3>
                    <p style="font-size: 2rem; font-weight: bold; color: #155724; margin: 0;"><?php echo $stats['recibos_pagados']; ?></p>
                </div>
            </div>
            
            <div class="card-bordered" style="background: #f8d7da;">
                <div style="text-align: center;">
                    <div style="font-size: 2rem; margin-bottom: 0.5rem;">💰</div>
                    <h3 style="color: #721c24; font-size: 0.9rem; margin-bottom: 0.5rem;">Monto Pendiente</h3>
                    <p style="font-size: 1.5rem; font-weight: bold; color: #721c24; margin: 0;">S/ <?php echo number_format($stats['monto_pendiente'], 2); ?></p>
                </div>
            </div>
            
            <div class="card-bordered" style="background: #d1ecf1;">
                <div style="text-align: center;">
                    <div style="font-size: 2rem; margin-bottom: 0.5rem;">💵</div>
                    <h3 style="color: #0c5460; font-size: 0.9rem; margin-bottom: 0.5rem;">Monto Pagado</h3>
                    <p style="font-size: 1.5rem; font-weight: bold; color: #0c5460; margin: 0;">S/ <?php echo number_format($stats['monto_pagado'], 2); ?></p>
                </div>
            </div>
        </div>
        
        <!-- Tablas de reportes -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 2rem;">
            <div class="card">
                <div class="card-header">
                    <h3>👥 Usuarios por Rol</h3>
                </div>
                <div class="table-responsive">
                    <table class="table">
                <thead>
                    <tr>
                        <th>Rol</th>
                        <th>Total Usuarios</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($usuarios_por_rol as $rol): ?>
                        <tr>
                            <td data-label="Rol"><?php echo htmlspecialchars($rol['nombre']); ?></td>
                            <td data-label="Total Usuarios"><?php echo $rol['total']; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                    </table>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h3>📅 Ciclos Recientes</h3>
                </div>
                <?php if (empty($ciclos_recientes)): ?>
                    <div class="card-body">
                        <p style="text-align: center; color: #666; padding: 2rem;">No hay ciclos de facturación registrados.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table">
                    <thead>
                        <tr>
                            <th>Edificio</th>
                            <th>Período</th>
                            <th>Estado</th>
                            <th>Recibos</th>
                            <th>Monto Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($ciclos_recientes as $ciclo): ?>
                            <tr>
                                <td data-label="Edificio"><?php echo htmlspecialchars($ciclo['edificio_nombre']); ?></td>
                                <td data-label="Período"><?php echo date('F Y', strtotime($ciclo['fecha_periodo'])); ?></td>
                                <td data-label="Estado">
                                    <span class="badge badge-<?php echo strtolower($ciclo['estado']); ?>">
                                        <?php echo $ciclo['estado']; ?>
                                    </span>
                                </td>
                                <td data-label="Recibos"><?php echo $ciclo['total_recibos']; ?></td>
                                <td data-label="Monto Total">S/ <?php echo number_format($ciclo['monto_total'], 2); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<script>
(function() {
// Función para filtrar reportes por edificio
window.filtrarPorEdificio = function(edificioId) {
    if (edificioId) {
        window.location.href = '?edificio_id=' + edificioId;
    } else {
        window.location.href = 'reportes.php';
    }
}

// Función para limpiar el filtro
window.limpiarFiltro = function() {
    window.location.href = 'reportes.php';
}

// Mostrar mensaje si hay filtro activo
<?php if ($edificio_filtro > 0): ?>
    <?php 
    $nombre_edificio_filtrado = '';
    foreach ($edificios_usuario as $ed) {
        if ($ed['id'] == $edificio_filtro) {
            $nombre_edificio_filtrado = $ed['nombre'];
            break;
        }
    }
    ?>
    console.log('Filtro activo: <?php echo htmlspecialchars($nombre_edificio_filtrado, ENT_QUOTES); ?>');
<?php endif; ?>
})();
</script>

<?php include '../includes/admin_layout_end.php';
include '../includes/admin_layout_end.php';
include '../includes/footer.php'; ?>
