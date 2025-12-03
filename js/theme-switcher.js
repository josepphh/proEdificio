/* ============================================
   SISTEMA DE CAMBIO DE TEMAS
   ============================================ */

(function() {
    'use strict';
    
    // Obtener el tema guardado o usar 'default'
    const savedTheme = localStorage.getItem('theme') || 'default';
    
    // Aplicar el tema guardado inmediatamente
    if (savedTheme !== 'default') {
        document.documentElement.setAttribute('data-theme', savedTheme);
    }
    
    // Función para cambiar el tema
    function setTheme(theme) {
        if (theme === 'default') {
            document.documentElement.removeAttribute('data-theme');
        } else {
            document.documentElement.setAttribute('data-theme', theme);
        }
        
        // Guardar la preferencia
        localStorage.setItem('theme', theme);
        
        // Actualizar botones activos
        updateActiveThemeButton(theme);
    }
    
    // Función para actualizar el botón activo
    function updateActiveThemeButton(theme) {
        document.querySelectorAll('.theme-btn').forEach(btn => {
            btn.classList.remove('active');
            if (btn.dataset.theme === theme) {
                btn.classList.add('active');
            }
        });
    }
    
    // Inicializar cuando el DOM esté listo
    function initThemeSwitcher() {
        // Crear el selector de temas
        const themeSwitcher = document.createElement('div');
        themeSwitcher.className = 'theme-switcher';
        themeSwitcher.innerHTML = `
            <button class="theme-btn" data-theme="default" data-tooltip="Tema Original" title="Tema Original">
                🎨
            </button>
            <button class="theme-btn" data-theme="light" data-tooltip="Tema Claro" title="Tema Claro">
                ☀️
            </button>
            <button class="theme-btn" data-theme="dark" data-tooltip="Tema Oscuro" title="Tema Oscuro">
                🌙
            </button>
        `;
        
        // Agregar al body
        document.body.appendChild(themeSwitcher);
        
        // Actualizar botón activo inicial
        updateActiveThemeButton(savedTheme);
        
        // Agregar event listeners
        themeSwitcher.querySelectorAll('.theme-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const theme = this.dataset.theme;
                setTheme(theme);
            });
        });
    }
    
    // Inicializar cuando el DOM esté listo
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initThemeSwitcher);
    } else {
        initThemeSwitcher();
    }
    
    // Exponer función global para cambio programático
    window.setTheme = setTheme;
    
})();
