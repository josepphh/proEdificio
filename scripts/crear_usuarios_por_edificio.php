<?php
require_once __DIR__ . '/../config/database.php';

echo "========================================\n";
echo "CREAR USUARIOS POR EDIFICIO\n";
echo "========================================\n\n";

$database = new Database();
$conn = $database->getConnection();

if (!$conn) {
    die("Error: No se pudo conectar a la base de datos\n");
}

// Obtener todos los edificios activos
$edificios_query = "SELECT id, nombre FROM edificios WHERE activo = 1 ORDER BY nombre";
$edificios_result = $conn->query($edificios_query);

if (!$edificios_result || $edificios_result->num_rows === 0) {
    die("No se encontraron edificios activos en la base de datos\n");
}

echo "Edificios encontrados: " . $edificios_result->num_rows . "\n\n";

// Obtener IDs de roles
$rol_inquilino_query = "SELECT id FROM roles WHERE nombre = 'Inquilino'";
$rol_inquilino_result = $conn->query($rol_inquilino_query);
$rol_inquilino = $rol_inquilino_result->fetch_assoc();
$rol_inquilino_id = $rol_inquilino['id'];

$rol_seguridad_query = "SELECT id FROM roles WHERE nombre = 'Seguridad'";
$rol_seguridad_result = $conn->query($rol_seguridad_query);
$rol_seguridad = $rol_seguridad_result->fetch_assoc();
$rol_seguridad_id = $rol_seguridad['id'];

echo "ID Rol Inquilino: $rol_inquilino_id\n";
echo "ID Rol Seguridad: $rol_seguridad_id\n\n";

// Contraseña por defecto (hasheada)
$password_default = password_hash('password123', PASSWORD_DEFAULT);

$total_usuarios_creados = 0;
$total_asignaciones = 0;

// Procesar cada edificio
while ($edificio = $edificios_result->fetch_assoc()) {
    $edificio_id = $edificio['id'];
    $edificio_nombre = $edificio['nombre'];
    
    echo "========================================\n";
    echo "Procesando: $edificio_nombre (ID: $edificio_id)\n";
    echo "========================================\n";
    
    // Crear 3 inquilinos
    for ($i = 1; $i <= 3; $i++) {
        $nombre = "Inquilino $i - $edificio_nombre";
        $username = strtolower("inquilino{$i}_edif{$edificio_id}");
        $email = strtolower("inquilino{$i}.edificio{$edificio_id}@ejemplo.com");
        
        // Verificar si el usuario ya existe
        $check_query = "SELECT id FROM usuarios WHERE username = ? OR email = ?";
        $stmt_check = $conn->prepare($check_query);
        $stmt_check->bind_param("ss", $username, $email);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();
        
        if ($result_check->num_rows > 0) {
            echo "  ⚠️  Usuario ya existe: $username\n";
            $stmt_check->close();
            continue;
        }
        $stmt_check->close();
        
        // Insertar usuario
        $insert_query = "INSERT INTO usuarios (nombre, email, username, password, rol_id, activo, fecha_registro) 
                         VALUES (?, ?, ?, ?, ?, 1, NOW())";
        $stmt = $conn->prepare($insert_query);
        $stmt->bind_param("ssssi", $nombre, $email, $username, $password_default, $rol_inquilino_id);
        
        if ($stmt->execute()) {
            $usuario_id = $stmt->insert_id;
            echo "  ✅ Inquilino creado: $nombre (ID: $usuario_id, User: $username, Pass: password123)\n";
            
            // Asignar edificio
            $asignar_query = "INSERT INTO usuario_edificios (usuario_id, edificio_id, activo, fecha_asignacion) 
                             VALUES (?, ?, 1, NOW())";
            $stmt_asignar = $conn->prepare($asignar_query);
            $stmt_asignar->bind_param("ii", $usuario_id, $edificio_id);
            
            if ($stmt_asignar->execute()) {
                echo "     🏢 Asignado a: $edificio_nombre\n";
                $total_asignaciones++;
            }
            $stmt_asignar->close();
            
            $total_usuarios_creados++;
        } else {
            echo "  ❌ Error al crear: $nombre - " . $stmt->error . "\n";
        }
        $stmt->close();
    }
    
    echo "\n";
    
    // Crear 2 guardias de seguridad
    for ($i = 1; $i <= 2; $i++) {
        $nombre = "Seguridad $i - $edificio_nombre";
        $username = strtolower("seguridad{$i}_edif{$edificio_id}");
        $email = strtolower("seguridad{$i}.edificio{$edificio_id}@ejemplo.com");
        
        // Verificar si el usuario ya existe
        $check_query = "SELECT id FROM usuarios WHERE username = ? OR email = ?";
        $stmt_check = $conn->prepare($check_query);
        $stmt_check->bind_param("ss", $username, $email);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();
        
        if ($result_check->num_rows > 0) {
            echo "  ⚠️  Usuario ya existe: $username\n";
            $stmt_check->close();
            continue;
        }
        $stmt_check->close();
        
        // Insertar usuario
        $insert_query = "INSERT INTO usuarios (nombre, email, username, password, rol_id, activo, fecha_registro) 
                         VALUES (?, ?, ?, ?, ?, 1, NOW())";
        $stmt = $conn->prepare($insert_query);
        $stmt->bind_param("ssssi", $nombre, $email, $username, $password_default, $rol_seguridad_id);
        
        if ($stmt->execute()) {
            $usuario_id = $stmt->insert_id;
            echo "  ✅ Seguridad creado: $nombre (ID: $usuario_id, User: $username, Pass: password123)\n";
            
            // Asignar edificio
            $asignar_query = "INSERT INTO usuario_edificios (usuario_id, edificio_id, activo, fecha_asignacion) 
                             VALUES (?, ?, 1, NOW())";
            $stmt_asignar = $conn->prepare($asignar_query);
            $stmt_asignar->bind_param("ii", $usuario_id, $edificio_id);
            
            if ($stmt_asignar->execute()) {
                echo "     🏢 Asignado a: $edificio_nombre\n";
                $total_asignaciones++;
            }
            $stmt_asignar->close();
            
            $total_usuarios_creados++;
        } else {
            echo "  ❌ Error al crear: $nombre - " . $stmt->error . "\n";
        }
        $stmt->close();
    }
    
    echo "\n";
}

echo "========================================\n";
echo "RESUMEN FINAL\n";
echo "========================================\n";
echo "Total de usuarios creados: $total_usuarios_creados\n";
echo "Total de asignaciones realizadas: $total_asignaciones\n";
echo "\n";
echo "CREDENCIALES DE ACCESO:\n";
echo "Usuario: [ver arriba cada username]\n";
echo "Contraseña: password123 (para todos)\n";
echo "\n";

$conn->close();
echo "✅ Proceso completado exitosamente\n";
?>
