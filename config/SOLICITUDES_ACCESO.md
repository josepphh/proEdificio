# 📬 Sistema de Solicitudes de Acceso

## 🎯 Descripción

Sistema que permite a potenciales administradores de edificios solicitar acceso a la plataforma desde la página de inicio pública.

## ✨ Características

### Para Visitantes (Sin sesión):
- ✅ Botón "Solicitar Acceso" en la página de inicio
- ✅ Formulario modal con datos del edificio y contacto
- ✅ Validación de datos antes de enviar
- ✅ Confirmación visual de envío exitoso

### Para Administradores Totales:
- ✅ Página dedicada para ver todas las solicitudes
- ✅ Dashboard con contadores (Pendientes, Aprobadas, Rechazadas)
- ✅ Vista detallada de cada solicitud con todos los datos
- ✅ Botones para aprobar o rechazar
- ✅ Al aprobar, redirige a crear usuario con datos prellenados
- ✅ Historial completo de respuestas

## 📋 Instalación

### 1. Crear la Tabla en la Base de Datos

Tienes **dos opciones**:

#### Opción A: Ejecutar el script PHP (requiere XAMPP corriendo)
```bash
php c:\xampp\htdocs\proyectoEdificio\config\crear_tabla_solicitudes.php
```

#### Opción B: Ejecutar el SQL manualmente en phpMyAdmin
1. Abre phpMyAdmin (http://localhost/phpmyadmin)
2. Selecciona la base de datos `edificios_db`
3. Ve a la pestaña "SQL"
4. Copia y pega el contenido de `config/solicitudes_acceso.sql`
5. Click en "Continuar"

### 2. Verificar la Instalación

La tabla debe tener esta estructura:

```sql
DESCRIBE solicitudes_acceso;
```

Columnas esperadas:
- `id` - INT AUTO_INCREMENT PRIMARY KEY
- `nombre_completo` - VARCHAR(200) NOT NULL
- `email` - VARCHAR(150) NOT NULL
- `telefono` - VARCHAR(20)
- `nombre_edificio` - VARCHAR(200) NOT NULL
- `direccion_edificio` - TEXT
- `num_departamentos` - INT
- `mensaje` - TEXT
- `estado` - ENUM('PENDIENTE', 'APROBADA', 'RECHAZADA')
- `fecha_solicitud` - TIMESTAMP
- `fecha_respuesta` - TIMESTAMP NULL
- `respuesta_admin` - TEXT
- `usuario_creado_id` - INT NULL
- `activo` - TINYINT(1)

## 🚀 Uso

### Desde la Página de Inicio (Visitantes)

1. Ir a `http://localhost:8012/proyectoEdificio/`
2. Click en el botón **"🏢 Solicitar Acceso"**
3. Completar el formulario:
   - Nombre completo *
   - Email *
   - Teléfono *
   - Nombre del edificio *
   - Dirección del edificio (opcional)
   - Número de departamentos (opcional)
   - Mensaje adicional (opcional)
4. Click en **"📤 Enviar Solicitud"**
5. Esperar confirmación

### Panel de Administración (Admin Total)

1. Iniciar sesión como Administrador Total
2. Click en **"📬 Solicitudes"** en el menú de navegación
3. Ver todas las solicitudes con su estado
4. Para cada solicitud PENDIENTE:
   - **Aprobar**: Click en "✅ Aprobar" → Redirige a crear usuario con datos prellenados
   - **Rechazar**: Click en "❌ Rechazar" → Ingresar motivo (opcional)

## 📁 Archivos Creados

```
proyectoEdificio/
├── config/
│   ├── crear_tabla_solicitudes.php   # Script PHP para crear tabla
│   ├── solicitudes_acceso.sql        # Script SQL alternativo
│   └── SOLICITUDES_ACCESO.md         # Esta documentación
├── admin/
│   └── solicitudes.php               # Panel de administración
├── api/
│   └── gestionar_solicitud.php       # API para aprobar/rechazar
├── procesar_solicitud.php            # Procesa el formulario público
└── index.php                         # Actualizado con modal y botón
```

## 🔄 Flujo del Sistema

```
1. Visitante → Página Inicio → Click "Solicitar Acceso"
                     ↓
2. Completa formulario → Envía → procesar_solicitud.php
                     ↓
3. Valida datos → Guarda en BD → Estado: PENDIENTE
                     ↓
4. Admin Total → Ve solicitud en admin/solicitudes.php
                     ↓
5. Admin decide:
   - Aprobar → Redirige a crear usuario → Marca como APROBADA
   - Rechazar → Ingresa motivo → Marca como RECHAZADA
```

## 🎨 Validaciones Implementadas

### Frontend (JavaScript):
- ✅ Campos requeridos no vacíos
- ✅ Formato de email válido
- ✅ Número de departamentos positivo

### Backend (PHP):
- ✅ Validación de método POST
- ✅ Campos requeridos presentes
- ✅ Formato de email con `filter_var`
- ✅ Sanitización de datos con `trim()`
- ✅ Prevención de solicitudes duplicadas (email pendiente)
- ✅ Protección SQL injection con prepared statements

## 📊 Estados de Solicitud

| Estado | Color | Descripción |
|--------|-------|-------------|
| **PENDIENTE** | 🟡 Amarillo | Esperando revisión del administrador |
| **APROBADA** | 🟢 Verde | Admin aprobó y creó usuario |
| **RECHAZADA** | 🔴 Rojo | Admin rechazó la solicitud |

## 🔐 Permisos

- **Visitantes (sin sesión)**: Pueden enviar solicitudes
- **Administrador Total**: Puede ver, aprobar y rechazar solicitudes
- **Otros roles**: No tienen acceso al panel de solicitudes

## 💡 Mejoras Futuras

- [ ] Email automático al solicitante cuando cambie el estado
- [ ] Notificación en dashboard cuando hay nuevas solicitudes
- [ ] Opción de "archivar" solicitudes antiguas
- [ ] Estadísticas de solicitudes por mes
- [ ] Exportar solicitudes a Excel/CSV
- [ ] Sistema de comentarios internos entre admins
- [ ] Verificación de email antes de aprobar
- [ ] Integración con WhatsApp para notificaciones

## 🐛 Troubleshooting

### "No se pudo conectar a la base de datos"
- ✅ Verificar que XAMPP MySQL esté corriendo
- ✅ Verificar credenciales en `config/database.php`

### "Ya tienes una solicitud pendiente"
- ✅ El sistema previene múltiples solicitudes con el mismo email
- ✅ Esperar respuesta o cambiar email

### "Error al guardar la solicitud"
- ✅ Verificar que la tabla `solicitudes_acceso` exista
- ✅ Revisar logs de MySQL para detalles

### El botón "Solicitudes" no aparece
- ✅ Solo visible para rol "Administrador Total"
- ✅ Verificar sesión activa

## 📞 Soporte

Para reportar bugs o sugerir mejoras, contactar al equipo de desarrollo.

---

**Versión:** 1.0.0  
**Fecha:** Noviembre 18, 2025  
**Autor:** Sistema de Gestión de Edificios
