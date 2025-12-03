<body class="<?php echo (strpos($_SERVER['SCRIPT_NAME'], 'login.php') !== false) ? 'login-page' : ''; ?>">
    <header>
        <h1>Bienvenidos a Mi Sitio</h1>
        <button class="hamburger-menu" onclick="toggleMobileMenu()" aria-label="Menú">
            ☰
        </button>
        <div class="user-info">
            <?php if(isset($_SESSION['usuario_nombre'])): ?>
                <span class="welcome-text">
                    Bienvenido, <?php echo htmlspecialchars($_SESSION['usuario_nombre']); ?>
                    <?php if(isset($_SESSION['rol_nombre'])): ?>
                        <span class="user-role">(<?php echo htmlspecialchars($_SESSION['rol_nombre']); ?>)</span>
                    <?php endif; ?>
                    <?php if(isset($_SESSION['edificio_nombre'])): ?>
                        <span class="user-building">- <?php echo htmlspecialchars($_SESSION['edificio_nombre']); ?></span>
                    <?php endif; ?>
                </span>
                <button onclick="cerrarSesion();" class="logout-btn">Cerrar Sesión</button>
            <?php else: ?>
                <span class="welcome-text">No hay sesión activa</span>
            <?php endif; ?>
        </div>
        <nav class="header-links" id="headerNav">
            <button data-href="/proyectoEdificio/index.php" onclick="loadContent(this.dataset.href);">Inicio</button>
            <button data-href="/proyectoEdificio/negocio.php" onclick="loadContent(this.dataset.href);">Negocio</button>
            <button data-href="/proyectoEdificio/nosotros.php" onclick="loadContent(this.dataset.href);">Nosotros</button>
            <?php if(!isset($_SESSION['usuario_nombre'])): ?>
                <button data-href="/proyectoEdificio/login.php" onclick="loadContent(this.dataset.href);">Iniciar Sesión</button>
            <?php else: ?>
                <?php if(isset($_SESSION['rol_nombre']) && $_SESSION['rol_nombre'] === 'Administrador Total'): ?>
                    <button data-href="/proyectoEdificio/admin/panel.php" onclick="loadContent(this.dataset.href);">Panel Admin</button>
                    <button data-href="/proyectoEdificio/admin/solicitudes.php" onclick="loadContent(this.dataset.href);">📬 Solicitudes</button>
                    <button data-href="/proyectoEdificio/cambiar_password.php" onclick="loadContent(this.dataset.href);">🔒 Contraseña</button>
                <?php elseif(isset($_SESSION['rol_nombre']) && $_SESSION['rol_nombre'] === 'Administrador Edificio'): ?>
                    <button data-href="/proyectoEdificio/admin/panel.php" onclick="loadContent(this.dataset.href);">Panel Admin</button>
                    <button data-href="/proyectoEdificio/cambiar_password.php" onclick="loadContent(this.dataset.href);">🔒 Contraseña</button>
                <?php elseif(isset($_SESSION['rol_nombre']) && $_SESSION['rol_nombre'] === 'Inquilino'): ?>
                    <button data-href="/proyectoEdificio/mi_perfil.php" onclick="loadContent(this.dataset.href);">Mi Perfil</button>
                    <button data-href="/proyectoEdificio/mis_pagos.php" onclick="loadContent(this.dataset.href);">Mis Pagos</button>
                    <button data-href="/proyectoEdificio/reportar_incidencia.php" onclick="loadContent(this.dataset.href);">Reportar</button>
                    <button data-href="/proyectoEdificio/avisos.php" onclick="loadContent(this.dataset.href);">Avisos</button>
                    <button data-href="/proyectoEdificio/cambiar_password.php" onclick="loadContent(this.dataset.href);">🔒 Contraseña</button>
                <?php endif; ?>
            <?php endif; ?>
        </nav>
    </header>
    <main>