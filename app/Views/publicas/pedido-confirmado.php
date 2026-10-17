<?php require __DIR__ . '/../partials/layout-header.php'; ?>
<main id="contenido" class="container section">
    <section class="card empty">
        <span class="badge success">Pedido registrado</span>
        <h1>¡Gracias por tu pedido!</h1>
        <p class="lead">Tu número es <strong>#<?= (int) $_SESSION['pedido_confirmado'] ?></strong>. El equipo se comunicará para confirmar disponibilidad y retiro.</p>
        <div class="actions">
            <a class="button gold" href="productos.php">Seguir explorando</a><a class="button outline" href="index.php">Volver al inicio</a>
        </div>
    </section>
</main>
<?php require __DIR__ . '/../partials/layout-footer.php'; ?>
