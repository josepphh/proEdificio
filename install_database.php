<?php
/**
 * ========================================
 * INSTALADOR COMPLETO DE BASE DE DATOS
 * Sistema de Gestión de Edificios
 * ========================================
 * 
 * Este script instala TODO lo necesario:
 * - Crea la base de datos
 * - Crea todas las tablas
 * - Inserta roles y datos iniciales
 * - Crea usuarios administradores
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "╔════════════════════════════════════════════════════════╗\n";
echo "║   INSTALADOR DE BASE DE DATOS - SISTEMA DE EDIFICIOS  ║\n";
echo "╚════════════════════════════════════════════════════════╝\n\n";

// Configuración de la base de datos
$host = "localhost";
$username = "root";
$password = "";
$dbname = "edificios_db";

// ==========================================
// PASO 1: Conectar a MySQL y crear base de datos
// ==========================================
echo "📡 PASO 1: Conectando a MySQL...\n";
$conn = new mysqli($host, $username, $password);

if ($conn->connect_error) {
    die("❌ ERROR: No se pudo conectar a MySQL: " . $conn->connect_error . "\n");
}
echo "✅ Conexión exitosa a MySQL\n\n";

// Crear base de datos
echo "🗄️  PASO 2: Creando base de datos '$dbname'...\n";
$sql = "CREATE DATABASE IF NOT EXISTS $dbname CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
if ($conn->query($sql) === TRUE) {
    echo "✅ Base de datos '$dbname' creada exitosamente\n\n";
} else {
    die("❌ ERROR creando base de datos: " . $conn->error . "\n");
}

// Seleccionar la base de datos
$conn->select_db($dbname);

// ==========================================
// PASO 3: Crear tabla USUARIOS
// ==========================================
echo "👥 PASO 3: Creando tabla 'usuarios'...\n";
$sql = "CREATE TABLE IF NOT EXISTS usuarios (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    telefono VARCHAR(20) NULL,
    activo TINYINT(1) DEFAULT 1,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_username (username),
    INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

if ($conn->query($sql) === TRUE) {
    echo "✅ Tabla 'usuarios' creada exitosamente\n\n";
} else {
    die("❌ ERROR creando tabla usuarios: " . $conn->error . "\n");
}

// ==========================================
// PASO 4: Crear tabla ROLES
// ==========================================
echo "🎭 PASO 4: Creando tabla 'roles'...\n";
$sql = "CREATE TABLE IF NOT EXISTS roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL UNIQUE,
    descripcion TEXT,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_nombre (nombre)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

if ($conn->query($sql) === TRUE) {
    echo "✅ Tabla 'roles' creada exitosamente\n\n";
} else {
    die("❌ ERROR creando tabla roles: " . $conn->error . "\n");
}

// ==========================================
// PASO 5: Crear tabla EDIFICIOS
// ==========================================
echo "🏢 PASO 5: Creando tabla 'edificios'...\n";
$sql = "CREATE TABLE IF NOT EXISTS edificios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    direccion VARCHAR(255),
    ciudad VARCHAR(100) DEFAULT 'Lima',
    num_pisos INT,
    num_departamentos INT,
    activo TINYINT(1) DEFAULT 1,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_nombre (nombre),
    INDEX idx_activo (activo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

if ($conn->query($sql) === TRUE) {
    echo "✅ Tabla 'edificios' creada exitosamente\n\n";
} else {
    die("❌ ERROR creando tabla edificios: " . $conn->error . "\n");
}

// Agregar columna activo si la tabla ya existía
$result = $conn->query("SHOW COLUMNS FROM edificios LIKE 'activo'");
if ($result->num_rows == 0) {
    $sql = "ALTER TABLE edificios ADD COLUMN activo TINYINT(1) DEFAULT 1 AFTER num_departamentos";
    if ($conn->query($sql) === TRUE) {
        echo "✅ Columna 'activo' agregada a tabla edificios\n\n";
    }
}

// ==========================================
// PASO 6: Agregar columnas de relación a USUARIOS
// ==========================================
echo "🔗 PASO 6: Agregando relaciones a tabla 'usuarios'...\n";

// Verificar si la columna rol_id ya existe
$result = $conn->query("SHOW COLUMNS FROM usuarios LIKE 'rol_id'");
if ($result->num_rows == 0) {
    $sql = "ALTER TABLE usuarios 
            ADD COLUMN rol_id INT DEFAULT 3,
            ADD COLUMN edificio_id INT NULL,
            ADD COLUMN departamento VARCHAR(20) NULL";
    
    if ($conn->query($sql) === TRUE) {
        echo "✅ Columnas 'rol_id', 'edificio_id' y 'departamento' agregadas\n";
    } else {
        echo "⚠️  ADVERTENCIA: " . $conn->error . "\n";
    }
} else {
    echo "✅ Las columnas de relación ya existen\n";
}

// Agregar claves foráneas (solo si no existen)
$result = $conn->query("SELECT CONSTRAINT_NAME FROM information_schema.TABLE_CONSTRAINTS 
                        WHERE TABLE_SCHEMA = '$dbname' AND TABLE_NAME = 'usuarios' 
                        AND CONSTRAINT_NAME = 'fk_usuario_rol'");

if ($result->num_rows == 0) {
    $sql = "ALTER TABLE usuarios 
            ADD CONSTRAINT fk_usuario_rol FOREIGN KEY (rol_id) REFERENCES roles(id) ON DELETE SET NULL,
            ADD CONSTRAINT fk_usuario_edificio FOREIGN KEY (edificio_id) REFERENCES edificios(id) ON DELETE SET NULL";
    
    if ($conn->query($sql) === TRUE) {
        echo "✅ Claves foráneas agregadas exitosamente\n\n";
    } else {
        echo "⚠️  ADVERTENCIA: " . $conn->error . "\n\n";
    }
} else {
    echo "✅ Claves foráneas ya existen\n\n";
}

// ==========================================
// PASO 7: Insertar ROLES predefinidos
// ==========================================
echo "🎯 PASO 7: Insertando roles del sistema...\n";

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
        echo "   ✓ Rol '{$rol[0]}' insertado\n";
    }
    $stmt->close();
}
echo "\n";

// ==========================================
// PASO 8: Crear tabla SOLICITUDES_ACCESO
// ==========================================
echo "📬 PASO 8: Creando tabla 'solicitudes_acceso'...\n";
$sql = "CREATE TABLE IF NOT EXISTS solicitudes_acceso (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre_completo VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    telefono VARCHAR(20),
    nombre_edificio VARCHAR(100) NOT NULL,
    direccion_edificio VARCHAR(255),
    num_departamentos INT,
    mensaje TEXT,
    estado ENUM('pendiente', 'aprobada', 'rechazada') DEFAULT 'pendiente',
    activo TINYINT(1) DEFAULT 1,
    fecha_solicitud TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_respuesta TIMESTAMP NULL,
    INDEX idx_estado (estado),
    INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

if ($conn->query($sql) === TRUE) {
    echo "✅ Tabla 'solicitudes_acceso' creada\n\n";
} else {
    echo "⚠️  ADVERTENCIA: " . $conn->error . "\n\n";
}

// ==========================================
// PASO 9: Crear tabla USUARIO_EDIFICIOS (relación muchos a muchos)
// ==========================================
echo "🔗 PASO 9: Creando tabla 'usuario_edificios'...\n";
$sql = "CREATE TABLE IF NOT EXISTS usuario_edificios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    edificio_id INT NOT NULL,
    activo TINYINT(1) DEFAULT 1,
    fecha_asignacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (edificio_id) REFERENCES edificios(id) ON DELETE CASCADE,
    UNIQUE KEY unique_usuario_edificio (usuario_id, edificio_id),
    INDEX idx_usuario (usuario_id),
    INDEX idx_edificio (edificio_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

if ($conn->query($sql) === TRUE) {
    echo "✅ Tabla 'usuario_edificios' creada\n\n";
} else {
    echo "⚠️  ADVERTENCIA: " . $conn->error . "\n\n";
}

// ==========================================
// PASO 10: Crear tabla AVISOS
// ==========================================
echo "📢 PASO 10: Creando tabla 'avisos'...\n";
$sql = "CREATE TABLE IF NOT EXISTS avisos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    edificio_id INT NULL COMMENT 'NULL = aviso general para todos',
    titulo VARCHAR(255) NOT NULL,
    contenido TEXT NOT NULL,
    tipo ENUM('INFORMATIVO', 'URGENTE', 'MANTENIMIENTO', 'EVENTO') DEFAULT 'INFORMATIVO',
    fecha_publicacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    fecha_vencimiento DATE NULL,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    FOREIGN KEY (edificio_id) REFERENCES edificios(id) ON DELETE CASCADE,
    INDEX idx_edificio (edificio_id),
    INDEX idx_tipo (tipo),
    INDEX idx_fecha (fecha_publicacion)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

if ($conn->query($sql) === TRUE) {
    echo "✅ Tabla 'avisos' creada\n\n";
} else {
    echo "⚠️  ADVERTENCIA: " . $conn->error . "\n\n";
}

// ==========================================
// PASO 11: Crear tabla INCIDENCIAS
// ==========================================
echo "🛠️  PASO 11: Creando tabla 'incidencias'...\n";
$sql = "CREATE TABLE IF NOT EXISTS incidencias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    edificio_id INT NOT NULL,
    titulo VARCHAR(255) NOT NULL,
    descripcion TEXT NOT NULL,
    tipo ENUM('MANTENIMIENTO', 'SEGURIDAD', 'LIMPIEZA', 'OTRO') DEFAULT 'OTRO',
    prioridad ENUM('BAJA', 'MEDIA', 'ALTA', 'URGENTE') DEFAULT 'MEDIA',
    estado ENUM('PENDIENTE', 'EN_PROCESO', 'RESUELTA', 'CANCELADA') DEFAULT 'PENDIENTE',
    fecha_reporte DATETIME DEFAULT CURRENT_TIMESTAMP,
    fecha_resolucion DATETIME NULL,
    observaciones TEXT,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (edificio_id) REFERENCES edificios(id) ON DELETE CASCADE,
    INDEX idx_usuario (usuario_id),
    INDEX idx_edificio (edificio_id),
    INDEX idx_estado (estado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

if ($conn->query($sql) === TRUE) {
    echo "✅ Tabla 'incidencias' creada\n\n";
} else {
    echo "⚠️  ADVERTENCIA: " . $conn->error . "\n\n";
}

// ==========================================
// PASO 12: Crear tabla COMENTARIOS_INCIDENCIA
// ==========================================
echo "💬 PASO 12: Creando tabla 'comentarios_incidencia'...\n";
$sql = "CREATE TABLE IF NOT EXISTS comentarios_incidencia (
    id INT AUTO_INCREMENT PRIMARY KEY,
    incidencia_id INT NOT NULL,
    usuario_id INT NOT NULL,
    comentario TEXT NOT NULL,
    fecha_comentario DATETIME DEFAULT CURRENT_TIMESTAMP,
    activo TINYINT(1) DEFAULT 1,
    FOREIGN KEY (incidencia_id) REFERENCES incidencias(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    INDEX idx_incidencia (incidencia_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

if ($conn->query($sql) === TRUE) {
    echo "✅ Tabla 'comentarios_incidencia' creada\n\n";
} else {
    echo "⚠️  ADVERTENCIA: " . $conn->error . "\n\n";
}

// ==========================================
// PASO 13: Crear tabla SERVICIOS
// ==========================================
echo "⚡ PASO 13: Creando tabla 'servicios'...\n";
$sql = "CREATE TABLE IF NOT EXISTS servicios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(20) NOT NULL UNIQUE,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    tipo_calculo ENUM('FIJO', 'VARIABLE', 'POR_M2', 'POR_PERSONA') DEFAULT 'FIJO',
    monto_base DECIMAL(10,2) DEFAULT 0.00,
    activo TINYINT(1) DEFAULT 1,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_codigo (codigo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

if ($conn->query($sql) === TRUE) {
    echo "✅ Tabla 'servicios' creada\n\n";
} else {
    echo "⚠️  ADVERTENCIA: " . $conn->error . "\n\n";
}

// ==========================================
// PASO 14: Crear tabla CICLOS_FACTURACION
// ==========================================
echo "📅 PASO 14: Creando tabla 'ciclos_facturacion'...\n";
$sql = "CREATE TABLE IF NOT EXISTS ciclos_facturacion (
    id INT AUTO_INCREMENT PRIMARY KEY,
    edificio_id INT NOT NULL,
    fecha_periodo DATE NOT NULL,
    estado ENUM('ABIERTO', 'CERRADO', 'ANULADO') DEFAULT 'ABIERTO',
    usuario_cierre_id INT NULL,
    fecha_cierre DATETIME NULL,
    activo TINYINT(1) DEFAULT 1,
    FOREIGN KEY (edificio_id) REFERENCES edificios(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_cierre_id) REFERENCES usuarios(id) ON DELETE SET NULL,
    INDEX idx_edificio (edificio_id),
    INDEX idx_periodo (fecha_periodo),
    INDEX idx_estado (estado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

if ($conn->query($sql) === TRUE) {
    echo "✅ Tabla 'ciclos_facturacion' creada\n\n";
} else {
    echo "⚠️  ADVERTENCIA: " . $conn->error . "\n\n";
}

// ==========================================
// PASO 15: Crear tabla GASTOS_EDIFICIO
// ==========================================
echo "💰 PASO 15: Creando tabla 'gastos_edificio'...\n";
$sql = "CREATE TABLE IF NOT EXISTS gastos_edificio (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ciclo_id INT NOT NULL,
    servicio_id INT NULL,
    monto_total DECIMAL(10,2) NOT NULL,
    descripcion TEXT,
    fecha_gasto DATE NOT NULL,
    activo TINYINT(1) DEFAULT 1,
    FOREIGN KEY (ciclo_id) REFERENCES ciclos_facturacion(id) ON DELETE CASCADE,
    FOREIGN KEY (servicio_id) REFERENCES servicios(id) ON DELETE SET NULL,
    INDEX idx_ciclo (ciclo_id),
    INDEX idx_servicio (servicio_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

if ($conn->query($sql) === TRUE) {
    echo "✅ Tabla 'gastos_edificio' creada\n\n";
} else {
    echo "⚠️  ADVERTENCIA: " . $conn->error . "\n\n";
}

// ==========================================
// PASO 16: Crear tabla LECTURAS_MEDIDOR
// ==========================================
echo "📊 PASO 16: Creando tabla 'lecturas_medidor'...\n";
$sql = "CREATE TABLE IF NOT EXISTS lecturas_medidor (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ciclo_id INT NOT NULL,
    usuario_id INT NOT NULL,
    servicio_id INT NOT NULL,
    lectura_anterior DECIMAL(10,2) DEFAULT 0.00,
    lectura_actual DECIMAL(10,2) NOT NULL,
    consumo DECIMAL(10,2) GENERATED ALWAYS AS (lectura_actual - lectura_anterior) STORED,
    fecha_lectura DATE NOT NULL,
    activo TINYINT(1) DEFAULT 1,
    FOREIGN KEY (ciclo_id) REFERENCES ciclos_facturacion(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (servicio_id) REFERENCES servicios(id) ON DELETE CASCADE,
    INDEX idx_ciclo (ciclo_id),
    INDEX idx_usuario (usuario_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

if ($conn->query($sql) === TRUE) {
    echo "✅ Tabla 'lecturas_medidor' creada\n\n";
} else {
    echo "⚠️  ADVERTENCIA: " . $conn->error . "\n\n";
}

// ==========================================
// PASO 17: Crear tabla RECIBOS_INQUILINO
// ==========================================
echo "🧾 PASO 17: Creando tabla 'recibos_inquilino'...\n";
$sql = "CREATE TABLE IF NOT EXISTS recibos_inquilino (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ciclo_id INT NOT NULL,
    usuario_id INT NOT NULL,
    monto_deuda DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    monto_pagado DECIMAL(10,2) DEFAULT 0.00,
    estado ENUM('PENDIENTE', 'PAGADO', 'VENCIDO', 'ANULADO') DEFAULT 'PENDIENTE',
    fecha_vencimiento DATE NULL,
    fecha_pago DATETIME NULL,
    activo TINYINT(1) DEFAULT 1,
    FOREIGN KEY (ciclo_id) REFERENCES ciclos_facturacion(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    INDEX idx_ciclo (ciclo_id),
    INDEX idx_usuario (usuario_id),
    INDEX idx_estado (estado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

if ($conn->query($sql) === TRUE) {
    echo "✅ Tabla 'recibos_inquilino' creada\n\n";
} else {
    echo "⚠️  ADVERTENCIA: " . $conn->error . "\n\n";
}

// ==========================================
// PASO 18: Crear tabla PAGOS_INQUILINO
// ==========================================
echo "💳 PASO 18: Creando tabla 'pagos_inquilino'...\n";
$sql = "CREATE TABLE IF NOT EXISTS pagos_inquilino (
    id INT AUTO_INCREMENT PRIMARY KEY,
    recibo_id INT NOT NULL,
    usuario_id INT NOT NULL,
    monto_pagado DECIMAL(10,2) NOT NULL,
    metodo_pago ENUM('EFECTIVO', 'TRANSFERENCIA', 'CHEQUE', 'TARJETA', 'YAPE', 'PLIN') DEFAULT 'TRANSFERENCIA',
    numero_operacion VARCHAR(50) NULL,
    comprobante_ruta VARCHAR(255) NULL,
    estado ENUM('PENDIENTE', 'VERIFICADO', 'RECHAZADO') DEFAULT 'PENDIENTE',
    observaciones TEXT,
    fecha_pago DATETIME DEFAULT CURRENT_TIMESTAMP,
    fecha_verificacion DATETIME NULL,
    usuario_verificacion_id INT NULL,
    activo TINYINT(1) DEFAULT 1,
    FOREIGN KEY (recibo_id) REFERENCES recibos_inquilino(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_verificacion_id) REFERENCES usuarios(id) ON DELETE SET NULL,
    INDEX idx_recibo (recibo_id),
    INDEX idx_usuario (usuario_id),
    INDEX idx_estado (estado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

if ($conn->query($sql) === TRUE) {
    echo "✅ Tabla 'pagos_inquilino' creada\n\n";
} else {
    echo "⚠️  ADVERTENCIA: " . $conn->error . "\n\n";
}

// ==========================================
// PASO 19: Crear USUARIO ADMINISTRADOR
// ==========================================
echo "👤 PASO 19: Creando usuario administrador...\n";

// Obtener el ID del rol "Administrador Total"
$result = $conn->query("SELECT id FROM roles WHERE nombre = 'Administrador Total'");
if ($result->num_rows > 0) {
    $rol = $result->fetch_assoc();
    $rol_id = $rol['id'];
    
    // Usuario único: adm / adm
    $username = "adm";
    $password = password_hash("adm", PASSWORD_DEFAULT);
    $nombre = "Administrador Total";
    $email = "josepphh@gmail.com";
    
    $stmt = $conn->prepare("INSERT IGNORE INTO usuarios (nombre, email, username, password, rol_id, activo) VALUES (?, ?, ?, ?, ?, 1)");
    $stmt->bind_param("ssssi", $nombre, $email, $username, $password, $rol_id);
    
    if ($stmt->execute() && $stmt->affected_rows > 0) {
        echo "   ✓ Usuario 'adm' creado exitosamente\n";
        echo "     - Usuario: adm\n";
        echo "     - Contraseña: adm\n";
    } else {
        echo "   ℹ️  Usuario 'adm' ya existe\n";
    }
    $stmt->close();
} else {
    echo "❌ ERROR: No se encontró el rol 'Administrador Total'\n";
}

echo "\n";

// ==========================================
// RESUMEN FINAL
// ==========================================
echo "╔════════════════════════════════════════════════════════╗\n";
echo "║              ✅ INSTALACIÓN COMPLETADA ✅              ║\n";
echo "╚════════════════════════════════════════════════════════╝\n\n";

echo "📊 RESUMEN DE LA INSTALACIÓN:\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

// Contar registros
$usuarios = $conn->query("SELECT COUNT(*) as total FROM usuarios")->fetch_assoc()['total'];
$roles = $conn->query("SELECT COUNT(*) as total FROM roles")->fetch_assoc()['total'];

echo "🗄️  Base de datos: $dbname\n";
echo "👥 Usuarios creados: $usuarios\n";
echo "🎭 Roles creados: $roles\n";
echo "📊 Tablas creadas: 14\n\n";

echo "🔐 CREDENCIALES DE ACCESO:\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "  • Username: adm\n";
echo "  • Password: adm\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

echo "🌐 PRÓXIMOS PASOS:\n";
echo "1. Accede al sistema: http://localhost:8012/proyectoEdificio/\n";
echo "2. Inicia sesión con una de las cuentas de administrador\n";
echo "3. Cambia las contraseñas por seguridad\n";
echo "4. Comienza a gestionar tus edificios\n\n";

echo "✅ ¡El sistema está listo para usar!\n\n";

$conn->close();
?>
