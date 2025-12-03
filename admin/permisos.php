<?php
require_once '../includes/session.php';
require_once '../includes/permissions.php';
require_once '../config/database.php';

// Solo Administrador Total puede gestionar permisos
requiereAutenticacion('../login.php');
requierePermiso('asignar_permisos');

$title = "Gestión de Permisos - Sistema de Edificios";
$pageTitle = "🔐 Permisos por Rol";
$useAdminLayout = true;
include '../includes/header.php';
include '../includes/admin_layout_start.php';

$database = new Database();
$conn = $database->getConnection();

// Obtener todos los roles
$sql_roles = "SELECT id, nombre, descripcion, activo FROM roles WHERE activo = 1 ORDER BY id";
$result_roles = $conn->query($sql_roles);

// Obtener todos los permisos agrupados por categoría
$sql_permisos = "SELECT id, codigo, nombre, descripcion, categoria FROM permisos WHERE activo = 1 ORDER BY categoria, nombre";
$result_permisos = $conn->query($sql_permisos);

$permisos_por_categoria = [];
while ($permiso = $result_permisos->fetch_assoc()) {
    $categoria = $permiso['categoria'] ?? 'General';
    if (!isset($permisos_por_categoria[$categoria])) {
        $permisos_por_categoria[$categoria] = [];
    }
    $permisos_por_categoria[$categoria][] = $permiso;
}
?>

<section class="section-white">
    <div class="container">
        <h2 class="section-title">🔐 Gestión de Roles y Permisos</h2>
        <p style="margin-bottom: 2rem; color: #666;">Administra qué permisos tiene cada rol en el sistema</p>

        <!-- Selector de Rol -->
        <div style="margin-bottom: 30px; padding: 20px; background: #f8f9fa; border-radius: 8px;">
            <label for="rolSelect" style="display: block; margin-bottom: 10px; font-weight: 600; font-size: 16px;">
                Seleccionar Rol:
            </label>
            <select id="rolSelect" onchange="cargarPermisosRol(this.value)" style="padding: 10px; font-size: 16px; border-radius: 6px; border: 1px solid #ddd; width: 100%; max-width: 400px;">
                <option value="">-- Seleccione un rol --</option>
                <?php while ($rol = $result_roles->fetch_assoc()): ?>
                    <option value="<?php echo $rol['id']; ?>"><?php echo htmlspecialchars($rol['nombre']); ?></option>
                <?php endwhile; ?>
            </select>
        </div>

        <!-- Información del Rol -->
        <div id="infoRol" style="display: none; margin-bottom: 30px; padding: 20px; background: #e3f2fd; border-radius: 8px; border-left: 4px solid #2196f3;">
            <h3 id="nombreRol" style="margin: 0 0 10px 0; color: #1976d2;"></h3>
            <p id="descripcionRol" style="margin: 0; color: #555;"></p>
            <div style="margin-top: 15px;">
                <span style="font-weight: 600;">Permisos asignados: </span>
                <span id="contadorPermisos" style="color: #2196f3; font-size: 18px; font-weight: bold;">0</span>
            </div>
        </div>

        <!-- Grid de Permisos por Categoría -->
        <div id="permisosContainer" style="display: none;">
            <?php foreach ($permisos_por_categoria as $categoria => $permisos): ?>
            <div class="permisos-categoria" style="margin-bottom: 30px;">
                <h3 style="color: #333; border-bottom: 2px solid #4CAF50; padding-bottom: 10px; margin-bottom: 20px;">
                    📁 <?php echo htmlspecialchars($categoria); ?>
                </h3>
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 15px;">
                    <?php foreach ($permisos as $permiso): ?>
                    <div class="permiso-card" style="background: #fff; border: 2px solid #e0e0e0; border-radius: 8px; padding: 15px; transition: all 0.2s;">
                        <label style="display: flex; align-items: start; cursor: pointer;">
                            <input 
                                type="checkbox" 
                                class="permiso-checkbox" 
                                data-permiso-id="<?php echo $permiso['id']; ?>"
                                data-permiso-codigo="<?php echo htmlspecialchars($permiso['codigo']); ?>"
                                onchange="togglePermiso(this)"
                                style="margin-right: 12px; margin-top: 4px; width: 18px; height: 18px; cursor: pointer;"
                            >
                            <div style="flex: 1;">
                                <div style="font-weight: 600; color: #333; margin-bottom: 5px;">
                                    <?php echo htmlspecialchars($permiso['nombre']); ?>
                                </div>
                                <div style="font-size: 13px; color: #666;">
                                    <?php echo htmlspecialchars($permiso['descripcion']); ?>
                                </div>
                                <div style="font-size: 11px; color: #999; margin-top: 5px; font-family: monospace;">
                                    <?php echo htmlspecialchars($permiso['codigo']); ?>
                                </div>
                            </div>
                        </label>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endforeach; ?>

            <!-- Botón para guardar cambios -->
            <div style="margin-top: 30px; padding: 20px; background: #f8f9fa; border-radius: 8px; text-align: center;">
                <button onclick="guardarCambios()" class="btn btn-primary" style="padding: 12px 40px; font-size: 16px;">
                    💾 Guardar Cambios
                </button>
                <button onclick="resetearPermisos()" class="btn btn-secondary" style="padding: 12px 40px; font-size: 16px; margin-left: 10px;">
                    🔄 Cancelar
                </button>
            </div>
        </div>

        <!-- Mensaje cuando no hay rol seleccionado -->
        <div id="mensajeVacio" style="text-align: center; padding: 60px 20px; color: #999;">
            <div style="font-size: 48px; margin-bottom: 20px;">🔐</div>
            <p style="font-size: 18px;">Selecciona un rol para gestionar sus permisos</p>
        </div>
    </div>
</section>

<style>
    .permiso-card:hover {
        border-color: #4CAF50 !important;
        box-shadow: 0 2px 8px rgba(76, 175, 80, 0.2);
        transform: translateY(-2px);
    }

    .permiso-card input[type="checkbox"]:checked + div {
        color: #4CAF50;
    }

    .permiso-card:has(input[type="checkbox"]:checked) {
        background: #f1f8f4 !important;
        border-color: #4CAF50 !important;
    }

    .btn {
        background-color: #4CAF50;
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: 6px;
        cursor: pointer;
        font-size: 14px;
        transition: all 0.3s;
    }

    .btn:hover {
        background-color: #45a049;
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.2);
    }

    .btn-secondary {
        background-color: #757575;
    }

    .btn-secondary:hover {
        background-color: #616161;
    }

    @keyframes slideIn {
        from {
            transform: translateX(400px);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }

    @keyframes slideOut {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(400px);
            opacity: 0;
        }
    }
</style>

<script>
let rolActual = null;
let permisosOriginales = [];
let cambiosPendientes = false;

// Cargar permisos del rol seleccionado
async function cargarPermisosRol(rolId) {
    if (!rolId) {
        document.getElementById('permisosContainer').style.display = 'none';
        document.getElementById('infoRol').style.display = 'none';
        document.getElementById('mensajeVacio').style.display = 'block';
        return;
    }

    try {
        const response = await fetch(`/proyectoEdificio/api/get_permisos_rol.php?rol_id=${rolId}`);
        const data = await response.json();

        if (data.success) {
            rolActual = rolId;
            permisosOriginales = data.permisos.map(p => p.id);
            
            // Actualizar información del rol
            document.getElementById('nombreRol').textContent = data.rol.nombre;
            document.getElementById('descripcionRol').textContent = data.rol.descripcion;
            document.getElementById('contadorPermisos').textContent = data.permisos.length;
            document.getElementById('infoRol').style.display = 'block';
            document.getElementById('mensajeVacio').style.display = 'none';
            document.getElementById('permisosContainer').style.display = 'block';

            // Marcar checkboxes correspondientes
            document.querySelectorAll('.permiso-checkbox').forEach(checkbox => {
                const permisoId = parseInt(checkbox.dataset.permisoId);
                checkbox.checked = permisosOriginales.includes(permisoId);
            });

            cambiosPendientes = false;
        } else {
            mostrarToast('Error al cargar permisos: ' + data.message, 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        mostrarToast('Error al cargar permisos del rol', 'error');
    }
}

// Toggle individual de permiso
function togglePermiso(checkbox) {
    cambiosPendientes = true;
    actualizarContador();
}

// Actualizar contador de permisos
function actualizarContador() {
    const checkboxes = document.querySelectorAll('.permiso-checkbox:checked');
    document.getElementById('contadorPermisos').textContent = checkboxes.length;
}

// Resetear permisos al estado original
function resetearPermisos() {
    if (cambiosPendientes && !confirm('¿Descartar los cambios realizados?')) {
        return;
    }
    cargarPermisosRol(rolActual);
}

// Guardar cambios
async function guardarCambios() {
    if (!rolActual) {
        mostrarToast('Selecciona un rol primero', 'warning');
        return;
    }

    if (!cambiosPendientes) {
        mostrarToast('No hay cambios para guardar', 'info');
        return;
    }

    const permisosSeleccionados = Array.from(document.querySelectorAll('.permiso-checkbox:checked'))
        .map(cb => parseInt(cb.dataset.permisoId));

    if (permisosSeleccionados.length === 0) {
        if (!confirm('¿Estás seguro de que quieres quitar TODOS los permisos a este rol? Los usuarios con este rol no podrán acceder a ninguna funcionalidad.')) {
            return;
        }
    }

    try {
        const response = await fetch('/proyectoEdificio/api/actualizar_permisos_rol.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                rol_id: rolActual,
                permisos: permisosSeleccionados
            })
        });

        const data = await response.json();

        if (data.success) {
            mostrarToast('✅ Permisos actualizados correctamente', 'success');
            permisosOriginales = permisosSeleccionados;
            cambiosPendientes = false;
        } else {
            mostrarToast('Error: ' + data.message, 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        mostrarToast('Error al guardar cambios', 'error');
    }
}

// Sistema de notificaciones toast
function mostrarToast(mensaje, tipo = 'info') {
    const toast = document.createElement('div');
    toast.className = `toast toast-${tipo}`;
    toast.textContent = mensaje;
    toast.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 15px 25px;
        background: ${tipo === 'success' ? '#4CAF50' : tipo === 'error' ? '#f44336' : tipo === 'warning' ? '#ff9800' : '#2196F3'};
        color: white;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.3);
        z-index: 10000;
        animation: slideIn 0.3s ease;
        font-size: 15px;
    `;
    
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.style.animation = 'slideOut 0.3s ease';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

// Advertir al usuario si intenta salir con cambios sin guardar
window.addEventListener('beforeunload', (e) => {
    if (cambiosPendientes) {
        e.preventDefault();
        e.returnValue = '';
    }
});
</script>

<?php 
include '../includes/admin_layout_end.php';
include '../includes/footer.php'; 
?>
