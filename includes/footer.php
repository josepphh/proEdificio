    </main>
    
    <!-- Footer -->
    <footer style="padding: 3rem 2rem; background: #2c3e50; color: white;">
        <div style="max-width: 1200px; margin: 0 auto;">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 2rem; text-align: center;">
                <div>
                    <h4 style="color: #3498db; margin-bottom: 1rem; font-size: 1.2rem;">📍 Ubicación</h4>
                    <p style="opacity: 0.9;">Lima, Perú</p>
                    <p style="opacity: 0.8; font-size: 0.9rem;">Disponible en todo el país</p>
                </div>
                <div>
                    <h4 style="color: #3498db; margin-bottom: 1rem; font-size: 1.2rem;">📧 Contacto</h4>
                    <p style="opacity: 0.9;">info@edificiosperu.com</p>
                    <p style="opacity: 0.8; font-size: 0.9rem;">Soporte técnico 24/7</p>
                </div>
                <div>
                    <h4 style="color: #3498db; margin-bottom: 1rem; font-size: 1.2rem;">⏰ Horario</h4>
                    <p style="opacity: 0.9;">Lunes a Viernes: 8:00 AM - 6:00 PM</p>
                    <p style="opacity: 0.8; font-size: 0.9rem;">Sistema disponible 24/7</p>
                </div>
            </div>
            <div style="margin-top: 3rem; text-align: center; padding-top: 2rem; border-top: 1px solid rgba(255,255,255,0.1);">
                <p style="opacity: 0.7; font-size: 0.9rem;">© <?php echo date('Y'); ?> Sistema de Gestión de Edificios - Todos los derechos reservados</p>
            </div>
        </div>
    </footer>
    
    <!-- Modal de Confirmación Global -->
    <div id="confirmModal" class="modal">
        <div class="modal-content modal-sm">
            <div class="modal-header">
                <h3 id="confirmTitle">⚠️ Confirmar Acción</h3>
            </div>
            <div class="modal-body">
                <p id="confirmMessage" style="font-size: 1.1rem; line-height: 1.6; color: #333;"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" onclick="closeConfirmModal(false)">Cancelar</button>
                <button type="button" class="btn btn-danger" id="confirmBtn" onclick="closeConfirmModal(true)">Confirmar</button>
            </div>
        </div>
    </div>
    
    <script>
    // Sistema de confirmación personalizado
    var confirmCallback = confirmCallback || null;
    
    function showConfirm(message, title = '⚠️ Confirmar Acción', btnText = 'Confirmar', btnClass = 'btn-danger') {
        return new Promise((resolve) => {
            confirmCallback = resolve;
            document.getElementById('confirmTitle').textContent = title;
            document.getElementById('confirmMessage').textContent = message;
            document.getElementById('confirmBtn').textContent = btnText;
            document.getElementById('confirmBtn').className = 'btn ' + btnClass;
            document.getElementById('confirmModal').style.display = 'block';
        });
    }
    
    function closeConfirmModal(result) {
        document.getElementById('confirmModal').style.display = 'none';
        if (confirmCallback) {
            confirmCallback(result);
            confirmCallback = null;
        }
    }
    
    // Cerrar con ESC
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && document.getElementById('confirmModal').style.display === 'block') {
            closeConfirmModal(false);
        }
    });
    </script>
</body>
</html>