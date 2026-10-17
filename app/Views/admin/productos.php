<?php require __DIR__ . '/../partials/admin-header.php'; ?>
<main id="contenido" class="container section">
    <div class="page-heading">
        <span class="eyebrow">ADMINISTRACIÓN</span>
        <h1>Gestión de productos</h1>
        <p>Organiza el catálogo y controla qué productos están disponibles.</p>
    </div>
    <?php show_flash(); ?>
    <a class="button gold" href="agregar-producto.php">+ Agregar producto</a>
    <form class="search-form admin-search" method="get">
        <label>Buscar
            <input name="q" value="<?= e($busqueda) ?>" placeholder="Nombre o código de producto" maxlength="120">
        </label>
        <label><span class="sr-only">Estado</span>
            <select name="estado">
                <option value="">Todos los estados</option>
                <option value="1" <?= $estado === '1' ? 'selected' : '' ?>>Activos</option>
                <option value="0" <?= $estado === '0' ? 'selected' : '' ?>>Inactivos</option>
            </select>
        </label>
        <button class="button outline">Buscar</button>
    </form>
    <p class="results-count"><?= count($productos) ?> productos · Catálogo</p>
    <div class="stack">
        <?php foreach ($productos as $producto): ?>
        <article class="card product-row">
            <div>
                <h3><?= e($producto['nombre']) ?></h3>
                <small>PRD-<?= str_pad((string) $producto['id_producto'], 3, '0', STR_PAD_LEFT) ?> · <?= e($producto['categoria']) ?></small>
            </div>
            <strong><?= money($producto['precio']) ?></strong><span><span class="badge <?= $producto['activo'] ? 'success' : 'neutral' ?>"><?= $producto['activo'] ? 'Activo' : 'Inactivo' ?></span></span><a class="button outline" href="editar-producto.php?id=<?= (int) $producto['id_producto'] ?>">Editar</a><a class="button red-button" href="eliminar-producto.php?id=<?= (int) $producto['id_producto'] ?>">Eliminar</a>
        </article>
        <?php endforeach; ?>
        <?php if (!$productos): ?>
        <section class="card empty">
            <h2>No encontramos productos</h2>
            <p>Probá con otro nombre o cambiá el filtro de estado.</p>
            <a class="button gold" href="productos.php">Ver todos los productos</a>
        </section>
        <?php endif; ?>
    </div>
</main>
<?php require __DIR__ . '/../partials/admin-footer.php'; ?>
