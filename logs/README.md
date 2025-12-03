# Directorio de Logs del Sistema

Este directorio contiene los archivos de registro del sistema.

## Archivos de Log

- **app.log**: Log general de aplicación (INFO, DEBUG, WARNING)
- **errors.log**: Log de errores y errores críticos
- **security.log**: Log de eventos de seguridad (intentos de login, accesos no autorizados, etc.)

## Rotación de Logs

Los archivos de log se rotan automáticamente cuando alcanzan 10MB.
Los archivos antiguos se comprimen con extensión .gz

## Limpieza

Para limpiar logs antiguos (más de 30 días), ejecutar:
```php
php scripts/limpiar_logs.php
```

## Formato de Log

```
[DD/MM/YYYY HH:MM:SS] [NIVEL] Mensaje | contexto=valor, key=value
```

## Niveles de Log

- **DEBUG**: Información detallada para diagnóstico
- **INFO**: Eventos informativos generales
- **WARNING**: Advertencias que no impiden el funcionamiento
- **ERROR**: Errores que deben ser atendidos
- **CRITICAL**: Errores críticos que requieren atención inmediata
- **SECURITY**: Eventos relacionados con seguridad

## Seguridad

⚠️ **IMPORTANTE**: Este directorio NO debe ser accesible públicamente.
Agregar reglas de .htaccess para denegar acceso directo.
