<?php
header('Content-Type: application/json; charset=UTF-8');
require_once '../includes/session.php';
require_once '../includes/permissions.php';
require_once '../config/database.php';
require_once '../includes/mail.php';

// Verificar autenticación y permiso
if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['success' => false, 'message' => 'No autenticado'], JSON_UNESCAPED_UNICODE);
    exit;
}

if (!tienePermiso('gestionar_avisos')) {
    echo json_encode(['success' => false, 'message' => 'Sin permiso para gestionar avisos'], JSON_UNESCAPED_UNICODE);
    exit;
}

$database = new Database();
$conn = $database->getConnection();

$accion = $_POST['accion'] ?? $_GET['accion'] ?? '';

try {
    switch ($accion) {
        case 'listar':
            listarAvisos($conn);
            break;
        
        case 'crear':
            crearAviso($conn);
            break;
        
        case 'editar':
            editarAviso($conn);
            break;
        
        case 'eliminar':
            eliminarAviso($conn);
            break;
        
        case 'obtener':
            obtenerAviso($conn);
            break;
        
        default:
            echo json_encode(['success' => false, 'message' => 'Acción no válida'], JSON_UNESCAPED_UNICODE);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()], JSON_UNESCAPED_UNICODE);
}

$conn->close();

function listarAvisos($conn) {
    $rol_nombre = $_SESSION['rol_nombre'] ?? '';
    $usuario_id = $_SESSION['usuario_id'] ?? 0;
    
    // Administrador Total ve todos los avisos
    // Administrador Edificio solo ve avisos generales y de sus edificios asignados
    if ($rol_nombre === 'Administrador Total') {
        $sql = "SELECT a.*, e.nombre as edificio_nombre 
                FROM avisos a 
                LEFT JOIN edificios e ON a.edificio_id = e.id 
                ORDER BY a.fecha_publicacion DESC";
        $stmt = $conn->prepare($sql);
    } else if ($rol_nombre === 'Administrador Edificio') {
        // Obtener avisos generales (NULL) o de cualquiera de sus edificios asignados
        $sql = "SELECT a.*, e.nombre as edificio_nombre 
                FROM avisos a 
                LEFT JOIN edificios e ON a.edificio_id = e.id 
                WHERE a.edificio_id IS NULL 
                   OR a.edificio_id IN (
                       SELECT edificio_id 
                       FROM usuario_edificios 
                       WHERE usuario_id = ? AND activo = 1
                   )
                ORDER BY a.fecha_publicacion DESC";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $usuario_id);
    } else {
        echo json_encode(['success' => false, 'message' => 'Sin permisos suficientes'], JSON_UNESCAPED_UNICODE);
        return;
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    $avisos = [];
    
    while ($row = $result->fetch_assoc()) {
        $avisos[] = [
            'id' => (int)$row['id'],
            'edificio_id' => $row['edificio_id'] ? (int)$row['edificio_id'] : null,
            'edificio_nombre' => $row['edificio_nombre'] ?? 'Todos los edificios',
            'titulo' => $row['titulo'],
            'contenido' => $row['contenido'],
            'tipo' => $row['tipo'],
            'fecha_publicacion' => $row['fecha_publicacion'],
            'fecha_vencimiento' => $row['fecha_vencimiento'],
            'activo' => (int)$row['activo']
        ];
    }
    
    $stmt->close();
    echo json_encode(['success' => true, 'avisos' => $avisos], JSON_UNESCAPED_UNICODE);
}

function crearAviso($conn) {
    $titulo = $_POST['titulo'] ?? '';
    $contenido = $_POST['contenido'] ?? '';
    $tipo = $_POST['tipo'] ?? 'INFORMATIVO';
    $edificio_id = $_POST['edificio_id'] ?? null;
    $fecha_vencimiento = $_POST['fecha_vencimiento'] ?? null;
    
    if (empty($titulo) || empty($contenido)) {
        echo json_encode(['success' => false, 'message' => 'Título y contenido son obligatorios'], JSON_UNESCAPED_UNICODE);
        return;
    }
    
    // Validar que Administrador Edificio solo pueda crear avisos de sus edificios asignados
    $rol_nombre = $_SESSION['rol_nombre'] ?? '';
    $usuario_id = $_SESSION['usuario_id'] ?? 0;
    
    if ($rol_nombre === 'Administrador Edificio' && $edificio_id !== null && $edificio_id !== '') {
        // Verificar que el edificio esté asignado al administrador
        $check = $conn->prepare("SELECT id FROM usuario_edificios WHERE usuario_id = ? AND edificio_id = ? AND activo = 1");
        $check->bind_param("ii", $usuario_id, $edificio_id);
        $check->execute();
        $result = $check->get_result();
        if ($result->num_rows === 0) {
            echo json_encode(['success' => false, 'message' => 'Solo puedes crear avisos para tus edificios asignados'], JSON_UNESCAPED_UNICODE);
            $check->close();
            return;
        }
        $check->close();
    }
    
    // Convertir edificio_id vacío a NULL
    if ($edificio_id === '' || $edificio_id === 'null') {
        $edificio_id = null;
    }
    
    // Convertir fecha_vencimiento vacía a NULL
    if ($fecha_vencimiento === '' || $fecha_vencimiento === 'null') {
        $fecha_vencimiento = null;
    }
    
    $stmt = $conn->prepare("INSERT INTO avisos (edificio_id, titulo, contenido, tipo, fecha_vencimiento) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("issss", $edificio_id, $titulo, $contenido, $tipo, $fecha_vencimiento);
    
    if ($stmt->execute()) {
        $nuevo_aviso_id = $conn->insert_id;
        $stmt->close();
        
        // Si es URGENTE, enviar emails a los inquilinos
        if ($tipo === 'URGENTE') {
            $mailer = new Mailer();
            $emails_enviados = 0;
            
            // Obtener inquilinos del edificio (o todos si es general)
            if ($edificio_id !== null) {
                // Aviso específico de un edificio
                $stmt_inquilinos = $conn->prepare("
                    SELECT DISTINCT u.nombre, u.email, e.nombre as edificio_nombre
                    FROM usuarios u
                    INNER JOIN usuario_edificios ue ON u.id = ue.usuario_id
                    INNER JOIN edificios e ON ue.edificio_id = e.id
                    WHERE ue.edificio_id = ? AND ue.activo = 1 AND u.activo = 1
                ");
                $stmt_inquilinos->bind_param("i", $edificio_id);
            } else {
                // Aviso general para todos
                $stmt_inquilinos = $conn->prepare("
                    SELECT DISTINCT u.nombre, u.email, 'Todos los edificios' as edificio_nombre
                    FROM usuarios u
                    INNER JOIN usuario_edificios ue ON u.id = ue.usuario_id
                    WHERE ue.activo = 1 AND u.activo = 1
                ");
            }
            
            $stmt_inquilinos->execute();
            $result = $stmt_inquilinos->get_result();
            
            while ($inquilino = $result->fetch_assoc()) {
                if ($inquilino['email']) {
                    $datos_aviso = [
                        'titulo' => $titulo,
                        'contenido' => $contenido,
                        'edificio' => $inquilino['edificio_nombre'],
                        'fecha' => date('Y-m-d H:i:s')
                    ];
                    if ($mailer->enviarAvisoUrgente($inquilino['email'], $inquilino['nombre'], $datos_aviso)) {
                        $emails_enviados++;
                    }
                }
            }
            $stmt_inquilinos->close();
            
            echo json_encode(['success' => true, 'message' => 'Aviso urgente creado. Emails enviados: ' . $emails_enviados], JSON_UNESCAPED_UNICODE);
        } else {
            echo json_encode(['success' => true, 'message' => 'Aviso creado exitosamente'], JSON_UNESCAPED_UNICODE);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al crear aviso: ' . $stmt->error], JSON_UNESCAPED_UNICODE);
        $stmt->close();
    }
}

function editarAviso($conn) {
    $id = $_POST['id'] ?? 0;
    $titulo = $_POST['titulo'] ?? '';
    $contenido = $_POST['contenido'] ?? '';
    $tipo = $_POST['tipo'] ?? 'INFORMATIVO';
    $edificio_id = $_POST['edificio_id'] ?? null;
    $fecha_vencimiento = $_POST['fecha_vencimiento'] ?? null;
    
    if (empty($id) || empty($titulo) || empty($contenido)) {
        echo json_encode(['success' => false, 'message' => 'Datos incompletos'], JSON_UNESCAPED_UNICODE);
        return;
    }
    
    // Validar que Administrador Edificio solo pueda editar avisos de sus edificios asignados
    $rol_nombre = $_SESSION['rol_nombre'] ?? '';
    $usuario_id = $_SESSION['usuario_id'] ?? 0;
    
    if ($rol_nombre === 'Administrador Edificio') {
        // Verificar que el aviso pertenezca a alguno de sus edificios o sea general
        $check = $conn->prepare("SELECT edificio_id FROM avisos WHERE id = ?");
        $check->bind_param("i", $id);
        $check->execute();
        $result = $check->get_result();
        $aviso = $result->fetch_assoc();
        $check->close();
        
        if (!$aviso) {
            echo json_encode(['success' => false, 'message' => 'Aviso no encontrado'], JSON_UNESCAPED_UNICODE);
            return;
        }
        
        // Si el aviso tiene edificio específico, verificar que esté en sus asignaciones
        if ($aviso['edificio_id'] !== null) {
            $check = $conn->prepare("SELECT id FROM usuario_edificios WHERE usuario_id = ? AND edificio_id = ? AND activo = 1");
            $check->bind_param("ii", $usuario_id, $aviso['edificio_id']);
            $check->execute();
            $result = $check->get_result();
            if ($result->num_rows === 0) {
                echo json_encode(['success' => false, 'message' => 'No tienes permiso para editar este aviso'], JSON_UNESCAPED_UNICODE);
                $check->close();
                return;
            }
            $check->close();
        }
    }
    
    // Convertir valores vacíos a NULL
    if ($edificio_id === '' || $edificio_id === 'null') {
        $edificio_id = null;
    }
    if ($fecha_vencimiento === '' || $fecha_vencimiento === 'null') {
        $fecha_vencimiento = null;
    }
    
    $stmt = $conn->prepare("UPDATE avisos SET edificio_id = ?, titulo = ?, contenido = ?, tipo = ?, fecha_vencimiento = ? WHERE id = ?");
    $stmt->bind_param("issssi", $edificio_id, $titulo, $contenido, $tipo, $fecha_vencimiento, $id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Aviso actualizado exitosamente'], JSON_UNESCAPED_UNICODE);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al actualizar aviso: ' . $stmt->error], JSON_UNESCAPED_UNICODE);
    }
    
    $stmt->close();
}

function eliminarAviso($conn) {
    $id = $_POST['id'] ?? $_GET['id'] ?? 0;
    
    if (empty($id)) {
        echo json_encode(['success' => false, 'message' => 'ID de aviso requerido'], JSON_UNESCAPED_UNICODE);
        return;
    }
    
    // Validar permisos si es Administrador Edificio
    $rol_nombre = $_SESSION['rol_nombre'] ?? '';
    $usuario_id = $_SESSION['usuario_id'] ?? 0;
    
    if ($rol_nombre === 'Administrador Edificio') {
        $check = $conn->prepare("SELECT edificio_id FROM avisos WHERE id = ?");
        $check->bind_param("i", $id);
        $check->execute();
        $result = $check->get_result();
        $aviso = $result->fetch_assoc();
        $check->close();
        
        if (!$aviso) {
            echo json_encode(['success' => false, 'message' => 'Aviso no encontrado'], JSON_UNESCAPED_UNICODE);
            return;
        }
        
        // Si el aviso tiene edificio específico, verificar que esté en sus asignaciones
        if ($aviso['edificio_id'] !== null) {
            $check = $conn->prepare("SELECT id FROM usuario_edificios WHERE usuario_id = ? AND edificio_id = ? AND activo = 1");
            $check->bind_param("ii", $usuario_id, $aviso['edificio_id']);
            $check->execute();
            $result = $check->get_result();
            if ($result->num_rows === 0) {
                echo json_encode(['success' => false, 'message' => 'No tienes permiso para eliminar este aviso'], JSON_UNESCAPED_UNICODE);
                $check->close();
                return;
            }
            $check->close();
        }
    }
    
    // Soft delete
    $stmt = $conn->prepare("UPDATE avisos SET activo = 0 WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Aviso eliminado exitosamente'], JSON_UNESCAPED_UNICODE);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al eliminar aviso: ' . $stmt->error], JSON_UNESCAPED_UNICODE);
    }
    
    $stmt->close();
}

function obtenerAviso($conn) {
    $id = $_GET['id'] ?? 0;
    
    if (empty($id)) {
        echo json_encode(['success' => false, 'message' => 'ID de aviso requerido'], JSON_UNESCAPED_UNICODE);
        return;
    }
    
    $stmt = $conn->prepare("SELECT a.*, e.nombre as edificio_nombre 
                            FROM avisos a 
                            LEFT JOIN edificios e ON a.edificio_id = e.id 
                            WHERE a.id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        $aviso = [
            'id' => (int)$row['id'],
            'edificio_id' => $row['edificio_id'] ? (int)$row['edificio_id'] : null,
            'edificio_nombre' => $row['edificio_nombre'] ?? 'Todos los edificios',
            'titulo' => $row['titulo'],
            'contenido' => $row['contenido'],
            'tipo' => $row['tipo'],
            'fecha_publicacion' => $row['fecha_publicacion'],
            'fecha_vencimiento' => $row['fecha_vencimiento'],
            'activo' => (int)$row['activo']
        ];
        echo json_encode(['success' => true, 'aviso' => $aviso], JSON_UNESCAPED_UNICODE);
    } else {
        echo json_encode(['success' => false, 'message' => 'Aviso no encontrado'], JSON_UNESCAPED_UNICODE);
    }
    
    $stmt->close();
}
?>
