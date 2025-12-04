<?php
require_once '../includes/session.php';
require_once '../config/database.php';

$database = new Database();
$conn = $database->getConnection();

// Obtener todas las solicitudes
$sql = "SELECT id, nombre_completo, email, estado FROM solicitudes_acceso WHERE activo = 1 ORDER BY id DESC";
$result = $conn->query($sql);

echo "<h2>Estado de Solicitudes</h2>";
echo "<table border='1' cellpadding='10'>";
echo "<tr><th>ID</th><th>Nombre</th><th>Email</th><th>Estado</th></tr>";

while ($row = $result->fetch_assoc()) {
    echo "<tr>";
    echo "<td>" . $row['id'] . "</td>";
    echo "<td>" . htmlspecialchars($row['nombre_completo']) . "</td>";
    echo "<td>" . htmlspecialchars($row['email']) . "</td>";
    echo "<td><strong>" . $row['estado'] . "</strong></td>";
    echo "</tr>";
}

echo "</table>";

$conn->close();
?>
