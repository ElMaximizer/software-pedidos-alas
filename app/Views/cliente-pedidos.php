<?php
require_once __DIR__ . '/../Core/app.php';
$clienteActual = current_client();
if (!$clienteActual) {
    redirect('app/Views/cliente-login.php');
}
require_once __DIR__ . '/../Services/pedidos.php';
$pedidosCliente = obtenerPedidosCliente((int) $clienteActual['id_cliente']);
$tituloPagina = 'Mis pedidos';
$seccionActiva = '';
require __DIR__ . '/partials/layout-header.php';
?>
<main id="contenido" class="container section">
    <a class="back-link" href="<?= e(base_url('public/index.php')) ?>">← Volver a la tienda</a>
    <div class="page-heading">
        <span class="eyebrow">TU CUENTA</span>
        <h1>Mis pedidos</h1>
        <p>Hola, <?= e($clienteActual['nombre']) ?>. Acá podés consultar tus compras más recientes.</p>
    </div>
    <?php if (!$pedidosCliente): ?>
    <section class="card empty">
        <h2>Todavía no tenés pedidos</h2>
        <p class="lead">Cuando hagas una compra con tu cuenta iniciada, aparecerá acá con su estado.</p>
        <a class="button gold" href="<?= e(base_url('public/productos.php')) ?>">Ver productos</a>
    </section>
    <?php else: ?>
    <div class="customer-orders-list">
        <?php foreach ($pedidosCliente as $pedido): ?>
        <article class="card customer-order-card">
            <div class="customer-order-heading">
                <div><span class="eyebrow">PEDIDO #<?= (int) $pedido['id_pedido'] ?></span><h2><?= e(date('d/m/Y', strtotime($pedido['creado_en']))) ?></h2></div>
                <span class="badge <?= $pedido['estado'] === 'Cancelado' ? 'danger' : 'success' ?>"><?= e($pedido['estado']) ?></span>
            </div>
            <p><?= e($pedido['productos'] ?: $pedido['producto']) ?></p>
            <div class="customer-order-meta"><span>Retiro: <?= $pedido['fecha_retiro'] ? e(date('d/m/Y', strtotime($pedido['fecha_retiro']))) : 'A coordinar' ?> · <?= e(substr((string) $pedido['hora_retiro'], 0, 5)) ?></span><strong><?= money($pedido['total']) ?></strong></div>
        </article>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</main>
<?php require __DIR__ . '/partials/layout-footer.php'; ?>
