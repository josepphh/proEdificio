# 🔐 Sistema de Permisos Dinámicos - Documentación Técnica

## 📋 Descripción General

El sistema implementa un **modelo de permisos dinámico basado en base de datos** que permite gestionar de forma granular qué acciones puede realizar cada rol sin necesidad de modificar código.

---

## 🎯 Características Principales

✅ **Dinámico**: Los permisos se leen desde la base de datos  
✅ **Granular**: Control fino sobre cada funcionalidad  
✅ **Modificable**: Se gestionan desde la interfaz web (`admin/permisos.php`)  
✅ **Categorizado**: Permisos organizados por categorías (Sistema, Finanzas, Reportes, etc.)  
✅ **Escalable**: Fácil agregar nuevos permisos sin tocar código  
✅ **Seguro**: Verificación en cada página y API  
✅ **Con Cache**: Los permisos se cachean en sesión para performance  

---

## 📊 Estructura de Datos

### Tabla: `permisos`

```sql
CREATE TABLE permisos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(50) NOT NULL UNIQUE,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    categoria VARCHAR(50),
    activo TINYINT(1) DEFAULT 1,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

### Tabla: `rol_permisos`

```sql
CREATE TABLE rol_permisos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    rol_id INT NOT NULL,
    permiso_id INT NOT NULL,
    fecha_asignacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (rol_id) REFERENCES roles(id) ON DELETE CASCADE,
    FOREIGN KEY (permiso_id) REFERENCES permisos(id) ON DELETE CASCADE,
    UNIQUE KEY unique_rol_permiso (rol_id, permiso_id)
);
```

---

## 📝 Lista de Permisos (23 + 1)

### **Sistema (7 permisos)**

| Código | Nombre | Descripción |
|--------|--------|-------------|
| `gestionar_usuarios` | Gestionar Usuarios | Crear, editar y desactivar usuarios del sistema |
| `gestionar_edificios` | Gestionar Edificios | Administrar edificios y sus propiedades |
| `gestionar_inquilinos` | Gestionar Inquilinos | Administrar inquilinos por edificio |
| `gestionar_roles` | Gestionar Roles | Administrar roles del sistema |
| `asignar_permisos` | Asignar Permisos a Roles | **CRÍTICO** - Gestionar permisos desde la interfaz |
| `acceso_completo` | Acceso Completo | Flag especial para bypass visual en Panel Admin |
| `acceso_panel_admin` | Acceso Panel Admin | Acceder al panel de administración |

### **Reportes (1 permiso)**

| Código | Nombre | Descripción |
|--------|--------|-------------|
| `ver_reportes` | Ver Reportes | Ver reportes y estadísticas (alcance automático según edificios asignados) |

### **Finanzas (5 permisos)**

| Código | Nombre | Descripción |
|--------|--------|-------------|
| `gestionar_gastos` | Gestionar Gastos | Registrar y administrar gastos de edificios |
| `procesar_cierre_mensual` | Procesar Cierre Mensual | Procesar cierre de facturación mensual |
| `pagar_servicios` | Pagar Servicios | Realizar pagos de servicios |
| `ver_mis_pagos` | Ver Mis Pagos | Ver historial de pagos propios |
| `registrar_pago` | Registrar Pago | Registrar un nuevo pago |

### **Comunicación (2 permisos)**

| Código | Nombre | Descripción |
|--------|--------|-------------|
| `gestionar_avisos` | Gestionar Avisos | Crear y administrar avisos para usuarios |
| `ver_avisos` | Ver Avisos | Ver avisos del sistema |

### **Operaciones (6 permisos)**

| Código | Nombre | Descripción |
|--------|--------|-------------|
| `gestionar_mantenimiento` | Gestionar Mantenimiento | Administrar mantenimiento del edificio |
| `gestionar_seguridad_edificio` | Gestionar Seguridad | Administrar seguridad del edificio |
| `reportar_incidencias` | Reportar Incidencias | Reportar problemas o incidentes |
| `registrar_visitas` | Registrar Visitas | Registrar visitantes al edificio |
| `ver_residentes` | Ver Residentes | Ver listado de residentes |
| `reportar_incidentes` | Reportar Incidentes | Reportar incidentes de seguridad |

### **Perfil (1 permiso)**

| Código | Nombre | Descripción |
|--------|--------|-------------|
| `ver_perfil` | Ver Perfil | Ver y editar perfil personal |

---

## 👥 Asignaciones por Rol

### 👑 Administrador Total (23 permisos)
**Tiene TODOS los permisos del sistema**

### 👨‍💼 Administrador Edificio (11 permisos)
- gestionar_usuarios
- gestionar_edificios
- gestionar_inquilinos
- ver_reportes
- gestionar_mantenimiento
- gestionar_seguridad_edificio
- gestionar_gastos
- procesar_cierre_mensual
- gestionar_avisos
- ver_avisos
- acceso_panel_admin

### 🏠 Inquilino (6 permisos)
- ver_perfil
- reportar_incidencias
- ver_avisos
- pagar_servicios
- ver_mis_pagos
- registrar_pago

### 🛡️ Seguridad (4 permisos)
- registrar_visitas
- ver_residentes
- reportar_incidentes
- ver_avisos

---

## 🔧 API de Permisos (includes/permissions.php)

### Funciones Principales

#### 1. `obtenerPermisosRol($rol_id)`
Obtiene todos los permisos de un rol desde la base de datos.

```php
function obtenerPermisosRol($rol_id) {
    // Lee de BD y cachea en $_SESSION['permisos_rol_' . $rol_id]
    // Retorna array de códigos de permisos: ['gestionar_usuarios', 'ver_reportes', ...]
}
```

#### 2. `tienePermiso($permiso, $bypass_admin_total = false)`
Verifica si el usuario actual tiene un permiso específico.

```php
// Verificación estricta (por defecto)
if (tienePermiso('gestionar_usuarios')) {
    // Usuario tiene el permiso
}

// Con bypass para Administrador Total (solo para display en Panel)
if (tienePermiso('gestionar_usuarios', true)) {
    // Muestra opción en Panel Admin
}
```

**Parámetros:**
- `$permiso` (string): Código del permiso a verificar
- `$bypass_admin_total` (bool): Si es `true` y el usuario tiene `acceso_completo`, retorna `true` automáticamente

**Retorna:** `bool` - true si tiene permiso, false si no

#### 3. `requierePermiso($permiso, $redirect = null)`
Verifica permiso y redirige a acceso denegado si no lo tiene.

```php
// Al inicio de cada página protegida
requierePermiso('gestionar_usuarios');

// Con redirect personalizado
requierePermiso('gestionar_usuarios', '../index.php');
```

**Parámetros:**
- `$permiso` (string): Código del permiso requerido
- `$redirect` (string|null): URL de redirect en caso de acceso denegado

**Comportamiento:**
- Si tiene permiso: continúa ejecución
- Si NO tiene permiso: muestra página de acceso denegado y termina script

---

## 🖥️ Interfaz de Gestión

### admin/permisos.php

Interfaz visual para gestionar permisos por rol.

**Características:**
- ✅ Selector de rol con información
- ✅ Grid categorizado de permisos
- ✅ Checkboxes para activar/desactivar
- ✅ Contador en tiempo real
- ✅ Guardado con transacciones
- ✅ Limpieza automática de caché

**Acceso:**
- Requiere permiso: `asignar_permisos`
- Solo Administrador Total por defecto

**Flujo:**
1. Usuario selecciona un rol
2. Sistema carga permisos actuales del rol
3. Usuario marca/desmarca checkboxes
4. Contador muestra total de permisos
5. Usuario guarda cambios
6. Sistema actualiza BD con transacción
7. Cache de sesión se limpia automáticamente

---

## 🔄 APIs de Permisos

### GET api/get_permisos_rol.php

Obtiene los permisos asignados a un rol.

**Endpoint:** `/api/get_permisos_rol.php?rol_id={id}`

**Respuesta:**
```json
{
    "success": true,
    "rol": {
        "id": 2,
        "nombre": "Administrador Edificio",
        "descripcion": "..."
    },
    "permisos": [
        {
            "id": 1,
            "codigo": "gestionar_usuarios",
            "nombre": "Gestionar Usuarios",
            "descripcion": "...",
            "categoria": "Sistema"
        },
        ...
    ]
}
```

### POST api/actualizar_permisos_rol.php

Actualiza los permisos de un rol.

**Endpoint:** `/api/actualizar_permisos_rol.php`

**Body:**
```json
{
    "rol_id": 2,
    "permisos": [1, 2, 3, 5, 8, 10, 12]
}
```

**Respuesta:**
```json
{
    "success": true,
    "message": "Permisos actualizados correctamente",
    "total": 7
}
```

**Características:**
- ✅ Usa transacciones (rollback en caso de error)
- ✅ Elimina asignaciones antiguas
- ✅ Inserta nuevas asignaciones
- ✅ Limpia cache de sesión del rol
- ✅ Validación de permisos del usuario

---

## 🎨 Uso en Código

### Proteger una Página

```php
<?php
require_once '../includes/session.php';
require_once '../includes/permissions.php';

// Verificar autenticación
requiereAutenticacion('../login.php');

// Verificar permiso específico
requierePermiso('gestionar_usuarios');

// Resto del código de la página
?>
```

### Mostrar/Ocultar Elementos

```php
<?php if (tienePermiso('gestionar_usuarios')): ?>
    <a href="usuarios.php">Gestión de Usuarios</a>
<?php endif; ?>
```

### Con Bypass para Panel Admin

```php
<!-- En admin/panel.php - Mostrar todas las opciones al Admin Total -->
<?php if (tienePermiso('gestionar_usuarios', true)): ?>
    <div class="card-bordered">
        <h3>Gestión de Usuarios</h3>
        <a href="usuarios.php">Ver Usuarios</a>
    </div>
<?php endif; ?>
```

### Verificar Múltiples Permisos

```php
// Verificar si tiene AL MENOS UNO de varios permisos
if (tienePermiso('gestionar_gastos') || tienePermiso('procesar_cierre_mensual')) {
    // Mostrar sección financiera
}

// Verificar si tiene TODOS los permisos
if (tienePermiso('gestionar_usuarios') && tienePermiso('gestionar_roles')) {
    // Mostrar configuración avanzada
}
```

---

## ⚡ Sistema de Cache

Los permisos se cachean en sesión para evitar consultas repetidas a la base de datos.

**Patrón de cache:**
```php
$_SESSION['permisos_rol_' . $rol_id] = ['permiso1', 'permiso2', ...];
```

**Limpieza de cache:**
- Automática al actualizar permisos de un rol
- Manual: `unset($_SESSION['permisos_rol_' . $rol_id]);`
- Al cerrar sesión

---

## 🔐 Permisos Especiales

### `acceso_completo`

Permiso tipo FLAG que activa el bypass condicional.

**Comportamiento:**
- Solo funciona cuando `tienePermiso($permiso, true)` se llama con el segundo parámetro en `true`
- Permite al Administrador Total **VER** todas las opciones en el Panel Admin
- NO da acceso automático a las páginas (cada página verifica permisos individualmente)

**Uso:**
```php
// En admin/panel.php - Bypass ACTIVADO
if (tienePermiso('gestionar_usuarios', true)) {
    // Admin Total ve esta tarjeta aunque no tenga el permiso específico
}

// En admin/usuarios.php - Bypass DESACTIVADO (verificación estricta)
requierePermiso('gestionar_usuarios'); // Admin Total DEBE tener este permiso
```

### `asignar_permisos`

**CRÍTICO** - Sin este permiso no se puede acceder a `admin/permisos.php`

Por defecto solo lo tiene Administrador Total.

---

## 📈 Agregar Nuevos Permisos

### Paso 1: Insertar en BD

```sql
INSERT INTO permisos (codigo, nombre, descripcion, categoria) 
VALUES ('nuevo_permiso', 'Nombre del Permiso', 'Descripción', 'Categoría');
```

### Paso 2: Asignar a Roles

```sql
INSERT INTO rol_permisos (rol_id, permiso_id) 
VALUES (1, LAST_INSERT_ID()); -- Asignar al Admin Total
```

### Paso 3: Usar en Código

```php
requierePermiso('nuevo_permiso');
```

¡Listo! No se requiere modificar ningún archivo PHP.

---

## 🧪 Testing de Permisos

### Verificar Permisos de un Rol

```sql
SELECT p.codigo, p.nombre, p.categoria
FROM permisos p
INNER JOIN rol_permisos rp ON p.id = rp.permiso_id
WHERE rp.rol_id = 1 AND p.activo = 1
ORDER BY p.categoria, p.nombre;
```

### Verificar Roles con un Permiso Específico

```sql
SELECT r.nombre, r.descripcion
FROM roles r
INNER JOIN rol_permisos rp ON r.id = rp.rol_id
INNER JOIN permisos p ON rp.permiso_id = p.id
WHERE p.codigo = 'gestionar_usuarios' AND r.activo = 1;
```

### Contar Permisos por Rol

```sql
SELECT r.nombre, COUNT(rp.permiso_id) as total_permisos
FROM roles r
LEFT JOIN rol_permisos rp ON r.id = rp.rol_id
WHERE r.activo = 1
GROUP BY r.id, r.nombre;
```

---

## 🚨 Troubleshooting

### "Acceso Denegado" en todas las páginas

**Causa:** Permisos no asignados al rol del usuario

**Solución:**
```sql
-- Verificar permisos del rol
SELECT * FROM rol_permisos WHERE rol_id = {tu_rol_id};

-- Si está vacío, asignar permisos desde admin/permisos.php
-- O manualmente con SQL
```

### Cambios en permisos no se reflejan

**Causa:** Cache de sesión

**Solución:**
1. Cierra sesión y vuelve a entrar
2. O limpia el cache manualmente:
```php
unset($_SESSION['permisos_rol_' . $rol_id]);
```

### Panel Admin no muestra opciones al Admin Total

**Causa:** Falta permiso `acceso_completo` o no se usa bypass

**Solución:**
1. Verificar que el Admin Total tenga `acceso_completo`:
```sql
SELECT * FROM permisos WHERE codigo = 'acceso_completo';
SELECT * FROM rol_permisos WHERE rol_id = 1 AND permiso_id = {id_acceso_completo};
```

2. Verificar que las tarjetas usen `tienePermiso($permiso, true)`

---

**Versión:** 2.0  
**Última actualización:** Noviembre 20, 2025  
**Sistema:** Gestión de Edificios
