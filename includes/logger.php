<?php
/**
 * Sistema de Logging
 * Gestión centralizada de logs del sistema
 * 
 * @version 1.0
 * @date 2025-11-19
 */

class Logger {
    private $logLevel;
    private $logLevels = [
        'DEBUG' => 0,
        'INFO' => 1,
        'WARNING' => 2,
        'ERROR' => 3,
        'CRITICAL' => 4
    ];

    /**
     * Constructor
     * @param string $logLevel Nivel mínimo de log (DEBUG, INFO, WARNING, ERROR, CRITICAL)
     */
    public function __construct($logLevel = 'INFO') {
        $this->logLevel = $logLevel;
        
        // Asegurar que exista el directorio de logs
        if (!file_exists(LOGS_PATH)) {
            mkdir(LOGS_PATH, 0755, true);
        }
    }

    /**
     * Escribe un mensaje de log
     * 
     * @param string $level Nivel del log
     * @param string $message Mensaje a registrar
     * @param array $context Contexto adicional (usuario, IP, etc.)
     * @param string $file Archivo de log específico (opcional)
     */
    private function write($level, $message, $context = [], $file = null) {
        // Verificar si el nivel cumple con el mínimo configurado
        if ($this->logLevels[$level] < $this->logLevels[$this->logLevel]) {
            return;
        }

        // Determinar archivo de log
        if ($file === null) {
            $file = ($level === 'ERROR' || $level === 'CRITICAL') 
                ? LOG_FILE_ERROR 
                : LOG_FILE_APP;
        }

        // Construir línea de log
        $timestamp = date(DATETIME_FORMAT);
        $logLine = "[{$timestamp}] [{$level}] {$message}";

        // Agregar contexto si existe
        if (!empty($context)) {
            $contextStr = [];
            foreach ($context as $key => $value) {
                if (is_array($value) || is_object($value)) {
                    $value = json_encode($value);
                }
                $contextStr[] = "{$key}={$value}";
            }
            $logLine .= " | " . implode(", ", $contextStr);
        }

        $logLine .= PHP_EOL;

        // Escribir al archivo
        file_put_contents($file, $logLine, FILE_APPEND);

        // Verificar tamaño del archivo y rotar si es necesario
        $this->rotateLogIfNeeded($file);
    }

    /**
     * Rota el archivo de log si excede el tamaño máximo
     * 
     * @param string $file Ruta del archivo
     */
    private function rotateLogIfNeeded($file) {
        if (!file_exists($file)) {
            return;
        }

        $fileSize = filesize($file);
        if ($fileSize >= LOG_MAX_SIZE) {
            $timestamp = date('Y-m-d_H-i-s');
            $newFile = $file . '.' . $timestamp;
            rename($file, $newFile);
            
            // Comprimir archivo antiguo si está disponible
            if (function_exists('gzencode')) {
                $content = file_get_contents($newFile);
                file_put_contents($newFile . '.gz', gzencode($content));
                unlink($newFile);
            }
        }
    }

    /**
     * Log de nivel DEBUG
     * Información detallada para diagnóstico
     * 
     * @param string $message Mensaje
     * @param array $context Contexto adicional
     */
    public function debug($message, $context = []) {
        $this->write('DEBUG', $message, $context);
    }

    /**
     * Log de nivel INFO
     * Eventos informativos generales
     * 
     * @param string $message Mensaje
     * @param array $context Contexto adicional
     */
    public function info($message, $context = []) {
        $this->write('INFO', $message, $context);
    }

    /**
     * Log de nivel WARNING
     * Advertencias que no impiden el funcionamiento
     * 
     * @param string $message Mensaje
     * @param array $context Contexto adicional
     */
    public function warning($message, $context = []) {
        $this->write('WARNING', $message, $context);
    }

    /**
     * Log de nivel ERROR
     * Errores que deben ser atendidos
     * 
     * @param string $message Mensaje
     * @param array $context Contexto adicional
     */
    public function error($message, $context = []) {
        $this->write('ERROR', $message, $context);
    }

    /**
     * Log de nivel CRITICAL
     * Errores críticos que requieren atención inmediata
     * 
     * @param string $message Mensaje
     * @param array $context Contexto adicional
     */
    public function critical($message, $context = []) {
        $this->write('CRITICAL', $message, $context);
    }

    /**
     * Log de seguridad
     * Eventos relacionados con seguridad del sistema
     * 
     * @param string $message Mensaje
     * @param array $context Contexto adicional
     */
    public function security($message, $context = []) {
        $this->write('SECURITY', $message, $context, LOG_FILE_SECURITY);
    }

    /**
     * Log de actividad de usuario
     * Registra acciones importantes de usuarios
     * 
     * @param string $action Acción realizada
     * @param int $userId ID del usuario
     * @param string $details Detalles adicionales
     */
    public function userActivity($action, $userId, $details = '') {
        $context = [
            'user_id' => $userId,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
        ];
        
        if (!empty($details)) {
            $context['details'] = $details;
        }

        $this->info("User Activity: {$action}", $context);
    }

    /**
     * Log de consultas SQL (solo en modo debug)
     * 
     * @param string $query Consulta SQL
     * @param float $executionTime Tiempo de ejecución en segundos
     */
    public function sqlQuery($query, $executionTime = null) {
        if (DEBUG_MODE) {
            $context = [];
            if ($executionTime !== null) {
                $context['execution_time'] = round($executionTime, 4) . 's';
            }
            $this->debug("SQL Query: {$query}", $context);
        }
    }

    /**
     * Obtiene las últimas N líneas de un archivo de log
     * 
     * @param string $file Archivo de log
     * @param int $lines Número de líneas a obtener
     * @return array Array de líneas
     */
    public static function getLastLines($file, $lines = 100) {
        if (!file_exists($file)) {
            return [];
        }

        $content = file($file);
        return array_slice($content, -$lines);
    }

    /**
     * Limpia logs antiguos (más de X días)
     * 
     * @param int $days Días de antigüedad
     * @return int Número de archivos eliminados
     */
    public static function cleanOldLogs($days = 30) {
        $deleted = 0;
        $cutoffTime = time() - ($days * 86400);
        
        $files = glob(LOGS_PATH . '/*.log.*');
        foreach ($files as $file) {
            if (filemtime($file) < $cutoffTime) {
                unlink($file);
                $deleted++;
            }
        }
        
        return $deleted;
    }

    /**
     * Obtiene el tamaño total de los logs
     * 
     * @return array Array con información de tamaño
     */
    public static function getLogStats() {
        $stats = [
            'total_size' => 0,
            'files' => 0,
            'details' => []
        ];

        $files = glob(LOGS_PATH . '/*');
        foreach ($files as $file) {
            if (is_file($file)) {
                $size = filesize($file);
                $stats['total_size'] += $size;
                $stats['files']++;
                $stats['details'][basename($file)] = [
                    'size' => $size,
                    'size_formatted' => self::formatBytes($size),
                    'modified' => date(DATETIME_FORMAT, filemtime($file))
                ];
            }
        }

        $stats['total_size_formatted'] = self::formatBytes($stats['total_size']);
        return $stats;
    }

    /**
     * Formatea bytes a formato legible
     * 
     * @param int $bytes Bytes
     * @return string Tamaño formateado
     */
    private static function formatBytes($bytes) {
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        return round($bytes, 2) . ' ' . $units[$i];
    }
}

// ===========================
// FUNCIONES HELPER GLOBALES
// ===========================

/**
 * Obtiene instancia global del logger
 * 
 * @return Logger Instancia del logger
 */
function logger() {
    static $logger = null;
    if ($logger === null) {
        $logger = new Logger(LOG_LEVEL);
    }
    return $logger;
}

/**
 * Log rápido de información
 * 
 * @param string $message Mensaje
 * @param array $context Contexto
 */
function log_info($message, $context = []) {
    logger()->info($message, $context);
}

/**
 * Log rápido de error
 * 
 * @param string $message Mensaje
 * @param array $context Contexto
 */
function log_error($message, $context = []) {
    logger()->error($message, $context);
}

/**
 * Log rápido de actividad de usuario
 * 
 * @param string $action Acción
 * @param int $userId ID de usuario
 * @param string $details Detalles
 */
function log_user_activity($action, $userId, $details = '') {
    logger()->userActivity($action, $userId, $details);
}

/**
 * Log rápido de seguridad
 * 
 * @param string $message Mensaje
 * @param array $context Contexto
 */
function log_security($message, $context = []) {
    logger()->security($message, $context);
}

/**
 * Maneja excepciones no capturadas registrándolas
 * 
 * @param Throwable $exception Excepción
 */
function logExceptionHandler($exception) {
    $message = "Uncaught Exception: " . $exception->getMessage();
    $context = [
        'file' => $exception->getFile(),
        'line' => $exception->getLine(),
        'trace' => $exception->getTraceAsString()
    ];
    logger()->critical($message, $context);
}

/**
 * Maneja errores PHP registrándolos
 * 
 * @param int $errno Número de error
 * @param string $errstr Mensaje de error
 * @param string $errfile Archivo donde ocurrió
 * @param int $errline Línea donde ocurrió
 */
function logErrorHandler($errno, $errstr, $errfile, $errline) {
    $message = "PHP Error [{$errno}]: {$errstr}";
    $context = [
        'file' => $errfile,
        'line' => $errline
    ];
    
    // Determinar nivel según tipo de error
    if ($errno === E_ERROR || $errno === E_CORE_ERROR || $errno === E_COMPILE_ERROR) {
        logger()->critical($message, $context);
    } elseif ($errno === E_WARNING || $errno === E_CORE_WARNING || $errno === E_COMPILE_WARNING) {
        logger()->warning($message, $context);
    } else {
        logger()->error($message, $context);
    }
    
    return false; // Permitir que el manejador por defecto también procese
}

// Registrar manejadores si el logging está habilitado
if (LOG_ENABLED) {
    set_exception_handler('logExceptionHandler');
    set_error_handler('logErrorHandler');
}
?>
