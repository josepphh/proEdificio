<?php
// Sistema de permisos y verificación de roles - VERSIÓN DINÁMICA CON BASE DE DATOS

/**
 * Obtiene los permisos de un rol desde la base de datos
 * Incluye caché en sesión para mejorar rendimiento
 */
function obtenerPermisosRol($rol_id) {
    // Verificar si ya están en caché de sesión
    $cache_key = 'permisos_rol_' . $rol_id;
    if (isset($_SESSION[$cache_key])) {
        return $_SESSION[$cache_key];
    }
    
    require_once __DIR__ . '/../config/database.php';
    $database = new Database();
    $conn = $database->getConnection();
    
    $sql = "SELECT p.codigo 
            FROM permisos p
            INNER JOIN rol_permisos rp ON p.id = rp.permiso_id
            WHERE rp.rol_id = ? AND p.activo = 1";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $rol_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $permisos = [];
    while ($row = $result->fetch_assoc()) {
        $permisos[] = $row['codigo'];
    }
    
    $stmt->close();
    $conn->close();
    
    // Guardar en caché de sesión
    $_SESSION[$cache_key] = $permisos;
    
    return $permisos;
}

/**
 * Verifica si el usuario tiene un permiso específico
 */
function tienePermiso($permiso, $bypass_admin_total = false) {
    if (!isset($_SESSION['usuario_id']) || !isset($_SESSION['rol_id'])) {
        return false;
    }
    
    $permisos = obtenerPermisosRol($_SESSION['rol_id']);
    
    // Bypass especial: Administrador Total siempre ve las opciones del panel
    // Pero sigue necesitando el permiso real para acceder a las páginas
    if ($bypass_admin_total && in_array('acceso_completo', $permisos)) {
        return true;
    }
    
    return in_array($permiso, $permisos);
}

/**
 * Requiere un permiso específico, redirige si no lo tiene
 */
function requierePermiso($permiso, $redirect = null) {
    if (!tienePermiso($permiso)) {
        // Guardar mensaje de error en sesión
        $_SESSION['error_permiso'] = "No tienes permiso para acceder a esta sección. Permiso requerido: <strong>" . htmlspecialchars($permiso) . "</strong>";
        $_SESSION['rol_actual'] = $_SESSION['rol_nombre'] ?? 'Sin rol asignado';
        
        // Redirigir a página de acceso denegado
        $redirect_url = $redirect ?? '/proyectoEdificio/acceso_denegado.php';
        header("Location: $redirect_url");
        exit();
    }
}

/**
 * Verifica si el usuario tiene un rol específico
 */
function esRol($rol_nombre) {
    return isset($_SESSION['rol_nombre']) && $_SESSION['rol_nombre'] === $rol_nombre;
}

/**
 * Verifica si el usuario está autenticado
 */
function estaAutenticado() {
    return isset($_SESSION['usuario_id']);
}

/**
 * Requiere autenticación, redirige al login si no está autenticado
 */
function requiereAutenticacion($redirect = 'login.php') {
    if (!estaAutenticado()) {
        header("Location: $redirect");
        exit();
    }
}

/**
 * Verifica si el usuario pertenece al edificio especificado
 */
function perteneceAlEdificio($edificio_id) {
    if (!isset($_SESSION['usuario_id'])) {
        return false;
    }
    
    // Administrador Total tiene acceso a todos los edificios
    if (esRol('Administrador Total')) {
        return true;
    }
    
    // Verificar en la tabla usuario_edificios si el usuario está asignado al edificio
    require_once __DIR__ . '/../config/database.php';
    $database = new Database();
    $conn = $database->getConnection();
    
    if (!$conn) {
        return false;
    }
    
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM usuario_edificios WHERE usuario_id = ? AND edificio_id = ? AND activo = 1");
    $stmt->bind_param("ii", $_SESSION['usuario_id'], $edificio_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    $conn->close();
    
    return $row['count'] > 0;
}
