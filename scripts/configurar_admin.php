<?php
require_once 'config/database.php';

echo "=== Verificando y configurando usuario administrador ===\n\n";

$database = new Database();
$conn = $database->getConnection();

if (!$conn) {
    die("❌ Error de conexión a la base de datos\n");
}

// Buscar usuario 'admin'
echo "1. Buscando usuario 'admin'...\n";
$result = $conn->query("SELECT id, username, nombre, email, rol_id, activo FROM usuarios WHERE username = 'admin'");

if ($result && $result->num_rows > 0) {
    $user = $result->fetch_assoc();
    echo "   ✅ Usuario 'admin' encontrado (ID: {$user['id']})\n";
    
    // Verificar password
    $pass_result = $conn->query("SELECT password FROM usuarios WHERE username = 'admin'");
    $pass_data = $pass_result->fetch_assoc();
    
    if (password_verify('admin123', $pass_data['password'])) {
        echo "   ✅ Password 'admin123' es VÁLIDO\n\n";
    } else {
        echo "   ⚠️  Password NO coincide. Actualizando...\n";
        $new_hash = password_hash('admin123', PASSWORD_DEFAULT);
        $update_stmt = $conn->prepare("UPDATE usuarios SET password = ? WHERE username = 'admin'");
        $update_stmt->bind_param("s", $new_hash);
        
        if ($update_stmt->execute()) {
            echo "   ✅ Password actualizado a 'admin123'\n\n";
        }
    }
} else {
    echo "   ⚠️  Usuario 'admin' NO encontrado\n";
    echo "   🔧 Creando usuario...\n";
    
    $password_hash = password_hash('admin123', PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO usuarios (nombre, email, username, password, rol_id, activo) VALUES (?, ?, ?, ?, ?, 1)");
    $nombre = "Administrador Total";
    $email = "admin@edificios.com";
    $username = "admin";
    $rol_id = 1;
    
    $stmt->bind_param("ssssi", $nombre, $email, $username, $password_hash, $rol_id);
    
    if ($stmt->execute()) {
        echo "   ✅ Usuario 'admin' creado exitosamente\n\n";
    } else {
        echo "   ❌ Error: " . $stmt->error . "\n\n";
    }
}

// Buscar usuario 'adm'
echo "2. Buscando usuario 'adm'...\n";
$result2 = $conn->query("SELECT id, username, nombre, email, rol_id, activo FROM usuarios WHERE username = 'adm'");

if ($result2 && $result2->num_rows > 0) {
    $user = $result2->fetch_assoc();
    echo "   ✅ Usuario 'adm' encontrado (ID: {$user['id']})\n";
    
    // Verificar password
    $pass_result = $conn->query("SELECT password FROM usuarios WHERE username = 'adm'");
    $pass_data = $pass_result->fetch_assoc();
    
    if (password_verify('adm', $pass_data['password'])) {
        echo "   ✅ Password 'adm' es VÁLIDO\n\n";
    } else {
        echo "   ⚠️  Password NO coincide. Actualizando...\n";
        $new_hash = password_hash('adm', PASSWORD_DEFAULT);
        $update_stmt = $conn->prepare("UPDATE usuarios SET password = ? WHERE username = 'adm'");
        $update_stmt->bind_param("s", $new_hash);
        
        if ($update_stmt->execute()) {
            echo "   ✅ Password actualizado a 'adm'\n\n";
        }
    }
} else {
    echo "   ⚠️  Usuario 'adm' NO encontrado\n";
    echo "   🔧 Creando usuario...\n";
    
    $password_hash = password_hash('adm', PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO usuarios (nombre, email, username, password, rol_id, activo) VALUES (?, ?, ?, ?, ?, 1)");
    $nombre = "Admin Total";
    $email = "adm@edificios.com";
    $username = "adm";
    $rol_id = 1;
    
    $stmt->bind_param("ssssi", $nombre, $email, $username, $password_hash, $rol_id);
    
    if ($stmt->execute()) {
        echo "   ✅ Usuario 'adm' creado exitosamente\n\n";
    } else {
        echo "   ❌ Error: " . $stmt->error . "\n\n";
    }
}

echo "════════════════════════════════════════════════\n";
echo "✅ CONFIGURACIÓN COMPLETADA\n\n";
echo "Puedes usar cualquiera de estas credenciales:\n\n";
echo "Opción 1:\n";
echo "   Usuario: admin\n";
echo "   Password: admin123\n\n";
echo "Opción 2:\n";
echo "   Usuario: adm\n";
echo "   Password: adm\n";
echo "════════════════════════════════════════════════\n";

$conn->close();
?>
