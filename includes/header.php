<?php
// No incluir session.php aquí - debe ser incluido por cada página antes de header.php
$title = isset($title) ? $title : "Mi Sitio Web";

// Determinar la ruta base relativa para assets (js, css)
// Contar cuántos niveles de profundidad tiene el script actual
$script_path = $_SERVER['SCRIPT_NAME'];
$depth = substr_count($script_path, '/') - 2; // -2 porque /proyectoEdificio/ no cuenta
$base_path = str_repeat('../', max(0, $depth));
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <title><?php echo $title; ?></title>
    <link rel="stylesheet" href="<?php echo $base_path; ?>assets/css/main.css">
    <link rel="stylesheet" href="<?php echo $base_path; ?>assets/css/themes.css">
    <link rel="stylesheet" href="<?php echo $base_path; ?>css/modal-dark-mode.css?v=2.0">
    <link rel="stylesheet" href="<?php echo $base_path; ?>css/admin-dark-mode.css?v=2.0">
    <?php if (isset($useAdminLayout) && $useAdminLayout): ?>
    <link rel="stylesheet" href="<?php echo $base_path; ?>assets/css/sidebar.css">
    <link rel="stylesheet" href="<?php echo $base_path; ?>assets/css/layout.css">
    <link rel="stylesheet" href="<?php echo $base_path; ?>assets/css/header.css">
    <?php else: ?>
    <link rel="stylesheet" href="<?php echo $base_path; ?>assets/css/navigation.css">
    <?php endif; ?>
</head>
<?php if (isset($useAdminLayout) && $useAdminLayout): ?>
<body class="admin-layout">
<?php else: ?>
<body>
<?php endif; ?>
<script src="<?php echo $base_path; ?>js/app.js" defer></script>
<script src="<?php echo $base_path; ?>js/theme-switcher.js" defer></script>
<?php if (isset($useAdminLayout) && $useAdminLayout): ?>
<script src="<?php echo $base_path; ?>js/sidebar.js" defer></script>
<script src="<?php echo $base_path; ?>js/update-title.js" defer></script>
<?php endif; ?>
