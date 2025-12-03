# 📦 Guía de Instalación Limpia - Sistema de Gestión de Edificios v2.0

## 🎯 Introducción

Esta guía describe cómo realizar una **instalación limpia** del sistema. Una instalación limpia incluye **SOLO los datos mínimos necesarios** para que el sistema funcione, sin datos de ejemplo ni información de prueba.

---

## ✅ Requisitos Previos

- **Servidor web**: Apache (XAMPP, WAMP, LAMP)
- **PHP**: Versión 7.4 o superior
- **MySQL**: Versión 5.7 o superior
- **Extensiones PHP**: mysqli, mbstring
- **Espacio**: ~50MB mínimo

---

## 📋 ¿Qué incluye una instalación limpia?

### ✅ **Datos Maestros (Obligatorios)**

1. **4 Roles del sistema:**
   - Administrador Total
   - Administrador Edificio
   - Inquilino
   - Seguridad

2. **23 Permisos del sistema:**
   - 6 permisos de Sistema
   - 1 permiso de Reportes
   - 5 permisos de Finanzas
   - 2 permisos de Comunicación
   - 6 permisos de Operaciones
   - 1 permiso de Perfil
   - 1 permiso de Gestión (asignar_permisos)
   - 1 permiso de Acceso (acceso_completo)

3. **Asignaciones de permisos a roles:**
   - Administrador Total: 23 permisos
   - Administrador Edificio: 11 permisos
   - Inquilino: 6 permisos
   - Seguridad: 4 permisos

4. **1 Usuario Administrador Total:**
   - Usuario: `admin`
   - Contraseña: `admin123`
   - ⚠️ **Debe cambiarse inmediatamente después de instalar**

### ❌ **NO incluye (se crea desde la interfaz):**

- Edificios
- Usuarios adicionales
- Servicios
- Medidores
- Ciclos de facturación
- Gastos
- Recibos
- Pagos
- Incidencias
- Avisos

---

## 🚀 Método 1: Instalación Automática (Recomendado)

### Paso 1: Colocar archivos

1. Descarga/clona el proyecto en tu servidor web:
   ```
   C:\xampp\htdocs\proyectoEdificio\
   ```

### Paso 2: Ejecutar script de instalación

2. Abre tu navegador y accede a:
   ```
   http://localhost:8012/proyectoEdificio/install/instalar.php
   ```

3. El script automáticamente:
   - ✅ Crea la base de datos `edificios_db`
   - ✅ Crea las 15 tablas del sistema
   - ✅ Inserta los 4 roles
   - ✅ Inserta los 23 permisos
   - ✅ Asigna permisos a roles
   - ✅ Crea el usuario administrador con password hasheado

### Paso 3: Verificar instalación

4. Verás un mensaje de confirmación:
   ```
   ✅ Sistema listo para usar!
   
   Credenciales de acceso inicial:
   Usuario:     admin
   Contraseña:  admin123
   ```

### Paso 4: Acceder al sistema

5. Accede a:
   ```
   http://localhost:8012/proyectoEdificio/
   ```

6. Inicia sesión con las credenciales mostradas

7. **¡IMPORTANTE!** Cambia la contraseña inmediatamente:
   - Ve a tu perfil
   - Actualiza la contraseña
   - Usa una contraseña segura

---

## 🛠️ Método 2: Instalación Manual (Avanzado)

### Paso 1: Crear base de datos

1. Abre phpMyAdmin (`http://localhost:8012/phpmyadmin/`)

2. Crea una nueva base de datos:
   - Nombre: `edificios_db`
   - Cotejamiento: `utf8mb4_unicode_ci`

### Paso 2: Generar hash de contraseña

3. Ejecuta este código PHP para generar el hash:
   ```php
   <?php
   echo password_hash('admin123', PASSWORD_DEFAULT);
   ?>
   ```

4. Copia el hash generado

### Paso 3: Importar SQL

5. Abre el archivo:
   ```
   proyectoEdificio/install/instalacion_limpia.sql
   ```

6. Busca la línea 82:
   ```sql
   INSERT INTO usuarios ... password VARCHAR(255) NOT NULL '$2y$10$YourHashedPasswordHere' ...
   ```

7. Reemplaza `$2y$10$YourHashedPasswordHere` con el hash que generaste

8. Importa el archivo SQL completo en phpMyAdmin

### Paso 4: Verificar datos

9. Ejecuta estas queries para verificar:
   ```sql
   SELECT COUNT(*) FROM roles;           -- Debe ser 4
   SELECT COUNT(*) FROM permisos;        -- Debe ser 23
   SELECT COUNT(*) FROM rol_permisos;    -- Debe ser 44
   SELECT COUNT(*) FROM usuarios;        -- Debe ser 1
   ```

### Paso 5: Acceder

10. Accede al sistema y cambia la contraseña

---

## 📊 Estructura de Datos Instalados

### Roles (4)

| ID | Nombre | Permisos |
|----|--------|----------|
| 1 | Administrador Total | 23 permisos |
| 2 | Administrador Edificio | 11 permisos |
| 3 | Inquilino | 6 permisos |
| 4 | Seguridad | 4 permisos |

### Permisos por Categoría (23)

**Sistema (7):**
- gestionar_usuarios
- gestionar_edificios
- gestionar_inquilinos
- gestionar_roles
- asignar_permisos
- acceso_completo
- acceso_panel_admin

**Reportes (1):**
- ver_reportes

**Finanzas (5):**
- gestionar_gastos
- procesar_cierre_mensual
- pagar_servicios
- ver_mis_pagos
- registrar_pago

**Comunicación (2):**
- gestionar_avisos
- ver_avisos

**Operaciones (6):**
- gestionar_mantenimiento
- gestionar_seguridad_edificio
- reportar_incidencias
- registrar_visitas
- ver_residentes
- reportar_incidentes

**Perfil (1):**
- ver_perfil

### Tablas Creadas (15)

**Maestras:**
- roles
- permisos
- rol_permisos
- edificios
- usuarios
- usuario_edificios

**Operacionales:**
- servicios
- medidores
- lecturas_medidor
- ciclos_facturacion
- gastos_edificio
- recibos_inquilino
- pagos_inquilino
- incidencias
- avisos

---

## 🎯 Próximos Pasos Después de Instalar

### 1. Cambiar contraseña del admin
- Ve a tu perfil
- Actualiza la contraseña

### 2. Crear edificios
- Accede a **Gestión de Edificios**
- Crea los edificios que administrarás
- Completa toda la información (dirección, pisos, departamentos, etc.)

### 3. Crear Administradores de Edificio
- Accede a **Gestión de Usuarios**
- Crea usuarios con rol "Administrador Edificio"
- Asígnales los edificios correspondientes

### 4. Los Administradores de Edificio crean Inquilinos
- Cada admin de edificio crea sus inquilinos
- Asigna departamentos/unidades

### 5. Configurar servicios (opcional)
- Luz, agua, gas, internet, mantenimiento
- Configura medidores si aplica

### 6. Iniciar ciclos de facturación
- Procesa el primer cierre mensual
- Se generan recibos automáticamente

---

## ⚠️ Solución de Problemas

### Error: "Access denied for user"
**Solución:** Verifica las credenciales de MySQL en `config/database.php`

### Error: "Table already exists"
**Solución:** La base de datos ya existe. Elimínala primero si quieres reinstalar:
```sql
DROP DATABASE edificios_db;
```

### Error: "Cannot connect to MySQL"
**Solución:** 
- Verifica que XAMPP/MySQL esté corriendo
- Verifica el puerto (3306 por defecto)
- Revisa `config/database.php`

### No puedo iniciar sesión
**Solución:**
- Verifica que el usuario existe: `SELECT * FROM usuarios WHERE username = 'admin'`
- Regenera el hash de password
- Verifica que la tabla `usuarios` tenga el rol_id correcto (1 = Admin Total)

### Las páginas muestran "Acceso Denegado"
**Solución:**
- Verifica que los permisos estén asignados: `SELECT * FROM rol_permisos WHERE rol_id = 1`
- Debe haber 23 asignaciones para el Administrador Total

---

## 🔒 Seguridad Post-Instalación

### Obligatorio:
1. ✅ Cambiar contraseña del usuario `admin`
2. ✅ Eliminar o proteger la carpeta `/install/`
3. ✅ Configurar SSL/HTTPS en producción
4. ✅ Cambiar credenciales de base de datos por defecto

### Recomendado:
5. ✅ Configurar backups automáticos de la base de datos
6. ✅ Revisar permisos de archivos en el servidor
7. ✅ Activar logs de acceso y errores
8. ✅ Implementar límite de intentos de login

---

## 📝 Notas Importantes

- Esta es una instalación **LIMPIA** - sin datos de ejemplo
- Todos los edificios, usuarios adicionales, y configuraciones se hacen desde la interfaz web
- El sistema es **multi-edificio** - puedes gestionar varios edificios desde una instalación
- Los permisos son **dinámicos** - puedes modificarlos desde la interfaz en `admin/permisos.php`

---

## 📞 Soporte

Si encuentras problemas durante la instalación:

1. Revisa los logs de PHP (`php_error.log`)
2. Revisa los logs de MySQL
3. Verifica que todas las extensiones PHP estén activas
4. Consulta la documentación técnica en `/docs/`

---

**Versión:** 2.0  
**Última actualización:** Noviembre 20, 2025  
**Sistema:** Gestión de Edificios
