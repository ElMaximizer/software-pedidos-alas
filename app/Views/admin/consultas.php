<?php require __DIR__ . '/../partials/admin-header.php'; ?>
<main id="contenido" class="container section">
    <a class="back-link" href="principal.php">← Volver al panel</a>
    <div class="page-heading">
        <h1>Consultas recibidas</h1>
        <p>Mensajes enviados desde el formulario de contacto.</p>
    </div>
    <?php show_flash(); ?>
    <?php if ($error): ?>
    <div class="notice danger">
        <?= e($error) ?>
    </div>
    <?php endif; ?>
    <div class="stack">
        <?php foreach ($mensajes as $mensaje): ?>
        <article class="card">
            <span class="badge <?= $mensaje['leido'] ? 'neutral' : 'success' ?>"><?= $mensaje['leido'] ? 'Leída' : 'Nueva' ?></span>
            <h2><?= e($mensaje['motivo']) ?></h2>
            <p><?= e($mensaje['nombre']) ?> · <a href="mailto:<?= e($mensaje['email']) ?>"><?= e($mensaje['email']) ?></a> · <?= e($mensaje['creado_en']) ?></p>
            <p class="preserve-lines"><?= e($mensaje['mensaje']) ?></p>
            <?php if (!$mensaje['leido']): ?>
            <form method="post">
                <?php csrf_field(); ?>
                <input type="hidden" name="id_mensaje" value="<?= (int) $mensaje['id_mensaje'] ?>">
                <button class="button outline">Marcar como leída</button>
            </form>
            <?php endif; ?>
        </article>
        <?php endforeach; ?>
        <?php if (!$mensajes): ?>
        <div class="card empty">
            <h2>Todavía no hay consultas</h2>
        </div>
        <?php endif; ?>
    </div>
</main>
<?php require __DIR__ . '/../partials/admin-footer.php'; ?>
