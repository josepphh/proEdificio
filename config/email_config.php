<?php
/**
 * ================================================
 * CONFIGURACIÓN DE EMAIL - Gmail
 * ================================================
 * 
 * INSTRUCCIONES PARA CONFIGURAR GMAIL:
 * 
 * 1. Habilitar verificación en 2 pasos:
 *    https://myaccount.google.com/security
 * 
 * 2. Generar contraseña de aplicación:
 *    https://myaccount.google.com/apppasswords
 *    - Selecciona "Correo"
 *    - Selecciona "Otro dispositivo"
 *    - Copia la contraseña (16 caracteres)
 * 
 * 3. Reemplaza los valores abajo con tus datos
 */

return [
    // Configuración SMTP Gmail
    'smtp_host'     => 'smtp.gmail.com',
    'smtp_port'     => 587,
    'smtp_secure'   => 'tls',  // tls o ssl
    'smtp_auth'     => true,
    
    // TUS CREDENCIALES (CAMBIAR AQUÍ)
    'smtp_username' => 'TU_EMAIL@gmail.com',        // ← Tu email de Gmail
    'smtp_password' => 'xxxx xxxx xxxx xxxx',       // ← Contraseña de aplicación (16 caracteres)
    
    // Remitente
    'from_email'    => 'TU_EMAIL@gmail.com',        // ← Mismo que smtp_username
    'from_name'     => 'Sistema de Edificios',
    
    // Configuración adicional
    'charset'       => 'UTF-8',
    'debug'         => false,  // Cambiar a true para ver errores SMTP
];
