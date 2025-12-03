# 🎨 Tema Unificado - Sistema de Gestión de Edificios

## Paleta de Colores Oficial

### Colores Primarios
El sistema usa una paleta consistente de colores en todas las pantallas:

```css
/* Morado/Azul - Color Principal del Sistema */
--color-primary: #667eea
--color-primary-dark: #764ba2
--gradient-primary: linear-gradient(135deg, #667eea 0%, #764ba2 100%)

/* Azul Claro - Color Secundario/Acento */
--color-secondary: #4facfe
--color-secondary-dark: #00a8e8
--gradient-secondary: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%)

/* Verde - Color de Énfasis */
--color-accent: #43e97b
--gradient-accent: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%)

/* Azul Google - Títulos */
--color-title: #1a73e8
```

### Colores de Estado
```css
--color-success: #28a745    /* Verde - Éxito */
--color-danger: #dc3545     /* Rojo - Error */
--color-warning: #ffc107    /* Amarillo - Advertencia */
--color-info: #17a2b8       /* Cyan - Información */
```

### Colores Neutros
```css
--color-white: #ffffff
--color-gray-50: #f8f9fa    /* Fondo alternativo */
--color-gray-500: #666666   /* Texto secundario */
--color-gray-700: #333333   /* Texto principal */
--color-gray-800: #2c3e50   /* Texto oscuro */
```

## Uso de Colores por Sección

### 🏠 Páginas Principales

| Página | Hero Section | Cards Principales | Fondo Secciones |
|--------|-------------|-------------------|-----------------|
| **index.php** | Gradiente Secundario (Azul) | Gradiente Primario (Morado) | Blanco/Gris alternado |
| **negocio.php** | Gradiente Secundario (Azul) | Gradiente Primario (Morado) | Blanco/Gris alternado |
| **nosotros.php** | Gradiente Secundario (Azul) | Gradiente Primario (Morado) | Blanco/Gris alternado |
| **login.php** | Gradiente Primario (Morado) | Card Blanco | - |
| **registro.php** | - | Card Blanco con título azul | Gris claro |

### 🎯 Componentes Específicos

#### Hero Sections (Cabeceras de página)
```html
<section class="hero-section">
    <div class="hero-content">
        <h1>Título Principal</h1>
        <p>Descripción</p>
    </div>
</section>
```
**Color**: Gradiente secundario (azul claro) - `#4facfe → #00f2fe`

#### Value Cards (Tarjetas de Valor)
```html
<div class="cards-grid">
    <div class="value-card">
        <div class="value-card-icon">🚀</div>
        <h3>Título</h3>
        <p>Descripción</p>
    </div>
</div>
```
**Color**: Gradiente primario (morado/azul) - `#667eea → #764ba2`

#### Service Cards (Tarjetas de Servicio)
```html
<div class="card-bordered">
    <div class="card-bordered-icon">💳</div>
    <h3>Servicio</h3>
    <p>Descripción</p>
</div>
```
**Color**: Fondo blanco con borde superior primario

## Secciones Alternadas

### Patrón de Diseño
Las páginas alternan entre fondo blanco y gris para crear ritmo visual:

```html
<!-- Sección 1: Blanco -->
<section class="section-white">
    <div class="container">
        <h2 class="section-title">Título</h2>
        <!-- Contenido -->
    </div>
</section>

<!-- Sección 2: Gris -->
<section class="section-gray">
    <div class="container">
        <h2 class="section-title">Título</h2>
        <!-- Contenido -->
    </div>
</section>

<!-- Sección 3: Blanco -->
<section class="section-white">
    <!-- Contenido -->
</section>
```

## Títulos y Textos

### Títulos de Sección
```html
<h2 class="section-title">🎯 Título de Sección</h2>
```
**Color**: `#1a73e8` (Azul Google)  
**Alineación**: Centrado  
**Uso**: Títulos principales de cada sección

### Títulos en Cards
```html
<div class="card-header">
    <h1>📝 Título del Card</h1>
    <p>Descripción</p>
</div>
```
**Color h1-h6**: `#1a73e8` (Azul Google)  
**Descripción**: Gris claro

### Texto Normal
```html
<p class="text-muted">Texto secundario</p>
<p>Texto principal</p>
```
**Color principal**: `#333` (gray-700)  
**Color secundario**: `#666` (gray-500)

## Botones

### Variantes de Botones
```html
<!-- Primario: Gradiente morado/azul -->
<button class="btn btn-primary">Acción Principal</button>

<!-- Secundario: Azul sólido -->
<button class="btn btn-secondary">Acción Secundaria</button>

<!-- Éxito: Verde -->
<button class="btn btn-success">Confirmar</button>

<!-- Peligro: Rojo -->
<button class="btn btn-danger">Eliminar</button>

<!-- Advertencia: Amarillo -->
<button class="btn btn-warning">Advertencia</button>

<!-- Light: Gris claro -->
<button class="btn btn-light">Cancelar</button>
```

## Estados y Badges

### Prioridades
```html
<span class="badge badge-info">Baja</span>      <!-- Cyan -->
<span class="badge badge-warning">Media</span>   <!-- Amarillo -->
<span class="badge badge-danger">Alta</span>     <!-- Rojo -->
<span class="badge badge-danger">Urgente</span>  <!-- Rojo oscuro -->
```

### Estados
```html
<span class="badge badge-warning">Pendiente</span>    <!-- Amarillo -->
<span class="badge badge-info">En Proceso</span>      <!-- Cyan -->
<span class="badge badge-success">Resuelta</span>     <!-- Verde -->
<span class="badge badge-secondary">Cancelada</span>  <!-- Gris -->
```

## Formularios

### Inputs y Campos
```html
<div class="form-group">
    <label class="form-label">Campo</label>
    <input type="text" class="form-input">
</div>
```
**Color border normal**: `#e0e0e0` (gris claro)  
**Color border :focus**: `#667eea` (primario)  
**Color placeholder**: `#999` (gris medio)

### Alertas en Formularios
```html
<div class="alert alert-success">✅ Registro exitoso</div>
<div class="alert alert-danger">❌ Error en el formulario</div>
<div class="alert alert-warning">⚠️ Campos incompletos</div>
<div class="alert alert-info">ℹ️ Información adicional</div>
```

## Navegación

### Header
```css
background: #007bff (Azul estándar)
color: white
```

### Botones de Navegación
```css
/* Estado normal */
background: rgba(255, 255, 255, 0.1)
border: 1px solid rgba(255, 255, 255, 0.3)

/* Estado hover */
background: rgba(255, 255, 255, 0.2)
transform: translateY(-2px)

/* Estado activo */
background: white
color: #4481eb
```

## Consistencia Visual

### ✅ Reglas de Oro

1. **Hero Sections**: Siempre usar gradiente secundario (azul claro)
2. **Value/Service Cards**: Siempre usar gradiente primario (morado/azul)
3. **Títulos de Sección**: Siempre usar `#1a73e8` (azul Google)
4. **Alternar fondos**: Blanco → Gris → Blanco → Gris
5. **Botones principales**: Usar `.btn-primary` (gradiente morado)
6. **Textos de ayuda**: Usar `.text-muted` (gris 500)

### ❌ Evitar

- ❌ Usar colores personalizados inline (`style="color: #xxx"`)
- ❌ Mezclar diferentes gradientes en la misma página
- ❌ Usar más de 3 colores primarios por página
- ❌ Títulos en negro/gris oscuro (usar azul `#1a73e8`)
- ❌ Fondos de sección sin alternar

## Ejemplos de Uso

### Página Típica
```html
<?php include 'includes/header.php'; ?>
<?php include 'includes/nav.php'; ?>

<!-- Hero Section -->
<section class="hero-section">
    <div class="hero-content">
        <h1>🎯 Título Principal</h1>
        <p>Descripción de la página</p>
    </div>
</section>

<!-- Sección 1: Blanco -->
<section class="section-white">
    <div class="container">
        <h2 class="section-title">Título Sección 1</h2>
        <div class="cards-grid">
            <div class="value-card">
                <div class="value-card-icon">🚀</div>
                <h3>Card 1</h3>
                <p>Descripción</p>
            </div>
            <!-- Más cards -->
        </div>
    </div>
</section>

<!-- Sección 2: Gris -->
<section class="section-gray">
    <div class="container">
        <h2 class="section-title">Título Sección 2</h2>
        <!-- Contenido -->
    </div>
</section>

<?php include 'includes/footer.php'; ?>
```

## Archivos Refactorizados

### ✅ Completados (con tema unificado)
- `registro.php` - Formulario con tema consistente
- `negocio.php` - Hero azul + cards morados
- `nosotros.php` - Hero azul + secciones alternadas

### 🔄 Pendientes de refactorizar
- `index.php`
- `login.php`
- `reportar_incidencia.php`
- `mi_perfil.php`
- `mis_pagos.php`
- `avisos.php`
- Archivos en `admin/`

## Migración Rápida

Para actualizar una página al tema unificado:

1. **Hero Section**:
   ```html
   <!-- Antes -->
   <section style="background: linear-gradient(...)">
   
   <!-- Después -->
   <section class="hero-section">
   ```

2. **Títulos**:
   ```html
   <!-- Antes -->
   <h2 style="color: #xxx; text-align: center;">
   
   <!-- Después -->
   <h2 class="section-title">
   ```

3. **Secciones**:
   ```html
   <!-- Antes -->
   <section style="padding: 4rem; background: white;">
   
   <!-- Después -->
   <section class="section-white">
   ```

4. **Cards de Valor**:
   ```html
   <!-- Antes -->
   <div style="background: linear-gradient(...); padding: 2.5rem;">
   
   <!-- Después -->
   <div class="value-card">
   ```

---

**Fecha de creación**: 19 de Noviembre 2025  
**Versión**: 1.0  
**Última actualización**: 19/11/2025
