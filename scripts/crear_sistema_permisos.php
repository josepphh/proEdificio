<?php
require_once __DIR__ . '/../config/database.php';

echo "========================================\n";
echo "MIGRACIÓN: Sistema de Permisos Dinámico\n";
echo "========================================\n\n";

$database = new Database();
$conn = $database->getConnection();

// 1. Crear tabla de permisos
echo "1. Creando tabla 'permisos'...\n";
$sql_permisos = "CREATE TABLE IF NOT EXISTS permisos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(50) NOT NULL UNIQUE,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    categoria VARCHAR(50),
    activo TINYINT(1) DEFAULT 1,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_codigo (codigo),
    INDEX idx_activo (activo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

if ($conn->query($sql_permisos)) {
    echo "   ✅ Tabla 'permisos' creada\n\n";
} else {
    echo "   ❌ Error: " . $conn->error . "\n";
    $conn->close();
    exit;
}

// 2. Crear tabla de relación roles-permisos
echo "2. Creando tabla 'rol_permisos'...\n";
$sql_rol_permisos = "CREATE TABLE IF NOT EXISTS rol_permisos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    rol_id INT NOT NULL,
    permiso_id INT NOT NULL,
    activo TINYINT(1) DEFAULT 1,
    fecha_asignacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (rol_id) REFERENCES roles(id) ON DELETE CASCADE,
    FOREIGN KEY (permiso_id) REFERENCES permisos(id) ON DELETE CASCADE,
    UNIQUE KEY unique_rol_permiso (rol_id, permiso_id),
    INDEX idx_rol (rol_id),
    INDEX idx_permiso (permiso_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

if ($conn->query($sql_rol_permisos)) {
    echo "   ✅ Tabla 'rol_permisos' creada\n\n";
} else {
    echo "   ❌ Error: " . $conn->error . "\n";
    $conn->close();
    exit;
}

// 3. Insertar permisos base
echo "3. Insertando permisos base del sistema...\n";
$permisos_base = [
    // Categoría: Sistema
    ['gestionar_usuarios', 'Gestionar Usuarios', 'Crear, editar y desactivar usuarios del sistema', 'Sistema'],
    ['gestionar_edificios', 'Gestionar Edificios', 'Administrar edificios y sus propiedades', 'Sistema'],
    ['gestionar_inquilinos', 'Gestionar Inquilinos', 'Administrar inquilinos por edificio', 'Sistema'],
    ['gestionar_roles', 'Gestionar Roles', 'Administrar roles y permisos', 'Sistema'],
    ['acceso_completo', 'Acceso Completo', 'Acceso total al sistema', 'Sistema'],
    ['acceso_panel_admin', 'Acceso Panel Admin', 'Acceder al panel de administración', 'Sistema'],
    
    // Categoría: Reportes
    ['ver_reportes_globales', 'Ver Reportes Globales', 'Ver reportes de todo el sistema', 'Reportes'],
    ['ver_reportes_edificio', 'Ver Reportes de Edificio', 'Ver reportes de edificios específicos', 'Reportes'],
    
    // Categoría: Finanzas
    ['gestionar_gastos', 'Gestionar Gastos', 'Registrar y administrar gastos', 'Finanzas'],
    ['procesar_cierre_mensual', 'Procesar Cierre Mensual', 'Procesar cierre de facturación mensual', 'Finanzas'],
    ['pagar_servicios', 'Pagar Servicios', 'Realizar pagos de servicios', 'Finanzas'],
    ['ver_mis_pagos', 'Ver Mis Pagos', 'Ver historial de pagos propios', 'Finanzas'],
    ['registrar_pago', 'Registrar Pago', 'Registrar un nuevo pago', 'Finanzas'],
    
    // Categoría: Comunicación
    ['gestionar_avisos', 'Gestionar Avisos', 'Crear y administrar avisos', 'Comunicación'],
    ['ver_avisos', 'Ver Avisos', 'Ver avisos del sistema', 'Comunicación'],
    
    // Categoría: Operaciones
    ['gestionar_mantenimiento', 'Gestionar Mantenimiento', 'Administrar mantenimiento del edificio', 'Operaciones'],
    ['gestionar_seguridad_edificio', 'Gestionar Seguridad', 'Administrar seguridad del edificio', 'Operaciones'],
    ['reportar_incidencias', 'Reportar Incidencias', 'Reportar problemas o incidentes', 'Operaciones'],
    ['registrar_visitas', 'Registrar Visitas', 'Registrar visitantes', 'Operaciones'],
    ['ver_residentes', 'Ver Residentes', 'Ver listado de residentes', 'Operaciones'],
    ['reportar_incidentes', 'Reportar Incidentes', 'Reportar incidentes de seguridad', 'Operaciones'],
    
    // Categoría: Perfil
    ['ver_perfil', 'Ver Perfil', 'Ver perfil personal', 'Perfil']
];

$stmt = $conn->prepare("INSERT INTO permisos (codigo, nombre, descripcion, categoria) VALUES (?, ?, ?, ?)");
$contador = 0;
foreach ($permisos_base as $permiso) {
    $stmt->bind_param("ssss", $permiso[0], $permiso[1], $permiso[2], $permiso[3]);
    if ($stmt->execute()) {
        $contador++;
        echo "   ✅ {$permiso[1]}\n";
    }
}
$stmt->close();
echo "\n   Total: $contador permisos insertados\n\n";

// 4. Asignar permisos a roles existentes
echo "4. Asignando permisos a roles existentes...\n";

// Obtener IDs de roles
$roles = [];
$result = $conn->query("SELECT id, nombre FROM roles");
while ($row = $result->fetch_assoc()) {
    $roles[$row['nombre']] = $row['id'];
}

// Obtener IDs de permisos
$permisos = [];
$result = $conn->query("SELECT id, codigo FROM permisos");
while ($row = $result->fetch_assoc()) {
    $permisos[$row['codigo']] = $row['id'];
}

// Asignaciones basadas en includes/permissions.php
$asignaciones = [
    'Administrador Total' => [
        'gestionar_usuarios', 'gestionar_edificios', 'gestionar_inquilinos', 
        'ver_reportes_globales', 'gestionar_roles', 'gestionar_gastos',
        'procesar_cierre_mensual', 'gestionar_avisos', 'ver_avisos',
        'acceso_completo', 'acceso_panel_admin'
    ],
    'Administrador Edificio' => [
        'gestionar_usuarios', 'gestionar_edificios', 'gestionar_inquilinos',
        'ver_reportes_edificio', 'gestionar_mantenimiento', 'gestionar_seguridad_edificio',
        'gestionar_gastos', 'procesar_cierre_mensual', 'gestionar_avisos',
        'ver_avisos', 'acceso_panel_admin'
    ],
    'Inquilino' => [
        'ver_perfil', 'reportar_incidencias', 'ver_avisos',
        'pagar_servicios', 'ver_mis_pagos', 'registrar_pago'
    ],
    'Seguridad' => [
        'registrar_visitas', 'ver_residentes', 'reportar_incidentes', 'ver_avisos'
    ]
];

$stmt = $conn->prepare("INSERT INTO rol_permisos (rol_id, permiso_id) VALUES (?, ?)");
$total_asignaciones = 0;

foreach ($asignaciones as $rol_nombre => $permisos_rol) {
    if (!isset($roles[$rol_nombre])) continue;
    
    echo "\n   Rol: $rol_nombre\n";
    $rol_id = $roles[$rol_nombre];
    
    foreach ($permisos_rol as $permiso_codigo) {
        if (!isset($permisos[$permiso_codigo])) continue;
        
        $permiso_id = $permisos[$permiso_codigo];
        $stmt->bind_param("ii", $rol_id, $permiso_id);
        
        if ($stmt->execute()) {
            $total_asignaciones++;
            echo "     ✅ $permiso_codigo\n";
        }
    }
}
$stmt->close();

echo "\n   Total: $total_asignaciones asignaciones realizadas\n\n";

echo "========================================\n";
echo "✅ MIGRACIÓN COMPLETADA EXITOSAMENTE\n";
echo "========================================\n";
echo "\nPróximos pasos:\n";
echo "1. Modificar includes/permissions.php para leer de BD\n";
echo "2. Crear interfaz de gestión de permisos\n\n";

$conn->close();
?>
