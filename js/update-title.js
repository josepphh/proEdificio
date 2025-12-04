// Script para actualizar el título del header dinámicamente
// Este script se ejecuta después de cargar contenido via AJAX

(function () {
    // Guardar la función original loadContent
    const originalLoadContent = window.loadContent;

    // Sobrescribir loadContent para agregar actualización de título
    window.loadContent = function (page) {
        // Llamar a la función original
        if (originalLoadContent) {
            originalLoadContent(page);
        }

        // Esperar un momento para que el contenido se cargue
        setTimeout(() => {
            const pageTitleElement = document.getElementById('pageTitle');
            if (pageTitleElement) {
                // Buscar el título en el contenido cargado
                const h2Title = document.querySelector('main h2.section-title, section h2.section-title');
                const anyH2 = document.querySelector('main h2:first-of-type, section h2:first-of-type');
                const anyH1 = document.querySelector('main h1:first-of-type, section h1:first-of-type');

                let newTitle = null;
                if (h2Title) {
                    newTitle = h2Title.textContent.trim();
                } else if (anyH2) {
                    newTitle = anyH2.textContent.trim();
                } else if (anyH1) {
                    newTitle = anyH1.textContent.trim();
                }

                if (newTitle) {
                    pageTitleElement.textContent = newTitle;
                    console.log('⚡ Título actualizado:', newTitle);
                } else {
                    console.warn('⚠️ No se encontró título en la página');
                }
            }
        }, 100); // Esperar 100ms para que el DOM se actualice
    };
})();
