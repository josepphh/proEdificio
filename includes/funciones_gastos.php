<?php
/**
 * Funciones de cálculo para el sistema de gastos
 * 
 * Contiene la lógica matemática para:
 * - Prorrateo por días vividos
 * - Cálculo de consumo por medidores
 */

require_once __DIR__ . '/../config/database.php';

/**
 * Calcula los días vividos por un usuario en un período específico
 * 
 * @param string $fecha_ingreso Fecha de ingreso del usuario (Y-m-d)
 * @param string|null $fecha_salida Fecha de salida del usuario o null si sigue activo
 * @param string $fecha_periodo Fecha de inicio del período (Y-m-d)
 * @param int $dias_del_mes Total de días del mes
 * @return int Número de días vividos en el período
 */
function calcularDiasVividos($fecha_ingreso, $fecha_salida, $fecha_periodo, $dias_del_mes) {
    // Convertir fechas a timestamps
    $inicio_periodo = strtotime($fecha_periodo);
    $fin_periodo = strtotime($fecha_periodo . " +1 month -1 day");
    
    // Fecha de ingreso del usuario
    $ingreso = strtotime($fecha_ingreso);
    
    // Fecha de salida (si existe) o fin del período
    $salida = $fecha_salida ? strtotime($fecha_salida) : $fin_periodo;
    
    // Calcular el inicio efectivo (el más tardío entre ingreso y inicio del período)
    $inicio_efectivo = max($ingreso, $inicio_periodo);
    
    // Calcular el fin efectivo (el más temprano entre salida y fin del período)
    $fin_efectivo = min($salida, $fin_periodo);
    
    // Calcular días vividos
    $dias_vividos = ($fin_efectivo - $inicio_efectivo) / (60 * 60 * 24) + 1;
    
    // Asegurar que esté en el rango válido (0 a días_del_mes)
    $dias_vividos = max(0, min($dias_vividos, $dias_del_mes));
    
    return (int)$dias_vividos;
}

/**
 * Calcula el prorrateo de un gasto de cuota fija entre inquilinos
 * basado en los días vividos
 * 
 * @param mysqli $conn Conexión a la base de datos
 * @param int $edificio_id ID del edificio
 * @param string $fecha_periodo Fecha del período (Y-m-d)
 * @param int $dias_del_mes Total de días del mes
 * @param float $monto_total Monto total a prorratear
 * @return array Array asociativo [usuario_id => monto]
 */
function calcularProrrateoCuotaFija($conn, $edificio_id, $fecha_periodo, $dias_del_mes, $monto_total) {
    $deudas = [];
    
    // Obtener todos los inquilinos activos del edificio
    $stmt = $conn->prepare("
        SELECT id, fecha_ingreso, fecha_salida 
        FROM usuarios 
        WHERE edificio_id = ? AND activo = 1 AND rol_id IN (3, 2)
        ORDER BY id
    ");
    $stmt->bind_param("i", $edificio_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $inquilinos = [];
    $dias_por_inquilino = [];
    $total_dias_edificio = 0;
    
    // Calcular días vividos para cada inquilino
    while ($row = $result->fetch_assoc()) {
        $inquilino_id = $row['id'];
        $fecha_ingreso = $row['fecha_ingreso'] ? $row['fecha_ingreso'] : $fecha_periodo;
        $fecha_salida = $row['fecha_salida'];
        
        $dias_vividos = calcularDiasVividos($fecha_ingreso, $fecha_salida, $fecha_periodo, $dias_del_mes);
        
        $inquilinos[] = $row;
        $dias_por_inquilino[$inquilino_id] = $dias_vividos;
        $total_dias_edificio += $dias_vividos;
    }
    
    $stmt->close();
    
    // Si no hay días totales, retornar array vacío
    if ($total_dias_edificio == 0) {
        return $deudas;
    }
    
    // Calcular precio por día
    $precio_por_dia = $monto_total / $total_dias_edificio;
    
    // Asignar monto a cada inquilino
    foreach ($dias_por_inquilino as $inquilino_id => $dias) {
        $monto = $dias * $precio_por_dia;
        $deudas[$inquilino_id] = round($monto, 2);
    }
    
    return $deudas;
}

/**
 * Calcula el prorrateo de un gasto de consumo (agua) entre inquilinos
 * basado en las lecturas de medidores
 * 
 * @param mysqli $conn Conexión a la base de datos
 * @param int $ciclo_id ID del ciclo de facturación
 * @param int $servicio_id ID del servicio
 * @param float $monto_total Monto total a prorratear
 * @return array Array asociativo [usuario_id => monto]
 */
function calcularProrrateoConsumo($conn, $ciclo_id, $servicio_id, $monto_total) {
    $deudas = [];
    
    // Obtener todas las lecturas del ciclo y servicio
    $stmt = $conn->prepare("
        SELECT usuario_id, consumo_calculado 
        FROM lecturas_medidor 
        WHERE ciclo_id = ? AND servicio_id = ?
    ");
    $stmt->bind_param("ii", $ciclo_id, $servicio_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $lecturas = [];
    $total_consumo = 0;
    
    // Sumar todos los consumos
    while ($row = $result->fetch_assoc()) {
        $consumo = (float)$row['consumo_calculado'];
        $lecturas[$row['usuario_id']] = $consumo;
        $total_consumo += $consumo;
    }
    
    $stmt->close();
    
    // Si no hay consumo total, retornar array vacío
    if ($total_consumo == 0) {
        return $deudas;
    }
    
    // Calcular precio por unidad de consumo
    $precio_por_unidad = $monto_total / $total_consumo;
    
    // Asignar monto a cada inquilino según su consumo
    foreach ($lecturas as $usuario_id => $consumo) {
        $monto = $consumo * $precio_por_unidad;
        $deudas[$usuario_id] = round($monto, 2);
    }
    
    return $deudas;
}

/**
 * Obtiene el total de días del mes para una fecha dada
 * 
 * @param string $fecha_periodo Fecha en formato Y-m-d
 * @return int Número de días del mes
 */
function obtenerDiasDelMes($fecha_periodo) {
    return (int)date('t', strtotime($fecha_periodo));
}

?>

