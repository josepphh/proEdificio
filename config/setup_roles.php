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
 * ❌ Inserta edificios de ejemplo (datos no deseados)
 * ❌ Usa ALTER TABLE (no es instalación limpia)
 * ❌ Falta columna 'activo' en roles y edificios
 * ❌ Falta columnas telefono y email en edificios
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
║  Falta el sistema de permisos completo.       ║
║                                                ║
║  USAR EN SU LUGAR:                             ║
║  • install/instalar.php (automático)           ║
║  • install/instalacion_limpia.sql (manual)     ║
║                                                ║
║  Documentación: install/INSTALACION.md         ║
║                                                ║
╚════════════════════════════════════════════════╝
");

require_once 'database.php';

$database = new Database();
$conn = $database->getConnection();

if (!$conn) {
    die("Error de conexión a la base de datos");
}

echo "Iniciando configuración de roles y edificios...<br><br>";

// Crear tabla de roles
$sql = "CREATE TABLE IF NOT EXISTS roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL UNIQUE,
    descripcion TEXT,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if ($conn->query($sql) === TRUE) {
    echo "✓ Tabla 'roles' creada exitosamente<br>";
} else {
    echo "Error creando tabla roles: " . $conn->error . "<br>";
}

// Crear tabla de edificios
$sql = "CREATE TABLE IF NOT EXISTS edificios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    direccion VARCHAR(255),
    ciudad VARCHAR(100) DEFAULT 'Lima',
    num_pisos INT,
    num_departamentos INT,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if ($conn->query($sql) === TRUE) {
    echo "✓ Tabla 'edificios' creada exitosamente<br>";
} else {
    echo "Error creando tabla edificios: " . $conn->error . "<br>";
}

// Insertar roles predefinidos
$roles = [
    ['Administrador Total', 'Acceso completo al sistema, gestión de todos los edificios y usuarios'],
    ['Administrador Edificio', 'Administra un edificio específico y sus inquilinos'],
    ['Inquilino', 'Usuario residente del edificio'],
    ['Seguridad', 'Personal de seguridad del edificio']
];

foreach ($roles as $rol) {
    $stmt = $conn->prepare("INSERT IGNORE INTO roles (nombre, descripcion) VALUES (?, ?)");
    $stmt->bind_param("ss", $rol[0], $rol[1]);
    if ($stmt->execute()) {
        echo "✓ Rol '{$rol[0]}' insertado<br>";
    }
    $stmt->close();
}

// Insertar edificios de ejemplo
$edificios = [
    ['Torre San Isidro', 'Av. Camino Real 1234, San Isidro', 'Lima', 15, 45],
    ['Edificio Miraflores', 'Av. Larco 567, Miraflores', 'Lima', 10, 30],
    ['Residencial Los Olivos', 'Av. Universitaria 890, Los Olivos', 'Lima', 8, 24]
];

foreach ($edificios as $edificio) {
    $stmt = $conn->prepare("INSERT IGNORE INTO edificios (nombre, direccion, ciudad, num_pisos, num_departamentos) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssii", $edificio[0], $edificio[1], $edificio[2], $edificio[3], $edificio[4]);
    if ($stmt->execute()) {
        echo "✓ Edificio '{$edificio[0]}' insertado<br>";
    }
    $stmt->close();
}

// Verificar si la columna rol_id ya existe en usuarios
$result = $conn->query("SHOW COLUMNS FROM usuarios LIKE 'rol_id'");
if ($result->num_rows == 0) {
    // Agregar columnas a la tabla usuarios
    $sql = "ALTER TABLE usuarios 
            ADD COLUMN rol_id INT DEFAULT 3,
            ADD COLUMN edificio_id INT NULL,
            ADD CONSTRAINT fk_usuario_rol FOREIGN KEY (rol_id) REFERENCES roles(id),
            ADD CONSTRAINT fk_usuario_edificio FOREIGN KEY (edificio_id) REFERENCES edificios(id)";
    
    if ($conn->query($sql) === TRUE) {
        echo "✓ Columnas 'rol_id' y 'edificio_id' agregadas a tabla usuarios<br>";
    } else {
        echo "Error modificando tabla usuarios: " . $conn->error . "<br>";
    }
} else {
    echo "✓ Columnas ya existen en tabla usuarios<br>";
}

echo "<br>✓ Configuración completada exitosamente<br>";
echo "<br><a href='../index.php'>Volver al inicio</a>";

$conn->close();
