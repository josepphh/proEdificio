<?php
/**
 * API para obtener los permisos de un rol específico
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

$rol_id = isset($_GET['rol_id']) ? intval($_GET['rol_id']) : 0;

if ($rol_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID de rol inválido']);
    exit;
}

try {
    // Obtener información del rol
    $sql_rol = "SELECT id, nombre, descripcion FROM roles WHERE id = ? AND activo = 1";
    $stmt = $conn->prepare($sql_rol);
    $stmt->bind_param("i", $rol_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Rol no encontrado']);
        exit;
    }
    
    $rol = $result->fetch_assoc();
    $stmt->close();
    
    // Obtener permisos del rol
    $sql_permisos = "SELECT p.id, p.codigo, p.nombre, p.descripcion, p.categoria
                     FROM permisos p
                     INNER JOIN rol_permisos rp ON p.id = rp.permiso_id
                     WHERE rp.rol_id = ? AND p.activo = 1
                     ORDER BY p.categoria, p.nombre";
    
    $stmt = $conn->prepare($sql_permisos);
    $stmt->bind_param("i", $rol_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $permisos = [];
    while ($row = $result->fetch_assoc()) {
        $permisos[] = $row;
    }
    $stmt->close();
    
    echo json_encode([
        'success' => true,
        'rol' => $rol,
        'permisos' => $permisos
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error al obtener permisos: ' . $e->getMessage()
    ]);
}

$conn->close();
