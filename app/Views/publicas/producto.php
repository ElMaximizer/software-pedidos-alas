<?php require __DIR__ . '/../partials/layout-header.php'; ?>
<main id="contenido" class="container section">
    <a class="back-link" href="productos.php">← Volver a productos</a>
    <?php if (!$producto): ?>
    <section class="card empty">
        <h1>Producto no disponible</h1>
        <p>Este producto ya no está en el catálogo. Explorá las opciones disponibles.</p>
        <a class="button gold" href="productos.php">Ver productos</a>
    </section>
    <?php else: ?>
    <div class="detail-grid">
        <img class="detail-picture" src="<?= e(product_image($producto)) ?>" width="400" height="245" alt="<?= e($producto['nombre']) ?>">
        <section>
            <?php if (!empty($producto['categoria'])): ?>
            <span class="badge neutral"><?= e($producto['categoria']) ?></span>
            <?php endif; ?>
            <h1><?= e($producto['nombre']) ?></h1>
            <p class="lead"><?= e($producto['descripcion']) ?></p>
            <p>Elaborado con dedicación para acompañar tus momentos.</p>
            <h2><?= money($producto['precio']) ?><?php if (!empty($producto['presentacion'])): ?> / <?= e($producto['presentacion']) ?><?php endif; ?></h2>
            <hr>
            <h4>Ingredientes</h4>
            <p><?= e(($producto['ingredientes'] ?? '') ?: 'Consultá los ingredientes con el equipo antes de realizar tu pedido.') ?></p>
            <aside class="callout">
                <h4>Información de alérgenos</h4>
                <p><?= e(($producto['alergenos'] ?? '') ?: 'Consultá por alérgenos antes de realizar tu pedido.') ?></p>
            </aside>
            <?php show_flash(); ?>
            <form method="post" action="carrito.php" class="add-cart">
                <?php csrf_field(); ?>
                <input type="hidden" name="accion" value="agregar">
                <input type="hidden" name="id_producto" value="<?= (int) $producto['id_producto'] ?>">
                <label>Cantidad
                    <input type="number" name="cantidad" min="1" max="99" value="1" required>
                </label>
                <button class="button gold">Agregar al carrito</button>
            </form>
            <a class="text-link" href="contacto.php?producto=<?= urlencode($producto['nombre']) ?>">Consultar disponibilidad →</a>
            <p class="small">Te confirmaremos disponibilidad y forma de retiro.</p>
        </section>
    </div>
    <?php endif; ?>
</main>
<?php require __DIR__ . '/../partials/layout-footer.php'; ?>
