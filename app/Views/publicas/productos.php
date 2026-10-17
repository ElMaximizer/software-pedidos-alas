<?php require __DIR__ . '/../partials/layout-header.php'; ?>
<main id="contenido" class="container section">
    <div class="page-heading">
        <span class="eyebrow">NUESTRO CATÁLOGO</span>
        <h1>Hecho con dedicación.</h1>
        <p>Panes, bizcochos y tortas para acompañar tus momentos.</p>
    </div>
    <form class="search-form" method="get">
        <label>Buscar productos
            <input name="q" value="<?= e($busqueda) ?>" placeholder="Nombre o ingrediente" maxlength="120">
        </label>
        <input type="hidden" name="categoria" value="<?= e($categoria) ?>">
        <button class="button navy">Buscar</button>
    </form>
    <nav class="filters" aria-label="Categorías">
        <?php foreach (['' => 'Todos'] + array_combine(categories(), categories()) as $valor => $etiqueta): ?>
        <a class="button <?= $categoria === $valor ? 'navy' : 'outline' ?>" <?= $categoria === $valor ? 'aria-current="true"' : '' ?> href="?<?= e(http_build_query(['q' => $busqueda, 'categoria' => $valor])) ?>"><?= e($etiqueta) ?></a>
        <?php endforeach; ?>
    </nav>
    <?php show_flash(); ?>
    <?php if (!$productos): ?>
    <section class="card empty">
        <h2>No encontramos ese producto</h2>
        <p>Prueba con otra palabra o vuelve a ver todas las categorías.</p>
        <a class="button gold" href="productos.php">Ver todos los productos</a>
    </section>
    <?php else: ?>
    <p class="results-count"><?= count($productos) ?> productos · Ordenar: destacados</p>
    <div class="product-grid">
        <?php foreach ($productos as $producto) { require __DIR__ . '/../partials/product-card.php'; } ?>
    </div>
    <?php endif; ?>
    <aside class="callout">
        <h3>¿Buscas algo para una ocasión especial?</h3>
        <p>Escríbenos y cuéntanos qué tienes en mente.</p>
    </aside>
    <a class="button outline" href="contacto.php">Hacer una consulta</a>
</main>
<?php require __DIR__ . '/../partials/layout-footer.php'; ?>
