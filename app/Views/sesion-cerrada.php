<?php
require_once __DIR__ . '/../Core/app.php';
$esEquipo = get('tipo') === 'equipo';
$tituloPagina = 'Sesión cerrada';
$seccionActiva = '';
require __DIR__ . '/partials/layout-header.php';
?>
<main id="contenido" class="container section">
    <section class="card empty">
        <span class="badge success">Sesión cerrada</span>
        <h1><?= $esEquipo ? 'Saliste del panel administrativo' : 'Cerraste sesión correctamente' ?></h1>
        <p class="lead"><?= $esEquipo ? 'La sesión del equipo se cerró correctamente. Gracias por cuidar el acceso a Panadería Alas.' : 'Gracias por visitar Panadería Alas. Cuando quieras, podés volver a explorar nuestros productos.' ?></p>
        <div class="actions">
            <?php if ($esEquipo): ?>
            <a class="button gold" href="<?= e(base_url('app/Views/login.php')) ?>">Volver al acceso del equipo</a>
            <a class="button outline" href="<?= e(base_url('public/index.php')) ?>">Ir al sitio</a>
            <?php else: ?>
            <a class="button gold" href="<?= e(base_url('public/index.php')) ?>">Volver al inicio</a>
            <a class="button outline" href="<?= e(base_url('app/Views/cliente-login.php')) ?>">Iniciar sesión de nuevo</a>
            <?php endif; ?>
        </div>
    </section>
</main>
<?php require __DIR__ . '/partials/layout-footer.php'; ?>
