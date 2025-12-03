<?php
require_once __DIR__ . '/../config/database.php';

$database = new Database();
$conn = $database->getConnection();

// Reactivar el permiso acceso_completo
$sql = "UPDATE permisos 
        SET activo = 1, 
            nombre = 'Acceso Completo', 
            descripcion = 'Muestra todas las opciones en el Panel Admin (el acceso real a las páginas sigue requiriendo permisos específicos)'
        WHERE codigo = 'acceso_completo'";

if ($conn->query($sql)) {
    echo "✓ Permiso 'acceso_completo' reactivado correctamente\n";
} else {
    echo "✗ Error: " . $conn->error . "\n";
}

$conn->close();
