<?php require __DIR__ . '/../partials/layout-header.php'; ?>
<main id="contenido" class="container section">
    <a class="back-link" href="productos.php">← Seguir comprando</a>
    <div class="page-heading">
        <h1>Tu carrito</h1>
        <p>Revisa tus productos antes de realizar el pedido.</p>
    </div>
    <?php if ($error): ?>
    <div class="notice danger" role="alert">
        <?= e($error) ?>
    </div>
    <?php endif; ?>
    <?php if (!$articulos): ?>
    <section class="card empty">
        <h2>Tu carrito está vacío</h2>
        <p>Explora el catálogo y encuentra algo rico para compartir.</p>
        <a class="button gold" href="productos.php">Ver productos</a>
    </section>
    <?php else: ?>
    <p><?= count($articulos) ?> productos · <?= array_sum(array_column($articulos, 'cantidad_carrito')) ?> unidades</p>
    <div class="form-grid">
        <div class="stack">
            <?php foreach ($articulos as $articulo): ?>
            <article class="card cart-row">
                <img src="<?= e(product_image($articulo)) ?>" width="100" height="85" alt="<?= e($articulo['nombre']) ?>">
                <div>
                    <span class="eyebrow"><?= e($articulo['categoria']) ?></span>
                    <h3><?= e($articulo['nombre']) ?></h3>
                    <p class="small"><?= money($articulo['precio']) ?> / <?= e($articulo['presentacion']) ?></p>
                    <form method="post">
                        <?php csrf_field(); ?>
                        <input type="hidden" name="accion" value="quitar">
                        <input type="hidden" name="id_producto" value="<?= (int) $articulo['id_producto'] ?>">
                        <button class="text-button red">Quitar</button>
                    </form>
                </div>
                <form class="quantity-form" method="post">
                    <?php csrf_field(); ?>
                    <input type="hidden" name="accion" value="actualizar">
                    <input type="hidden" name="id_producto" value="<?= (int) $articulo['id_producto'] ?>">
                    <label class="sr-only" for="cantidad-<?= (int) $articulo['id_producto'] ?>">Cantidad de <?= e($articulo['nombre']) ?></label>
                    <input id="cantidad-<?= (int) $articulo['id_producto'] ?>" type="number" name="cantidad" min="1" max="99" value="<?= (int) $articulo['cantidad_carrito'] ?>" required>
                    <button class="text-button">Actualizar</button>
                </form>
                <strong><?= money($articulo['precio'] * $articulo['cantidad_carrito']) ?></strong>
            </article>
            <?php endforeach; ?>
            <form method="post">
                <?php csrf_field(); ?>
                <input type="hidden" name="accion" value="vaciar">
                <button class="text-button red">Vaciar carrito</button>
            </form>
        </div>
        <aside class="card summary">
            <h2>Resumen del pedido</h2>
            <div class="summary-line">
                <span>Subtotal (<?= array_sum(array_column($articulos, 'cantidad_carrito')) ?> unidades)</span><strong><?= money($total) ?></strong>
            </div>
            <div class="summary-line">
                <span>Retiro en el local</span><strong>Sin costo</strong>
            </div>
            <hr>
            <div class="summary-line total">
                <strong>Total estimado</strong><strong><?= money($total) ?></strong>
            </div>
            <a class="button gold full" href="realizar-pedido.php">Realizar pedido →</a>
            <p class="small">Completa tus datos y coordinaremos disponibilidad y retiro.</p>
        </aside>
    </div>
    <?php endif; ?>
</main>
<?php require __DIR__ . '/../partials/layout-footer.php'; ?>
