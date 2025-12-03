<?php
require_once 'includes/session.php';
require_once 'includes/permissions.php';
require_once 'config/database.php';

requiereAutenticacion('login.php');
requierePermiso('ver_avisos'); // Redirige a acceso_denegado.php automáticamente

$title = "Avisos - Sistema de Edificios";
include 'includes/header.php';
include 'includes/nav.php';

$database = new Database();
$conn = $database->getConnection();

// Obtener edificios del usuario desde usuario_edificios
$usuario_id = $_SESSION['usuario_id'];
$stmt_edificios = $conn->prepare("SELECT edificio_id FROM usuario_edificios WHERE usuario_id = ? AND activo = 1");
$stmt_edificios->bind_param("i", $usuario_id);
$stmt_edificios->execute();
$result_edificios = $stmt_edificios->get_result();
$edificios_usuario = [];
while ($row = $result_edificios->fetch_assoc()) {
    $edificios_usuario[] = $row['edificio_id'];
}
$stmt_edificios->close();

// Verificar si hay tabla de avisos (crearla si no existe)
$result = $conn->query("SHOW TABLES LIKE 'avisos'");
if ($result->num_rows == 0) {
    // Crear tabla de avisos
    $sql = "CREATE TABLE IF NOT EXISTS avisos (
        id INT AUTO_INCREMENT PRIMARY KEY,
        edificio_id INT NULL COMMENT 'NULL = aviso general para todos',
        titulo VARCHAR(255) NOT NULL,
        contenido TEXT NOT NULL,
        tipo ENUM('INFORMATIVO', 'URGENTE', 'MANTENIMIENTO', 'EVENTO') DEFAULT 'INFORMATIVO',
        fecha_publicacion DATETIME DEFAULT CURRENT_TIMESTAMP,
        fecha_vencimiento DATE NULL,
        activo TINYINT(1) NOT NULL DEFAULT 1,
        FOREIGN KEY (edificio_id) REFERENCES edificios(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    $conn->query($sql);
    
    // Insertar avisos de ejemplo
    $avisos_ejemplo = [
        ['Bienvenida', 'Bienvenidos al sistema de gestión de edificios. Aquí podrán ver sus recibos, reportar incidencias y mantenerse informados.', 'INFORMATIVO'],
        ['Mantenimiento de Ascensores', 'El próximo lunes se realizará mantenimiento preventivo de los ascensores de 8:00 AM a 12:00 PM.', 'MANTENIMIENTO'],
        ['Reunión de Vecinos', 'Se convoca a reunión de vecinos el próximo sábado a las 10:00 AM en el salón comunal.', 'EVENTO']
    ];
    
    foreach ($avisos_ejemplo as $aviso) {
        $stmt = $conn->prepare("INSERT INTO avisos (titulo, contenido, tipo) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $aviso[0], $aviso[1], $aviso[2]);
        $stmt->execute();
        $stmt->close();
    }
}

// Obtener avisos (generales y de los edificios del usuario)
if (!empty($edificios_usuario)) {
    $placeholders = str_repeat('?,', count($edificios_usuario) - 1) . '?';
    $stmt = $conn->prepare("
        SELECT id, titulo, contenido, tipo, fecha_publicacion, fecha_vencimiento
        FROM avisos
        WHERE (edificio_id IS NULL OR edificio_id IN ($placeholders)) 
        AND activo = 1
        AND (fecha_vencimiento IS NULL OR fecha_vencimiento >= CURDATE())
        ORDER BY 
            CASE tipo
                WHEN 'URGENTE' THEN 1
                WHEN 'MANTENIMIENTO' THEN 2
                WHEN 'EVENTO' THEN 3
                ELSE 4
            END,
            fecha_publicacion DESC
    ");
    $types = str_repeat('i', count($edificios_usuario));
    $stmt->bind_param($types, ...$edificios_usuario);
} else {
    // Si no tiene edificios asignados, solo avisos generales
    $stmt = $conn->prepare("
        SELECT id, titulo, contenido, tipo, fecha_publicacion, fecha_vencimiento
        FROM avisos
        WHERE edificio_id IS NULL 
        AND activo = 1
        AND (fecha_vencimiento IS NULL OR fecha_vencimiento >= CURDATE())
        ORDER BY 
            CASE tipo
                WHEN 'URGENTE' THEN 1
                WHEN 'MANTENIMIENTO' THEN 2
                WHEN 'EVENTO' THEN 3
                ELSE 4
            END,
            fecha_publicacion DESC
    ");
}

$stmt->execute();
$result = $stmt->get_result();
$avisos = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$conn->close();
?>

<section class="content-section">
    <h2 class="section-title">Avisos y Noticias</h2>
    
    <?php if (empty($avisos)): ?>
        <div class="no-avisos">
            <p>No hay avisos disponibles en este momento.</p>
        </div>
    <?php else: ?>
        <div class="avisos-grid">
            <?php foreach ($avisos as $aviso): ?>
                <div class="aviso-card aviso-<?php echo strtolower($aviso['tipo']); ?>">
                    <div class="aviso-header">
                        <h3><?php echo htmlspecialchars($aviso['titulo']); ?></h3>
                        <span class="badge badge-tipo-<?php echo strtolower($aviso['tipo']); ?>">
                            <?php 
                            $tipos = [
                                'INFORMATIVO' => 'Informativo',
                                'URGENTE' => 'Urgente',
                                'MANTENIMIENTO' => 'Mantenimiento',
                                'EVENTO' => 'Evento'
                            ];
                            echo $tipos[$aviso['tipo']] ?? $aviso['tipo'];
                            ?>
                        </span>
                    </div>
                    <div class="aviso-content">
                        <p><?php echo nl2br(htmlspecialchars($aviso['contenido'])); ?></p>
                    </div>
                    <div class="aviso-footer">
                        <span class="fecha">
                            Publicado: <?php echo date('d/m/Y H:i', strtotime($aviso['fecha_publicacion'])); ?>
                        </span>
                        <?php if ($aviso['fecha_vencimiento']): ?>
                            <span class="fecha-vencimiento">
                                Válido hasta: <?php echo date('d/m/Y', strtotime($aviso['fecha_vencimiento'])); ?>
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<style>
    .avisos-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
        gap: 1.5rem;
    }
    
    .aviso-card {
        background: white;
        padding: 1.5rem;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        border-left: 4px solid #007bff;
    }
    
    .aviso-card.aviso-urgente {
        border-left-color: #dc3545;
        background: #fff5f5;
    }
    
    .aviso-card.aviso-mantenimiento {
        border-left-color: #ffc107;
        background: #fffef5;
    }
    
    .aviso-card.aviso-evento {
        border-left-color: #28a745;
        background: #f5fff5;
    }
    
    .aviso-header {
        display: flex;
        justify-content: space-between;
        align-items: start;
        margin-bottom: 1rem;
    }
    
    .aviso-header h3 {
        margin: 0;
        color: #333;
        flex: 1;
    }
    
    .aviso-content {
        margin-bottom: 1rem;
        color: #666;
        line-height: 1.6;
    }
    
    .aviso-footer {
        display: flex;
        justify-content: space-between;
        font-size: 0.875rem;
        color: #999;
        padding-top: 1rem;
        border-top: 1px solid #eee;
    }
    
    .badge {
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
        white-space: nowrap;
    }
    
    .badge-tipo-informativo {
        background: #d1ecf1;
        color: #0c5460;
    }
    
    .badge-tipo-urgente {
        background: #f8d7da;
        color: #721c24;
    }
    
    .badge-tipo-mantenimiento {
        background: #fff3cd;
        color: #856404;
    }
    
    .badge-tipo-evento {
        background: #d4edda;
        color: #155724;
    }
    
    .no-avisos {
        background: white;
        padding: 3rem;
        border-radius: 8px;
        text-align: center;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }
</style>

<?php include 'includes/footer.php'; ?>

