<?php
/**
 * API para actualizar los permisos de un rol
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/permissions.php';

header('Content-Type: application/json');

// Verificar que tenga permiso
if (!tienePermiso('asignar_permisos')) {
    echo json_encode(['success' => false, 'message' => 'No tienes permiso para realizar esta acción']);
    exit;
}

$database = new Database();
$conn = $database->getConnection();

// Leer datos JSON
$data = json_decode(file_get_contents('php://input'), true);

$rol_id = isset($data['rol_id']) ? intval($data['rol_id']) : 0;
$permisos = isset($data['permisos']) ? $data['permisos'] : [];

if ($rol_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID de rol inválido']);
    exit;
}

// Validar que el rol existe
$sql_check = "SELECT id, nombre FROM roles WHERE id = ? AND activo = 1";
$stmt = $conn->prepare($sql_check);
$stmt->bind_param("i", $rol_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Rol no encontrado']);
    exit;
}

$rol = $result->fetch_assoc();
$stmt->close();

try {
    // Iniciar transacción
    $conn->begin_transaction();
    
    // Eliminar todos los permisos actuales del rol
    $sql_delete = "DELETE FROM rol_permisos WHERE rol_id = ?";
    $stmt = $conn->prepare($sql_delete);
    $stmt->bind_param("i", $rol_id);
    $stmt->execute();
    $stmt->close();
    
    // Insertar los nuevos permisos
    if (!empty($permisos)) {
        $sql_insert = "INSERT INTO rol_permisos (rol_id, permiso_id) VALUES (?, ?)";
        $stmt = $conn->prepare($sql_insert);
        
        foreach ($permisos as $permiso_id) {
            $permiso_id = intval($permiso_id);
            if ($permiso_id > 0) {
                $stmt->bind_param("ii", $rol_id, $permiso_id);
                $stmt->execute();
            }
        }
        $stmt->close();
    }
    
    // Commit de la transacción
    $conn->commit();
    
    // Limpiar caché de permisos en sesión para este rol
    $cache_key = 'permisos_rol_' . $rol_id;
    if (isset($_SESSION[$cache_key])) {
        unset($_SESSION[$cache_key]);
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Permisos actualizados correctamente',
        'rol' => $rol['nombre'],
        'total_permisos' => count($permisos)
    ]);
    
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode([
        'success' => false,
        'message' => 'Error al actualizar permisos: ' . $e->getMessage()
    ]);
}

$conn->close();
