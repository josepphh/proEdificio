<?php
/**
 * Admin Layout Wrapper - Start
 * Incluir al inicio de cada página admin (después de header.php)
 * 
 * Uso:
 * $title = "Título de la Página";
 * $useAdminLayout = true;
 * include '../includes/header.php';
 * include '../includes/admin_layout_start.php';
 */

// Verificar que se haya incluido el header
if (!isset($useAdminLayout) || !$useAdminLayout) {
    die('Error: Este archivo solo debe usarse con $useAdminLayout = true');
}
?>

<!-- Sidebar -->
<?php include __DIR__ . '/sidebar.php'; ?>

<!-- Main Wrapper -->
<div class="main-wrapper">
    <!-- Top Header -->
    <header class="top-header">
        <div class="top-header-left">
            <button class="sidebar-toggle" onclick="toggleSidebar()" aria-label="Toggle Sidebar">
                ☰
            </button>
            <div class="header-info-group">
                <h1 class="top-header-title" id="pageTitle">
                    <?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) : htmlspecialchars($title); ?>
                </h1>
                <div class="user-info-sidebar">
                    <div class="user-avatar">
                        <?php echo strtoupper(substr($_SESSION['usuario_nombre'] ?? 'U', 0, 1)); ?>
                    </div>
                    <div class="user-details">
                        <span class="user-name"><?php echo htmlspecialchars($_SESSION['usuario_nombre'] ?? 'Usuario'); ?></span>
                        <span class="user-role-badge"><?php echo htmlspecialchars($_SESSION['rol_nombre'] ?? 'Usuario'); ?></span>
                    </div>
                </div>
            </div>
        </div>
        <div class="top-header-right">
            <a href="/proyectoEdificio/index.php" 
               class="btn-home-badge" 
               title="Volver a la página principal">
                <span class="badge-icon">🏠</span>
                <span class="badge-label">Ir a Inicio</span>
            </a>
        </div>
    </header>
    
    <!-- Main Content -->
    <main class="main-content">
