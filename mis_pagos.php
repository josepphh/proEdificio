<?php
require_once 'includes/session.php';
require_once 'includes/permissions.php';
require_once 'config/database.php';

requiereAutenticacion('login.php');

// Solo inquilinos pueden ver sus pagos
if (!esRol('Inquilino')) {
    header('Location: index.php');
    exit;
}

$title = "Mis Pagos - Sistema de Edificios";
include 'includes/header.php';
include 'includes/nav.php';

$database = new Database();
$conn = $database->getConnection();

$usuario_id = $_SESSION['usuario_id'];

// Obtener recibos del usuario
$stmt = $conn->prepare("
    SELECT r.id, r.monto_deuda, r.estado, r.fecha_vencimiento, r.fecha_pago,
           c.fecha_periodo, e.nombre as edificio_nombre
    FROM recibos_inquilino r
    INNER JOIN ciclos_facturacion c ON r.ciclo_id = c.id
    INNER JOIN edificios e ON c.edificio_id = e.id
    WHERE r.usuario_id = ? AND r.activo = 1
    ORDER BY c.fecha_periodo DESC
");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();
$recibos = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Obtener pagos registrados
$pagos_query = "
    SELECT p.*, r.monto_deuda, c.fecha_periodo
    FROM pagos_inquilino p
    INNER JOIN recibos_inquilino r ON p.recibo_id = r.id
    INNER JOIN ciclos_facturacion c ON r.ciclo_id = c.id
    WHERE p.usuario_id = ? AND p.activo = 1
    ORDER BY p.fecha_creacion DESC
";
$stmt = $conn->prepare($pagos_query);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();
$pagos = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$conn->close();
?>

<section class="content-section">
    <h2 class="section-title">Mis Pagos y Deudas</h2>
    
    <div class="pagos-container">
        <div class="pagos-section">
            <h3>Recibos Pendientes</h3>
            
            <?php if (empty($recibos)): ?>
                <p>No tienes recibos pendientes.</p>
            <?php else: ?>
                <div class="recibos-grid">
                    <?php foreach ($recibos as $recibo): ?>
                        <div class="recibo-card <?php echo strtolower($recibo['estado']); ?>">
                            <div class="recibo-header">
                                <h4><?php echo date('F Y', strtotime($recibo['fecha_periodo'])); ?></h4>
                                <span class="estado-badge estado-<?php echo strtolower($recibo['estado']); ?>">
                                    <?php 
                                    $estados = ['PENDIENTE' => 'Pendiente', 'PAGADO' => 'Pagado', 'VENCIDO' => 'Vencido'];
                                    echo $estados[$recibo['estado']] ?? $recibo['estado'];
                                    ?>
                                </span>
                            </div>
                            
                            <div class="recibo-body">
                                <p class="monto"><?php echo "S/ " . number_format($recibo['monto_deuda'], 2); ?></p>
                                <p class="fecha-vencimiento">
                                    Vence: <?php echo date('d/m/Y', strtotime($recibo['fecha_vencimiento'])); ?>
                                </p>
                                
                                <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                                    <?php if ($recibo['estado'] === 'PENDIENTE'): ?>
                                        <button class="btn-pagar" onclick="mostrarModalPago(<?php echo $recibo['id']; ?>, <?php echo $recibo['monto_deuda']; ?>)" style="flex: 1;">
                                            💳 Registrar Pago
                                        </button>
                                    <?php endif; ?>
                                    <a href="generar_recibo_pdf.php?id=<?php echo $recibo['id']; ?>" 
                                       class="btn-pdf" 
                                       target="_blank"
                                       style="flex: 1; background: #dc3545; color: white; text-decoration: none; padding: 0.75rem; border-radius: 4px; text-align: center; display: inline-block; font-weight: bold;">
                                        📄 Descargar PDF
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="pagos-section">
            <h3>Historial de Pagos</h3>
            
            <?php if (empty($pagos)): ?>
                <p>No has registrado ningún pago aún.</p>
            <?php else: ?>
                <table class="pagos-table">
                    <thead>
                        <tr>
                            <th>Período</th>
                            <th>Monto</th>
                            <th>Método</th>
                            <th>Fecha</th>
                            <th>Estado</th>
                            <th>Voucher</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pagos as $pago): ?>
                            <tr>
                                <td data-label="Período"><?php echo date('M Y', strtotime($pago['fecha_periodo'])); ?></td>
                                <td data-label="Monto"><?php echo "S/ " . number_format($pago['monto_pagado'], 2); ?></td>
                                <td data-label="Método"><?php echo $pago['metodo_pago']; ?></td>
                                <td data-label="Fecha"><?php echo date('d/m/Y H:i', strtotime($pago['fecha_pago'])); ?></td>
                                <td data-label="Estado">
                                    <span class="estado-badge estado-<?php echo strtolower($pago['estado']); ?>">
                                        <?php 
                                        $estados_pago = ['PENDIENTE' => 'Pendiente', 'VERIFICADO' => 'Verificado', 'RECHAZADO' => 'Rechazado'];
                                        echo $estados_pago[$pago['estado']] ?? $pago['estado'];
                                        ?>
                                    </span>
                                </td>
                                <td data-label="Voucher">
                                    <?php if ($pago['voucher_path']): ?>
                                        <a href="/proyectoEdificio/uploads/vouchers/<?php echo htmlspecialchars($pago['voucher_path']); ?>" 
                                           target="_blank" class="btn-ver">Ver</a>
                                    <?php else: ?>
                                        <span>-</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Modal para registrar pago -->
<div id="modalPago" class="modal">
    <div class="modal-content">
        <span class="close" onclick="cerrarModalPago()">&times;</span>
        <div class="modal-header">
            <h3>💳 Registrar Pago</h3>
        </div>
        <form id="formPago" onsubmit="event.preventDefault(); registrarPago();" enctype="multipart/form-data">
        <div class="modal-body">
            <input type="hidden" id="recibo_id" name="recibo_id">
            
            <div class="form-group">
                <label>Monto a Pagar (S/)</label>
                <input type="number" id="monto" name="monto" step="0.01" required>
            </div>
            
            <div class="form-group">
                <label>Método de Pago *</label>
                <select id="metodo_pago" name="metodo_pago" required>
                    <option value="">Seleccione método</option>
                    <option value="EFECTIVO">Efectivo</option>
                    <option value="YAPE">Yape</option>
                    <option value="PLIN">Plin</option>
                    <option value="TRANSFERENCIA">Transferencia Bancaria</option>
                    <option value="OTRO">Otro</option>
                </select>
            </div>
            
            <div class="form-group">
                <label>Fecha de Pago *</label>
                <input type="date" id="fecha_pago" name="fecha_pago" required 
                       value="<?php echo date('Y-m-d'); ?>">
            </div>
            
            <div class="form-group">
                <label>Voucher (Opcional)</label>
                <input type="file" id="voucher" name="voucher" accept="image/*,.pdf">
                <small>Formatos permitidos: JPG, PNG, PDF (máx. 5MB)</small>
            </div>
            
            <div class="form-group">
                <label>Observaciones</label>
                <textarea id="observaciones" name="observaciones" rows="3" class="form-input"></textarea>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-light" onclick="cerrarModalPago()">Cancelar</button>
            <button type="submit" class="btn btn-primary">💵 Registrar Pago</button>
        </div>
        </form>
        
        <div id="mensajePago" class="alert" style="margin-top: 1rem; display: none;"></div>
    </div>
</div>

<style>
    .pagos-container {
        max-width: 1200px;
        margin: 0 auto;
    }
    
    .pagos-section {
        background: white;
        padding: 2rem;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        margin-bottom: 2rem;
    }
    
    .recibos-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 1.5rem;
        margin-top: 1rem;
    }
    
    .recibo-card {
        border: 2px solid #ddd;
        border-radius: 8px;
        padding: 1.5rem;
        background: white;
    }
    
    .recibo-card.pendiente {
        border-color: #ffc107;
    }
    
    .recibo-card.pagado {
        border-color: #28a745;
    }
    
    .recibo-card.vencido {
        border-color: #dc3545;
    }
    
    .recibo-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1rem;
    }
    
    .recibo-body .monto {
        font-size: 1.5rem;
        font-weight: bold;
        color: #007bff;
        margin: 0.5rem 0;
    }
    
    .btn-pagar {
        background: #28a745;
        color: white;
        border: none;
        padding: 0.75rem 1.5rem;
        border-radius: 5px;
        cursor: pointer;
        width: 100%;
        margin-top: 1rem;
    }
    
    .estado-badge {
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        font-size: 0.875rem;
        font-weight: 600;
    }
    
    .estado-pendiente {
        background: #fff3cd;
        color: #856404;
    }
    
    .estado-pagado {
        background: #d4edda;
        color: #155724;
    }
    
    .estado-vencido {
        background: #f8d7da;
        color: #721c24;
    }
    
    .pagos-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 1rem;
    }
    
    .pagos-table th,
    .pagos-table td {
        padding: 0.75rem;
        text-align: left;
        border-bottom: 1px solid #ddd;
    }
    
    .pagos-table th {
        background: #f8f9fa;
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
        background: rgba(0, 0, 0, 0.5);
    }
    
    .modal-content {
        background: white;
        margin: 5% auto;
        padding: 2rem;
        border-radius: 8px;
        max-width: 500px;
        position: relative;
    }
    
    .close {
        position: absolute;
        right: 1rem;
        top: 1rem;
        font-size: 2rem;
        cursor: pointer;
        color: #aaa;
    }
    
    .form-group {
        margin-bottom: 1rem;
    }
    
    .form-group label {
        display: block;
        margin-bottom: 0.5rem;
        font-weight: 600;
    }
    
    .form-group input,
    .form-group select,
    .form-group textarea {
        width: 100%;
        padding: 0.75rem;
        border: 1px solid #ddd;
        border-radius: 5px;
    }
    
    .btn-primario {
        background: #007bff;
        color: white;
        border: none;
        padding: 0.75rem 1.5rem;
        border-radius: 5px;
        cursor: pointer;
    }
    
    .btn-secundario {
        background: #6c757d;
        color: white;
        border: none;
        padding: 0.75rem 1.5rem;
        border-radius: 5px;
        cursor: pointer;
        margin-left: 0.5rem;
    }
</style>

<script>
function mostrarModalPago(reciboId, monto) {
    document.getElementById('recibo_id').value = reciboId;
    document.getElementById('monto').value = monto.toFixed(2);
    document.getElementById('modalPago').style.display = 'block';
}

function cerrarModalPago() {
    document.getElementById('modalPago').style.display = 'none';
    document.getElementById('formPago').reset();
}

async function registrarPago() {
    const form = document.getElementById('formPago');
    const formData = new FormData(form);
    
    try {
        const response = await fetch('/proyectoEdificio/procesar_pago.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            mostrarMensajePago('Pago registrado exitosamente. Pendiente de verificación.', 'success');
            setTimeout(() => {
                location.reload();
            }, 2000);
        } else {
            mostrarMensajePago(result.message || 'Error al registrar el pago', 'error');
        }
    } catch (error) {
        mostrarMensajePago('Error de conexión: ' + error.message, 'error');
    }
}

function mostrarMensajePago(mensaje, tipo) {
    const div = document.getElementById('mensajePago');
    div.textContent = mensaje;
    div.className = tipo;
    div.style.display = 'block';
}

// El modal ya NO se cierra al hacer clic fuera
// Solo se puede cerrar con el botón X o Cancelar
</script>

<?php include 'includes/footer.php'; ?>

