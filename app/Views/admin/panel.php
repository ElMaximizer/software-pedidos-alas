<?php require __DIR__ . '/../partials/admin-header.php'; ?>
<main id="contenido" class="container section">
    <div class="page-heading">
        <span class="eyebrow">ADMINISTRACIÓN</span>
        <h1>Panel de administración</h1>
        <p>Una vista clara de lo que necesita tu atención hoy.</p>
    </div>
    <?php show_flash(); ?>
    <div class="stats-grid">
        <article class="card">
            <p>Pedidos de hoy</p>
            <strong class="stat-value"><?= (int) $pedidosHoy['n'] ?></strong>
            <p><?= (int) $pedidosHoy['pendientes'] ?> pendientes de preparar</p>
        </article>
        <article class="card">
            <p>Productos activos</p>
            <strong class="stat-value"><?= (int) $productosActivos['n'] ?></strong>
            <p><?= (int) $productosActivos['categorias'] ?> categorías disponibles</p>
        </article>
        <article class="card">
            <p>Por entregar</p>
            <strong class="stat-value"><?= (int) $pedidosPendientes['n'] ?></strong>
            <p>Revisa los horarios de retiro</p>
        </article>
    </div>
    <div class="actions">
        <a class="button gold" href="agregar-pedido.php">+ Nuevo pedido</a><a class="button outline" href="agregar-producto.php">+ Agregar producto</a>
    </div>
    <h2 class="list-title">Próximos retiros</h2>
    <?php require __DIR__ . '/../partials/order-table.php'; ?>
    <a class="text-link list-link" href="pedidos.php">Ver todos los pedidos →</a>
    <section class="operations-links">
        <h2>Atención del equipo</h2>
        <div class="actions">
            <a class="button outline" href="consultas.php">Consultas recibidas (<?= $consultasNuevas ?> nuevas)</a>
            <?php if ($usuarioActual['rol'] === 'administrador'): ?>
            <a class="button outline" href="accesos.php">Solicitudes de acceso (<?= $solicitudesPendientes ?>)</a><a class="button outline" href="correos.php">Bandeja de correo local</a>
            <?php endif; ?>
        </div>
    </section>
</main>
<?php require __DIR__ . '/../partials/admin-footer.php'; ?>
