<?php
require_once __DIR__ . '/../config/database.php';

$database = new Database();
$conn = $database->getConnection();

echo "========================================\n";
echo "VERIFICACIÓN DE USUARIOS CREADOS\n";
echo "========================================\n\n";

// Contar usuarios por rol y edificio
$query = "SELECT 
            e.nombre as edificio,
            r.nombre as rol,
            COUNT(DISTINCT u.id) as total_usuarios,
            GROUP_CONCAT(DISTINCT u.username ORDER BY u.username SEPARATOR ', ') as usuarios
          FROM edificios e
          LEFT JOIN usuario_edificios ue ON e.id = ue.edificio_id AND ue.activo = 1
          LEFT JOIN usuarios u ON ue.usuario_id = u.id AND u.activo = 1
          LEFT JOIN roles r ON u.rol_id = r.id
          WHERE e.activo = 1 AND r.nombre IN ('Inquilino', 'Seguridad')
          GROUP BY e.id, e.nombre, r.id, r.nombre
          ORDER BY e.nombre, r.nombre";

$result = $conn->query($query);

$edificio_actual = '';
while ($row = $result->fetch_assoc()) {
    if ($edificio_actual != $row['edificio']) {
        if ($edificio_actual != '') echo "\n";
        echo "📍 {$row['edificio']}\n";
        echo str_repeat("-", 60) . "\n";
        $edificio_actual = $row['edificio'];
    }
    
    echo "  {$row['rol']}: {$row['total_usuarios']} usuario(s)\n";
    if ($row['usuarios']) {
        $usuarios_array = explode(', ', $row['usuarios']);
        foreach ($usuarios_array as $usuario) {
            echo "    - $usuario\n";
        }
    }
}

echo "\n========================================\n";
echo "RESUMEN TOTAL\n";
echo "========================================\n";

$total_inquilinos = $conn->query("SELECT COUNT(*) as total FROM usuarios u 
                                   INNER JOIN roles r ON u.rol_id = r.id 
                                   WHERE r.nombre = 'Inquilino' AND u.activo = 1")->fetch_assoc()['total'];

$total_seguridad = $conn->query("SELECT COUNT(*) as total FROM usuarios u 
                                  INNER JOIN roles r ON u.rol_id = r.id 
                                  WHERE r.nombre = 'Seguridad' AND u.activo = 1")->fetch_assoc()['total'];

echo "Total Inquilinos: $total_inquilinos\n";
echo "Total Seguridad: $total_seguridad\n";
echo "Total General: " . ($total_inquilinos + $total_seguridad) . "\n";
echo "\n";
echo "Contraseña para todos: password123\n";

$conn->close();
?>
