<article class="product-card">
    <a class="product-picture" href="<?= e(base_url('public/producto.php?id=' . $producto['id_producto'])) ?>">
    <img src="<?= e(product_image($producto)) ?>" width="400" height="245" alt="<?= e($producto['nombre']) ?>" loading="lazy">
    </a>
    <?php if (!empty($producto['categoria'])): ?>
    <span class="eyebrow"><?= e($producto['categoria']) ?></span>
    <?php endif; ?>
    <h3><a href="<?= e(base_url('public/producto.php?id=' . $producto['id_producto'])) ?>"><?= e($producto['nombre']) ?></a></h3>
    <p><?= e($producto['descripcion']) ?></p>
    <div class="price">
        <?= money($producto['precio']) ?> <?php if (!empty($producto['presentacion'])): ?><small>/ <?= e($producto['presentacion']) ?></small><?php endif; ?>
    </div>
    <a class="button outline full" href="<?= e(base_url('public/producto.php?id=' . $producto['id_producto'])) ?>">Ver producto →</a>
</article>
