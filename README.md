# 🏢 Sistema de Gestión de Edificios v2.0

Sistema web profesional multi-edificio para la administración integral de propiedades inmobiliarias con control de acceso basado en roles y permisos dinámicos, gestión financiera, facturación automática, y arquitectura modular escalable.

## 🎨 **NUEVO**: Sistema de Estilos CSS Centralizado

El proyecto ahora cuenta con un sistema de diseño modular y reutilizable:

- ✅ **CSS Centralizado**: `assets/css/main.css` + `navigation.css` (861 líneas)
- ✅ **Variables CSS**: Design tokens para colores, espaciado, tipografía
- ✅ **Componentes Reutilizables**: Botones, formularios, cards, tablas, alertas, badges
- ✅ **Sistema Responsive**: Breakpoints optimizados para desktop, tablet y móvil
- ✅ **Código Limpio**: Sin estilos inline, HTML más legible
- ✅ **97% Reducción**: header.php de 782 líneas a 21 líneas

**📖 Documentación completa**: [docs/CSS_CENTRALIZACION.md](docs/CSS_CENTRALIZACION.md)

## ✨ Características Principales

### 🔐 Sistema de Roles
El sistema implementa 4 tipos de usuarios con diferentes niveles de acceso:

1. **Administrador Total**
   - Acceso completo al sistema
   - Gestión de todos los edificios
   - Gestión de usuarios y roles
   - Acceso al panel de administración

2. **Administrador Edificio**
   - Administra un edificio específico
   - Gestiona inquilinos de su edificio
   - Reportes y mantenimiento del edificio

3. **Inquilino**
   - Usuario residente del edificio
   - Puede reportar incidencias
   - Ver avisos y noticias
   - Acceso a servicios básicos

4. **Seguridad**
   - Personal de seguridad del edificio
   - Registro de visitas
   - Reportar incidentes
   - Ver lista de residentes

## 📁 Estructura del Proyecto

```
proyectoEdificio/
├── admin/                      # 🔧 Panel de administración
│   ├── panel.php              # Dashboard principal
│   ├── usuarios.php           # Gestión de usuarios
│   └── edificios.php          # Gestión de edificios
├── api/                        # 🔌 Endpoints API REST
│   └── get_edificios.php      # Lista de edificios (JSON)
├── config/                     # ⚙️ Configuración
│   ├── database.php           # Clase de conexión MySQLi (utf8mb4)
│   ├── db_setup.php           # ❌ DEPRECADO - Usar install/
│   └── setup_roles.php        # ❌ DEPRECADO - Usar install/
├── install/                    # 🆕 Instalación del sistema
│   ├── instalar.php           # ✅ Instalador automático
│   ├── instalacion_limpia.sql # ✅ Script SQL completo
│   └── INSTALACION.md         # 📖 Guía de instalación
├── includes/                   # 📦 Archivos compartidos
│   ├── header.php             # Header y estilos CSS
│   ├── nav.php                # Navegación dinámica
│   ├── footer.php             # Footer
│   ├── session.php            # Manejo de sesiones PHP
│   └── permissions.php        # Sistema de permisos y roles
├── js/                         # 📜 JavaScript
│   └── app.js                 # SPA navigation y funciones principales
├── scripts/                    # 🛠️ Scripts de utilidad
│   ├── list_users.php         # Listar usuarios de BD
│   ├── verificar_sistema.php  # Verificar estado del sistema
│   ├── add_soft_delete.php    # ❌ DEPRECADO
│   └── update_existing_users.php  # ❌ DEPRECADO
├── tests/                      # 🧪 Testing
│   └── test_registro.php      # Tests automatizados de registro
├── index.php                   # 🏠 Página principal
├── negocio.php                 # 💼 Información del negocio
├── nosotros.php                # 👥 Acerca de
├── registro.php                # 📝 Formulario de registro
├── login.php                   # 🔑 Inicio de sesión
├── logout.php                  # 🚪 Cerrar sesión
├── procesar_registro.php       # ✅ Backend de registro (validado)
├── procesar_login.php          # ✅ Backend de login
├── test_validaciones.php       # 🧪 Test de validaciones
├── REVISION_REGISTRO.md        # 📋 Documentación técnica completa
└── README.md                   # 📖 Este archivo
```

## 🚀 Instalación Rápida

### Requisitos
- PHP 7.4+
- MySQL 5.7+
- Apache (XAMPP/WAMP/LAMP)

### Instalación Automática (Recomendado)

1. Clona el repositorio:
```bash
git clone https://github.com/DesaPeru/proyectoEdificio.git
cd proyectoEdificio
```

2. Ejecuta el instalador:
```
http://localhost:8012/proyectoEdificio/install/instalar.php
```

3. Accede con las credenciales iniciales:
   - Usuario: `admin`
   - Contraseña: `admin123`
   - ⚠️ **Cambiar inmediatamente después del primer acceso**

4. ¡Listo! Comienza a crear edificios y usuarios desde la interfaz.

📖 **Guía completa**: [install/INSTALACION.md](install/INSTALACION.md)

---

## 📊 Base de Datos

### Instalación Limpia Incluye:

**Datos Maestros:**
- ✅ 4 Roles base (Admin Total, Admin Edificio, Inquilino, Seguridad)
- ✅ 23 Permisos del sistema (dinámicos y modificables)
- ✅ 44 Asignaciones rol-permiso
- ✅ 1 Usuario Administrador Total

**Tablas Operacionales:**
- ✅ 15 tablas creadas (vacías, se llenan con el uso)

Todo lo demás (edificios, usuarios, servicios, etc.) se crea desde la interfaz web.

### Tablas Principales (15)

#### **Maestras (6)**
- `roles` - Roles del sistema con permisos asociados
- `permisos` - 23 permisos dinámicos categorizados
- `rol_permisos` - Relación N:N roles-permisos
- `edificios` - Propiedades inmobiliarias gestionadas
- `usuarios` - Usuarios del sistema con roles
- `usuario_edificios` - Asignación N:N de edificios a administradores

#### **Operacionales (9)**
- `servicios` - Servicios por edificio (luz, agua, gas, etc.)
- `medidores` - Medidores de consumo asignados
- `lecturas_medidor` - Lecturas mensuales de medidores
- `ciclos_facturacion` - Períodos de facturación mensual
- `gastos_edificio` - Gastos registrados por ciclo
- `recibos_inquilino` - Recibos generados automáticamente
- `pagos_inquilino` - Pagos registrados por inquilinos
- `incidencias` - Reportes de problemas/mantenimiento
- `avisos
- direccion
- ciudad
- num_pisos
- num_departamentos
- activo (TINYINT: 1=Activo, 0=Inactivo)
- fecha_creacion

#### `usuarios`
- id (PK)
- nombre
- email
- username
- password (hashed)
- rol_id (FK a roles)
- edificio_id (FK a edificios)
- activo (TINYINT: 1=Activo, 0=Inactivo)
- fecha_registro

## 🚀 Instalación Completa

> ⚠️ **IMPORTANTE**: La sección de "Instalación Rápida" anterior es la recomendada.  
> Esta sección detallada se mantiene solo como referencia.

### ❌ Scripts Deprecados (NO USAR)

Los siguientes scripts están **obsoletos** y NO deben usarse:
- ❌ `config/db_setup.php` - Incompleto (sin permisos)
- ❌ `config/setup_roles.php` - Incompleto (sin permisos)
- ❌ `scripts/add_soft_delete.php` - Innecesario
- ❌ `scripts/update_existing_users.php` - Innecesario

**Usar en su lugar:**
- ✅ `install/instalar.php` (automático)
- ✅ `install/instalacion_limpia.sql` (manual)

### 📖 Documentación de Instalación

Para instrucciones detalladas, consulta:
- **[install/INSTALACION.md](install/INSTALACION.md)** - Guía completa de instalación
- **[docs/SISTEMA_PERMISOS.md](docs/SISTEMA_PERMISOS.md)** - Sistema de permisos

### Credenciales Iniciales

Después de la instalación, accede con:
```
Usuario:     admin
Contraseña:  admin123
Rol:         Administrador Total

```

> ⚠️ **CAMBIAR CONTRASEÑA**: Después del primer acceso, cambia inmediatamente la contraseña del usuario admin.
>
> 💡 **Verificación**: Ejecuta `php scripts/verificar_sistema.php` para confirmar que todo está funcionando correctamente.

### 3. Configuración de XAMPP

```yaml
Apache:
  Puerto: 8012
  DocumentRoot: C:\xampp\htdocs\proyectoEdificio

MySQL:
  Puerto: 3306 (default)
  Usuario: root
  Password: (vacío)
  Base de Datos: edificios_db
  Charset: utf8mb4
```

### 4. Acceso al Sistema

```
URL Principal: http://localhost:8012/proyectoEdificio/
Panel Admin:   http://localhost:8012/proyectoEdificio/admin/panel.php
API Edificios: http://localhost:8012/proyectoEdificio/api/get_edificios.php
```

## 📝 Sistema de Registro

### Registro Público

Los usuarios pueden auto-registrarse seleccionando uno de estos roles:

- **👤 Inquilino**: Para residentes del edificio
- **🛡️ Seguridad**: Para personal de seguridad

> ⚠️ Los roles de **Administrador** deben ser asignados manualmente por un Administrador Total.

### Validaciones Implementadas

#### Nombre Completo
- ✅ Mínimo 3 caracteres
- ✅ Máximo 100 caracteres
- ✅ Campo requerido

#### Correo Electrónico
- ✅ Formato válido (RFC 5322)
- ✅ Máximo 100 caracteres
- ✅ Único en el sistema
- ✅ Verificación en base de datos

#### Nombre de Usuario
- ✅ Mínimo 3 caracteres
- ✅ Máximo 50 caracteres
- ✅ Solo letras, números y guión bajo (_)
- ✅ Único en el sistema
- ✅ Sin espacios ni caracteres especiales

#### Contraseña
- ✅ Mínimo 8 caracteres
- ✅ Máximo 100 caracteres
- ✅ Al menos una letra (a-z, A-Z)
- ✅ Al menos un número (0-9)
- ✅ Confirmación requerida
- ✅ Hash bcrypt (PASSWORD_DEFAULT)

#### Rol y Edificio
- ✅ Solo roles públicos (Inquilino, Seguridad)
- ✅ Edificio obligatorio
- ✅ Verificación de ID válido

## Sistema de Permisos

El archivo `includes/permissions.php` contiene las funciones de control de acceso:

- `tienePermiso($permiso)`: Verifica si el usuario tiene un permiso específico
- `requierePermiso($permiso, $redirect)`: Requiere un permiso, redirige si no lo tiene
- `esRol($rol_nombre)`: Verifica si el usuario tiene un rol específico
- `estaAutenticado()`: Verifica si hay sesión activa
- `requiereAutenticacion($redirect)`: Requiere login, redirige al login si no está autenticado
- `perteneceAlEdificio($edificio_id)`: Verifica si el usuario pertenece a un edificio

## 🛡️ Seguridad

### Medidas Implementadas

#### Autenticación y Autorización
- ✅ **Password Hashing**: bcrypt con `password_hash()` (PASSWORD_DEFAULT)
- ✅ **Sesiones Seguras**: Manejo robusto de sesiones PHP
- ✅ **Control de Acceso**: Sistema de roles con verificación en cada request
- ✅ **RBAC**: Role-Based Access Control implementado

#### Protección contra Ataques
- ✅ **SQL Injection**: Prepared statements en todas las consultas
- ✅ **XSS Protection**: `htmlspecialchars()` en todas las salidas
- ✅ **Input Validation**: Validación en frontend y backend
- ✅ **Error Handling**: No expone información sensible al usuario

#### Base de Datos
- ✅ **Charset**: UTF-8 (utf8mb4) para prevenir encoding issues
- ✅ **Foreign Keys**: Integridad referencial implementada
- ✅ **Passwords**: Nunca almacenadas en texto plano
- ✅ **Error Logging**: `error_log()` para debugging sin exponer errores

#### Validación de Entrada
- ✅ **Sanitización**: `trim()`, `filter_var()` en todos los inputs
- ✅ **Tipo de Datos**: Conversión con `intval()`, validación de tipos
- ✅ **Longitud**: Límites máximos y mínimos en todos los campos
- ✅ **Caracteres**: Regex para validar patrones permitidos

## Edificios de Ejemplo

El sistema incluye 3 edificios de ejemplo:
1. Torre San Isidro (15 pisos, 45 departamentos)
2. Edificio Miraflores (10 pisos, 30 departamentos)
3. Residencial Los Olivos (8 pisos, 24 departamentos)

## Panel de Administración

Accesible solo para usuarios con rol "Administrador Total":

- **Gestión de Usuarios**: Ver, editar y desactivar usuarios (con opción de restaurar)
- **Gestión de Edificios**: Administrar edificios y sus propiedades (con opción de restaurar)
- **Gestión de Roles**: Configurar roles y permisos (con opción de restaurar)
- **Sistema de Soft Delete**: Los registros no se eliminan físicamente, se marcan como inactivos

### 🔄 Sistema de Soft Delete

El sistema implementa **borrado lógico** (soft delete) para preservar la integridad de los datos:

#### Características:
- **No elimina registros**: Los datos permanecen en la base de datos
- **Campo `activo`**: Indica el estado del registro (1 = Activo, 0 = Inactivo)
- **Recuperación**: Los registros inactivos pueden ser restaurados
- **Auditoría**: Se mantiene el histórico completo de datos
- **Cumplimiento legal**: Permite generar reportes históricos

#### Aplicación en las tablas:
- `usuarios.activo`: Usuarios desactivados no pueden iniciar sesión
- `edificios.activo`: Edificios inactivos no aparecen en selectores
- `roles.activo`: Roles inactivos no se pueden asignar a nuevos usuarios

#### Ventajas:
- ✅ **Auditoría completa**: Sabes qué se desactivó y cuándo
- ✅ **Recuperación fácil**: Restaurar con un clic
- ✅ **Reportes históricos**: Análisis de datos pasados
- ✅ **Integridad referencial**: Las relaciones se mantienen
- ✅ **Sin pérdida de datos**: Información recuperable

## 💻 Tecnologías Utilizadas

### Backend
- **PHP**: 8.0.30 (XAMPP)
- **Base de Datos**: MySQL/MariaDB 10.4.32
- **Servidor Web**: Apache 2.4.58

### Frontend
- **HTML5**: Estructura semántica
- **CSS3**: Estilos modernos y responsivos
- **JavaScript**: Vanilla JS (ES6+)
  - SPA Navigation con History API
  - Fetch API para comunicación asíncrona
  - DOM Manipulation

### Seguridad
- **Password Hashing**: bcrypt (PASSWORD_DEFAULT)
- **Prepared Statements**: MySQLi
- **Session Management**: PHP nativo
- **Input Validation**: Frontend + Backend

### Herramientas de Desarrollo
- **XAMPP**: Entorno de desarrollo local
- **VS Code**: Editor de código (recomendado)
- **Git**: Control de versiones
- **Copilot**: Asistente de desarrollo IA

## 🧪 Testing

### Tests Automatizados

```bash
# Ejecutar test completo de registro
php tests/test_registro.php

# Ejecutar test de validaciones
php test_validaciones.php

# Verificar sintaxis PHP
php -l procesar_registro.php
```

### Tests Implementados

- ✅ **12 casos de prueba** en `test_registro.php`
  - 6 validaciones de campos
  - 4 registros exitosos
  - 2 verificaciones de duplicados

- ✅ **5 casos de prueba** en `test_validaciones.php`
  - Contraseña corta
  - Contraseña sin números
  - Contraseña sin letras
  - Username con símbolos
  - Email inválido

### Debugging

#### Logs del Servidor
```bash
# Ver logs de PHP
tail -f C:\xampp\php\logs\php_error_log

# Buscar errores específicos
grep "Error MySQL" C:\xampp\php\logs\php_error_log
```

#### Consola del Navegador (F12)
- Logs detallados de cada operación
- Estados de formulario
- Respuestas del servidor
- Errores de validación

---

## 📚 Documentación

### Archivos de Documentación

- **`README.md`**: Este archivo - guía general del proyecto
- **`GUIA_USUARIO.md`**: Manual completo de usuario con paso a paso
- **`docs/`**: Documentación técnica detallada para desarrolladores
  - `DIAGRAMA_BASE_DATOS.md` - Estructura de base de datos
  - `REVISION_REGISTRO.md` - Sistema de registro y validaciones
  - `NOTIFICACIONES_EMAIL.md` - Configuración de emails
  - `RESPONSIVE_DESIGN.md` - Diseño responsive y móvil

### Cómo Contribuir

1. Fork el repositorio
2. Crea una rama para tu feature (`git checkout -b feature/nueva-funcionalidad`)
3. Commit tus cambios (`git commit -am 'Agrega nueva funcionalidad'`)
4. Push a la rama (`git push origin feature/nueva-funcionalidad`)
5. Crea un Pull Request

---

## 🚀 Roadmap - Próximas Mejoras

### Corto Plazo (v1.1)
- [ ] Sistema de recuperación de contraseña
- [ ] Verificación de email por código
- [ ] Panel para Administrador de Edificio
- [ ] Dashboard con métricas básicas

### Mediano Plazo (v1.2)
- [ ] Sistema de reportes de incidencias
- [ ] Sistema de avisos y notificaciones
- [ ] Gestión de pagos de servicios comunes
- [ ] Registro de visitas

### Largo Plazo (v2.0)
- [ ] Aplicación móvil (React Native)
- [ ] Sistema de tickets de mantenimiento
- [ ] Calendario de eventos y reservas
- [ ] Chat en tiempo real
- [ ] Integración con servicios de pago

---

## 👥 Autor

**Proyecto desarrollado para la gestión de edificios en Perú**

- Desarrollador: DesaPeru
- Repositorio: [github.com/DesaPeru/proyectoEdificio](https://github.com/DesaPeru/proyectoEdificio)
- Rama actual: `lineaGerard`

---

## 📅 Historial de Versiones

### v1.1.0 (Noviembre 2025)
- ✅ **Sistema de Soft Delete**: Borrado lógico en lugar de físico
- ✅ **Restauración de Registros**: Recuperar usuarios, edificios y roles desactivados
- ✅ **Panel Admin Completo**: CRUD completo con modales para usuarios, edificios y roles
- ✅ **Auditoría**: Histórico completo de datos preservado
- ✅ Campo `activo` en todas las tablas principales

### v1.0.0 (Noviembre 2025)
- ✅ Sistema de registro completo con validaciones robustas
- ✅ Sistema de login y autenticación
- ✅ Control de acceso basado en roles (RBAC)
- ✅ Gestión de usuarios y edificios
- ✅ API REST para edificios
- ✅ Navegación SPA (Single Page Application)
- ✅ Tests automatizados
- ✅ Documentación técnica completa
- ✅ Seguridad implementada (SQL Injection, XSS, Password Hashing)

---

## 📄 Licencia

Este proyecto está bajo desarrollo privado para gestión de edificios.

---

## 🆘 Soporte

Para reportar bugs o solicitar nuevas funcionalidades:

1. Revisa la documentación en `REVISION_REGISTRO.md`
2. Verifica los logs de error
3. Ejecuta los tests: `php test_validaciones.php`
4. Abre un issue en el repositorio

---

**🏢 Sistema de Gestión de Edificios - Versión 1.1.0**  
*Desarrollado con ❤️ para la gestión eficiente de edificios en Perú*
