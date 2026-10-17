<?php require __DIR__ . '/../partials/admin-header.php'; ?>
<main id="contenido" class="container section">
    <div class="page-heading">
        <span class="eyebrow">ADMINISTRACIÓN</span>
        <h1>Gestión de pedidos</h1>
        <p>Consulta y actualiza cada pedido desde una sola lista.</p>
    </div>
    <?php show_flash(); ?>
    <a class="button gold" href="agregar-pedido.php">+ Nuevo pedido</a>
    <form class="search-form admin-search" method="get">
        <label>Buscar pedido
            <input name="q" value="<?= e($busqueda) ?>" placeholder="Número o nombre del cliente" maxlength="120">
        </label>
        <label><span class="sr-only">Fecha</span>
            <select name="fecha">
                <option value="">Todas las fechas</option>
                <option value="hoy" <?= $fecha === 'hoy' ? 'selected' : '' ?>>Hoy</option>
                <option value="proximos" <?= $fecha === 'proximos' ? 'selected' : '' ?>>Próximos retiros</option>
            </select>
        </label>
        <input type="hidden" name="estado" value="<?= e($estado) ?>">
        <button class="button outline">Buscar</button>
    </form>
    <nav class="filters" aria-label="Estado del pedido">
        <?php foreach (['' => 'Todos'] + array_combine(states(), states()) as $clave => $etiqueta): ?>
        <a class="button <?= $clave === $estado ? 'navy' : 'outline' ?>" href="?<?= e(http_build_query(['q' => $busqueda, 'fecha' => $fecha, 'estado' => $clave])) ?>"><?= e($etiqueta) ?></a>
        <?php endforeach; ?>
    </nav>
    <?php require __DIR__ . '/../partials/order-table.php'; ?>
    <p class="small">Mostrando <?= count($pedidos) ?> de <?= $total ?> pedidos</p>
    <?php if ($total > $porPagina): ?>
    <nav class="actions" aria-label="Páginas">
        <?php if ($pagina > 1): ?>
        <a class="button outline" href="?<?= e(http_build_query(['q'=>$busqueda,'fecha'=>$fecha,'estado'=>$estado,'pagina'=>$pagina-1])) ?>">← Anterior</a>
        <?php endif; ?>
        <?php if ($pagina * $porPagina < $total): ?>
        <a class="button outline" href="?<?= e(http_build_query(['q'=>$busqueda,'fecha'=>$fecha,'estado'=>$estado,'pagina'=>$pagina+1])) ?>">Siguiente →</a>
        <?php endif; ?>
    </nav>
    <?php endif; ?>
</main>
<?php require __DIR__ . '/../partials/admin-footer.php'; ?>
