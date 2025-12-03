<?php
/**
 * ========================================
 * ⚠️ SCRIPT DEPRECADO - NO USAR ⚠️
 * ========================================
 * Este script está obsoleto y NO crea el sistema completo.
 * 
 * USAR EN SU LUGAR:
 * - install/instalar.php (instalación automática)
 * - install/instalacion_limpia.sql (instalación manual)
 * 
 * RAZONES POR LAS QUE ESTE SCRIPT ESTÁ DEPRECADO:
 * ❌ No crea la tabla de permisos
 * ❌ No crea la tabla de rol_permisos  
 * ❌ No inserta los 23 permisos del sistema
 * ❌ No inserta las 44 asignaciones rol-permiso
 * ❌ No crea usuario_edificios (relación N:N)
 * ❌ Estructura de usuarios incompleta (sin rol_id)
 * ❌ No crea servicios, medidores, ciclos, etc.
 * 
 * DOCUMENTACIÓN COMPLETA:
 * - install/INSTALACION.md - Guía de instalación
 * - docs/SISTEMA_PERMISOS.md - Sistema de permisos
 * ========================================
 */

die("
╔════════════════════════════════════════════════╗
║       ⚠️  SCRIPT DEPRECADO - NO USAR  ⚠️        ║
╠════════════════════════════════════════════════╣
║                                                ║
║  Este script NO crea el sistema completo.     ║
║                                                ║
║  USAR EN SU LUGAR:                             ║
║  • install/instalar.php (automático)           ║
║  • install/instalacion_limpia.sql (manual)     ║
║                                                ║
║  Documentación: install/INSTALACION.md         ║
║                                                ║
╚════════════════════════════════════════════════╝
");

$servername = "localhost";  // MySQL usa el puerto 3306 por defecto
$username = "root";
$password = "";
$dbname = "edificios_db";

// Crear conexión
$conn = new mysqli($servername, $username, $password);

// Verificar conexión
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Crear base de datos
$sql = "CREATE DATABASE IF NOT EXISTS edificios_db";
if ($conn->query($sql) === TRUE) {
    echo "Base de datos creada exitosamente<br>";
} else {
    echo "Error creando base de datos: " . $conn->error . "<br>";
}

// Seleccionar la base de datos
$conn->select_db($dbname);

// Crear tabla usuarios
$sql = "CREATE TABLE IF NOT EXISTS usuarios (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if ($conn->query($sql) === TRUE) {
    echo "Tabla usuarios creada exitosamente<br>";
} else {
    echo "Error creando tabla: " . $conn->error . "<br>";
}

$conn->close();
echo "Configuración completada.";
?>