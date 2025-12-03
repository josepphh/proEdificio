# 📖 Guía de Usuario - Sistema de Gestión de Edificios

## 🎯 Tabla de Contenidos

1. [Inicio de Sesión](#inicio-de-sesión)
2. [Administrador Total](#administrador-total)
   - [Primer Acceso](#primer-acceso)
   - [Crear un Edificio](#crear-un-edificio)
   - [Gestionar Usuarios](#gestionar-usuarios)
   - [Administrar Roles y Permisos](#administrar-roles-y-permisos)
   - [Sistema de Cobranza](#sistema-de-cobranza)
   - [Gestión de Avisos](#gestión-de-avisos)
   - [Reportes e Incidencias](#reportes-e-incidencias)
   - [Solicitudes de Acceso](#solicitudes-de-acceso)
3. [Administrador de Edificio](#administrador-de-edificio)
4. [Inquilino](#inquilino)
5. [Preguntas Frecuentes](#preguntas-frecuentes)

---

## 🔐 Inicio de Sesión

### Acceder al Sistema

1. **Abrir el navegador** y acceder a: `http://localhost:8012/proyectoEdificio/`
2. **Hacer clic** en el botón **"🔐 Iniciar Sesión"**
3. **Ingresar credenciales por defecto:**
   - **Usuario:** `admin`
   - **Contraseña:** `admin123`
4. **Hacer clic** en **"🚀 Iniciar Sesión"**

> ⚠️ **Importante:** Cambia la contraseña del administrador después del primer acceso por seguridad.

---

## 👨‍💼 Administrador Total

El **Administrador Total** tiene control completo sobre el sistema y puede gestionar múltiples edificios.

### 🎬 Primer Acceso

Después de iniciar sesión por primera vez, verás el **Panel de Administración** con:

- 📊 **Dashboard principal** con estadísticas generales
- 🏢 **0 Edificios** (aún no hay edificios creados)
- 👥 **1 Usuario** (el administrador)
- 📝 **Menú de navegación** con todas las opciones

---

### 🏢 Crear un Edificio

**Paso 1:** Acceder al módulo de edificios
1. En el menú superior, haz clic en **"Panel Admin"**
2. Busca la opción **"Edificios"** o **"Gestionar Edificios"**
3. Haz clic en **"+ Nuevo Edificio"** o **"Agregar Edificio"**

**Paso 2:** Completar el formulario
```
📋 Formulario de Nuevo Edificio:
├── Nombre del Edificio: "Torre Miraflores"
├── Dirección: "Av. Larco 1234, Miraflores"
├── Número de Pisos: "15"
├── Número de Departamentos: "60"
├── Monto de Mantenimiento: "S/ 350.00"
└── Día de Pago: "5" (día del mes)
```

**Paso 3:** Guardar
- Haz clic en **"💾 Guardar Edificio"**
- Verás un mensaje de confirmación: **"✅ Edificio creado exitosamente"**

> 💡 **Consejo:** Puedes crear múltiples edificios si gestionas varios inmuebles.

---

### 👥 Gestionar Usuarios

#### Crear un Administrador de Edificio

**Paso 1:** Ir a Usuarios
1. Menú superior → **"Panel Admin"**
2. Selecciona **"Usuarios"**
3. Haz clic en **"+ Crear Usuario"**

**Paso 2:** Completar el formulario
```
👤 Nuevo Administrador de Edificio:
├── Nombre Completo: "Carlos Mendoza"
├── Usuario: "carlos.mendoza"
├── Email: "carlos@edificio.com"
├── Contraseña: "Admin2024!"
├── Confirmar Contraseña: "Admin2024!"
├── Rol: "Administrador Edificio" ⭐
├── Edificio: "Torre Miraflores"
└── Departamento: (dejar vacío)
```

**Paso 3:** Enviar invitación
- ✅ Marcar: **"Enviar email de bienvenida"**
- Haz clic en **"💾 Crear Usuario"**
- El usuario recibirá un correo con sus credenciales

#### Crear un Inquilino

**Paso 1:** Crear usuario inquilino
```
👤 Nuevo Inquilino:
├── Nombre Completo: "María Torres"
├── Usuario: "maria.torres"
├── Email: "maria@correo.com"
├── Contraseña: "Inquilino2024!"
├── Confirmar Contraseña: "Inquilino2024!"
├── Rol: "Inquilino" ⭐
├── Edificio: "Torre Miraflores"
└── Departamento: "501" (Piso 5, Depto 01)
```

**Paso 2:** Guardar
- ✅ Marcar: **"Enviar email de bienvenida"**
- Haz clic en **"💾 Crear Usuario"**

> 📧 **Nota:** El sistema enviará automáticamente un correo con las credenciales de acceso.

---

### 🔒 Administrar Roles y Permisos

#### Roles Predefinidos

El sistema incluye 3 roles predeterminados:

1. **Administrador Total** 👑
   - Control total del sistema
   - Gestión de múltiples edificios
   - Creación de usuarios y roles
   - Acceso a todos los reportes

2. **Administrador Edificio** 🏢
   - Gestión de su edificio asignado
   - Creación de inquilinos
   - Gestión de cobranza
   - Publicación de avisos

3. **Inquilino** 👤
   - Ver sus pagos y recibos
   - Reportar incidencias
   - Ver avisos del edificio
   - Actualizar su perfil

#### Crear un Rol Personalizado (Opcional)

**Paso 1:** Ir a Roles
1. Menú → **"Panel Admin"** → **"Roles"**
2. Haz clic en **"+ Crear Rol"**

**Paso 2:** Configurar permisos
```
🎭 Nuevo Rol: "Asistente Administrativo"
├── Nombre: "Asistente Administrativo"
├── Descripción: "Ayuda en tareas administrativas"
└── Permisos:
    ├── ✅ Ver usuarios
    ├── ✅ Ver edificios
    ├── ✅ Gestionar avisos
    ├── ❌ Crear usuarios
    └── ❌ Eliminar datos
```

---

### 💰 Sistema de Cobranza

#### Configurar Ciclo de Cobranza

**Paso 1:** Acceder a Cobranza
1. Menú → **"Panel Admin"** → **"Cobranza"**
2. Selecciona el edificio: **"Torre Miraflores"**

**Paso 2:** Crear Ciclo Mensual
```
📅 Nuevo Ciclo de Cobranza:
├── Edificio: "Torre Miraflores"
├── Mes: "Enero 2025"
├── Fecha de Corte: "05/01/2025"
├── Monto Base: "S/ 350.00"
└── Concepto: "Mantenimiento mensual"
```

**Paso 3:** Generar Recibos Automáticamente
- Haz clic en **"🔄 Generar Recibos"**
- El sistema creará automáticamente un recibo para cada inquilino
- Los inquilinos verán sus recibos en **"Mis Pagos"**

#### Registrar un Pago

**Paso 1:** Ir a Validar Pagos
1. Menú → **"Validar Pagos"**
2. Verás la lista de recibos pendientes

**Paso 2:** Procesar pago
```
💳 Validar Pago:
├── Inquilino: "María Torres - Depto 501"
├── Monto: "S/ 350.00"
├── Método de Pago: "Transferencia Bancaria"
├── Comprobante: [Subir imagen/PDF]
└── Fecha de Pago: "10/01/2025"
```

**Paso 3:** Aprobar
- Haz clic en **"✅ Aprobar Pago"**
- El recibo cambiará a estado **"PAGADO"**
- Se enviará correo de confirmación al inquilino

---

### 📢 Gestión de Avisos

#### Publicar un Aviso

**Paso 1:** Crear aviso
1. Menú → **"Avisos"**
2. Haz clic en **"+ Nuevo Aviso"**

**Paso 2:** Completar formulario
```
📋 Nuevo Aviso:
├── Título: "Corte de agua programado"
├── Edificio: "Torre Miraflores"
├── Tipo: "Urgente" ⚠️
├── Descripción: 
│   "Estimados vecinos,
│    El día 20/01/2025 de 8am a 12pm
│    habrá corte de agua por mantenimiento
│    de cisternas. Gracias por su comprensión."
└── Enviar Email: ✅ (Notificar a todos los inquilinos)
```

**Paso 3:** Publicar
- Haz clic en **"📤 Publicar Aviso"**
- Se enviará email a todos los inquilinos del edificio
- El aviso aparecerá en la página principal

#### Tipos de Avisos

- 🔴 **Urgente:** Cortes de servicios, emergencias
- 🟡 **Importante:** Reuniones, asambleas
- 🔵 **Informativo:** Actividades, eventos
- ⚪ **General:** Recordatorios, felicitaciones

---

### 🔧 Reportes e Incidencias

#### Ver Incidencias Reportadas

**Paso 1:** Acceder a incidencias
1. Menú → **"Incidencias"** o **"Reportes"**
2. Verás lista de incidencias por edificio

**Paso 2:** Gestionar incidencia
```
🛠️ Incidencia Reportada:
├── Reportada por: "María Torres - Depto 501"
├── Fecha: "15/01/2025 10:30"
├── Tipo: "Mantenimiento"
├── Descripción: "Ascensor #2 no funciona"
├── Estado: "PENDIENTE"
└── Prioridad: "Alta"
```

**Paso 3:** Actualizar estado
1. Haz clic en **"Ver Detalle"**
2. Selecciona nuevo estado:
   - 🟡 **En Proceso:** Técnico asignado
   - ✅ **Resuelta:** Problema solucionado
   - ❌ **Rechazada:** No procede
3. Agregar comentario: "Técnico asignado, llegará en 2 horas"
4. Haz clic en **"💾 Actualizar"**

> 📧 El inquilino recibirá email automático con la actualización.

---

### 📬 Solicitudes de Acceso

Cuando alguien solicita acceso al sistema desde la página principal:

**Paso 1:** Ver solicitudes
1. Menú → **"📬 Solicitudes"**
2. Verás lista de solicitudes pendientes

**Paso 2:** Revisar solicitud
```
📋 Solicitud de Acceso:
├── Nombre: "Roberto Silva"
├── Email: "roberto@correo.com"
├── Teléfono: "987654321"
├── Edificio: "Torre San Isidro"
├── Dirección: "Av. República 456"
├── Cantidad Deptos: "40"
├── Fecha: "18/01/2025"
└── Estado: "PENDIENTE"
```

**Paso 3:** Aprobar o Rechazar

**Opción A - Aprobar:**
1. Haz clic en **"✅ Aprobar"**
2. Se abrirá formulario pre-llenado para crear usuario
3. Verifica los datos y ajusta si es necesario
4. Selecciona rol: **"Administrador Edificio"**
5. Haz clic en **"💾 Crear Usuario"**
6. El solicitante recibirá email con sus credenciales

**Opción B - Rechazar:**
1. Haz clic en **"❌ Rechazar"**
2. Ingresa motivo: "Falta documentación del edificio"
3. Haz clic en **"Confirmar"**
4. El solicitante recibirá email informativo

---

### 📊 Generar Reportes

#### Reporte de Cobranza

**Paso 1:** Ir a Reportes
1. Menú → **"Reportes"**
2. Selecciona **"Reporte de Cobranza"**

**Paso 2:** Filtros
```
🔍 Filtros:
├── Edificio: "Torre Miraflores"
├── Mes: "Enero 2025"
└── Estado: "Todos"
```

**Paso 3:** Exportar
- Haz clic en **"📄 Exportar PDF"** o **"📊 Exportar Excel"**
- El reporte incluye:
  - Total recaudado
  - Pendientes de pago
  - Morosidad por departamento
  - Gráficos de estadísticas

#### Reporte de Usuarios

**Paso 1:** Seleccionar reporte
1. Menú → **"Reportes"** → **"Usuarios"**

**Paso 2:** Ver estadísticas
- Total de usuarios por rol
- Usuarios activos vs inactivos
- Usuarios por edificio
- Gráfico de distribución

---

## 🏢 Administrador de Edificio

El **Administrador de Edificio** gestiona un edificio específico asignado.

### Funcionalidades Principales

#### 1. Ver Dashboard
- Estadísticas del edificio asignado
- Resumen de pagos del mes
- Incidencias pendientes
- Próximos vencimientos

#### 2. Gestionar Inquilinos
```
Crear Nuevo Inquilino:
├── Nombre: "Juan Pérez"
├── Email: "juan@correo.com"
├── Departamento: "302"
├── Monto Mensual: "S/ 350.00"
└── Enviar email de bienvenida: ✅
```

#### 3. Publicar Avisos
- Solo para su edificio asignado
- Tipos: Urgente, Importante, Informativo

#### 4. Gestionar Cobranza
- Ver estado de pagos
- Aprobar pagos recibidos
- Enviar recordatorios
- Generar reportes del mes

#### 5. Atender Incidencias
- Ver reportes de inquilinos
- Asignar técnicos
- Actualizar estados
- Cerrar incidencias resueltas

---

## 👤 Inquilino

El **Inquilino** puede gestionar sus pagos y comunicarse con la administración.

### 📱 Mi Dashboard

Al iniciar sesión, el inquilino verá:

```
🏠 Mi Dashboard:
├── 💰 Pagos Pendientes: 1 recibo por S/ 350.00
├── ✅ Pagos Realizados: 5 pagos este año
├── 📢 Avisos Recientes: 2 avisos nuevos
└── 🛠️ Mis Incidencias: 1 en proceso
```

### 💳 Realizar un Pago

**Paso 1:** Ver recibos pendientes
1. Menú → **"Mis Pagos"**
2. Ver lista de recibos pendientes

**Paso 2:** Seleccionar recibo
```
📄 Recibo Pendiente:
├── Periodo: "Enero 2025"
├── Monto: "S/ 350.00"
├── Vencimiento: "05/01/2025"
└── Estado: "PENDIENTE"
```

**Paso 3:** Subir comprobante
1. Haz clic en **"💳 Pagar"**
2. Seleccionar método: "Transferencia Bancaria"
3. Subir comprobante (imagen o PDF)
4. Ingresar número de operación
5. Haz clic en **"📤 Enviar Comprobante"**

**Paso 4:** Esperar validación
- Estado cambia a: **"EN VALIDACIÓN"**
- Recibirás email cuando sea aprobado
- Puedes descargar PDF del recibo pagado

### 🛠️ Reportar una Incidencia

**Paso 1:** Ir a Reportar
1. Menú → **"Reportar"** o **"Incidencias"**
2. Haz clic en **"+ Nueva Incidencia"**

**Paso 2:** Completar formulario
```
🔧 Reportar Incidencia:
├── Tipo: "Mantenimiento"
├── Ubicación: "Ascensor Principal"
├── Prioridad: "Alta"
├── Descripción: 
│   "El ascensor hace ruido extraño
│    y se detiene entre pisos"
└── Fotos: [Subir hasta 3 fotos]
```

**Paso 3:** Enviar
- Haz clic en **"📤 Enviar Reporte"**
- Recibirás número de ticket
- Puedes hacer seguimiento del estado

### 📢 Ver Avisos

**Paso 1:** Acceder a avisos
1. Menú → **"Avisos"**
2. Ver lista de avisos del edificio

**Paso 2:** Leer detalle
- Haz clic en cualquier aviso
- Ver información completa
- Marcar como leído automáticamente

---

## ❓ Preguntas Frecuentes

### 🔐 Seguridad y Acceso

**P: ¿Cómo cambio mi contraseña?**
R: 
1. Menú → **"Mi Perfil"**
2. Sección **"Cambiar Contraseña"**
3. Ingresar contraseña actual y nueva contraseña
4. Haz clic en **"💾 Actualizar"**

**P: ¿Qué hago si olvido mi contraseña?**
R: Contacta al administrador del edificio para que restablezca tu contraseña.

**P: ¿Puedo tener múltiples roles?**
R: No, cada usuario tiene un solo rol asignado.

---

### 💰 Pagos y Cobranza

**P: ¿Cuándo debo pagar mi mantenimiento?**
R: El día de pago está configurado por edificio (generalmente día 5 de cada mes). Verifica en tu recibo.

**P: ¿Qué métodos de pago aceptan?**
R:
- Transferencia bancaria
- Depósito en efectivo
- Yape/Plin
- Cheque

**P: ¿Cuánto demora la aprobación de mi pago?**
R: Normalmente 24-48 horas hábiles después de subir el comprobante.

**P: ¿Puedo descargar mis recibos anteriores?**
R: Sí, en **"Mis Pagos"** → **"Historial"** → **"📄 Descargar PDF"**

---

### 🛠️ Incidencias y Mantenimiento

**P: ¿Cuánto tiempo tarda en resolverse una incidencia?**
R: Depende de la prioridad:
- 🔴 Alta: 24 horas
- 🟡 Media: 48-72 horas
- 🟢 Baja: 1 semana

**P: ¿Puedo cancelar una incidencia?**
R: Sí, si aún está en estado **"PENDIENTE"**, haz clic en **"❌ Cancelar"**

**P: ¿Recibiré notificaciones de mi incidencia?**
R: Sí, recibirás email cada vez que cambie el estado.

---

### 📢 Avisos y Comunicación

**P: ¿Cómo me entero de los avisos importantes?**
R:
- Email automático cuando se publica un aviso
- Notificación en dashboard al iniciar sesión
- Sección **"Avisos"** en el menú

**P: ¿Puedo responder a un aviso?**
R: Actualmente no, pero puedes contactar a la administración por otros medios.

---

### 🏢 Edificios y Departamentos

**P: ¿Puedo cambiar de departamento?**
R: Contacta al administrador para actualizar tu departamento asignado.

**P: ¿Qué pasa si me mudo?**
R: Informa a la administración para que desactiven tu usuario o lo reasignen al nuevo inquilino.

---

### 📧 Emails y Notificaciones

**P: ¿Por qué no recibo emails del sistema?**
R: Verifica:
1. Bandeja de spam
2. Email correcto en tu perfil
3. Configuración de servidor SMTP (administrador)

**P: ¿Puedo desactivar las notificaciones por email?**
R: Actualmente no, todos los emails importantes son obligatorios por seguridad.

---

## 🆘 Soporte Técnico

### Contacto

Si tienes problemas técnicos:

1. **Administrador del Edificio:**
   - Consulta el directorio del edificio
   
2. **Administrador del Sistema:**
   - Email: admin@edificios.com
   - Teléfono: (01) 123-4567

3. **Horario de Atención:**
   - Lunes a Viernes: 9:00 AM - 6:00 PM
   - Sábados: 9:00 AM - 1:00 PM
   - Urgencias: 24/7

---

## 📚 Recursos Adicionales

### Documentación Técnica

- **Manual de Instalación:** `README.md` (raíz del proyecto)
- **Documentación para Desarrolladores:** `docs/` (carpeta técnica)
- **Diseño Responsive:** `docs/RESPONSIVE_DESIGN.md`
- **Configuración de Email:** `docs/NOTIFICACIONES_EMAIL.md`
- **Base de Datos:** `docs/DIAGRAMA_BASE_DATOS.md`

### Video Tutoriales (Próximamente)

- ✅ Primer acceso como administrador
- ✅ Crear edificio y usuarios
- ✅ Gestionar cobranza mensual
- ✅ Publicar avisos
- ✅ Reportar incidencias

---

## 🎓 Consejos y Mejores Prácticas

### Para Administradores

1. **Seguridad:**
   - Cambia contraseñas regularmente
   - No compartas credenciales
   - Revisa logs de actividad

2. **Cobranza:**
   - Genera recibos el primer día del mes
   - Envía recordatorios a morosos
   - Valida pagos rápidamente

3. **Comunicación:**
   - Publica avisos con anticipación
   - Usa tipo correcto (Urgente solo para emergencias)
   - Responde incidencias en 24h

### Para Inquilinos

1. **Pagos:**
   - Paga antes del vencimiento
   - Guarda comprobantes originales
   - Sube fotos claras y legibles

2. **Incidencias:**
   - Reporta problemas inmediatamente
   - Incluye fotos si es posible
   - Sé específico en la descripción

3. **Perfil:**
   - Mantén email actualizado
   - Revisa avisos regularmente
   - Actualiza teléfono de contacto

---

## 📊 Glosario de Términos

| Término | Definición |
|---------|------------|
| **Ciclo de Cobranza** | Periodo mensual de facturación |
| **Recibo** | Documento de pago mensual |
| **Voucher** | Comprobante de pago bancario |
| **Mantenimiento** | Cuota mensual del edificio |
| **Morosidad** | Retraso en el pago |
| **Incidencia** | Reporte de problema o avería |
| **Aviso** | Comunicación oficial a inquilinos |
| **Dashboard** | Panel principal con resumen |
| **Rol** | Tipo de usuario con permisos específicos |

---

**Versión:** 1.0.0  
**Última actualización:** Noviembre 18, 2025  
**Sistema:** Gestión de Edificios v2.0

---

> 💡 **¿Sugerencias?** Si encuentras algo que no está claro o quieres agregar algo a esta guía, contacta al administrador del sistema.
