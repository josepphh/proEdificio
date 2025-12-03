# 📋 Actualización del Sistema de Instalación

## ✅ Tareas Completadas

### 1. ✅ Revisión de Estructura de Tablas

Se verificaron todas las tablas del sistema para asegurar que el script SQL tiene los campos correctos:

#### Tablas Validadas (15 tablas):

**Maestras:**
- ✅ `roles` - Correcta (con columna `activo`)
- ✅ `edificios` - Correcta (con `num_pisos`, `num_departamentos`, `telefono`, `email`, `activo`)
- ✅ `usuarios` - Correcta (SIN `edificio_id`, `fecha_ingreso`, `fecha_salida` - estos fueron removidos)
- ✅ `usuario_edificios` - Correcta (relación N:N con `fecha_asignacion`)
- ✅ `permisos` - Correcta (23 permisos del sistema)
- ✅ `rol_permisos` - Correcta (44 asignaciones iniciales)

**Operacionales:**
- ✅ `servicios` - Correcta
- ✅ `medidores` - Correcta
- ✅ `lecturas_medidor` - Correcta
- ✅ `ciclos_facturacion` - Correcta
- ✅ `gastos_edificio` - Correcta
- ✅ `recibos_inquilino` - Correcta
- ✅ `pagos_inquilino` - Correcta
- ✅ `incidencias` - Correcta
- ✅ `avisos` - Correcta

### 2. ✅ Script SQL de Instalación Limpia

**Archivo:** `install/instalacion_limpia.sql`

**Estado:** ✅ COMPLETO Y CORRECTO

**Contenido:**
- ✅ Crea 15 tablas con estructura correcta
- ✅ Inserta 4 roles base
- ✅ Inserta 23 permisos del sistema
- ✅ Inserta 44 asignaciones rol-permiso
- ✅ Crea 1 usuario Administrador Total (con placeholder para hash)
- ✅ Comentarios y documentación completa
- ✅ Todas las relaciones FK correctas
- ✅ Índices optimizados

### 3. ✅ Instalador Automático

**Archivo:** `install/instalar.php`

**Estado:** ✅ FUNCIONAL

**Características:**
- Genera automáticamente el hash de contraseña
- Ejecuta el script SQL completo
- Valida la instalación
- Muestra resumen con credenciales
- Interfaz visual atractiva

### 4. ✅ Scripts Antiguos Deprecados

Se agregaron avisos de deprecación a los scripts obsoletos:

#### `config/db_setup.php` ❌ DEPRECADO
**Razones:**
- ❌ Solo crea tabla usuarios (incompleta)
- ❌ No crea tabla permisos
- ❌ No crea tabla rol_permisos
- ❌ No crea usuario_edificios
- ❌ No crea las 9 tablas operacionales
- ❌ No inserta datos iniciales

**Acción:** Agregado mensaje de error con referencia a nuevo sistema

#### `config/setup_roles.php` ❌ DEPRECADO
**Razones:**
- ❌ No crea tabla permisos
- ❌ No crea tabla rol_permisos
- ❌ Falta columna `activo` en roles y edificios
- ❌ Falta columnas `telefono` y `email` en edificios
- ❌ Inserta edificios de ejemplo (no deseado)
- ❌ Usa ALTER TABLE (no es instalación limpia)

**Acción:** Agregado mensaje de error con referencia a nuevo sistema

#### Otros scripts deprecados:
- ❌ `scripts/add_soft_delete.php` - Innecesario (columna `activo` ya en SQL)
- ❌ `scripts/update_existing_users.php` - Innecesario (usuarios creados correctamente)

### 5. ✅ Documentación Actualizada

#### `README.md`
**Cambios:**
- ✅ Sección "Instalación Rápida" actualizada y priorizada
- ✅ Marcados scripts deprecados en estructura del proyecto
- ✅ Agregada carpeta `install/` a la estructura
- ✅ Eliminadas referencias obsoletas a scripts antiguos
- ✅ Referencias claras a nueva documentación

#### `install/INSTALACION.md` (Ya existía)
**Estado:** ✅ Completo
- Guía detallada de instalación automática y manual
- Requisitos del sistema
- Estructura de datos
- Troubleshooting
- Checklist de seguridad

#### `docs/SISTEMA_PERMISOS.md` (Ya existía)
**Estado:** ✅ Completo
- Documentación de 23 permisos
- Explicación de APIs
- Ejemplos de uso
- Queries de prueba

---

## 📊 Comparación: Scripts Antiguos vs Nuevo Sistema

### Scripts Antiguos (DEPRECADOS)

```
config/db_setup.php + config/setup_roles.php
├── ❌ Crea solo 3 tablas (usuarios, roles, edificios)
├── ❌ No crea tabla permisos
├── ❌ No crea tabla rol_permisos
├── ❌ No crea usuario_edificios (relación N:N)
├── ❌ No crea 9 tablas operacionales
├── ❌ Columnas faltantes (activo, telefono, email)
├── ❌ Inserta edificios de ejemplo
├── ❌ Usa ALTER TABLE (no es limpio)
└── ❌ Sin sistema de permisos dinámicos
```

### Nuevo Sistema (ACTUAL)

```
install/instalar.php + install/instalacion_limpia.sql
├── ✅ Crea 15 tablas completas
├── ✅ Sistema de permisos dinámicos (23 permisos)
├── ✅ Sistema de asignaciones (44 asignaciones)
├── ✅ Relación N:N usuarios-edificios
├── ✅ Todas las columnas correctas
├── ✅ Sin datos de ejemplo (base limpia)
├── ✅ Instalación limpia (sin ALTER TABLE)
├── ✅ Hash de contraseña automático
├── ✅ Validación de instalación
└── ✅ Documentación completa
```

---

## 🎯 Sistema de Instalación Actual

### Estructura de Archivos

```
install/
├── instalar.php              # ✅ Instalador automático (RECOMENDADO)
├── instalacion_limpia.sql    # ✅ Script SQL completo (manual)
└── INSTALACION.md            # 📖 Guía de instalación

docs/
└── SISTEMA_PERMISOS.md       # 📖 Documentación de permisos

config/
├── database.php              # ✅ Clase de conexión (usar)
├── db_setup.php              # ❌ DEPRECADO
└── setup_roles.php           # ❌ DEPRECADO

scripts/
├── verificar_sistema.php     # ✅ Verificar instalación (usar)
├── list_users.php            # ✅ Listar usuarios (usar)
├── add_soft_delete.php       # ❌ DEPRECADO
└── update_existing_users.php # ❌ DEPRECADO
```

---

## 🚀 Cómo Instalar (Proceso Actual)

### Opción 1: Instalación Automática (Recomendada)

1. Acceder a: `http://localhost:8012/proyectoEdificio/install/instalar.php`
2. El sistema automáticamente:
   - Genera hash de contraseña
   - Crea base de datos
   - Crea 15 tablas
   - Inserta roles y permisos
   - Crea usuario admin
3. Acceder con: `admin` / `admin123`
4. Cambiar contraseña inmediatamente

### Opción 2: Instalación Manual

1. Abrir phpMyAdmin
2. Crear base de datos: `edificios_db`
3. Importar: `install/instalacion_limpia.sql`
4. **IMPORTANTE:** Actualizar línea 82 del SQL con hash de contraseña:
   ```php
   // Generar hash
   echo password_hash('admin123', PASSWORD_DEFAULT);
   ```
5. Reemplazar `$2y$10$YourHashedPasswordHere` con el hash generado
6. Acceder al sistema

---

## 🔒 Seguridad

### Contraseñas
- ✅ Usuario admin creado con contraseña hasheada (`password_hash`)
- ⚠️ Contraseña inicial: `admin123` (CAMBIAR INMEDIATAMENTE)
- ✅ Algoritmo: `PASSWORD_DEFAULT` (bcrypt)

### Base de Datos
- ✅ Base limpia (sin datos de ejemplo)
- ✅ Relaciones FK con `ON DELETE CASCADE`
- ✅ Índices optimizados
- ✅ Charset: `utf8mb4` (soporte completo Unicode)

### Permisos
- ✅ 23 permisos granulares
- ✅ Sistema dinámico (modificable desde interfaz)
- ✅ 4 roles predefinidos con asignaciones correctas

---

## ✅ Verificación Post-Instalación

### 1. Verificar Tablas
```sql
SHOW TABLES;
-- Debe mostrar 15 tablas
```

### 2. Verificar Permisos
```sql
SELECT COUNT(*) FROM permisos;
-- Debe retornar: 23

SELECT COUNT(*) FROM rol_permisos;
-- Debe retornar: 44
```

### 3. Verificar Usuario Admin
```sql
SELECT username, rol_id FROM usuarios WHERE username = 'admin';
-- Debe retornar: admin | 1
```

### 4. Verificar Sistema
```bash
php scripts/verificar_sistema.php
```

---

## 📝 Notas Importantes

1. **Scripts Deprecados**: Los scripts en `config/` están marcados como deprecados y mostrarán un error si se ejecutan. Esto es intencional para evitar uso accidental.

2. **Base Limpia**: El nuevo sistema crea una base de datos limpia SIN datos de ejemplo. Los edificios y usuarios se crean desde la interfaz web.

3. **Relación N:N**: Se usa `usuario_edificios` para asignar múltiples edificios a administradores, NO el campo `edificio_id` en usuarios (ese campo fue removido).

4. **Sistema de Permisos**: Los 23 permisos son dinámicos y pueden modificarse desde `admin/permisos.php` sin tocar código.

5. **Hash de Contraseña**: El instalador automático genera el hash. Si usas instalación manual, debes generar el hash manualmente.

---

## 🎉 Resultado Final

✅ Sistema de instalación completo y funcional
✅ Scripts antiguos deprecados con avisos claros
✅ Documentación actualizada y completa
✅ Base de datos con estructura correcta validada
✅ 15 tablas creadas correctamente
✅ 23 permisos del sistema configurados
✅ 4 roles con asignaciones correctas
✅ Usuario admin inicial configurado
✅ Proceso de instalación simplificado

**El sistema está listo para instalaciones limpias en nuevos entornos.**
