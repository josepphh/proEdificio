<?php
/**
 * Clase para el envío de correos electrónicos
 * Usa PHPMailer para gestionar notificaciones del sistema
 */

require_once __DIR__ . '/../vendor/phpmailer/src/PHPMailer.php';
require_once __DIR__ . '/../vendor/phpmailer/src/SMTP.php';
require_once __DIR__ . '/../vendor/phpmailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Mailer {
    private $mailer;
    private $from_email;
    private $from_name;
    
    public function __construct() {
        $this->mailer = new PHPMailer(true);
        
        // Configuración SMTP (ajustar según proveedor)
        // Para desarrollo, usar Mailtrap, Gmail, o similar
        try {
            // Cargar configuración desde archivo
            $config = require __DIR__ . '/../config/email_config.php';
            
            $this->mailer->isSMTP();
            $this->mailer->Host       = $config['smtp_host'];
            $this->mailer->SMTPAuth   = $config['smtp_auth'];
            $this->mailer->Username   = $config['smtp_username'];
            $this->mailer->Password   = $config['smtp_password'];
            $this->mailer->SMTPSecure = $config['smtp_secure'] === 'tls' ? PHPMailer::ENCRYPTION_STARTTLS : PHPMailer::ENCRYPTION_SMTPS;
            $this->mailer->Port       = $config['smtp_port'];
            $this->mailer->CharSet    = $config['charset'];
            
            // Habilitar debug si está configurado
            if ($config['debug']) {
                $this->mailer->SMTPDebug = 2;
            }
            
            $this->from_email = $config['from_email'];
            $this->from_name  = $config['from_name'];
            
            $this->mailer->setFrom($this->from_email, $this->from_name);
        } catch (Exception $e) {
            error_log("Error configurando mailer: " . $e->getMessage());
        }
    }
    
    /**
     * Enviar notificación de nuevo recibo generado
     */
    public function enviarNotificacionRecibo($destinatario_email, $destinatario_nombre, $datos_recibo) {
        try {
            $this->mailer->clearAddresses();
            $this->mailer->addAddress($destinatario_email, $destinatario_nombre);
            
            $this->mailer->isHTML(true);
            $this->mailer->Subject = 'Nuevo Recibo de Gastos Comunes - ' . date('F Y', strtotime($datos_recibo['periodo']));
            
            $body = $this->getTemplateRecibo($destinatario_nombre, $datos_recibo);
            $this->mailer->Body = $body;
            $this->mailer->AltBody = strip_tags($body);
            
            $this->mailer->send();
            return true;
        } catch (Exception $e) {
            error_log("Error enviando email de recibo: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Enviar confirmación de pago registrado
     */
    public function enviarConfirmacionPago($destinatario_email, $destinatario_nombre, $datos_pago) {
        try {
            $this->mailer->clearAddresses();
            $this->mailer->addAddress($destinatario_email, $destinatario_nombre);
            
            $this->mailer->isHTML(true);
            $this->mailer->Subject = 'Pago Registrado - Pendiente de Validación';
            
            $body = $this->getTemplatePagoRegistrado($destinatario_nombre, $datos_pago);
            $this->mailer->Body = $body;
            $this->mailer->AltBody = strip_tags($body);
            
            $this->mailer->send();
            return true;
        } catch (Exception $e) {
            error_log("Error enviando email de confirmación: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Enviar notificación de pago verificado
     */
    public function enviarPagoVerificado($destinatario_email, $destinatario_nombre, $datos_pago) {
        try {
            $this->mailer->clearAddresses();
            $this->mailer->addAddress($destinatario_email, $destinatario_nombre);
            
            $this->mailer->isHTML(true);
            $this->mailer->Subject = '✅ Pago Verificado - Recibo Pagado';
            
            $body = $this->getTemplatePagoVerificado($destinatario_nombre, $datos_pago);
            $this->mailer->Body = $body;
            $this->mailer->AltBody = strip_tags($body);
            
            $this->mailer->send();
            return true;
        } catch (Exception $e) {
            error_log("Error enviando email de verificación: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Enviar notificación de pago rechazado
     */
    public function enviarPagoRechazado($destinatario_email, $destinatario_nombre, $datos_pago, $motivo) {
        try {
            $this->mailer->clearAddresses();
            $this->mailer->addAddress($destinatario_email, $destinatario_nombre);
            
            $this->mailer->isHTML(true);
            $this->mailer->Subject = '❌ Pago Rechazado - Acción Requerida';
            
            $body = $this->getTemplatePagoRechazado($destinatario_nombre, $datos_pago, $motivo);
            $this->mailer->Body = $body;
            $this->mailer->AltBody = strip_tags($body);
            
            $this->mailer->send();
            return true;
        } catch (Exception $e) {
            error_log("Error enviando email de rechazo: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Enviar notificación de nuevo aviso urgente
     */
    public function enviarAvisoUrgente($destinatario_email, $destinatario_nombre, $datos_aviso) {
        try {
            $this->mailer->clearAddresses();
            $this->mailer->addAddress($destinatario_email, $destinatario_nombre);
            
            $this->mailer->isHTML(true);
            $this->mailer->Subject = '⚠️ AVISO URGENTE - ' . $datos_aviso['titulo'];
            
            $body = $this->getTemplateAvisoUrgente($destinatario_nombre, $datos_aviso);
            $this->mailer->Body = $body;
            $this->mailer->AltBody = strip_tags($body);
            
            $this->mailer->send();
            return true;
        } catch (Exception $e) {
            error_log("Error enviando email de aviso urgente: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Recordatorio de pago próximo a vencer
     */
    public function enviarRecordatorioVencimiento($destinatario_email, $destinatario_nombre, $datos_recibo) {
        try {
            $this->mailer->clearAddresses();
            $this->mailer->addAddress($destinatario_email, $destinatario_nombre);
            
            $this->mailer->isHTML(true);
            $this->mailer->Subject = '⏰ Recordatorio: Pago Próximo a Vencer';
            
            $body = $this->getTemplateRecordatorio($destinatario_nombre, $datos_recibo);
            $this->mailer->Body = $body;
            $this->mailer->AltBody = strip_tags($body);
            
            $this->mailer->send();
            return true;
        } catch (Exception $e) {
            error_log("Error enviando recordatorio: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Enviar email de bienvenida a nuevo usuario
     */
    public function enviarBienvenidaUsuario($destinatario_email, $destinatario_nombre, $datos_usuario) {
        try {
            $this->mailer->clearAddresses();
            $this->mailer->addAddress($destinatario_email, $destinatario_nombre);
            
            $this->mailer->isHTML(true);
            $this->mailer->Subject = '🎉 Bienvenido al Sistema de Edificios';
            
            $body = $this->getTemplateBienvenida($destinatario_nombre, $datos_usuario);
            $this->mailer->Body = $body;
            $this->mailer->AltBody = strip_tags($body);
            
            $this->mailer->send();
            return true;
        } catch (Exception $e) {
            error_log("Error enviando email de bienvenida: " . $e->getMessage());
            return false;
        }
    }
    
    // ============ PLANTILLAS HTML ============
    
    private function getTemplateRecibo($nombre, $datos) {
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center; border-radius: 8px 8px 0 0; }
                .content { background: white; padding: 30px; border: 1px solid #ddd; }
                .footer { background: #f8f9fa; padding: 20px; text-align: center; font-size: 12px; color: #666; border-radius: 0 0 8px 8px; }
                .btn { display: inline-block; padding: 12px 30px; background: #667eea; color: white; text-decoration: none; border-radius: 4px; margin: 20px 0; }
                .info-box { background: #f8f9fa; padding: 15px; border-left: 4px solid #667eea; margin: 15px 0; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>Nuevo Recibo Generado</h1>
                </div>
                <div class='content'>
                    <p>Estimado/a <strong>{$nombre}</strong>,</p>
                    <p>Le informamos que se ha generado un nuevo recibo de gastos comunes:</p>
                    
                    <div class='info-box'>
                        <strong>📋 Detalles del Recibo</strong><br><br>
                        <strong>Periodo:</strong> " . date('F Y', strtotime($datos['periodo'])) . "<br>
                        <strong>Edificio:</strong> {$datos['edificio']}<br>
                        <strong>Monto:</strong> S/ " . number_format($datos['monto'], 2) . "<br>
                        <strong>Fecha de Vencimiento:</strong> " . date('d/m/Y', strtotime($datos['vencimiento'])) . "
                    </div>
                    
                    <p>Por favor, proceda a realizar el pago antes de la fecha de vencimiento.</p>
                    
                    <center>
                        <a href='{$datos['link_sistema']}' class='btn'>Ver Recibo y Pagar</a>
                    </center>
                    
                    <p style='margin-top: 30px; font-size: 14px; color: #666;'>
                        <strong>Datos de pago:</strong><br>
                        Banco: BCP<br>
                        Cuenta: 194-1234567-0-89<br>
                        CCI: 002-194-001234567089-15
                    </p>
                </div>
                <div class='footer'>
                    <p>Este es un mensaje automático. Por favor no responda a este correo.</p>
                    <p>Sistema de Administración de Edificios &copy; " . date('Y') . "</p>
                </div>
            </div>
        </body>
        </html>
        ";
    }
    
    private function getTemplatePagoRegistrado($nombre, $datos) {
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white; padding: 30px; text-align: center; border-radius: 8px 8px 0 0; }
                .content { background: white; padding: 30px; border: 1px solid #ddd; }
                .footer { background: #f8f9fa; padding: 20px; text-align: center; font-size: 12px; color: #666; border-radius: 0 0 8px 8px; }
                .info-box { background: #fff3cd; padding: 15px; border-left: 4px solid #ffc107; margin: 15px 0; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>💳 Pago Registrado</h1>
                </div>
                <div class='content'>
                    <p>Hola <strong>{$nombre}</strong>,</p>
                    <p>Hemos recibido su registro de pago:</p>
                    
                    <div class='info-box'>
                        <strong>Información del Pago</strong><br><br>
                        <strong>Periodo:</strong> " . date('F Y', strtotime($datos['periodo'])) . "<br>
                        <strong>Monto:</strong> S/ " . number_format($datos['monto'], 2) . "<br>
                        <strong>Método:</strong> {$datos['metodo']}<br>
                        <strong>Fecha:</strong> " . date('d/m/Y H:i', strtotime($datos['fecha'])) . "
                    </div>
                    
                    <p><strong>⚠️ Estado:</strong> <span style='color: #ffc107;'>PENDIENTE DE VALIDACIÓN</span></p>
                    <p>Su pago será verificado por la administración. Le notificaremos cuando sea aprobado.</p>
                    
                    <p style='margin-top: 20px; font-size: 14px; color: #666;'>
                        Este proceso puede tomar hasta 24-48 horas hábiles.
                    </p>
                </div>
                <div class='footer'>
                    <p>Sistema de Administración de Edificios</p>
                </div>
            </div>
        </body>
        </html>
        ";
    }
    
    private function getTemplatePagoVerificado($nombre, $datos) {
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); color: white; padding: 30px; text-align: center; border-radius: 8px 8px 0 0; }
                .content { background: white; padding: 30px; border: 1px solid #ddd; }
                .footer { background: #f8f9fa; padding: 20px; text-align: center; font-size: 12px; color: #666; border-radius: 0 0 8px 8px; }
                .success-box { background: #d4edda; padding: 20px; border-left: 4px solid #28a745; margin: 15px 0; text-align: center; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>✅ Pago Verificado</h1>
                </div>
                <div class='content'>
                    <p>Hola <strong>{$nombre}</strong>,</p>
                    
                    <div class='success-box'>
                        <h2 style='color: #28a745; margin: 0;'>¡Pago Aprobado!</h2>
                        <p style='margin: 10px 0 0 0;'>Su pago ha sido verificado exitosamente</p>
                    </div>
                    
                    <p><strong>Detalles:</strong></p>
                    <ul>
                        <li><strong>Periodo:</strong> " . date('F Y', strtotime($datos['periodo'])) . "</li>
                        <li><strong>Monto:</strong> S/ " . number_format($datos['monto'], 2) . "</li>
                        <li><strong>Estado del Recibo:</strong> <span style='color: #28a745;'>PAGADO</span></li>
                    </ul>
                    
                    <p>Gracias por su pago puntual. Su recibo ha sido actualizado automáticamente.</p>
                    
                    <p style='font-size: 14px; color: #666; margin-top: 30px;'>
                        Puede descargar su comprobante desde el sistema en cualquier momento.
                    </p>
                </div>
                <div class='footer'>
                    <p>Sistema de Administración de Edificios</p>
                </div>
            </div>
        </body>
        </html>
        ";
    }
    
    private function getTemplatePagoRechazado($nombre, $datos, $motivo) {
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #f5576c 0%, #f093fb 100%); color: white; padding: 30px; text-align: center; border-radius: 8px 8px 0 0; }
                .content { background: white; padding: 30px; border: 1px solid #ddd; }
                .footer { background: #f8f9fa; padding: 20px; text-align: center; font-size: 12px; color: #666; border-radius: 0 0 8px 8px; }
                .error-box { background: #f8d7da; padding: 15px; border-left: 4px solid #dc3545; margin: 15px 0; }
                .btn { display: inline-block; padding: 12px 30px; background: #dc3545; color: white; text-decoration: none; border-radius: 4px; margin: 20px 0; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>❌ Pago Rechazado</h1>
                </div>
                <div class='content'>
                    <p>Hola <strong>{$nombre}</strong>,</p>
                    <p>Lamentamos informarle que su pago ha sido rechazado por la administración:</p>
                    
                    <div class='error-box'>
                        <strong>Periodo:</strong> " . date('F Y', strtotime($datos['periodo'])) . "<br>
                        <strong>Monto Registrado:</strong> S/ " . number_format($datos['monto'], 2) . "<br><br>
                        <strong>Motivo del Rechazo:</strong><br>
                        " . ($motivo ? htmlspecialchars($motivo) : 'No especificado') . "
                    </div>
                    
                    <p><strong>¿Qué debe hacer?</strong></p>
                    <ul>
                        <li>Verificar los datos bancarios y el comprobante de pago</li>
                        <li>Registrar nuevamente el pago con la información correcta</li>
                        <li>Contactar a la administración si tiene dudas</li>
                    </ul>
                    
                    <center>
                        <a href='{$datos['link_sistema']}' class='btn'>Registrar Pago Nuevamente</a>
                    </center>
                </div>
                <div class='footer'>
                    <p>Sistema de Administración de Edificios</p>
                </div>
            </div>
        </body>
        </html>
        ";
    }
    
    private function getTemplateAvisoUrgente($nombre, $datos) {
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #ff6b6b 0%, #ee5a6f 100%); color: white; padding: 30px; text-align: center; border-radius: 8px 8px 0 0; }
                .content { background: white; padding: 30px; border: 1px solid #ddd; }
                .footer { background: #f8f9fa; padding: 20px; text-align: center; font-size: 12px; color: #666; border-radius: 0 0 8px 8px; }
                .urgent-box { background: #fff3cd; padding: 20px; border: 2px solid #ffc107; margin: 15px 0; border-radius: 4px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>⚠️ AVISO URGENTE</h1>
                </div>
                <div class='content'>
                    <p>Estimado/a <strong>{$nombre}</strong>,</p>
                    
                    <div class='urgent-box'>
                        <h2 style='color: #856404; margin: 0 0 10px 0;'>{$datos['titulo']}</h2>
                        <p style='margin: 0;'>" . nl2br(htmlspecialchars($datos['contenido'])) . "</p>
                    </div>
                    
                    <p><strong>Edificio:</strong> {$datos['edificio']}</p>
                    <p><strong>Fecha:</strong> " . date('d/m/Y H:i', strtotime($datos['fecha'])) . "</p>
                    
                    <p style='color: #dc3545; font-weight: bold;'>
                        Este es un aviso de prioridad alta que requiere su atención inmediata.
                    </p>
                </div>
                <div class='footer'>
                    <p>Sistema de Administración de Edificios</p>
                </div>
            </div>
        </body>
        </html>
        ";
    }
    
    private function getTemplateRecordatorio($nombre, $datos) {
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); color: white; padding: 30px; text-align: center; border-radius: 8px 8px 0 0; }
                .content { background: white; padding: 30px; border: 1px solid #ddd; }
                .footer { background: #f8f9fa; padding: 20px; text-align: center; font-size: 12px; color: #666; border-radius: 0 0 8px 8px; }
                .btn { display: inline-block; padding: 12px 30px; background: #4facfe; color: white; text-decoration: none; border-radius: 4px; margin: 20px 0; }
                .warning-box { background: #fff3cd; padding: 15px; border-left: 4px solid #ffc107; margin: 15px 0; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>⏰ Recordatorio de Pago</h1>
                </div>
                <div class='content'>
                    <p>Hola <strong>{$nombre}</strong>,</p>
                    <p>Le recordamos que tiene un recibo próximo a vencer:</p>
                    
                    <div class='warning-box'>
                        <strong>Periodo:</strong> " . date('F Y', strtotime($datos['periodo'])) . "<br>
                        <strong>Monto:</strong> S/ " . number_format($datos['monto'], 2) . "<br>
                        <strong>Vencimiento:</strong> " . date('d/m/Y', strtotime($datos['vencimiento'])) . "<br>
                        <strong>Días restantes:</strong> {$datos['dias_restantes']}
                    </div>
                    
                    <p>Por favor, realice su pago antes de la fecha de vencimiento para evitar cargos adicionales.</p>
                    
                    <center>
                        <a href='{$datos['link_sistema']}' class='btn'>Pagar Ahora</a>
                    </center>
                </div>
                <div class='footer'>
                    <p>Sistema de Administración de Edificios</p>
                </div>
            </div>
        </body>
        </html>
        ";
    }
    
    private function getTemplateBienvenida($nombre, $datos) {
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center; border-radius: 8px 8px 0 0; }
                .content { background: white; padding: 30px; border: 1px solid #ddd; }
                .footer { background: #f8f9fa; padding: 20px; text-align: center; font-size: 12px; color: #666; border-radius: 0 0 8px 8px; }
                .btn { display: inline-block; padding: 12px 30px; background: #667eea; color: white; text-decoration: none; border-radius: 4px; margin: 20px 0; }
                .info-box { background: #e3f2fd; padding: 20px; border-left: 4px solid #2196f3; margin: 15px 0; border-radius: 4px; }
                .credentials { background: #f8f9fa; padding: 15px; border-radius: 4px; font-family: monospace; margin: 15px 0; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>🎉 ¡Bienvenido!</h1>
                </div>
                <div class='content'>
                    <p>Estimado/a <strong>{$nombre}</strong>,</p>
                    <p>¡Bienvenido al Sistema de Administración de Edificios!</p>
                    
                    <p>Su cuenta ha sido creada exitosamente con el siguiente perfil:</p>
                    
                    <div class='info-box'>
                        <strong>📋 Información de su cuenta</strong><br><br>
                        <strong>Rol:</strong> {$datos['rol']}<br>
                        <strong>Usuario:</strong> {$datos['username']}
                    </div>
                    
                    <div class='credentials'>
                        <strong>🔐 Credenciales de Acceso</strong><br><br>
                        <strong>Usuario:</strong> {$datos['username']}<br>
                        <strong>Contraseña temporal:</strong> {$datos['password']}<br><br>
                        <small style='color: #dc3545;'>⚠️ Por seguridad, le recomendamos cambiar su contraseña después del primer inicio de sesión.</small>
                    </div>
                    
                    <p><strong>¿Qué puedes hacer en el sistema?</strong></p>
                    <ul>
                        <li>Ver y gestionar información de tu edificio</li>
                        <li>Consultar recibos de gastos comunes</li>
                        <li>Registrar pagos y subir comprobantes</li>
                        <li>Reportar incidencias y ver avisos</li>
                        <li>Actualizar tu perfil</li>
                    </ul>
                    
                    <center>
                        <a href='{$datos['link_sistema']}' class='btn'>Ingresar al Sistema</a>
                    </center>
                    
                    <p style='margin-top: 30px; font-size: 14px; color: #666;'>
                        Si tiene alguna duda o problema para acceder, por favor contacte con la administración.
                    </p>
                </div>
                <div class='footer'>
                    <p>Sistema de Administración de Edificios &copy; " . date('Y') . "</p>
                    <p>Este es un mensaje automático. Por favor no responda a este correo.</p>
                </div>
            </div>
        </body>
        </html>
        ";
    }
}
?>
