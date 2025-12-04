<?php
/**
 * Clase Helper para Roles
 * Proporciona métodos para trabajar con roles dinámicamente
 */

class RoleHelper {
    private static $roles_cache = null;
    
    /**
     * Obtiene todos los roles activos de la base de datos
     * @return array Array de roles con id y nombre
     */
    public static function getAllRoles() {
        if (self::$roles_cache !== null) {
            return self::$roles_cache;
        }
        
        require_once __DIR__ . '/database.php';
        $database = new Database();
        $conn = $database->getConnection();
        
        $sql = "SELECT id, nombre FROM roles WHERE activo = 1 ORDER BY nombre";
        $result = $conn->query($sql);
        
        $roles = [];
        while ($row = $result->fetch_assoc()) {
            $roles[] = $row;
        }
        
        $conn->close();
        self::$roles_cache = $roles;
        return $roles;
    }
    
    /**
     * Verifica si un rol existe en la base de datos
     * @param string $rolNombre Nombre del rol a verificar
     * @return bool True si existe, false si no
     */
    public static function roleExists($rolNombre) {
        $roles = self::getAllRoles();
        foreach ($roles as $rol) {
            if ($rol['nombre'] === $rolNombre) {
                return true;
            }
        }
        return false;
    }
    
    /**
     * Obtiene el ID de un rol por su nombre
     * @param string $rolNombre Nombre del rol
     * @return int|null ID del rol o null si no existe
     */
    public static function getRoleId($rolNombre) {
        $roles = self::getAllRoles();
        foreach ($roles as $rol) {
            if ($rol['nombre'] === $rolNombre) {
                return $rol['id'];
            }
        }
        return null;
    }
    
    /**
     * Limpia el caché de roles (útil después de crear/editar roles)
     */
    public static function clearCache() {
        self::$roles_cache = null;
    }
}
?>
