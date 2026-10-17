<?php require __DIR__ . '/../partials/admin-header.php'; ?>
<main id="contenido" class="container section">
    <a class="back-link" href="principal.php">← Volver al panel</a>
    <div class="page-heading">
        <h1>Bandeja de correo local</h1>
        <p>Entorno de desarrollo: los mensajes se guardan aquí y no se envían por Internet.</p>
    </div>
    <div class="stack">
        <?php foreach ($correos as $correo): ?>
        <article class="card">
            <h2><?= e($correo['asunto']) ?></h2>
            <p>Para <?= e($correo['destinatario']) ?> · <?= e($correo['creado_en']) ?></p>
            <p class="preserve-lines"><?= e($correo['cuerpo']) ?></p>
            <?php if (preg_match('~(/[^\s]+/app/Views/restablecer\.php\?token=[a-f0-9]{64})~', $correo['cuerpo'], $link)): ?>
            <a class="button outline" href="<?= e($link[1]) ?>">Abrir enlace de recuperación</a>
            <?php endif; ?>
        </article>
        <?php endforeach; ?>
        <?php if (!$correos): ?>
        <section class="card empty">
            <h2>No hay mensajes todavía</h2>
            <p>Las solicitudes de acceso y recuperación generan mensajes de prueba aquí.</p>
        </section>
        <?php endif; ?>
    </div>
</main>
<?php require __DIR__ . '/../partials/admin-footer.php'; ?>
