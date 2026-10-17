<?php require __DIR__ . '/../partials/admin-header.php'; ?>
<main id="contenido" class="container section">
    <div class="page-heading">
        <span class="eyebrow">ADMINISTRACIÓN</span>
        <h1><?= $tituloPagina ?></h1>
        <p><?= $esEliminacion ? 'Revisa el producto y sus consecuencias antes de confirmar la eliminación.' : 'Completa los datos y revisa la información antes de guardar.' ?></p>
    </div>
    <?php if ($error): ?>
    <div class="notice danger" role="alert">
        <?= e($error) ?>
    </div>
    <?php endif; ?>
    <?php if ($esEliminacion): ?>
    <form class="card confirmation" method="post">
        <?php csrf_field(); ?>
        <input type="hidden" name="id_producto" value="<?= $id ?>">
        <span class="badge danger">Eliminar producto</span>
        <h2>¿Eliminar <?= e($producto['nombre']) ?>?</h2>
        <p class="lead">Dejará de aparecer en el catálogo. Los pedidos anteriores conservarán su información.</p>
        <aside class="callout">
            <h4>Puedes desactivarlo en su lugar</h4>
            <p>Si solo necesitas ocultarlo temporalmente, cambia su estado a Inactivo.</p>
        </aside>
        <div class="actions">
            <a class="button outline" href="productos.php">Volver a productos</a><button class="button red-button">Eliminar producto</button>
        </div>
    </form>
    <?php else: ?>
    <a class="back-link" href="productos.php">← Volver a productos</a>
    <form class="form-grid" method="post" enctype="multipart/form-data">
        <?php csrf_field(); ?>
        <input type="hidden" name="id_producto" value="<?= $id ?>">
        <section class="card stack">
            <h2>Información del producto</h2>
            <label>Nombre *
                <input name="nombre" value="<?= e($valores['nombre']) ?>" placeholder="Nombre del producto" maxlength="120" required>
            </label>
            <label>Categoría *
                <select name="categoria" data-product-category>
                    <?php foreach (categories() as $categoria): ?>
                    <option <?= $valores['categoria'] === $categoria ? 'selected' : '' ?>><?= $categoria ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>Descripción<textarea name="descripcion" rows="3" maxlength="280" placeholder="Describe el sabor, la textura y la presentación."><?= e($valores['descripcion']) ?></textarea><small>Visible en el catálogo.</small></label>
            <label>Precio *
                <input name="precio" type="number" min="0.01" max="99999999.99" step="0.01" value="<?= e($valores['precio']) ?>" placeholder="0,00" required>
                <small>Importe en la moneda del catálogo.</small>
            </label>
            <label>Presentación *
                <input name="presentacion" value="<?= e($valores['presentacion']) ?>" placeholder="Unidad, peso o porciones" maxlength="80" required>
            </label>
            <label>Ingredientes<textarea name="ingredientes" rows="3" maxlength="5000" placeholder="Indica los ingredientes."><?= e($valores['ingredientes']) ?></textarea></label>
            <label>Información de alérgenos
                <input name="alergenos" value="<?= e($valores['alergenos']) ?>" placeholder="Por ejemplo: contiene gluten y leche." maxlength="500">
            </label>
            <label>Estado
                <select name="activo">
                    <option value="1" <?= (int) $valores['activo'] === 1 ? 'selected' : '' ?>>Activo</option>
                    <option value="0" <?= (int) $valores['activo'] === 0 ? 'selected' : '' ?>>Inactivo</option>
                </select>
                <small>Los productos inactivos no aparecen en el catálogo.</small>
            </label>
            <div class="actions">
                <button class="button gold"><?= $esEdicion ? 'Guardar cambios' : 'Agregar producto' ?></button><a class="button outline" href="productos.php">Cancelar</a>
            </div>
        </section>
        <aside class="card image-panel">
            <h2>Imagen del producto</h2>
            <img data-image-preview src="<?= e(product_image($valores)) ?>" alt="Vista previa del producto" width="400" height="245">
            <label class="upload-label button outline full"><?= $esEdicion ? 'Cambiar imagen' : 'Seleccionar imagen' ?>
                <input type="file" name="imagen" accept="image/png,image/jpeg" data-image-input>
            </label>
            <p class="small">JPG o PNG, hasta 4 MB. Elige una imagen clara del producto.</p>
        </aside>
    </form>
    <?php endif; ?>
</main>
<?php require __DIR__ . '/../partials/admin-footer.php'; ?>
