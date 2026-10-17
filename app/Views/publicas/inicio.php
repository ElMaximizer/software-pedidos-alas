<?php require __DIR__ . '/../partials/layout-header.php'; ?>
<main id="contenido">
    <section class="hero">
        <div class="container hero-grid">
            <div>
                <span class="eyebrow">HORNEADO FRESCO CADA DÍA</span>
                <h1>El sabor de siempre,<br> hecho para compartir.</h1>
                <p class="lead">Panes, bizcochos y tortas elaborados con dedicación. Encuentra tu próximo favorito en Panadería Alas.</p>
                <div class="actions">
                    <a class="button gold" href="productos.php">Ver productos</a><a class="button outline" href="nosotros.php">Conocer la panadería</a>
                </div>
                <p class="eyebrow hero-trust">PANES · BIZCOCHOS · TORTAS</p>
            </div>
            <img class="bakery-picture" src="images/panes.png" alt="Panes, bizcochos y tortas artesanales" width="664" height="398">
        </div>
    </section>
    <section class="white-section">
        <div class="container">
            <div class="page-heading">
                <span class="eyebrow">ELIGE TU MOMENTO</span>
                <h2>Algo rico para cada día.</h2>
                <p>Explora nuestra selección artesanal.</p>
            </div>
            <div class="category-grid">
                <?php foreach ($categoriasInicio as [$categoria, $etiqueta, $descripcion]): ?>
                <a class="category-card" href="productos.php?categoria=<?= urlencode($categoria) ?>"><span class="eyebrow"><?= $etiqueta ?></span>
                <h3><?= $categoria ?></h3>
                <p><?= $descripcion ?></p>
                <strong>Explorar →</strong></a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <section class="container section">
        <div class="page-heading">
            <span class="eyebrow">HECHOS PARA DISFRUTAR</span>
            <h2>Los favoritos de la casa</h2>
            <p>Una selección para empezar.</p>
        </div>
        <div class="product-grid">
            <?php foreach ($productos as $producto): ?>
                <?php require __DIR__ . '/../partials/product-card.php'; ?>
            <?php endforeach; ?>
        </div>
    </section>
</main>
<?php require __DIR__ . '/../partials/layout-footer.php'; ?>
