# Sistema de Gestión de Roles y Permisos Dinámico

## 📋 Resumen

Se ha implementado un **sistema completo de gestión de roles y permisos** que permite al Administrador Total configurar dinámicamente qué permisos tiene cada rol en el sistema, sin necesidad de modificar código.

---

## 🎯 Características Principales

### 1. **Base de Datos Dinámica**
- **Tabla `permisos`**: Almacena todos los permisos disponibles en el sistema
  - Campos: id, codigo, nombre, descripcion, categoria, activo
  - 14 permisos base organizados en 7 categorías

- **Tabla `rol_permisos`**: Relación muchos-a-muchos entre roles y permisos
  - Permite asignar múltiples permisos a cada rol
  - Con claves foráneas y restricciones de unicidad

### 2. **Interfaz Visual de Gestión**
Ubicación: `/admin/permisos.php`

**Funcionalidades:**
- ✅ Selector de rol con información descriptiva
- ✅ Grid categorizado de permisos con checkboxes
- ✅ Contador en tiempo real de permisos asignados
- ✅ Confirmaciones para cambios críticos
- ✅ Indicador visual de cambios sin guardar
- ✅ Sistema de notificaciones toast

**Diseño:**
- Cards responsivos con efectos hover
- Código del permiso visible (estilo monospace)
- Colores diferenciados por estado
- Advertencia antes de salir con cambios sin guardar

### 3. **Sistema de Permisos Optimizado**
Actualizado en `/includes/permissions.php`

**Mejoras:**
- ✅ Lee permisos desde la base de datos
- ✅ Caché en sesión para mejor rendimiento
- ✅ Soporte para permiso especial `acceso_completo`
- ✅ Compatibilidad total con el código existente

### 4. **APIs RESTful**

#### `GET /api/get_permisos_rol.php?rol_id=X`
Obtiene los permisos de un rol específico
```json
{
  "success": true,
  "rol": {
    "id": 1,
    "nombre": "Administrador Total",
    "descripcion": "..."
  },
  "permisos": [...]
}
```

#### `POST /api/actualizar_permisos_rol.php`
Actualiza los permisos de un rol
```json
{
  "rol_id": 1,
  "permisos": [1, 2, 3, ...]
}
```

**Seguridad:**
- Verificación del permiso `asignar_permisos`
- Transacciones SQL para integridad de datos
- Logging de cambios realizados

---

## 📊 Permisos Disponibles

### Categoría: Sistema
- **acceso_completo**: Acceso total sin restricciones
- **acceso_panel_admin**: Acceso al panel de administración

### Categoría: Usuarios
- **gestionar_usuarios**: Crear, editar y eliminar usuarios
- **ver_usuarios**: Ver listado de usuarios

### Categoría: Edificios
- **gestionar_edificios**: Crear, editar y eliminar edificios
- **ver_edificios**: Ver listado de edificios

### Categoría: Roles
- **gestionar_roles**: Crear, editar y eliminar roles
- **asignar_permisos**: Asignar y remover permisos a roles ⭐

### Categoría: Pagos
- **gestionar_pagos**: Gestionar pagos de usuarios
- **ver_pagos**: Ver historial de pagos propios

### Categoría: Reportes
- **crear_reportes**: Crear reportes y solicitudes
- **gestionar_reportes**: Ver y gestionar todos los reportes

### Categoría: Avisos
- **gestionar_avisos**: Crear, editar y eliminar avisos
- **ver_avisos**: Ver avisos del sistema

---

## 🔧 Migración de Permisos

El script `/scripts/crear_sistema_permisos.php` realiza:

1. ✅ Crea las tablas `permisos` y `rol_permisos`
2. ✅ Inserta los 14 permisos base categorizados
3. ✅ Migra los permisos actuales de cada rol
4. ✅ Mantiene compatibilidad con el sistema anterior

**Ejecución:**
```bash
php scripts/crear_sistema_permisos.php
```

**Configuración migrada:**
- Administrador Total: 11 permisos
- Administrador Edificio: 10 permisos
- Inquilino: 5 permisos
- Seguridad: 3 permisos

---

## 🎨 Interfaz de Usuario

### Acceso
**Panel Admin** → **Permisos por Rol** 🔐

### Flujo de Trabajo
1. Seleccionar un rol del dropdown
2. Ver información del rol y contador actual
3. Marcar/desmarcar permisos organizados por categoría
4. Guardar cambios
5. Confirmación con toast de éxito

### Validaciones
- ⚠️ Advertencia al quitar todos los permisos
- ⚠️ Alerta antes de salir con cambios sin guardar
- ✅ Confirmación visual de guardado exitoso

---

## 🔐 Seguridad

### Control de Acceso
- Solo usuarios con permiso `asignar_permisos` pueden acceder
- Por defecto, solo Administrador Total tiene este permiso
- Redirección a página de acceso denegado si no autorizado

### Integridad de Datos
- Transacciones SQL para operaciones atómicas
- Validación de IDs de roles y permisos
- Relaciones con claves foráneas y CASCADE

### Auditoría
- Logging automático en tabla `logs_sistema`
- Registro del usuario que realiza cambios
- Timestamp y detalles de la operación

---

## 📈 Ventajas del Sistema

### Para Administradores
✅ **Flexibilidad Total**: Cambiar permisos sin modificar código  
✅ **Gestión Visual**: Interfaz intuitiva y organizada  
✅ **Control Granular**: Asignar permisos específicos según necesidad  
✅ **Auditoría Completa**: Registro de todos los cambios

### Para Desarrolladores
✅ **Mantenibilidad**: Lógica centralizada en BD  
✅ **Escalabilidad**: Fácil agregar nuevos permisos  
✅ **Performance**: Caché en sesión optimiza consultas  
✅ **Compatibilidad**: API funciona con código existente

### Para el Sistema
✅ **Seguridad**: Control preciso de acceso  
✅ **Consistencia**: Permisos centralizados  
✅ **Trazabilidad**: Log de cambios  
✅ **Robustez**: Transacciones y validaciones

---

## 🚀 Próximos Pasos Posibles

### Mejoras Futuras
1. **Gestión de Permisos**: Interfaz para crear/editar permisos (no solo asignarlos)
2. **Permisos Temporales**: Asignar permisos con fecha de expiración
3. **Permisos por Usuario**: Override de permisos a nivel individual
4. **Dashboard de Auditoría**: Visualizar histórico de cambios de permisos
5. **Exportar/Importar**: Configuraciones de permisos entre entornos
6. **Plantillas de Roles**: Copiar permisos de un rol a otro

---

## 📝 Notas Técnicas

### Caché de Sesión
Los permisos se cachean en `$_SESSION['permisos_rol_X']` para evitar consultas repetitivas a la BD. La caché se limpia automáticamente al cerrar sesión.

### Permiso Especial: `acceso_completo`
Un rol con este permiso tiene acceso a TODAS las funcionalidades, sin necesidad de verificar cada permiso individual.

### Compatibilidad
El nuevo sistema es 100% compatible con el código existente. Todas las llamadas a `tienePermiso()` y `requierePermiso()` funcionan sin modificaciones.

---

## 🎓 Documentación Relacionada

- **Tabla de Roles**: `/admin/roles.php` - Gestión de roles (activar/desactivar)
- **Gestión de Usuarios**: `/admin/usuarios.php` - Asignar roles a usuarios
- **Panel Admin**: `/admin/panel.php` - Acceso centralizado

---

**Fecha de Implementación**: Noviembre 2025  
**Versión**: 1.0  
**Estado**: ✅ Implementado y Funcional
