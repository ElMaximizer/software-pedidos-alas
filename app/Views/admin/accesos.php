<?php require __DIR__ . '/../partials/admin-header.php'; ?>
<main id="contenido" class="container section">
    <a class="back-link" href="principal.php">← Volver al panel</a>
    <div class="page-heading">
        <h1>Solicitudes de acceso</h1>
        <p>Como dueño, aprobá o rechazá solicitudes y elegí el rol con el que ingresará cada persona.</p>
    </div>
    <?php show_flash(); ?>
    <?php if ($error): ?>
    <div class="notice danger" role="alert">
        <?= e($error) ?>
    </div>
    <?php endif; ?>
    <div class="stack">
        <?php foreach ($solicitudes as $solicitud): ?>
        <article class="card access-row">
            <div>
                <h3><?= e($solicitud['nombre'] ?: 'Usuario existente') ?></h3>
                <p><?= e($solicitud['cedula']) ?> · <?= e($solicitud['email']) ?></p>
                <span class="badge <?= badge($solicitud['estado']) ?>"><?= e($solicitud['estado']) ?></span>
            </div>
            <?php if ($solicitud['estado'] === 'pendiente'): ?>
            <form class="actions" method="post">
                <?php csrf_field(); ?>
                <input type="hidden" name="id_usuario" value="<?= (int) $solicitud['id_sol'] ?>">
                <label>Al aprobar<select name="rol"><option value="empleado">Empleado</option><option value="administrador">Administrador</option></select></label>
                <button name="accion" value="aprobar" class="button gold">Aprobar acceso</button><button name="accion" value="rechazar" class="button red-button">Rechazar</button>
            </form>
            <?php endif; ?>
        </article>
        <?php endforeach; ?>
    </div>
</main>
<?php require __DIR__ . '/../partials/admin-footer.php'; ?>

