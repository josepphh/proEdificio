<?php
/**
 * Script para crear la tabla de solicitudes de acceso
 * Ejecutar una sola vez para crear la estructura en la base de datos
 */

require_once 'database.php';

$database = new Database();
$conn = $database->getConnection();

if (!$conn) {
    die("Error: No se pudo conectar a la base de datos");
}

// SQL para crear la tabla de solicitudes
$sql = "CREATE TABLE IF NOT EXISTS solicitudes_acceso (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre_completo VARCHAR(200) NOT NULL,
    email VARCHAR(150) NOT NULL,
    telefono VARCHAR(20),
    nombre_edificio VARCHAR(200) NOT NULL,
    direccion_edificio TEXT,
    num_departamentos INT,
    mensaje TEXT,
    estado ENUM('PENDIENTE', 'APROBADA', 'RECHAZADA') DEFAULT 'PENDIENTE',
    fecha_solicitud TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_respuesta TIMESTAMP NULL,
    respuesta_admin TEXT,
    usuario_creado_id INT NULL,
    activo TINYINT(1) DEFAULT 1,
    INDEX idx_email (email),
    INDEX idx_estado (estado),
    INDEX idx_fecha (fecha_solicitud)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

if ($conn->query($sql)) {
    echo "✅ Tabla 'solicitudes_acceso' creada exitosamente\n";
    
    // Verificar la estructura
    $result = $conn->query("DESCRIBE solicitudes_acceso");
    echo "\n📋 Estructura de la tabla:\n";
    echo str_repeat("-", 80) . "\n";
    printf("%-25s %-20s %-10s %-10s\n", "Campo", "Tipo", "Nulo", "Extra");
    echo str_repeat("-", 80) . "\n";
    
    while ($row = $result->fetch_assoc()) {
        printf("%-25s %-20s %-10s %-10s\n", 
            $row['Field'], 
            $row['Type'], 
            $row['Null'], 
            $row['Extra']
        );
    }
    
    echo "\n✨ Sistema de solicitudes de acceso listo para usar!\n";
} else {
    echo "❌ Error al crear la tabla: " . $conn->error . "\n";
}

$conn->close();
?>
