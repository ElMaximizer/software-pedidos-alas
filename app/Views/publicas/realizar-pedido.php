<?php require __DIR__ . '/../partials/layout-header.php'; ?>
<main id="contenido" class="container section">
    <a class="back-link" href="carrito.php">← Volver al carrito</a>
    <div class="page-heading">
        <h1>Realizar pedido</h1>
        <p>Completa tus datos para coordinar el retiro.</p>
    </div>
    <form method="post" class="form-grid">
        <?php csrf_field(); ?>
        <input type="hidden" name="pedido_nonce" value="<?= e($nonce) ?>">
        <div class="card stack">
            <h2>Cliente y retiro</h2>
            <?php if ($error): ?>
            <div class="notice danger" role="alert">
                <?= e($error) ?>
            </div>
            <?php endif; ?>
            <label>Nombre del cliente *
                <input name="cliente_nombre" value="<?= e(post('cliente_nombre')) ?>" maxlength="120" required autocomplete="name">
            </label>
            <label>Teléfono *
                <input type="tel" name="telefono" value="<?= e(post('telefono')) ?>" maxlength="30" required autocomplete="tel">
            </label>
            <label>Fecha de retiro *
                <input type="date" name="fecha_retiro" value="<?= e(post('fecha_retiro', date('Y-m-d', strtotime('+1 day')))) ?>" min="<?= date('Y-m-d') ?>" required>
            </label>
            <label>Hora de retiro *
                <input type="time" name="hora_retiro" value="<?= e(post('hora_retiro', '10:00')) ?>" required>
            </label>
            <label>Notas del pedido<textarea name="notas" rows="4" maxlength="2000"><?= e(post('notas')) ?></textarea></label>
        </div>
        <aside class="card summary">
            <h2>Resumen</h2>
            <?php foreach ($articulos as $articulo): ?>
            <div class="summary-line">
                <span><?= e($articulo['nombre']) ?> × <?= (int) $articulo['cantidad_carrito'] ?></span><strong><?= money($articulo['precio'] * $articulo['cantidad_carrito']) ?></strong>
            </div>
            <?php endforeach; ?>
            <hr>
            <h2>Total <?= money($total) ?></h2>
            <?php if ($puedeConfirmar): ?>
            <button class="button gold full">Confirmar pedido</button>
            <?php else: ?>
            <a class="button gold full" href="carrito.php">Revisar carrito</a>
            <?php endif; ?>
            <p class="small">El pedido queda pendiente de confirmación por el equipo. No se realiza ningún cobro en línea.</p>
        </aside>
    </form>
</main>
<?php require __DIR__ . '/../partials/layout-footer.php'; ?>
