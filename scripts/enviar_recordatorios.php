<?php
/**
 * Script para Enviar Recordatorios Automáticos de Pago
 * 
 * Este script debe ejecutarse diariamente mediante cron job
 * Envía recordatorios a inquilinos con recibos próximos a vencer (3-5 días)
 * 
 * Ejemplo de cron (ejecutar diariamente a las 9:00 AM):
 * 0 9 * * * php c:\xampp\htdocs\proyectoEdificio\scripts\enviar_recordatorios.php
 * 
 * Para Windows Task Scheduler:
 * - Programa: C:\xampp\php\php.exe
 * - Argumentos: -f "c:\xampp\htdocs\proyectoEdificio\scripts\enviar_recordatorios.php"
 * - Frecuencia: Diaria a las 9:00 AM
 */

// Configurar zona horaria
date_default_timezone_set('America/Lima');

// Incluir dependencias
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/mail.php';

// Log de ejecución
$log_file = __DIR__ . '/recordatorios.log';
$fecha_ejecucion = date('Y-m-d H:i:s');

function escribirLog($mensaje) {
    global $log_file, $fecha_ejecucion;
    $linea = "[{$fecha_ejecucion}] {$mensaje}\n";
    file_put_contents($log_file, $linea, FILE_APPEND);
    echo $linea;
}

escribirLog("=== INICIO DE EJECUCIÓN ===");

try {
    // Conectar a la base de datos
    $database = new Database();
    $conn = $database->getConnection();
    
    if (!$conn) {
        throw new Exception("Error de conexión a la base de datos");
    }
    
    escribirLog("Conexión a base de datos establecida");
    
    // Configuración: días antes del vencimiento para enviar recordatorio
    $dias_anticipacion_min = 3;
    $dias_anticipacion_max = 5;
    
    $fecha_min = date('Y-m-d', strtotime("+{$dias_anticipacion_min} days"));
    $fecha_max = date('Y-m-d', strtotime("+{$dias_anticipacion_max} days"));
    
    escribirLog("Buscando recibos con vencimiento entre {$fecha_min} y {$fecha_max}");
    
    // Buscar recibos pendientes próximos a vencer
    // Solo enviamos recordatorio si no se ha enviado antes (usar una tabla de recordatorios_enviados si es necesario)
    $sql = "
        SELECT r.id as recibo_id, r.monto_deuda, r.fecha_vencimiento,
               u.nombre as usuario_nombre, u.email as usuario_email,
               c.fecha_periodo,
               e.nombre as edificio_nombre
        FROM recibos_inquilino r
        INNER JOIN usuarios u ON r.usuario_id = u.id
        INNER JOIN ciclos_facturacion c ON r.ciclo_id = c.id
        INNER JOIN edificios e ON c.edificio_id = e.id
        WHERE r.estado = 'PENDIENTE' 
          AND r.activo = 1
          AND r.fecha_vencimiento BETWEEN ? AND ?
          AND u.email IS NOT NULL
          AND u.email != ''
        ORDER BY r.fecha_vencimiento ASC
    ";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $fecha_min, $fecha_max);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $total_recibos = $result->num_rows;
    escribirLog("Recibos encontrados: {$total_recibos}");
    
    if ($total_recibos === 0) {
        escribirLog("No hay recibos pendientes próximos a vencer");
        $stmt->close();
        $conn->close();
        escribirLog("=== FIN DE EJECUCIÓN ===\n");
        exit(0);
    }
    
    // Inicializar mailer
    $mailer = new Mailer();
    
    $enviados = 0;
    $errores = 0;
    
    // Procesar cada recibo
    while ($recibo = $result->fetch_assoc()) {
        $dias_restantes = floor((strtotime($recibo['fecha_vencimiento']) - time()) / (60 * 60 * 24));
        
        escribirLog("Procesando recibo #{$recibo['recibo_id']} - {$recibo['usuario_nombre']} - {$dias_restantes} días restantes");
        
        $datos_recibo = [
            'periodo' => $recibo['fecha_periodo'],
            'monto' => $recibo['monto_deuda'],
            'vencimiento' => $recibo['fecha_vencimiento'],
            'dias_restantes' => $dias_restantes,
            'link_sistema' => 'http://localhost:8012/proyectoEdificio/mis_pagos.php'
        ];
        
        try {
            if ($mailer->enviarRecordatorioVencimiento($recibo['usuario_email'], $recibo['usuario_nombre'], $datos_recibo)) {
                $enviados++;
                escribirLog("✓ Email enviado a {$recibo['usuario_email']}");
            } else {
                $errores++;
                escribirLog("✗ Error al enviar email a {$recibo['usuario_email']}");
            }
        } catch (Exception $e) {
            $errores++;
            escribirLog("✗ Excepción al enviar email: " . $e->getMessage());
        }
        
        // Pequeña pausa entre envíos para no sobrecargar el servidor SMTP
        usleep(500000); // 0.5 segundos
    }
    
    $stmt->close();
    $conn->close();
    
    escribirLog("=== RESUMEN ===");
    escribirLog("Total de recibos: {$total_recibos}");
    escribirLog("Emails enviados exitosamente: {$enviados}");
    escribirLog("Errores: {$errores}");
    escribirLog("=== FIN DE EJECUCIÓN ===\n");
    
    // Código de salida según resultado
    exit($errores > 0 ? 1 : 0);
    
} catch (Exception $e) {
    escribirLog("ERROR CRÍTICO: " . $e->getMessage());
    escribirLog("=== FIN DE EJECUCIÓN CON ERROR ===\n");
    exit(2);
}
?>
