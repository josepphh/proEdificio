# 📊 Diagrama de Base de Datos - Sistema de Gestión de Edificios

## 🗄️ Base de Datos: `edificios_db`

---

## 📋 Tablas y Relaciones

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                          SISTEMA DE EDIFICIOS                                │
└─────────────────────────────────────────────────────────────────────────────┘

┌──────────────────┐
│     ROLES        │
├──────────────────┤
│ PK id            │
│    nombre        │──┐
│    descripcion   │  │
│    fecha_creacion│  │
│    activo        │  │
└──────────────────┘  │
                      │
                      │ 1:N
                      │
┌──────────────────┐  │    ┌──────────────────────┐
│   EDIFICIOS      │  │    │  USUARIO_EDIFICIOS   │
├──────────────────┤  │    ├──────────────────────┤
│ PK id            │──┼───▶│ PK id                │
│    nombre        │  │    │ FK usuario_id        │
│    direccion     │  │    │ FK edificio_id       │
│    ciudad        │  │    │    fecha_asignacion  │
│    pisos         │  │    │    activo            │
│    departamentos │  │    └──────────────────────┘
│    telefono      │  │              │
│    email         │  │              │
│    activo        │  │              │
└──────────────────┘  │              │
       │              │              │
       │ 1:N          │              │
       │              │              │ N:N
       │              │              │
       │              │              │
       │              │              │
       │              │              │
┌──────────────────┐  │              │
│    USUARIOS      │◀─┘              │
├──────────────────┤                 │
│ PK id            │◀────────────────┘
│    nombre        │
│    email         │
│    username      │
│    password      │
│ FK rol_id        │
│ FK edificio_id   │ (principal - compatibilidad)
│    fecha_registro│
│    fecha_ingreso │
│    fecha_salida  │
│    activo        │
└──────────────────┘
       │
       │ 1:N
       │
       ├──────────────────────────────────────────┐
       │                                          │
       │                                          │
┌──────────────────┐                    ┌──────────────────┐
│    AVISOS        │                    │   INCIDENCIAS    │
├──────────────────┤                    ├──────────────────┤
│ PK id            │                    │ PK id            │
│ FK edificio_id   │                    │ FK usuario_id    │
│    titulo        │                    │ FK edificio_id   │
│    contenido     │                    │    titulo        │
│    tipo          │                    │    descripcion   │
│    fecha_pub     │                    │    estado        │
│    fecha_venc    │                    │    prioridad     │
│    activo        │                    │    fecha_reporte │
└──────────────────┘                    │    fecha_resol   │
                                        │    activo        │
                                        └──────────────────┘

┌──────────────────┐
│    SERVICIOS     │
├──────────────────┤
│ PK id            │
│ FK edificio_id   │──┐
│    nombre        │  │
│    tipo          │  │ 1:N
│    monto_fijo    │  │
│    activo        │  │
└──────────────────┘  │
                      │
                      │
┌──────────────────┐  │
│    MEDIDORES     │◀─┘
├──────────────────┤
│ PK id            │──┐
│ FK usuario_id    │  │
│ FK servicio_id   │  │
│    numero        │  │ 1:N
│    tipo          │  │
│    ubicacion     │  │
│    fecha_inst    │  │
│    activo        │  │
└──────────────────┘  │
                      │
                      │
┌──────────────────┐  │
│ LECTURAS_MEDIDOR │◀─┘
├──────────────────┤
│ PK id            │
│ FK medidor_id    │
│ FK ciclo_id      │
│    lectura_ant   │
│    lectura_act   │
│    consumo       │
│    fecha_lectura │
│    observaciones │
│    activo        │
└──────────────────┘


┌─────────────────────┐
│ CICLOS_FACTURACION  │
├─────────────────────┤
│ PK id               │──┐
│ FK edificio_id      │  │
│    mes              │  │
│    anio             │  │ 1:N
│    fecha_inicio     │  │
│    fecha_cierre     │  │
│    estado           │  │
│    activo           │  │
└─────────────────────┘  │
                         │
                         │
                         │
                         ├───────────────────────┐
                         │                       │
                         │                       │
┌─────────────────────┐  │          ┌──────────────────┐
│  GASTOS_EDIFICIO    │◀─┘          │ RECIBOS_INQUILINO│
├─────────────────────┤              ├──────────────────┤
│ PK id               │              │ PK id            │
│ FK edificio_id      │              │ FK usuario_id    │
│ FK ciclo_id         │              │ FK ciclo_id      │
│    concepto         │              │    monto_alquiler│
│    monto            │              │    monto_servicios│
│    fecha_gasto      │              │    monto_consumo │
│    comprobante      │              │    monto_deuda   │
│    activo           │              │    estado        │
└─────────────────────┘              │    fecha_emision │
                                     │    fecha_venc    │
                                     │    activo        │
                                     └──────────────────┘
                                            │
                                            │ 1:N
                                            │
                                     ┌──────────────────┐
                                     │ PAGOS_INQUILINO  │
                                     ├──────────────────┤
                                     │ PK id            │
                                     │ FK recibo_id     │
                                     │ FK usuario_id    │
                                     │    monto         │
                                     │    fecha_pago    │
                                     │    metodo_pago   │
                                     │    referencia    │
                                     │    activo        │
                                     └──────────────────┘
```

---

## 🔑 Relaciones Principales

### 1. **ROLES ↔ USUARIOS** (1:N)
- Un rol puede tener múltiples usuarios
- Cada usuario tiene un rol

### 2. **EDIFICIOS ↔ USUARIOS** (1:N - Compatibilidad)
- Un edificio puede tener múltiples usuarios
- Campo `edificio_id` en usuarios (principal/legacy)

### 3. **USUARIOS ↔ EDIFICIOS** (N:N via USUARIO_EDIFICIOS)
- **Nueva implementación** para Administradores Edificio
- Un administrador puede gestionar múltiples edificios
- Un edificio puede tener múltiples administradores

### 4. **EDIFICIOS ↔ SERVICIOS** (1:N)
- Un edificio tiene múltiples servicios
- Cada servicio pertenece a un edificio

### 5. **USUARIOS ↔ MEDIDORES** (1:N)
- Un usuario (inquilino) puede tener múltiples medidores
- Cada medidor pertenece a un usuario

### 6. **MEDIDORES ↔ LECTURAS_MEDIDOR** (1:N)
- Un medidor tiene múltiples lecturas
- Cada lectura pertenece a un medidor

### 7. **EDIFICIOS ↔ CICLOS_FACTURACION** (1:N)
- Un edificio tiene múltiples ciclos de facturación
- Cada ciclo pertenece a un edificio

### 8. **CICLOS_FACTURACION ↔ GASTOS_EDIFICIO** (1:N)
- Un ciclo tiene múltiples gastos
- Cada gasto pertenece a un ciclo

### 9. **CICLOS_FACTURACION ↔ RECIBOS_INQUILINO** (1:N)
- Un ciclo genera múltiples recibos
- Cada recibo pertenece a un ciclo

### 10. **USUARIOS ↔ RECIBOS_INQUILINO** (1:N)
- Un usuario (inquilino) tiene múltiples recibos
- Cada recibo pertenece a un usuario

### 11. **RECIBOS_INQUILINO ↔ PAGOS_INQUILINO** (1:N)
- Un recibo puede tener múltiples pagos (parciales)
- Cada pago se aplica a un recibo

### 12. **USUARIOS ↔ INCIDENCIAS** (1:N)
- Un usuario puede reportar múltiples incidencias
- Cada incidencia es reportada por un usuario

### 13. **EDIFICIOS ↔ AVISOS** (1:N)
- Un edificio puede tener múltiples avisos
- Un aviso puede ser general (NULL) o para un edificio específico

---

## 📊 Tipos de Datos por Tabla

### **ROLES**
```sql
id              INT (PK, AUTO_INCREMENT)
nombre          VARCHAR(50)
descripcion     TEXT
fecha_creacion  DATETIME DEFAULT CURRENT_TIMESTAMP
activo          TINYINT(1) DEFAULT 1
```

### **EDIFICIOS**
```sql
id              INT (PK, AUTO_INCREMENT)
nombre          VARCHAR(100)
direccion       VARCHAR(255)
ciudad          VARCHAR(100)
pisos           INT
departamentos   INT
telefono        VARCHAR(20)
email           VARCHAR(100)
activo          TINYINT(1) DEFAULT 1
```

### **USUARIOS**
```sql
id              INT (PK, AUTO_INCREMENT)
nombre          VARCHAR(100)
email           VARCHAR(100) UNIQUE
username        VARCHAR(50) UNIQUE
password        VARCHAR(255)
rol_id          INT (FK → roles.id)
edificio_id     INT (FK → edificios.id) [NULLABLE]
fecha_registro  DATETIME DEFAULT CURRENT_TIMESTAMP
fecha_ingreso   DATE [NULLABLE]
fecha_salida    DATE [NULLABLE]
activo          TINYINT(1) DEFAULT 1
```

### **USUARIO_EDIFICIOS** (Nueva - N:N)
```sql
id                 INT (PK, AUTO_INCREMENT)
usuario_id         INT (FK → usuarios.id)
edificio_id        INT (FK → edificios.id)
fecha_asignacion   DATETIME DEFAULT CURRENT_TIMESTAMP
activo             TINYINT(1) DEFAULT 1
UNIQUE KEY (usuario_id, edificio_id)
```

### **SERVICIOS**
```sql
id              INT (PK, AUTO_INCREMENT)
edificio_id     INT (FK → edificios.id)
nombre          VARCHAR(100)
tipo            ENUM('LUZ','AGUA','GAS','INTERNET','MANTENIMIENTO','OTRO')
monto_fijo      DECIMAL(10,2)
activo          TINYINT(1) DEFAULT 1
```

### **MEDIDORES**
```sql
id              INT (PK, AUTO_INCREMENT)
usuario_id      INT (FK → usuarios.id)
servicio_id     INT (FK → servicios.id)
numero_medidor  VARCHAR(50)
tipo            ENUM('LUZ','AGUA','GAS')
ubicacion       VARCHAR(100)
fecha_instalacion DATE
activo          TINYINT(1) DEFAULT 1
```

### **LECTURAS_MEDIDOR**
```sql
id              INT (PK, AUTO_INCREMENT)
medidor_id      INT (FK → medidores.id)
ciclo_id        INT (FK → ciclos_facturacion.id)
lectura_anterior DECIMAL(10,2)
lectura_actual  DECIMAL(10,2)
consumo         DECIMAL(10,2)
fecha_lectura   DATE
observaciones   TEXT
activo          TINYINT(1) DEFAULT 1
```

### **CICLOS_FACTURACION**
```sql
id              INT (PK, AUTO_INCREMENT)
edificio_id     INT (FK → edificios.id)
mes             INT
anio            INT
fecha_inicio    DATE
fecha_cierre    DATE
estado          ENUM('ABIERTO','CERRADO') DEFAULT 'ABIERTO'
activo          TINYINT(1) DEFAULT 1
```

### **GASTOS_EDIFICIO**
```sql
id              INT (PK, AUTO_INCREMENT)
edificio_id     INT (FK → edificios.id)
ciclo_id        INT (FK → ciclos_facturacion.id)
concepto        VARCHAR(255)
monto           DECIMAL(10,2)
fecha_gasto     DATE
comprobante     VARCHAR(255)
activo          TINYINT(1) DEFAULT 1
```

### **RECIBOS_INQUILINO**
```sql
id              INT (PK, AUTO_INCREMENT)
usuario_id      INT (FK → usuarios.id)
ciclo_id        INT (FK → ciclos_facturacion.id)
monto_alquiler  DECIMAL(10,2)
monto_servicios DECIMAL(10,2)
monto_consumo   DECIMAL(10,2)
monto_deuda     DECIMAL(10,2)
estado          ENUM('PENDIENTE','PAGADO','VENCIDO') DEFAULT 'PENDIENTE'
fecha_emision   DATETIME DEFAULT CURRENT_TIMESTAMP
fecha_vencimiento DATE
activo          TINYINT(1) DEFAULT 1
```

### **PAGOS_INQUILINO**
```sql
id              INT (PK, AUTO_INCREMENT)
recibo_id       INT (FK → recibos_inquilino.id)
usuario_id      INT (FK → usuarios.id)
monto           DECIMAL(10,2)
fecha_pago      DATETIME DEFAULT CURRENT_TIMESTAMP
metodo_pago     ENUM('EFECTIVO','TRANSFERENCIA','TARJETA','OTRO')
numero_referencia VARCHAR(100)
activo          TINYINT(1) DEFAULT 1
```

### **INCIDENCIAS**
```sql
id              INT (PK, AUTO_INCREMENT)
usuario_id      INT (FK → usuarios.id)
edificio_id     INT (FK → edificios.id)
titulo          VARCHAR(255)
descripcion     TEXT
estado          ENUM('PENDIENTE','EN_PROCESO','RESUELTO') DEFAULT 'PENDIENTE'
prioridad       ENUM('BAJA','MEDIA','ALTA') DEFAULT 'MEDIA'
fecha_reporte   DATETIME DEFAULT CURRENT_TIMESTAMP
fecha_resolucion DATETIME
activo          TINYINT(1) DEFAULT 1
```

### **AVISOS**
```sql
id                  INT (PK, AUTO_INCREMENT)
edificio_id         INT (FK → edificios.id) [NULLABLE]
titulo              VARCHAR(255)
contenido           TEXT
tipo                ENUM('INFORMATIVO','URGENTE','MANTENIMIENTO','EVENTO')
fecha_publicacion   DATETIME DEFAULT CURRENT_TIMESTAMP
fecha_vencimiento   DATE [NULLABLE]
activo              TINYINT(1) DEFAULT 1
```

---

## 🔐 Soft Delete

**Todas las tablas implementan soft delete** mediante el campo `activo`:
- `activo = 1` → Registro activo
- `activo = 0` → Registro eliminado (soft delete)

---

## 📈 Índices y Optimizaciones

- **Primary Keys**: AUTO_INCREMENT en todas las tablas
- **Foreign Keys**: Con ON DELETE CASCADE
- **UNIQUE Keys**: 
  - usuarios.email
  - usuarios.username
  - usuario_edificios (usuario_id, edificio_id)
- **Charset**: utf8mb4_unicode_ci (soporte completo Unicode)

---

## 🎯 Resumen de Tablas

| # | Tabla | Propósito | Relaciones |
|---|-------|-----------|------------|
| 1 | roles | Gestión de permisos | → usuarios |
| 2 | edificios | Inmuebles gestionados | → usuarios, servicios, ciclos, avisos |
| 3 | usuarios | Usuarios del sistema | → roles, edificios, medidores, recibos, pagos |
| 4 | usuario_edificios | Asignación N:N de edificios a admins | → usuarios, edificios |
| 5 | servicios | Servicios por edificio | → edificios, medidores |
| 6 | medidores | Medidores de consumo | → usuarios, servicios, lecturas |
| 7 | lecturas_medidor | Lecturas de medidores | → medidores, ciclos |
| 8 | ciclos_facturacion | Períodos de facturación | → edificios, gastos, recibos |
| 9 | gastos_edificio | Gastos comunes | → edificios, ciclos |
| 10 | recibos_inquilino | Recibos generados | → usuarios, ciclos, pagos |
| 11 | pagos_inquilino | Pagos realizados | → recibos, usuarios |
| 12 | incidencias | Reportes de problemas | → usuarios, edificios |
| 13 | avisos | Notificaciones y avisos | → edificios |

---

**Total de Tablas**: 13  
**Relaciones Implementadas**: 15+  
**Sistema de Soft Delete**: ✅ Todas las tablas  
**Charset**: utf8mb4_unicode_ci
