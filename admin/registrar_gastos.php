<?php
require_once '../includes/session.php';
require_once '../includes/permissions.php';
require_once '../config/database.php';

requiereAutenticacion('../login.php');
requierePermiso('gestionar_gastos'); // Redirige a acceso_denegado.php automáticamente

$title = "Registrar Gastos - Sistema de Edificios";
$pageTitle = "💰 Registrar Gastos";
$useAdminLayout = true;
include '../includes/header.php';
include '../includes/admin_layout_start.php';
?>
<link rel="stylesheet" href="../css/modal-styles.css">
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

// Obtener servicios
$servicios_query = "SELECT id, codigo, nombre, tipo_calculo FROM servicios WHERE activo = 1 ORDER BY nombre";
$servicios_result = $conn->query($servicios_query);
$servicios = [];
while ($servicio = $servicios_result->fetch_assoc()) {
    $servicios[] = $servicio;
}

$conn->close();
?>

<section class="section-white">
    <div class="container">
        <h2 class="section-title">💸 Registrar Gastos del Mes</h2>
        
        <div class="card">
        <form id="formRegistrarGastos" onsubmit="event.preventDefault(); registrarGastos();">
            <div class="form-group">
                <label for="edificio_id">Edificio *</label>
                <select id="edificio_id" name="edificio_id" required>
                    <option value="">Seleccione un edificio</option>
                    <?php foreach ($edificios as $edificio): ?>
                        <option value="<?php echo htmlspecialchars($edificio['id']); ?>">
                            <?php echo htmlspecialchars($edificio['nombre']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="mes">Período (Mes) *</label>
                <input type="month" id="mes" name="mes" required 
                       value="<?php echo date('Y-m'); ?>">
            </div>
            
            <div id="gastos-container">
                <h3>Gastos del Mes</h3>
                <div class="gasto-item" data-index="0">
                    <div class="form-row">
                        <div class="form-group">
                            <label>Servicio *</label>
                            <select name="gastos[0][servicio_id]" required>
                                <option value="">Seleccione servicio</option>
                                <?php foreach ($servicios as $servicio): ?>
                                    <option value="<?php echo htmlspecialchars($servicio['id']); ?>" 
                                            data-tipo="<?php echo htmlspecialchars($servicio['tipo_calculo']); ?>">
                                        <?php echo htmlspecialchars($servicio['nombre']); ?> 
                                        (<?php echo $servicio['tipo_calculo'] === 'CUOTA_FIJA' ? 'Cuota Fija' : 'Consumo'; ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label>Monto Total (S/) *</label>
                            <input type="number" name="gastos[0][monto]" step="0.01" min="0" required 
                                   placeholder="0.00">
                        </div>
                        
                        <div class="form-group">
                            <label>Descripción</label>
                            <input type="text" name="gastos[0][descripcion]" 
                                   placeholder="Descripción del gasto">
                        </div>
                        
                        <div class="form-group">
                            <button type="button" class="btn btn-danger" onclick="eliminarGasto(0)" style="display:none;">Eliminar</button>
                        </div>
                    </div>
                </div>
            </div>
            
            <div style="margin-top: 1rem;">
                <button type="button" class="btn btn-secondary" onclick="agregarGasto()">+ Agregar Otro Gasto</button>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Guardar y Procesar Cierre</button>
                <button type="button" class="btn btn-secondary" onclick="window.history.back();">Cancelar</button>
            </div>
        </form>
    </div>
    
    <div id="mensaje" style="margin-top: 1rem; display: none;"></div>
</section>

<style>
    .form-container {
        background: white;
        padding: 2rem;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        max-width: 1000px;
        margin: 0 auto;
    }
    
    .form-group {
        margin-bottom: 1rem;
    }
    
    .form-group label {
        display: block;
        margin-bottom: 0.5rem;
        font-weight: 600;
        color: #333;
    }
    
    .form-group input,
    .form-group select {
        width: 100%;
        padding: 0.75rem;
        border: 1px solid #ddd;
        border-radius: 5px;
        font-size: 1rem;
    }
    
    .form-row {
        display: grid;
        grid-template-columns: 2fr 1fr 2fr auto;
        gap: 1rem;
        align-items: end;
    }
    
    .gasto-item {
        padding: 1rem;
        border: 1px solid #ddd;
        border-radius: 5px;
        margin-bottom: 1rem;
        background: #f9f9f9;
    }
    
    .btn-primario {
        background: #007bff;
        color: white;
        border: none;
        padding: 0.75rem 1.5rem;
        border-radius: 5px;
        cursor: pointer;
        font-size: 1rem;
    }
    
    .btn-primario:hover {
        background: #0056b3;
    }
    
    .btn-secundario {
        background: #6c757d;
        color: white;
        border: none;
        padding: 0.75rem 1.5rem;
        border-radius: 5px;
        cursor: pointer;
        font-size: 1rem;
        margin-left: 0.5rem;
    }
    
    .btn-eliminar {
        background: #dc3545;
        color: white;
        border: none;
        padding: 0.5rem 1rem;
        border-radius: 5px;
        cursor: pointer;
    }
    
    .form-actions {
        margin-top: 2rem;
        text-align: right;
    }
    
</style>

<script>
(function() {
let gastoIndex = 1;

window.agregarGasto = function() {
    const container = document.getElementById('gastos-container');
    const nuevoGasto = document.querySelector('.gasto-item').cloneNode(true);
    nuevoGasto.setAttribute('data-index', gastoIndex);
    
    // Actualizar nombres de inputs
    nuevoGasto.querySelectorAll('input, select').forEach(input => {
        const name = input.getAttribute('name');
        if (name) {
            input.setAttribute('name', name.replace('[0]', '[' + gastoIndex + ']'));
            input.value = '';
        }
    });
    
    // Mostrar botón eliminar
    nuevoGasto.querySelector('.btn-eliminar').style.display = 'block';
    nuevoGasto.querySelector('.btn-eliminar').setAttribute('onclick', 'eliminarGasto(' + gastoIndex + ')');
    
    container.appendChild(nuevoGasto);
    gastoIndex++;
}

window.eliminarGasto = function(index) {
    const item = document.querySelector(`.gasto-item[data-index="${index}"]`);
    if (item) {
        item.remove();
    }
}

window.registrarGastos = async function() {
    const form = document.getElementById('formRegistrarGastos');
    const formData = new FormData(form);
    
    // Convertir FormData a objeto
    const data = {
        edificio_id: formData.get('edificio_id'),
        mes: formData.get('mes'),
        gastos: []
    };
    
    // Recopilar gastos
    const gastosItems = document.querySelectorAll('.gasto-item');
    gastosItems.forEach(item => {
        const servicio_id = item.querySelector('[name*="[servicio_id]"]').value;
        const monto = item.querySelector('[name*="[monto]"]').value;
        const descripcion = item.querySelector('[name*="[descripcion]"]').value;
        
        if (servicio_id && monto) {
            data.gastos.push({
                servicio_id: servicio_id,
                monto: parseFloat(monto),
                descripcion: descripcion || ''
            });
        }
    });
    
    if (data.gastos.length === 0) {
        mostrarMensaje('Debe agregar al menos un gasto', 'error');
        return;
    }
    
    try {
        const response = await fetch('/proyectoEdificio/procesar_cierre.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        
        if (result.success) {
            mostrarMensaje(result.message || 'Gastos registrados y cierre procesado exitosamente', 'success');
            setTimeout(() => {
                window.location.href = '/proyectoEdificio/admin/panel.php';
            }, 2000);
        } else {
            mostrarMensaje(result.message || 'Error al procesar gastos', 'error');
        }
    } catch (error) {
        mostrarMensaje('Error de conexión: ' + error.message, 'error');
    }
}

function mostrarMensaje(mensaje, tipo) {
    const div = document.getElementById('mensaje');
    div.textContent = mensaje;
    div.className = tipo;
    div.style.display = 'block';
    
    setTimeout(() => {
        div.style.display = 'none';
    }, 5000);
}
})();
</script>

<?php include '../includes/admin_layout_end.php';
include '../includes/admin_layout_end.php';


