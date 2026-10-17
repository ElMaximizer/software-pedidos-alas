<?php
require_once __DIR__ . '/../../Core/app.php';
$esAdministracion = $esAdministracion ?? false;
$seccionActiva = $seccionActiva ?? '';
$tituloPagina = $tituloPagina ?? 'Panadería Alas';
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="Panes, bizcochos y tortas elaborados con dedicación en Panadería Alas.">
        <title><?= e($tituloPagina) ?> | Panadería Alas</title> <link rel="stylesheet" href="<?= e(base_url('public/css/alas.css')) ?>?v=<?= filemtime(__DIR__ . '/../../../public/css/alas.css') ?>">
        <script src="<?= e(base_url('public/js/alas.js')) ?>" defer></script>
    </head>
    <body class="<?= $esAdministracion ? 'admin-page' : 'public-page' ?>">
        <a class="skip-link" href="#contenido">Ir al contenido</a>
        <header class="site-header">
            <div class="container header-inner">
                <a class="brand" href="<?= e(base_url($esAdministracion ? 'app/Views/principal.php' : 'public/index.php')) ?>"><strong>PANADERÍA ALAS</strong><span><?= $esAdministracion ? 'GESTIÓN DE PRODUCTOS Y PEDIDOS' : 'PANADERÍA ARTESANAL' ?></span></a>
                <?php if ($esAdministracion): ?>
                <form action="<?= e(base_url('app/Controllers/logout.php')) ?>" method="post">
                    <?php csrf_field(); ?>
                    <button class="button gold exit-button">Salir</button>
                </form>
                <?php else: ?>
                <nav class="public-nav" aria-label="Navegación principal">
                    <?php foreach (['inicio' => ['Inicio', 'index.php'], 'productos' => ['Productos', 'productos.php'], 'nosotros' => ['Nosotros', 'nosotros.php'], 'contacto' => ['Contacto', 'contacto.php']] as $clave => [$etiqueta, $ruta]): ?>
                    <a <?= $seccionActiva === $clave ? 'aria-current="page"' : '' ?> href="<?= e(base_url('public/' . $ruta)) ?>"><?= e($etiqueta) ?></a>
                    <?php endforeach; ?>
                    <a class="cart-link" href="<?= e(base_url('public/carrito.php')) ?>">
                    <img src="<?= e(base_url('public/images/carrito.svg')) ?>" width="20" height="20" alt="">
                    Carrito <span class="cart-count"><?= array_sum($_SESSION['carrito'] ?? []) ?></span></a>
                </nav>
                <a class="button gold access-button" href="<?= e(base_url('app/Views/login.php')) ?>">Acceso</a>
                <?php endif; ?>
            </div>
        </header>
        <?php if ($esAdministracion): ?>
        <nav class="admin-nav" aria-label="Administración">
            <div class="container">
                <?php foreach (['resumen' => ['Resumen', 'principal.php'], 'productos' => ['Productos', 'productos.php'], 'pedidos' => ['Pedidos', 'pedidos.php']] as $clave => [$etiqueta, $ruta]): ?>
                <a class="button <?= $seccionActiva === $clave ? 'navy' : 'outline' ?>" <?= $seccionActiva === $clave ? 'aria-current="page"' : '' ?> href="<?= e(base_url('app/Views/' . $ruta)) ?>"><?= $etiqueta ?></a>
                <?php endforeach; ?>
            </div>
        </nav>
        <?php endif; ?>
