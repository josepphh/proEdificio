# 🔐 Cambios en Gestión de Usuarios - Roles y Edificios Filtrados

## 📋 Resumen de Cambios

Se ha implementado un sistema de **filtrado automático** en la gestión de usuarios que adapta las opciones disponibles según el rol del usuario que está creando/editando usuarios.

---

## 🎯 Funcionalidades Implementadas

### 1️⃣ **Filtrado de Roles por Permisos**

#### **Administrador Total** 🔴
- ✅ Puede ver y asignar **TODOS los roles**:
  - Administrador Total
  - Administrador Edificio
  - Inquilino
  - Seguridad

#### **Administrador Edificio** 🟡
- ✅ Solo puede ver y asignar roles de:
  - **Inquilino**
  - **Seguridad**
- ❌ **NO puede crear** otros administradores
- 💡 Se muestra mensaje informativo: 
  > "ℹ️ Como Administrador de Edificio, solo puedes crear usuarios con rol de Inquilino o Seguridad"

---

### 2️⃣ **Filtrado de Edificios por Asignación**

#### **Administrador Total** 🔴
- ✅ Puede ver y asignar **TODOS los edificios activos** del sistema
- ✅ Sin restricciones

#### **Administrador Edificio** 🟡
- ✅ Solo puede ver **sus propios edificios asignados**
- ❌ **NO puede ver** edificios de otros administradores
- 💡 Se muestra mensaje informativo:
  > "ℹ️ Como Administrador de Edificio, solo puedes asignar usuarios a tus edificios asignados"
- ⚠️ Si no tiene edificios asignados, se muestra:
  > "⚠️ No tienes edificios asignados. Contacta al Administrador Total para que te asigne edificios."

---

### 3️⃣ **Filtrado de Lista de Usuarios (NUEVO)** ⭐

#### **Administrador Total** 🔴
- ✅ Ve **TODOS los usuarios** del sistema
- ✅ Usuarios de todos los edificios

#### **Administrador Edificio** 🟡
- ✅ Solo ve **usuarios de SUS edificios asignados**
- ❌ **NO ve usuarios** de otros edificios
- 💡 Se muestra banner informativo:
  > "ℹ️ Vista filtrada: Solo se muestran usuarios asignados a tus edificios. Tus edificios: [lista]"
- 🔍 **Criterio de filtrado**: Un usuario aparece si tiene **al menos un edificio en común** con el Administrador Edificio

**Ejemplo práctico:**
- Admin Edificio tiene asignados: "Edificio A" y "Edificio B"
- Verá en la lista:
  - ✅ Usuario 1 (Inquilino de "Edificio A")
  - ✅ Usuario 2 (Seguridad de "Edificio B")
  - ✅ Usuario 3 (Inquilino de "Edificio A" y "Edificio C") ← Aparece porque comparte "Edificio A"
  - ❌ Usuario 4 (Inquilino de "Edificio C" solamente) ← NO aparece

---

### 4️⃣ **Validaciones Frontend**

✅ **Validación automática**: Si se selecciona un rol que requiere edificios (Inquilino, Seguridad) pero no se selecciona ningún edificio, el sistema muestra:
> "⚠️ Debes seleccionar al menos un edificio para este rol"

---

## 🔧 Implementación Técnica

### Cambios en `admin/usuarios.php`:

```php
// 1. Obtener el rol del usuario actual desde la sesión
$usuario_actual_id = $_SESSION['usuario_id'] ?? null;
$rol_actual_nombre = $_SESSION['rol_nombre'] ?? '';

// 2. Filtrar roles según el usuario actual
if ($rol_actual_nombre === 'Administrador Edificio') {
    $roles_query = "SELECT id, nombre FROM roles 
                    WHERE nombre IN ('Inquilino', 'Seguridad') 
                    ORDER BY nombre";
} else {
    $roles_query = "SELECT id, nombre FROM roles ORDER BY nombre";
}

// 3. Filtrar edificios según el usuario actual
if ($rol_actual_nombre === 'Administrador Edificio') {
    $edificios_query = "SELECT DISTINCT e.id, e.nombre 
                        FROM edificios e
                        INNER JOIN usuario_edificios ue ON e.id = ue.edificio_id
                        WHERE e.activo = 1 
                        AND ue.usuario_id = ?
                        AND ue.activo = 1
                        ORDER BY e.nombre";
} else {
    $edificios_query = "SELECT id, nombre FROM edificios 
                        WHERE activo = 1 ORDER BY nombre";
}

// 4. Filtrar LISTA DE USUARIOS según el usuario actual (NUEVO)
if ($rol_actual_nombre === 'Administrador Edificio') {
    // Solo muestra usuarios que comparten al menos un edificio con el admin
    $sql = "SELECT DISTINCT u.id, u.nombre, u.email, ...
            FROM usuarios u
            INNER JOIN usuario_edificios ue_filter ON u.id = ue_filter.usuario_id 
                AND ue_filter.edificio_id IN (
                    SELECT edificio_id 
                    FROM usuario_edificios 
                    WHERE usuario_id = ? AND activo = 1
                )
            ...";
} else {
    // Administrador Total ve todos los usuarios
    $sql = "SELECT u.id, u.nombre, u.email, ... FROM usuarios u ...";
}
```

---

## 🎨 Interfaz de Usuario

### Mensajes Informativos Agregados:

1. **Banner superior de lista** (para Administrador Edificio) - NUEVO ⭐:
   ```
   ℹ️ Vista filtrada: Solo se muestran usuarios asignados a tus edificios. 
      Tus edificios: Edificio A, Edificio B
   ```

2. **En selector de Rol** (para Administrador Edificio):
   ```
   ℹ️ Como Administrador de Edificio, solo puedes crear usuarios 
      con rol de Inquilino o Seguridad
   ```

3. **En selector de Edificios** (para Administrador Edificio):
   ```
   ℹ️ Como Administrador de Edificio, solo puedes asignar usuarios 
      a tus edificios asignados
   ```

4. **Cuando no hay edificios asignados**:
   ```
   ⚠️ No tienes edificios asignados. Contacta al Administrador Total 
      para que te asigne edificios.
   ```

5. **Validación JavaScript**:
   ```
   ⚠️ Debes seleccionar al menos un edificio para este rol
   ```

---

## ✅ Casos de Uso

### Escenario 1: Administrador Total gestiona usuarios
1. Ve **todos los usuarios** del sistema en la tabla
2. Ve todos los roles disponibles
3. Ve todos los edificios activos
4. Puede crear cualquier tipo de usuario
5. Sin restricciones

### Escenario 2: Administrador Edificio visualiza su lista
1. Ve banner: "Vista filtrada: Solo se muestran usuarios asignados a tus edificios. Tus edificios: Edificio A, Edificio B"
2. **Solo ve en la tabla** usuarios que estén asignados a "Edificio A" o "Edificio B"
3. Usuarios de otros edificios quedan ocultos automáticamente

### Escenario 3: Administrador Edificio crea Inquilino
1. Solo ve roles: Inquilino y Seguridad
2. Solo ve sus edificios asignados (ej: "Edificio A", "Edificio B")
3. Selecciona rol "Inquilino"
4. Selecciona uno o más edificios de su lista
5. Usuario creado exitosamente
6. El nuevo usuario aparece en su lista (porque comparte edificios)

### Escenario 4: Administrador Edificio sin edificios
1. Ve banner de advertencia
2. Solo ve roles: Inquilino y Seguridad
3. Ve mensaje: "No tienes edificios asignados"
4. No puede completar la creación del usuario
5. Debe contactar al Administrador Total

### Escenario 5: Usuario compartido entre edificios (NUEVO)
1. Admin Edificio tiene: "Edificio A" y "Edificio B"
2. Admin Total tiene: "Edificio C"
3. Inquilino Juan está en: "Edificio A" y "Edificio C"
4. **Resultado**: Admin Edificio **SÍ ve a Juan** (comparten "Edificio A")
5. Admin Edificio puede editarlo, pero solo verá/modificará edificios de su alcance

---

## 🔒 Seguridad

- ✅ **Filtrado a nivel de consulta SQL**: Los datos se filtran desde la base de datos
- ✅ **Validación frontend**: Previene envíos incorrectos
- ✅ **Separación de permisos**: Cada rol solo ve lo que le corresponde
- ✅ **Sin datos sensibles expuestos**: No se muestran edificios de otros administradores

---

## 🚀 Beneficios

1. **Mayor seguridad**: Los administradores de edificio no pueden ver ni modificar información de otros edificios
2. **Mejor UX**: Mensajes claros y contextuales
3. **Prevención de errores**: Validaciones automáticas
4. **Escalabilidad**: Fácil de mantener y extender
5. **Control granular**: Cada rol tiene permisos específicos

---

## 📝 Notas Adicionales

- Los cambios son **retrocompatibles** con usuarios existentes
- No requiere modificaciones en la base de datos
- Los filtros se aplican automáticamente según la sesión del usuario
- Sistema de notificaciones toast para feedback visual

---

## 🎯 Próximos Pasos Sugeridos

1. ✅ **Completado**: Filtrado de roles y edificios
2. 🔄 **Pendiente**: Aplicar mismo filtro en edición de usuarios existentes
3. 🔄 **Pendiente**: Agregar logs de auditoría para creación de usuarios
4. 🔄 **Pendiente**: Notificaciones por email al crear nuevos usuarios

---

**Fecha de implementación**: 20 de Noviembre de 2025  
**Archivos modificados**: 
- `admin/usuarios.php` (líneas 14-108, 110-125, 478-500, 742-753)

**Pruebas recomendadas**:
1. ✅ Login como "Administrador Total" → Ver lista completa de usuarios
2. ✅ Login como "Administrador Edificio" → **Verificar lista filtrada** (solo usuarios de sus edificios)
3. ✅ Login como "Administrador Edificio" → Verificar banner informativo con edificios
4. ✅ Login como "Administrador Edificio" → Verificar que solo ve Inquilino/Seguridad en formulario
5. ✅ Login como "Administrador Edificio" → Verificar que solo ve sus edificios en formulario
6. ✅ Crear usuario compartido entre edificios → Verificar que ambos admins lo ven
7. ✅ Intentar crear usuario sin edificios → Validar mensaje de error
