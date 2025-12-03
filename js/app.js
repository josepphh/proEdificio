// Función para actualizar el botón activo
function updateActiveLink(currentPage) {
    // Remover la clase active de todos los botones
    document.querySelectorAll('.header-links button').forEach(button => {
        button.classList.remove('active');
    });
    
    // Agregar la clase active al botón correspondiente a la página actual
    const activeButton = document.querySelector(`.header-links button[data-href="${currentPage}"]`);
    if (activeButton) {
        activeButton.classList.add('active');
    }
}

// Función para cargar contenido de forma dinámica
function loadContent(page) {
    const mainContent = document.querySelector('main');
    
    // Mostrar un indicador de carga
    mainContent.innerHTML = '<div class="loading">Cargando...</div>';
    
    // Actualizar el enlace activo
    updateActiveLink(page);
    // Realizar la petición AJAX
    fetch(page)
        .then(response => response.text())
        .then(html => {
            // Extraer solo el contenido dentro de main
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            
            // Intentar obtener el contenido de <main>, si no existe, buscar <section>
            const mainElement = doc.querySelector('main');
            const sectionElement = doc.querySelector('section.content-section');
            const content = mainElement ? mainElement.innerHTML : (sectionElement ? sectionElement.outerHTML : html);
            
            // Actualizar el contenido
            mainContent.innerHTML = content;

            // Extraer y aplicar estilos inline (pero solo una vez)
            const styles = doc.querySelectorAll('style');
            styles.forEach(oldStyle => {
                // Crear un identificador único basado en el contenido
                const styleId = 'dynamic-style-' + btoa(oldStyle.textContent.substring(0, 50)).replace(/[^a-zA-Z0-9]/g, '');
                
                // Solo agregar si no existe ya
                if (!document.getElementById(styleId)) {
                    const newStyle = document.createElement('style');
                    newStyle.id = styleId;
                    newStyle.textContent = oldStyle.textContent;
                    document.head.appendChild(newStyle);
                }
            });

            // Extraer y ejecutar solo los scripts inline (no los externos)
            const scripts = doc.querySelectorAll('script');
            scripts.forEach(oldScript => {
                // Solo ejecutar scripts inline, no los externos (src)
                if (!oldScript.src) {
                    const newScript = document.createElement('script');
                    
                    // Copiar el contenido del script
                    newScript.textContent = oldScript.textContent;
                    
                    // Agregar y ejecutar el script
                    document.body.appendChild(newScript);
                    
                    // Remover inmediatamente para evitar duplicados
                    document.body.removeChild(newScript);
                }
            });

            // Actualizar la URL sin recargar la página
            window.history.pushState({}, '', page);
        })
        .catch(error => {
            mainContent.innerHTML = '<div class="error">Error al cargar el contenido</div>';
            console.error('Error:', error);
        });
}

// Manejar la navegación del historial
window.addEventListener('popstate', () => {
    loadContent(window.location.pathname);
});

// Establecer el enlace activo inicial cuando se carga la página
document.addEventListener('DOMContentLoaded', () => {
    updateActiveLink(window.location.pathname);
    
    // Inicializar eventos para el menú móvil
    initMobileMenu();
});

// Función para cerrar sesión
async function cerrarSesion() {
    const confirmado = await showConfirm(
        '¿Estás seguro de que deseas cerrar sesión?',
        '🚪 Cerrar Sesión',
        'Cerrar Sesión',
        'btn-primary'
    );
    if (confirmado) {
        window.location.href = '/proyectoEdificio/cerrar_sesion.php';
    }
}

// Función para toggle del menú móvil
function toggleMobileMenu() {
    const nav = document.getElementById('headerNav');
    const hamburger = document.querySelector('.hamburger-menu');
    
    if (nav && hamburger) {
        nav.classList.toggle('show');
        hamburger.classList.toggle('active');
        
        // Prevenir scroll cuando el menú está abierto
        if (nav.classList.contains('show')) {
            document.body.style.overflow = 'hidden';
        } else {
            document.body.style.overflow = '';
        }
    }
}

// Función para inicializar eventos del menú móvil
function initMobileMenu() {
    const nav = document.getElementById('headerNav');
    const hamburger = document.querySelector('.hamburger-menu');
    
    if (nav && hamburger) {
        // Cerrar menú al hacer clic en un enlace
        nav.querySelectorAll('button').forEach(button => {
            button.addEventListener('click', () => {
                if (window.innerWidth <= 768 && nav.classList.contains('show')) {
                    toggleMobileMenu();
                }
            });
        });
        
        // Cerrar menú al hacer clic fuera de él
        document.addEventListener('click', (e) => {
            if (window.innerWidth <= 768 && 
                nav.classList.contains('show') && 
                !nav.contains(e.target) && 
                !hamburger.contains(e.target)) {
                toggleMobileMenu();
            }
        });
        
        // Cerrar menú al redimensionar ventana (salir de vista móvil)
        window.addEventListener('resize', () => {
            if (window.innerWidth > 768 && nav.classList.contains('show')) {
                nav.classList.remove('show');
                hamburger.classList.remove('active');
                document.body.style.overflow = '';
            }
        });
    }
}

// Agregar estilos para el indicador de carga (solo una vez)
if (!document.getElementById('app-loading-styles')) {
    const style = document.createElement('style');
    style.id = 'app-loading-styles';
    style.textContent = `
        .loading {
            text-align: center;
            padding: 2rem;
            color: #666;
        }
        .error {
            text-align: center;
            padding: 2rem;
            color: #ff0000;
        }
    `;
    document.head.appendChild(style);
}