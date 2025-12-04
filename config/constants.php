<?php
/**
 * Constantes del Sistema
 * Define todos los valores constantes usados en la aplicación
 */

// ============================================
// ESTADOS DE SOLICITUDES DE ACCESO
// ============================================
define('SOLICITUD_PENDIENTE', 'PENDIENTE');
define('SOLICITUD_APROBADA', 'APROBADA');
define('SOLICITUD_RECHAZADA', 'RECHAZADA');

// ============================================
// ESTADOS DE INCIDENCIAS
// ============================================
define('INCIDENCIA_PENDIENTE', 'PENDIENTE');
define('INCIDENCIA_EN_PROCESO', 'EN_PROCESO');
define('INCIDENCIA_RESUELTA', 'RESUELTA');
define('INCIDENCIA_CERRADA', 'CERRADA');

// ============================================
// ESTADOS DE PAGOS
// ============================================
define('PAGO_PENDIENTE', 'PENDIENTE');
define('PAGO_VALIDADO', 'VALIDADO');
define('PAGO_RECHAZADO', 'RECHAZADO');

// ============================================
// ESTADOS DE CICLOS DE FACTURACIÓN
// ============================================
define('CICLO_ABIERTO', 'ABIERTO');
define('CICLO_CERRADO', 'CERRADO');
define('CICLO_PROCESADO', 'PROCESADO');

// ============================================
// TIPOS DE AVISOS
// ============================================
define('AVISO_INFORMATIVO', 'INFORMATIVO');
define('AVISO_URGENTE', 'URGENTE');
define('AVISO_MANTENIMIENTO', 'MANTENIMIENTO');
define('AVISO_EVENTO', 'EVENTO');

// ============================================
// NOTA IMPORTANTE SOBRE ROLES
// ============================================
// Los roles NO se definen como constantes porque son DINÁMICOS
// y se gestionan desde la base de datos (tabla: roles)
// 
// Si creas un nuevo rol en la base de datos, estará disponible
// automáticamente sin necesidad de modificar código.
//
// Para trabajar con roles, usa las funciones en includes/permissions.php:
// - esRol('Nombre del Rol')
// - tienePermiso('nombre_permiso')
//
// O usa la clase RoleHelper en config/role_helper.php:
// - RoleHelper::getAllRoles()
// - RoleHelper::roleExists('Nombre del Rol')
// ============================================

// ============================================
// TIPOS DE CÁLCULO DE SERVICIOS
// ============================================
define('CALCULO_CUOTA_FIJA', 'CUOTA_FIJA');
define('CALCULO_CONSUMO', 'CONSUMO');

// ============================================
// MENSAJES DEL SISTEMA
// ============================================
define('MSG_EXITO_GUARDAR', 'Datos guardados correctamente');
define('MSG_EXITO_ELIMINAR', 'Registro eliminado correctamente');
define('MSG_EXITO_ACTUALIZAR', 'Datos actualizados correctamente');
define('MSG_ERROR_GUARDAR', 'Error al guardar los datos');
define('MSG_ERROR_ELIMINAR', 'Error al eliminar el registro');
define('MSG_ERROR_ACTUALIZAR', 'Error al actualizar los datos');
define('MSG_ERROR_PERMISOS', 'No tienes permisos para realizar esta acción');
define('MSG_ERROR_AUTENTICACION', 'Debes iniciar sesión para acceder');

// ============================================
// CONFIGURACIÓN DE PAGINACIÓN
// ============================================
define('REGISTROS_POR_PAGINA', 20);

// ============================================
// CONFIGURACIÓN DE ARCHIVOS
// ============================================
define('MAX_FILE_SIZE', 5242880); // 5MB en bytes
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/gif']);
define('ALLOWED_DOCUMENT_TYPES', ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document']);

?>
