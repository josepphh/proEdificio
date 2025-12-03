<?php
require_once 'includes/session.php';
require_once 'includes/permissions.php';
require_once 'config/database.php';

requiereAutenticacion('login.php');
requierePermiso('reportar_incidencias'); // Redirige a acceso_denegado.php automáticamente

$title = "Reportar Incidencia - Sistema de Edificios";
include 'includes/header.php';
include 'includes/nav.php';

$database = new Database();
$conn = $database->getConnection();

$usuario_id = $_SESSION['usuario_id'];

// Obtener edificios del usuario
$stmt_edificios = $conn->prepare("
    SELECT e.id, e.nombre 
    FROM edificios e
    INNER JOIN usuario_edificios ue ON e.id = ue.edificio_id
    WHERE ue.usuario_id = ? AND ue.activo = 1
    ORDER BY e.nombre
");
$stmt_edificios->bind_param("i", $usuario_id);
$stmt_edificios->execute();
$result_edificios = $stmt_edificios->get_result();
$edificios_usuario = $result_edificios->fetch_all(MYSQLI_ASSOC);
$stmt_edificios->close();

// Verificar si hay tabla de incidencias (crearla si no existe)
$result = $conn->query("SHOW TABLES LIKE 'incidencias'");
if ($result->num_rows == 0) {
    // Crear tabla de incidencias
    $sql = "CREATE TABLE IF NOT EXISTS incidencias (
        id INT AUTO_INCREMENT PRIMARY KEY,
        usuario_id INT NOT NULL,
        edificio_id INT NOT NULL,
        titulo VARCHAR(255) NOT NULL,
        descripcion TEXT NOT NULL,
        tipo ENUM('MANTENIMIENTO', 'SEGURIDAD', 'LIMPIEZA', 'OTRO') DEFAULT 'OTRO',
        prioridad ENUM('BAJA', 'MEDIA', 'ALTA', 'URGENTE') DEFAULT 'MEDIA',
        estado ENUM('PENDIENTE', 'EN_PROCESO', 'RESUELTA', 'CANCELADA') DEFAULT 'PENDIENTE',
        fecha_reporte DATETIME DEFAULT CURRENT_TIMESTAMP,
        fecha_resolucion DATETIME NULL,
        observaciones TEXT,
        activo TINYINT(1) NOT NULL DEFAULT 1,
        FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
        FOREIGN KEY (edificio_id) REFERENCES edificios(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    $conn->query($sql);
}

// Obtener incidencias del usuario
$stmt = $conn->prepare("
    SELECT id, titulo, tipo, prioridad, estado, fecha_reporte, fecha_resolucion
    FROM incidencias
    WHERE usuario_id = ? AND activo = 1
    ORDER BY fecha_reporte DESC
    LIMIT 10
");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();
$incidencias = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$conn->close();
?>

<section class="content-section">
    <h2 class="section-title">Reportar Incidencia</h2>
    
    <div class="form-container">
        <form id="formIncidencia" onsubmit="event.preventDefault(); reportarIncidencia();">
            <?php if (count($edificios_usuario) > 1): ?>
            <div class="form-group">
                <label for="edificio_id">Edificio *</label>
                <select id="edificio_id" name="edificio_id" required>
                    <option value="">Seleccione un edificio</option>
                    <?php foreach ($edificios_usuario as $edificio): ?>
                        <option value="<?php echo htmlspecialchars($edificio['id']); ?>">
                            <?php echo htmlspecialchars($edificio['nombre']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php elseif (count($edificios_usuario) === 1): ?>
            <input type="hidden" id="edificio_id" name="edificio_id" value="<?php echo htmlspecialchars($edificios_usuario[0]['id']); ?>">
            <div class="alert alert-info" style="margin-bottom: 1.5rem; padding: 0.75rem; background: #d1ecf1; border: 1px solid #bee5eb; border-radius: 5px; color: #0c5460;">
                <strong>Edificio:</strong> <?php echo htmlspecialchars($edificios_usuario[0]['nombre']); ?>
            </div>
            <?php else: ?>
            <div class="alert alert-warning" style="margin-bottom: 1.5rem; padding: 0.75rem; background: #fff3cd; border: 1px solid #ffeeba; border-radius: 5px; color: #856404;">
                No tienes edificios asignados. Contacta al administrador.
            </div>
            <?php endif; ?>
            
            <div class="form-group">
                <label for="titulo">Título de la Incidencia *</label>
                <input type="text" id="titulo" name="titulo" required 
                       placeholder="Ej: Fuga de agua en el baño" maxlength="255">
            </div>
            
            <div class="form-group">
                <label for="tipo">Tipo de Incidencia *</label>
                <select id="tipo" name="tipo" required>
                    <option value="">Seleccione un tipo</option>
                    <option value="MANTENIMIENTO">Mantenimiento</option>
                    <option value="SEGURIDAD">Seguridad</option>
                    <option value="LIMPIEZA">Limpieza</option>
                    <option value="OTRO">Otro</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="prioridad">Prioridad *</label>
                <select id="prioridad" name="prioridad" required>
                    <option value="MEDIA">Media</option>
                    <option value="BAJA">Baja</option>
                    <option value="ALTA">Alta</option>
                    <option value="URGENTE">Urgente</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="descripcion">Descripción Detallada *</label>
                <textarea id="descripcion" name="descripcion" rows="6" required 
                          placeholder="Describe la incidencia con el mayor detalle posible..."></textarea>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn-primario">Reportar Incidencia</button>
                <button type="reset" class="btn-secundario">Limpiar</button>
            </div>
        </form>
        
        <div id="mensaje" style="margin-top: 1rem; display: none;"></div>
    </div>
    
    <div class="incidencias-section">
        <h3>Mis Incidencias Reportadas</h3>
        <?php if (empty($incidencias)): ?>
            <p>No has reportado ninguna incidencia aún.</p>
        <?php else: ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Título</th>
                        <th>Tipo</th>
                        <th>Prioridad</th>
                        <th>Estado</th>
                        <th>Fecha</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($incidencias as $incidencia): ?>
                        <tr>
                            <td data-label="Título"><?php echo htmlspecialchars($incidencia['titulo']); ?></td>
                            <td data-label="Tipo"><?php echo $incidencia['tipo']; ?></td>
                            <td data-label="Prioridad">
                                <span class="badge badge-prioridad-<?php echo strtolower($incidencia['prioridad']); ?>">
                                    <?php echo $incidencia['prioridad']; ?>
                                </span>
                            </td>
                            <td data-label="Estado">
                                <span class="badge badge-estado-<?php echo strtolower($incidencia['estado']); ?>">
                                    <?php echo $incidencia['estado']; ?>
                                </span>
                            </td>
                            <td data-label="Fecha"><?php echo date('d/m/Y H:i', strtotime($incidencia['fecha_reporte'])); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</section>

<style>
    .form-container {
        background: white;
        padding: 2rem;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        max-width: 800px;
        margin: 0 auto 2rem;
    }
    
    .form-group {
        margin-bottom: 1.5rem;
    }
    
    .form-group label {
        display: block;
        margin-bottom: 0.5rem;
        font-weight: 600;
        color: #333;
    }
    
    .form-group input,
    .form-group select,
    .form-group textarea {
        width: 100%;
        padding: 0.75rem;
        border: 1px solid #ddd;
        border-radius: 5px;
        font-size: 1rem;
    }
    
    .form-group textarea {
        resize: vertical;
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
    
    .form-actions {
        margin-top: 2rem;
        text-align: right;
    }
    
    .incidencias-section {
        background: white;
        padding: 2rem;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }
    
    .data-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 1rem;
    }
    
    .data-table th,
    .data-table td {
        padding: 0.75rem;
        text-align: left;
        border-bottom: 1px solid #ddd;
    }
    
    .data-table th {
        background: #f8f9fa;
        font-weight: 600;
    }
    
    .badge {
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        font-size: 0.875rem;
        font-weight: 600;
    }
    
    .badge-prioridad-baja { background: #d1ecf1; color: #0c5460; }
    .badge-prioridad-media { background: #fff3cd; color: #856404; }
    .badge-prioridad-alta { background: #f8d7da; color: #721c24; }
    .badge-prioridad-urgente { background: #dc3545; color: white; }
    
    .badge-estado-pendiente { background: #fff3cd; color: #856404; }
    .badge-estado-en_proceso { background: #d1ecf1; color: #0c5460; }
    .badge-estado-resuelta { background: #d4edda; color: #155724; }
    .badge-estado-cancelada { background: #f8d7da; color: #721c24; }
    
    #mensaje {
        padding: 1rem;
        border-radius: 5px;
    }
    
    #mensaje.success {
        background: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }
    
    #mensaje.error {
        background: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }
</style>

<script>
async function reportarIncidencia() {
    const form = document.getElementById('formIncidencia');
    const formData = new FormData(form);
    
    const edificioId = formData.get('edificio_id');
    if (!edificioId) {
        mostrarMensaje('Por favor selecciona un edificio', 'error');
        return;
    }
    
    const data = {
        edificio_id: edificioId,
        titulo: formData.get('titulo'),
        tipo: formData.get('tipo'),
        prioridad: formData.get('prioridad'),
        descripcion: formData.get('descripcion')
    };
    
    try {
        const response = await fetch('/proyectoEdificio/api/reportar_incidencia.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        
        if (result.success) {
            mostrarMensaje('Incidencia reportada exitosamente', 'success');
            form.reset();
            setTimeout(() => {
                location.reload();
            }, 1500);
        } else {
            mostrarMensaje(result.message || 'Error al reportar la incidencia', 'error');
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
</script>

<?php include 'includes/footer.php'; ?>

