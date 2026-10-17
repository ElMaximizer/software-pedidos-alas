<?php require __DIR__ . '/../partials/layout-header.php'; ?>
<main id="contenido">
    <section class="container section">
        <div class="page-heading">
            <span class="eyebrow">NUESTRA PANADERÍA</span>
            <h1>El sabor que nos reúne.</h1>
            <p>En Alas creemos que los pequeños momentos merecen algo hecho con dedicación.</p>
        </div>
        <div class="about-grid">
            <img class="bakery-picture" src="images/panes.png" alt="Panes, bizcochos y tortas de Panadería Alas" width="664" height="420">
            <div>
                <h2>De nuestras manos<br>a tu mesa.</h2>
                <p class="lead">Un pan para el desayuno, bizcochos para una charla o una torta para celebrar. Queremos acompañarte con sabores que inviten a compartir.</p>
                <p>Nuestra propuesta combina recetas familiares, ingredientes seleccionados y cuidado en cada preparación.</p>
                <a class="button gold" href="productos.php">Explorar productos</a>
            </div>
        </div>
    </section>
    <section class="white-section">
        <div class="container values-grid">
            <?php foreach (['Dedicación' => 'Cuidado en cada paso de la elaboración.', 'Ingredientes' => 'Una selección pensada para lograr un buen sabor.', 'Momentos' => 'Opciones para disfrutar y compartir.'] as $titulo => $texto): ?>
            <article class="card">
                <span class="value-number">0<?= ++$numeroValor ?></span>
                <h3><?= $titulo ?></h3>
                <p><?= $texto ?></p>
            </article>
            <?php endforeach; ?>
        </div>
    </section>
</main>
<?php require __DIR__ . '/../partials/layout-footer.php'; ?>
