/**
 * Sidebar JavaScript
 * Sistema de Gestión de Edificios
 * 
 * Funcionalidades:
 * - Toggle sidebar en móviles
 * - Marcar item activo según URL
 * - Cerrar sidebar al hacer clic en overlay
 */

(function() {
    'use strict';
    
    // Toggle sidebar en móviles
    window.toggleSidebar = function() {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebarOverlay');
        const body = document.body;
        
        if (sidebar && overlay) {
            sidebar.classList.toggle('active');
            overlay.classList.toggle('active');
            body.classList.toggle('sidebar-open');
        }
    };
    
    // Marcar item activo según la URL actual
    function setActiveMenuItem() {
        const currentPath = window.location.pathname;
        const sidebarItems = document.querySelectorAll('.sidebar-item');
        
        sidebarItems.forEach(item => {
            const itemHref = item.getAttribute('href');
            
            // Remover clase active de todos
            item.classList.remove('active');
            
            // Agregar clase active al item que coincide con la URL
            if (itemHref && currentPath.includes(itemHref)) {
                item.classList.add('active');
            }
            
            // También verificar por data-page
            const dataPage = item.getAttribute('data-page');
            if (dataPage && currentPath.includes(dataPage)) {
                item.classList.add('active');
            }
        });
    }
    
    // Cerrar sidebar en móvil al hacer clic en un item
    function closeSidebarOnItemClick() {
        const sidebarItems = document.querySelectorAll('.sidebar-item');
        
        sidebarItems.forEach(item => {
            item.addEventListener('click', function() {
                // Solo cerrar en móviles
                if (window.innerWidth <= 768) {
                    const sidebar = document.getElementById('sidebar');
                    const overlay = document.getElementById('sidebarOverlay');
                    const body = document.body;
                    
                    if (sidebar && overlay) {
                        sidebar.classList.remove('active');
                        overlay.classList.remove('active');
                        body.classList.remove('sidebar-open');
                    }
                }
            });
        });
    }
    
    // Inicializar cuando el DOM esté listo
    document.addEventListener('DOMContentLoaded', function() {
        setActiveMenuItem();
        closeSidebarOnItemClick();
        
        // Actualizar item activo cuando cambie la URL (para SPA)
        window.addEventListener('popstate', setActiveMenuItem);
        
        // También actualizar cuando se cargue contenido dinámicamente
        if (typeof window.loadContent === 'function') {
            const originalLoadContent = window.loadContent;
            window.loadContent = function(url) {
                originalLoadContent(url);
                setTimeout(setActiveMenuItem, 100);
            };
        }
    });
    
    // Cerrar sidebar al presionar ESC en móvil
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const sidebar = document.getElementById('sidebar');
            if (sidebar && sidebar.classList.contains('active')) {
                toggleSidebar();
            }
        }
    });
    
})();
