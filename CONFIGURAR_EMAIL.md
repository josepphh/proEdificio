# 📧 CONFIGURACIÓN DE EMAIL - GMAIL

## 🚀 Pasos para configurar Gmail

### **1. Habilitar verificación en 2 pasos**
1. Ve a tu cuenta de Google: https://myaccount.google.com/security
2. En "Cómo inicias sesión en Google", haz clic en "Verificación en 2 pasos"
3. Sigue los pasos para habilitarla (necesitas tu teléfono)

### **2. Generar contraseña de aplicación**
1. Ve a: https://myaccount.google.com/apppasswords
2. En "Seleccionar app", elige: **Correo**
3. En "Seleccionar dispositivo", elige: **Otro (nombre personalizado)**
4. Escribe: `Sistema Edificios`
5. Click en **Generar**
6. **COPIA** la contraseña de 16 caracteres (algo como: `abcd efgh ijkl mnop`)

### **3. Configurar en el sistema**
1. Abre el archivo: `config/email_config.php`
2. Reemplaza estos valores:

```php
// Línea 20 - Tu email de Gmail
'smtp_username' => 'tu_email_real@gmail.com',

// Línea 21 - La contraseña de aplicación que copiaste
'smtp_password' => 'abcd efgh ijkl mnop',

// Línea 24 - Mismo email
'from_email'    => 'tu_email_real@gmail.com',
```

3. Guarda el archivo

### **4. Probar que funciona**
Crea un usuario nuevo desde el panel admin y verifica que llegue el email de bienvenida.

---

## 🔍 Solución de problemas

### **Error: "Authentication failed"**
- ✅ Verifica que copiaste bien la contraseña de aplicación (16 caracteres)
- ✅ Asegúrate de habilitar la verificación en 2 pasos primero
- ✅ La contraseña NO es tu contraseña de Gmail normal

### **Error: "Could not connect to SMTP host"**
- ✅ Verifica tu conexión a internet
- ✅ Gmail puede estar bloqueado por tu firewall
- ✅ Cambia `'debug' => true` en `email_config.php` para ver más detalles

### **Los emails van a SPAM**
- ✅ Normal al principio, marca como "No es spam"
- ✅ Después de algunos emails, Gmail aprende

---

## 📝 Archivo a editar

**Ubicación:** `config/email_config.php`

Cambia solo estas 3 líneas:
- `smtp_username` → Tu email de Gmail
- `smtp_password` → Contraseña de aplicación (16 caracteres)
- `from_email` → Tu email de Gmail (mismo que username)

---

## ✅ Checklist

- [ ] Verificación en 2 pasos habilitada
- [ ] Contraseña de aplicación generada
- [ ] Archivo `email_config.php` editado
- [ ] Usuario de prueba creado
- [ ] Email de bienvenida recibido

---

**¡Listo!** Ahora el sistema enviará emails automáticamente. 🚀
