<?php
/**
 * Configuración Centralizada del Sistema
 * Archivo principal de configuración para el Sistema de Gestión de Edificios
 * 
 * @version 1.0
 * @date 2025-11-19
 */

// ===========================
// CONFIGURACIÓN DE BASE DE DATOS
// ===========================
define('DB_HOST', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'edificios_db');
define('DB_CHARSET', 'utf8mb4');

// ===========================
// CONFIGURACIÓN DE RUTAS
// ===========================
// Ruta base del proyecto
define('BASE_PATH', dirname(__DIR__));

// Rutas de directorios
define('CONFIG_PATH', BASE_PATH . '/config');
define('INCLUDES_PATH', BASE_PATH . '/includes');
define('API_PATH', BASE_PATH . '/api');
define('ADMIN_PATH', BASE_PATH . '/admin');
define('INQUILINO_PATH', BASE_PATH . '/inquilino');
define('UPLOADS_PATH', BASE_PATH . '/uploads');
define('LOGS_PATH', BASE_PATH . '/logs');

// Subdirectorios de uploads
define('UPLOADS_VOUCHERS', UPLOADS_PATH . '/vouchers');
define('UPLOADS_INCIDENCIAS', UPLOADS_PATH . '/incidencias');
define('UPLOADS_DOCUMENTOS', UPLOADS_PATH . '/documentos');
define('UPLOADS_TEMP', UPLOADS_PATH . '/temp');

// ===========================
// CONFIGURACIÓN DE URLs
// ===========================
// Puerto del servidor (ajustar según configuración de XAMPP)
define('SERVER_PORT', '8012');

// URL base del proyecto
define('BASE_URL', 'http://localhost:' . SERVER_PORT . '/proyectoEdificio');

// URLs de secciones
define('ADMIN_URL', BASE_URL . '/admin');
define('INQUILINO_URL', BASE_URL . '/inquilino');
define('API_URL', BASE_URL . '/api');
define('ASSETS_URL', BASE_URL . '/assets');

// ===========================
// CONFIGURACIÓN DE SESIÓN
// ===========================
define('SESSION_NAME', 'edificios_session');
define('SESSION_LIFETIME', 7200); // 2 horas en segundos
define('SESSION_COOKIE_SECURE', false); // Cambiar a true en producción con HTTPS
define('SESSION_COOKIE_HTTPONLY', true);
define('SESSION_COOKIE_SAMESITE', 'Lax');

// ===========================
// CONFIGURACIÓN DE SEGURIDAD
// ===========================
// Salt para hashing (cambiar en producción)
define('SECURITY_SALT', 'edificios_salt_2025');

// Configuración de archivos subidos
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB en bytes
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/jpg', 'image/png', 'image/gif']);
define('ALLOWED_DOCUMENT_TYPES', ['application/pdf', 'image/jpeg', 'image/jpg', 'image/png']);

// Configuración de intentos de login
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_LOCKOUT_TIME', 900); // 15 minutos en segundos

// ===========================
// CONFIGURACIÓN DE EMAIL
// ===========================
define('MAIL_FROM', 'sistema@edificios.com');
define('MAIL_FROM_NAME', 'Sistema de Gestión de Edificios');
define('MAIL_SMTP_HOST', 'smtp.gmail.com');
define('MAIL_SMTP_PORT', 587);
define('MAIL_SMTP_USERNAME', ''); // Configurar en producción
define('MAIL_SMTP_PASSWORD', ''); // Configurar en producción
define('MAIL_SMTP_SECURE', 'tls');

// ===========================
// CONFIGURACIÓN DE LOGGING
// ===========================
define('LOG_ENABLED', true);
define('LOG_LEVEL', 'INFO'); // DEBUG, INFO, WARNING, ERROR
define('LOG_FILE_APP', LOGS_PATH . '/app.log');
define('LOG_FILE_ERROR', LOGS_PATH . '/errors.log');
define('LOG_FILE_SECURITY', LOGS_PATH . '/security.log');
define('LOG_MAX_SIZE', 10 * 1024 * 1024); // 10MB

// ===========================
// CONFIGURACIÓN DE PAGINACIÓN
// ===========================
define('ITEMS_PER_PAGE', 10);
define('ITEMS_PER_PAGE_ADMIN', 20);

// ===========================
// CONFIGURACIÓN DE FECHAS Y FORMATO
// ===========================
define('TIMEZONE', 'America/Lima');
define('DATE_FORMAT', 'd/m/Y');
define('DATETIME_FORMAT', 'd/m/Y H:i:s');
define('LOCALE', 'es_PE');

// Establecer zona horaria
date_default_timezone_set(TIMEZONE);

// ===========================
// CONFIGURACIÓN DE FACTURACIÓN
// ===========================
define('MONEDA_SIMBOLO', 'S/');
define('MONEDA_CODIGO', 'PEN');
define('IVA_PORCENTAJE', 18); // IGV en Perú

// ===========================
// CONFIGURACIÓN DE ROLES
// ===========================
define('ROLE_ADMIN_TOTAL', 1);
define('ROLE_ADMIN_EDIFICIO', 2);
define('ROLE_INQUILINO', 3);

// Nombres de roles
define('ROLE_NAMES', [
    ROLE_ADMIN_TOTAL => 'Admin Total',
    ROLE_ADMIN_EDIFICIO => 'Admin Edificio',
    ROLE_INQUILINO => 'Inquilino'
]);

// ===========================
// CONFIGURACIÓN DE ESTADOS
// ===========================
// Estados de solicitudes de acceso
define('SOLICITUD_PENDIENTE', 'pendiente');
define('SOLICITUD_APROBADA', 'aprobada');
define('SOLICITUD_RECHAZADA', 'rechazada');

// Estados de pagos
define('PAGO_PENDIENTE', 'pendiente');
define('PAGO_VERIFICANDO', 'verificando');
define('PAGO_PAGADO', 'pagado');
define('PAGO_RECHAZADO', 'rechazado');

// Estados de incidencias
define('INCIDENCIA_PENDIENTE', 'pendiente');
define('INCIDENCIA_EN_PROCESO', 'en_proceso');
define('INCIDENCIA_RESUELTA', 'resuelta');
define('INCIDENCIA_CERRADA', 'cerrada');

// ===========================
// MODO DE DESARROLLO
// ===========================
define('DEBUG_MODE', true); // Cambiar a false en producción

// Configurar reporte de errores según el modo
if (DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    ini_set('log_errors', 1);
} else {
    error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
}

// ===========================
// CONFIGURACIÓN DE CACHE
// ===========================
define('CACHE_ENABLED', false); // Implementar en el futuro
define('CACHE_LIFETIME', 3600); // 1 hora

// ===========================
// MENSAJES DEL SISTEMA
// ===========================
define('MESSAGES', [
    'success' => [
        'login' => 'Inicio de sesión exitoso',
        'logout' => 'Sesión cerrada correctamente',
        'save' => 'Guardado exitosamente',
        'update' => 'Actualizado exitosamente',
        'delete' => 'Eliminado exitosamente',
        'upload' => 'Archivo subido correctamente',
    ],
    'error' => [
        'login' => 'Usuario o contraseña incorrectos',
        'permission' => 'No tienes permisos para realizar esta acción',
        'database' => 'Error de conexión con la base de datos',
        'file_upload' => 'Error al subir el archivo',
        'file_size' => 'El archivo excede el tamaño máximo permitido',
        'file_type' => 'Tipo de archivo no permitido',
        'required_fields' => 'Por favor, complete todos los campos requeridos',
        'generic' => 'Ha ocurrido un error. Por favor, intente nuevamente',
    ],
    'warning' => [
        'session_expired' => 'Tu sesión ha expirado. Por favor, inicia sesión nuevamente',
        'unsaved_changes' => 'Tienes cambios sin guardar',
    ],
    'info' => [
        'no_data' => 'No hay datos disponibles',
        'processing' => 'Procesando...',
    ]
]);

// ===========================
// FUNCIÓN DE AUTO-CARGA
// ===========================
/**
 * Retorna la conexión a la base de datos
 * Wrapper para mantener compatibilidad con código existente
 * 
 * @return mysqli Conexión a la base de datos
 */
function getDBConnection() {
    require_once CONFIG_PATH . '/database.php';
    $db = new Database();
    return $db->getConnection();
}

/**
 * Retorna una constante de configuración de forma segura
 * 
 * @param string $key Nombre de la constante
 * @param mixed $default Valor por defecto si no existe
 * @return mixed Valor de la configuración
 */
function config($key, $default = null) {
    return defined($key) ? constant($key) : $default;
}

/**
 * Retorna un mensaje del sistema
 * 
 * @param string $type Tipo de mensaje (success, error, warning, info)
 * @param string $key Clave del mensaje
 * @return string Mensaje
 */
function getMessage($type, $key) {
    $messages = MESSAGES;
    return isset($messages[$type][$key]) ? $messages[$type][$key] : '';
}

// ===========================
// INICIALIZACIÓN
// ===========================
// Crear directorios necesarios si no existen
$required_dirs = [
    LOGS_PATH,
    UPLOADS_VOUCHERS,
    UPLOADS_INCIDENCIAS,
    UPLOADS_DOCUMENTOS,
    UPLOADS_TEMP
];

foreach ($required_dirs as $dir) {
    if (!file_exists($dir)) {
        mkdir($dir, 0755, true);
    }
}

// Log de inicialización (solo en modo debug)
if (DEBUG_MODE && LOG_ENABLED) {
    error_log("[" . date(DATETIME_FORMAT) . "] Sistema inicializado - config.php cargado");
}
?>
