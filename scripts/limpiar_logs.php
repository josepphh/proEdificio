<?php
/**
 * Script de Limpieza de Logs Antiguos
 * Elimina archivos de log con más de X días de antigüedad
 * 
 * Uso: php scripts/limpiar_logs.php [dias]
 * Ejemplo: php scripts/limpiar_logs.php 30
 * 
 * @version 1.0
 */

// Cargar configuración
require_once dirname(__DIR__) . '/config/config.php';
require_once INCLUDES_PATH . '/logger.php';

// Obtener días desde argumentos de línea de comandos
$days = isset($argv[1]) ? (int)$argv[1] : 30;

echo "🧹 Limpiando logs con más de {$days} días de antigüedad...\n\n";

try {
    $deleted = Logger::cleanOldLogs($days);
    
    if ($deleted > 0) {
        echo "✅ Se eliminaron {$deleted} archivo(s) de log antiguo(s)\n";
        log_info("Limpieza de logs completada", ['deleted_files' => $deleted, 'days' => $days]);
    } else {
        echo "ℹ️  No se encontraron logs antiguos para eliminar\n";
    }
    
    // Mostrar estadísticas actuales
    echo "\n📊 Estadísticas de logs:\n";
    $stats = Logger::getLogStats();
    echo "   Total de archivos: {$stats['files']}\n";
    echo "   Tamaño total: {$stats['total_size_formatted']}\n\n";
    
    if (!empty($stats['details'])) {
        echo "   Detalle de archivos:\n";
        foreach ($stats['details'] as $filename => $info) {
            echo "   - {$filename}: {$info['size_formatted']} (modificado: {$info['modified']})\n";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Error al limpiar logs: " . $e->getMessage() . "\n";
    log_error("Error en limpieza de logs", ['error' => $e->getMessage()]);
    exit(1);
}

echo "\n✅ Proceso completado\n";
?>
