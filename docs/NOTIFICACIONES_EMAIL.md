# Sistema de Notificaciones por Email

## 📧 Configuración Inicial

### 1. Configurar SMTP en `includes/mail.php`

Editar las líneas 23-28:

```php
$this->mailer->Host       = 'smtp.gmail.com';  // Servidor SMTP
$this->mailer->Username   = 'tu_email@gmail.com';  // Email
$this->mailer->Password   = 'tu_password_app';      // Contraseña de aplicación
$this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
$this->mailer->Port       = 587;
```

#### Opción A: Gmail (Recomendado para desarrollo)

1. Ir a Google Account → Security → 2-Step Verification
2. Crear "App Password"
3. Usar ese password en `$this->mailer->Password`

#### Opción B: Mailtrap (Recomendado para testing)

```php
$this->mailer->Host       = 'smtp.mailtrap.io';
$this->mailer->Username   = 'tu_mailtrap_username';
$this->mailer->Password   = 'tu_mailtrap_password';
$this->mailer->Port       = 2525;
```

#### Opción C: Servidor SMTP propio

Configurar según especificaciones de tu proveedor.

---

## 📨 Tipos de Notificaciones Automáticas

### ✅ Implementadas y Activas

| Evento | Destinatarios | Plantilla | Archivo |
|--------|--------------|-----------|---------|
| **Cierre Mensual** | Todos los inquilinos con recibos | Notificación de nuevo recibo | `procesar_cierre.php` |
| **Pago Registrado** | Inquilino que registra pago | Confirmación de pago pendiente | `procesar_pago.php` |
| **Pago Verificado** | Inquilino con pago aprobado | Confirmación de pago verificado | `api/validar_pago.php` |
| **Pago Rechazado** | Inquilino con pago rechazado | Notificación de rechazo + motivo | `api/validar_pago.php` |
| **Usuario Creado** | Nuevo usuario | Bienvenida + credenciales | `api/gestionar_usuario.php` |
| **Aviso Urgente** | Inquilinos del edificio/todos | Alerta de aviso urgente | `api/gestionar_avisos.php` |
| **Recordatorio Vencimiento** | Inquilinos con recibos próximos | Recordatorio 3-5 días antes | `scripts/enviar_recordatorios.php` |

---

## ⏰ Configurar Recordatorios Automáticos

### Windows (Task Scheduler)

#### Opción 1: Ejecutar script automático (Recomendado)

```powershell
# Clic derecho en configurar_recordatorios.bat → Ejecutar como Administrador
.\scripts\configurar_recordatorios.bat
```

Esto crea una tarea programada que ejecuta diariamente a las 9:00 AM.

#### Opción 2: Configuración manual

1. Abrir Task Scheduler (`taskschd.msc`)
2. Crear tarea básica:
   - **Nombre**: Recordatorios_Edificios
   - **Trigger**: Diariamente a las 9:00 AM
   - **Acción**: Iniciar programa
   - **Programa**: `C:\xampp\php\php.exe`
   - **Argumentos**: `-f "c:\xampp\htdocs\proyectoEdificio\scripts\enviar_recordatorios.php"`

### Linux/Mac (Cron)

```bash
# Editar crontab
crontab -e

# Agregar línea (ejecutar diariamente a las 9:00 AM)
0 9 * * * php /ruta/completa/proyectoEdificio/scripts/enviar_recordatorios.php
```

---

## 🧪 Probar Notificaciones

### 1. Probar Recordatorios Manualmente

```powershell
# Windows PowerShell
.\scripts\probar_recordatorios.ps1

# O directamente con PHP
php scripts\enviar_recordatorios.php
```

### 2. Verificar Log de Ejecución

```
scripts/recordatorios.log
```

### 3. Probar Otras Notificaciones

#### Crear Usuario (Testing)
- Ir a `admin/usuarios.php`
- Crear usuario con email válido
- Verificar recepción de email de bienvenida

#### Procesar Cierre Mensual (Testing)
- Ir a `admin/procesar_cierre.php`
- Procesar cierre de un edificio
- Verificar emails enviados a inquilinos

#### Crear Aviso Urgente (Testing)
- Ir a `admin/avisos.php`
- Crear aviso tipo "URGENTE"
- Verificar emails enviados

#### Registrar y Validar Pago (Testing)
1. Como inquilino: registrar pago en `mis_pagos.php`
2. Como admin: validar en `admin/validar_pagos.php`
3. Verificar 2 emails: confirmación + verificación

---

## 📋 Personalización de Plantillas

Todas las plantillas están en `includes/mail.php`:

| Método | Plantilla | Línea |
|--------|-----------|-------|
| `enviarNotificacionRecibo()` | `getTemplateRecibo()` | ~180 |
| `enviarConfirmacionPago()` | `getTemplatePagoRegistrado()` | ~230 |
| `enviarPagoVerificado()` | `getTemplatePagoVerificado()` | ~280 |
| `enviarPagoRechazado()` | `getTemplatePagoRechazado()` | ~330 |
| `enviarAvisoUrgente()` | `getTemplateAvisoUrgente()` | ~380 |
| `enviarRecordatorioVencimiento()` | `getTemplateRecordatorio()` | ~430 |
| `enviarBienvenidaUsuario()` | `getTemplateBienvenida()` | ~480 |

### Personalizar Colores/Diseño

Editar sección `<style>` dentro de cada plantilla.

### Cambiar Remitente

Editar líneas 31-32 en `includes/mail.php`:

```php
$this->from_email = 'noreply@edificios.com';
$this->from_name  = 'Sistema de Edificios';
```

---

## 🔧 Solución de Problemas

### Los emails no se envían

1. **Verificar configuración SMTP** en `includes/mail.php`
2. **Verificar logs de PHP**: `c:\xampp\php\logs\php_error_log`
3. **Verificar log de recordatorios**: `scripts\recordatorios.log`
4. **Probar conexión SMTP**:
   ```php
   php -r "require 'vendor/phpmailer/src/PHPMailer.php'; 
          require 'vendor/phpmailer/src/SMTP.php'; 
          require 'vendor/phpmailer/src/Exception.php';"
   ```

### Gmail bloquea el envío

- Usar "App Password" (no tu contraseña normal)
- Activar "Less secure app access" (no recomendado)
- Mejor: usar Mailtrap para testing

### Recordatorios no se ejecutan

1. **Verificar que la tarea está creada**:
   ```powershell
   schtasks /query | findstr "Recordatorios"
   ```

2. **Ejecutar manualmente**:
   ```powershell
   schtasks /run /tn "Recordatorios_Edificios"
   ```

3. **Ver historial**:
   - Task Scheduler → Recordatorios_Edificios → History

### Errores en log

Revisar `scripts/recordatorios.log` para detalles específicos.

---

## 📊 Estadísticas de Emails

Los emails enviados en cada proceso se registran en la respuesta:

```json
// procesar_cierre.php
{
  "success": true,
  "message": "Cierre mensual procesado exitosamente. Emails enviados: 15",
  "ciclo_id": 42,
  "recibos_generados": 15,
  "emails_enviados": 15
}

// gestionar_avisos.php (aviso urgente)
{
  "success": true,
  "message": "Aviso urgente creado. Emails enviados: 23"
}

// enviar_recordatorios.php (log)
[2025-11-17 09:00:00] Total de recibos: 8
[2025-11-17 09:00:05] Emails enviados exitosamente: 7
[2025-11-17 09:00:05] Errores: 1
```

---

## 🎨 Características de las Plantillas

- ✅ Diseño responsive
- ✅ Colores con gradientes
- ✅ Compatible con Gmail, Outlook, Yahoo
- ✅ Fallback a texto plano automático
- ✅ Emojis para mejor visualización
- ✅ Links directos al sistema
- ✅ Información bancaria incluida
- ✅ Codificación UTF-8

---

## 🚀 Próximos Pasos

1. Configurar SMTP con credenciales reales
2. Probar todas las notificaciones
3. Configurar tarea programada de recordatorios
4. Personalizar plantillas según branding
5. Monitorear logs durante primera semana

---

## 📞 Soporte

Si encuentras problemas:
1. Revisar logs: `php_error_log` y `recordatorios.log`
2. Verificar configuración SMTP
3. Probar con Mailtrap primero
4. Contactar administrador del sistema
