<?php
require_once 'includes/session.php';
require_once 'config/database.php';
require_once 'vendor/fpdf/fpdf.php';

requiereAutenticacion('login.php');

// Obtener ID del recibo
$recibo_id = intval($_GET['id'] ?? 0);

if ($recibo_id <= 0) {
    die('Recibo no especificado');
}

$database = new Database();
$conn = $database->getConnection();

// Verificar que el recibo pertenece al usuario (o es admin)
$usuario_id = $_SESSION['usuario_id'];
$rol_nombre = $_SESSION['rol_nombre'] ?? '';
$es_admin = in_array($rol_nombre, ['Administrador Total', 'Administrador de Edificio']);

if ($es_admin) {
    // Admin puede ver todos
    $stmt = $conn->prepare("
        SELECT r.*, 
               u.nombre as inquilino_nombre, u.email as inquilino_email, u.telefono,
               e.nombre as edificio_nombre, e.direccion as edificio_direccion,
               c.fecha_periodo, c.fecha_generacion
        FROM recibos_inquilino r
        INNER JOIN usuarios u ON r.usuario_id = u.id
        INNER JOIN ciclos_facturacion c ON r.ciclo_id = c.id
        INNER JOIN edificios e ON c.edificio_id = e.id
        WHERE r.id = ?
    ");
    $stmt->bind_param("i", $recibo_id);
} else {
    // Inquilino solo ve sus propios recibos
    $stmt = $conn->prepare("
        SELECT r.*, 
               u.nombre as inquilino_nombre, u.email as inquilino_email, u.telefono,
               e.nombre as edificio_nombre, e.direccion as edificio_direccion,
               c.fecha_periodo, c.fecha_generacion
        FROM recibos_inquilino r
        INNER JOIN usuarios u ON r.usuario_id = u.id
        INNER JOIN ciclos_facturacion c ON r.ciclo_id = c.id
        INNER JOIN edificios e ON c.edificio_id = e.id
        WHERE r.id = ? AND r.usuario_id = ?
    ");
    $stmt->bind_param("ii", $recibo_id, $usuario_id);
}

$stmt->execute();
$result = $stmt->get_result();
$recibo = $result->fetch_assoc();
$stmt->close();

if (!$recibo) {
    $conn->close();
    die('Recibo no encontrado o sin permisos');
}

// Obtener detalle de gastos
$stmt = $conn->prepare("
    SELECT dg.*, s.nombre as servicio_nombre, s.tipo_cobro
    FROM detalle_gastos_recibo dg
    INNER JOIN servicios s ON dg.servicio_id = s.id
    WHERE dg.recibo_id = ?
    ORDER BY s.nombre
");
$stmt->bind_param("i", $recibo_id);
$stmt->execute();
$result = $stmt->get_result();

$detalles = [];
while ($row = $result->fetch_assoc()) {
    $detalles[] = $row;
}
$stmt->close();

// Obtener historial de pagos
$stmt = $conn->prepare("
    SELECT fecha_pago, monto_pagado, metodo_pago, estado
    FROM pagos_inquilino
    WHERE recibo_id = ? AND activo = 1
    ORDER BY fecha_pago DESC
");
$stmt->bind_param("i", $recibo_id);
$stmt->execute();
$result = $stmt->get_result();

$pagos = [];
while ($row = $result->fetch_assoc()) {
    $pagos[] = $row;
}
$stmt->close();

$conn->close();

// Generar PDF
class PDF extends FPDF
{
    function Header()
    {
        // Logo (si existe)
        /* if (file_exists('assets/logo.png')) {
            $this->Image('assets/logo.png', 10, 6, 30);
        } */
        
        $this->SetFont('Arial', 'B', 16);
        $this->Cell(0, 10, utf8_decode('RECIBO DE GASTOS COMUNES'), 0, 1, 'C');
        $this->SetFont('Arial', '', 10);
        $this->Cell(0, 5, 'Sistema de Administracion de Edificios', 0, 1, 'C');
        $this->Ln(5);
    }
    
    function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, utf8_decode('Página ') . $this->PageNo(), 0, 0, 'C');
    }
    
    function InfoBox($title, $content, $width)
    {
        $this->SetFillColor(240, 240, 240);
        $this->SetFont('Arial', 'B', 9);
        $this->Cell($width, 6, utf8_decode($title), 1, 0, 'L', true);
        $this->SetFont('Arial', '', 9);
        $this->Ln();
        $this->MultiCell($width, 6, utf8_decode($content), 1, 'L');
    }
}

$pdf = new PDF();
$pdf->AddPage();
$pdf->SetFont('Arial', '', 10);

// Información del recibo
$pdf->SetFillColor(52, 152, 219);
$pdf->SetTextColor(255, 255, 255);
$pdf->SetFont('Arial', 'B', 11);
$pdf->Cell(0, 8, utf8_decode('INFORMACIÓN DEL RECIBO'), 1, 1, 'C', true);

$pdf->SetTextColor(0, 0, 0);
$pdf->SetFont('Arial', '', 9);

// Datos en dos columnas
$pdf->Cell(95, 6, utf8_decode('Recibo N°: ') . $recibo['id'], 1, 0, 'L');
$pdf->Cell(95, 6, 'Periodo: ' . date('F Y', strtotime($recibo['fecha_periodo'])), 1, 1, 'L');

$pdf->Cell(95, 6, utf8_decode('Fecha Emisión: ') . date('d/m/Y', strtotime($recibo['fecha_generacion'])), 1, 0, 'L');
$pdf->Cell(95, 6, 'Fecha Vencimiento: ' . date('d/m/Y', strtotime($recibo['fecha_vencimiento'])), 1, 1, 'L');

$pdf->Cell(95, 6, 'Estado: ' . utf8_decode($recibo['estado']), 1, 0, 'L');
$pdf->Cell(95, 6, 'Tipo: ' . utf8_decode($recibo['tipo_recibo']), 1, 1, 'L');

$pdf->Ln(3);

// Información del edificio
$pdf->SetFillColor(46, 204, 113);
$pdf->SetTextColor(255, 255, 255);
$pdf->SetFont('Arial', 'B', 11);
$pdf->Cell(0, 8, 'EDIFICIO', 1, 1, 'C', true);

$pdf->SetTextColor(0, 0, 0);
$pdf->SetFont('Arial', '', 9);
$pdf->Cell(0, 6, utf8_decode($recibo['edificio_nombre']), 1, 1, 'L');
$pdf->Cell(0, 6, utf8_decode('Dirección: ') . utf8_decode($recibo['edificio_direccion']), 1, 1, 'L');

$pdf->Ln(3);

// Información del inquilino
$pdf->SetFillColor(155, 89, 182);
$pdf->SetTextColor(255, 255, 255);
$pdf->SetFont('Arial', 'B', 11);
$pdf->Cell(0, 8, 'INQUILINO', 1, 1, 'C', true);

$pdf->SetTextColor(0, 0, 0);
$pdf->SetFont('Arial', '', 9);
$pdf->Cell(0, 6, utf8_decode($recibo['inquilino_nombre']), 1, 1, 'L');
$pdf->Cell(95, 6, 'Email: ' . utf8_decode($recibo['inquilino_email']), 1, 0, 'L');
$pdf->Cell(95, 6, utf8_decode('Teléfono: ') . utf8_decode($recibo['telefono'] ?? 'N/A'), 1, 1, 'L');

$pdf->Ln(5);

// Detalle de gastos
$pdf->SetFillColor(230, 126, 34);
$pdf->SetTextColor(255, 255, 255);
$pdf->SetFont('Arial', 'B', 11);
$pdf->Cell(0, 8, 'DETALLE DE GASTOS', 1, 1, 'C', true);

$pdf->SetTextColor(0, 0, 0);
$pdf->SetFillColor(220, 220, 220);
$pdf->SetFont('Arial', 'B', 9);
$pdf->Cell(80, 7, 'Servicio', 1, 0, 'C', true);
$pdf->Cell(35, 7, 'Tipo Cobro', 1, 0, 'C', true);
$pdf->Cell(40, 7, 'Consumo', 1, 0, 'C', true);
$pdf->Cell(35, 7, 'Monto', 1, 1, 'C', true);

$pdf->SetFont('Arial', '', 9);
$total = 0;
foreach ($detalles as $detalle) {
    $pdf->Cell(80, 6, utf8_decode($detalle['servicio_nombre']), 1, 0, 'L');
    $pdf->Cell(35, 6, utf8_decode($detalle['tipo_cobro']), 1, 0, 'C');
    
    $consumo_text = 'N/A';
    if ($detalle['consumo'] !== null) {
        $consumo_text = $detalle['consumo'] . ' ' . ($detalle['tipo_cobro'] === 'POR_CONSUMO' ? 'm3' : '');
    }
    $pdf->Cell(40, 6, $consumo_text, 1, 0, 'C');
    $pdf->Cell(35, 6, 'S/ ' . number_format($detalle['monto'], 2), 1, 1, 'R');
    $total += $detalle['monto'];
}

// Total
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(155, 7, 'TOTAL A PAGAR', 1, 0, 'R');
$pdf->Cell(35, 7, 'S/ ' . number_format($total, 2), 1, 1, 'R');

$pdf->Ln(5);

// Historial de pagos (si existen)
if (count($pagos) > 0) {
    $pdf->SetFillColor(231, 76, 60);
    $pdf->SetTextColor(255, 255, 255);
    $pdf->SetFont('Arial', 'B', 11);
    $pdf->Cell(0, 8, 'HISTORIAL DE PAGOS', 1, 1, 'C', true);
    
    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetFillColor(220, 220, 220);
    $pdf->SetFont('Arial', 'B', 9);
    $pdf->Cell(40, 7, 'Fecha', 1, 0, 'C', true);
    $pdf->Cell(35, 7, 'Monto', 1, 0, 'C', true);
    $pdf->Cell(50, 7, utf8_decode('Método'), 1, 0, 'C', true);
    $pdf->Cell(65, 7, 'Estado', 1, 1, 'C', true);
    
    $pdf->SetFont('Arial', '', 9);
    foreach ($pagos as $pago) {
        $pdf->Cell(40, 6, date('d/m/Y', strtotime($pago['fecha_pago'])), 1, 0, 'C');
        $pdf->Cell(35, 6, 'S/ ' . number_format($pago['monto_pagado'], 2), 1, 0, 'R');
        $pdf->Cell(50, 6, utf8_decode($pago['metodo_pago']), 1, 0, 'L');
        $pdf->Cell(65, 6, utf8_decode($pago['estado']), 1, 1, 'C');
    }
    $pdf->Ln(3);
}

// Información de pago
$pdf->SetFillColor(52, 152, 219);
$pdf->SetTextColor(255, 255, 255);
$pdf->SetFont('Arial', 'B', 11);
$pdf->Cell(0, 8, utf8_decode('INFORMACIÓN DE PAGO'), 1, 1, 'C', true);

$pdf->SetTextColor(0, 0, 0);
$pdf->SetFont('Arial', '', 9);
$pdf->MultiCell(0, 5, utf8_decode("
Banco: Banco de Crédito del Perú (BCP)
Cuenta Corriente: 194-1234567-0-89
CCI: 002-194-001234567089-15
Titular: Administración " . $recibo['edificio_nombre'] . "

Importante: Al realizar el pago, enviar el comprobante a través del sistema.
Para dudas o consultas, comunicarse con la administración del edificio.
"), 1);

$pdf->Ln(3);

// Pie de página informativo
$pdf->SetFont('Arial', 'I', 8);
$pdf->MultiCell(0, 4, utf8_decode("
Este recibo fue generado automáticamente el " . date('d/m/Y H:i:s') . "
Sistema de Administración de Edificios - Todos los derechos reservados
"), 0, 'C');

// Output
$filename = 'Recibo_' . $recibo['id'] . '_' . date('Ymd', strtotime($recibo['fecha_periodo'])) . '.pdf';
$pdf->Output('D', $filename);
?>
