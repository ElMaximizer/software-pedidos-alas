<div class="order-table">
    <div class="order-table-head">
        <span>Pedido / cliente</span><span>Retiro</span><span>Productos</span><span>Total</span><span>Estado</span><span></span>
    </div>
    <?php foreach ($pedidos as $pedido): ?>
    <article class="order-row">
        <div>
            <strong>#<?= (int) $pedido['id_pedido'] ?></strong><small><?= e($pedido['cliente_nombre']) ?></small>
        </div>
        <div>
            <span><?= e(substr($pedido['hora_retiro'], 0, 5)) ?></span><small><?= e(date('d/m', strtotime($pedido['fecha_retiro']))) ?></small>
        </div>
        <span><?= e($pedido['producto']) ?></span><strong><?= money($pedido['total']) ?></strong><span><span class="badge <?= badge($pedido['estado']) ?>"><?= e($pedido['estado']) ?></span></span><a class="button outline" href="<?= e(base_url('app/Views/editar-pedido.php?id=' . $pedido['id_pedido'])) ?>">Ver</a>
    </article>
    <?php endforeach; ?>
    <?php if (!$pedidos): ?>
    <div class="card empty">
        <h2>No hay pedidos para mostrar</h2>
        <p>Probá con otra fecha o registrá un nuevo pedido.</p>
        <a class="button gold" href="<?= e(base_url('app/Views/agregar-pedido.php')) ?>">+ Nuevo pedido</a>
    </div>
    <?php endif; ?>
</div>
