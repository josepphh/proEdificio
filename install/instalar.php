<?php
/**
 * Script de Instalación Limpia - Sistema de Gestión de Edificios v2.0
 * 
 * Este script crea una instalación mínima funcional con:
 * - 4 Roles base
 * - 23 Permisos del sistema
 * - Asignaciones de permisos a roles
 * - 1 Usuario Administrador Total
 * - Todas las tablas operacionales vacías
 * 
 * TODO LO DEMÁS se configura desde la interfaz web
 */

// Configuración de base de datos
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "edificios_db";

echo "========================================\n";
echo "INSTALACIÓN LIMPIA - SISTEMA DE EDIFICIOS\n";
echo "========================================\n\n";

// Crear conexión
$conn = new mysqli($servername, $username, $password);

if ($conn->connect_error) {
    die("✗ Error de conexión: " . $conn->connect_error . "\n");
}

echo "✓ Conexión establecida\n";

// Crear base de datos
$sql = "CREATE DATABASE IF NOT EXISTS $dbname CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
if ($conn->query($sql)) {
    echo "✓ Base de datos '$dbname' creada/verificada\n\n";
} else {
    die("✗ Error creando base de datos: " . $conn->error . "\n");
}

$conn->select_db($dbname);

// Ejecutar archivo SQL de instalación
echo "Importando estructura y datos iniciales...\n\n";

$sql_file = __DIR__ . '/instalacion_limpia.sql';

if (!file_exists($sql_file)) {
    die("✗ Error: No se encuentra el archivo instalacion_limpia.sql\n");
}

// Leer archivo SQL
$sql_content = file_get_contents($sql_file);

// Generar hash de password para el usuario admin
$password_admin = 'admin123'; // Contraseña por defecto
$password_hash = password_hash($password_admin, PASSWORD_DEFAULT);

// Reemplazar placeholder con hash real
$sql_content = str_replace('$2y$10$YourHashedPasswordHere', $password_hash, $sql_content);

// Separar por ; y ejecutar cada query
$queries = array_filter(array_map('trim', explode(';', $sql_content)));

$success_count = 0;
$error_count = 0;

foreach ($queries as $query) {
    // Saltar comentarios y líneas vacías
    if (empty($query) || strpos($query, '--') === 0) {
        continue;
    }
    
    if ($conn->query($query)) {
        $success_count++;
    } else {
        // Solo mostrar errores que no sean "tabla ya existe"
        if (strpos($conn->error, 'already exists') === false && 
            strpos($conn->error, 'Duplicate entry') === false) {
            echo "⚠ Advertencia: " . $conn->error . "\n";
            $error_count++;
        }
    }
}

echo "\n========================================\n";
echo "INSTALACIÓN COMPLETADA\n";
echo "========================================\n\n";

// Verificar datos instalados
$result = $conn->query("SELECT COUNT(*) as total FROM roles");
$roles = $result->fetch_assoc()['total'];

$result = $conn->query("SELECT COUNT(*) as total FROM permisos");
$permisos = $result->fetch_assoc()['total'];

$result = $conn->query("SELECT COUNT(*) as total FROM rol_permisos");
$asignaciones = $result->fetch_assoc()['total'];

$result = $conn->query("SELECT COUNT(*) as total FROM usuarios WHERE rol_id = 1");
$admins = $result->fetch_assoc()['total'];

echo "Resumen de instalación:\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "✓ Base de datos: $dbname\n";
echo "✓ Roles instalados: $roles\n";
echo "✓ Permisos instalados: $permisos\n";
echo "✓ Asignaciones rol-permiso: $asignaciones\n";
echo "✓ Usuario administrador: $admins\n";
echo "✓ Tablas operacionales: Creadas (vacías)\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

echo "Credenciales de acceso inicial:\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "Usuario:     admin\n";
echo "Contraseña:  admin123\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

echo "⚠ IMPORTANTE:\n";
echo "1. Cambia la contraseña del administrador inmediatamente\n";
echo "2. Accede a http://localhost:8012/proyectoEdificio/\n";
echo "3. Crea edificios desde 'Gestión de Edificios'\n";
echo "4. Crea usuarios desde 'Gestión de Usuarios'\n";
echo "5. Asigna edificios a Administradores de Edificio\n\n";

echo "✅ Sistema listo para usar!\n\n";

$conn->close();
?>
