<?php require __DIR__ . '/../partials/admin-header.php'; ?>
<main id="contenido" class="container section">
    <div class="page-heading">
        <span class="eyebrow">ADMINISTRACIÓN</span>
        <h1><?= $tituloPagina ?></h1>
        <p><?= $esCancelacion ? 'Revisa el pedido y el motivo antes de confirmar la cancelación.' : 'Completa los datos y revisa la información antes de guardar.' ?></p>
    </div>
    <?php if ($error): ?>
    <div class="notice danger" role="alert">
        <?= e($error) ?>
    </div>
    <?php endif; ?>
    <?php if ($esCancelacion): ?>
    <form class="card confirmation stack" method="post">
        <?php csrf_field(); ?>
        <input type="hidden" name="id_pedido" value="<?= $id ?>">
        <input type="hidden" name="pedido_nonce" value="<?= e($nonce) ?>">
        <input type="hidden" name="pedido_version" value="<?= e($versionFormulario) ?>">
        <span class="badge danger">Cancelar pedido</span>
        <h2>¿Cancelar el pedido #<?= $id ?>?</h2>
        <p class="lead">El pedido dejará de estar pendiente. Su registro se conservará en el historial.</p>
        <aside class="callout">
            <h4>Revisa antes de confirmar</h4>
            <p><?= e($pedido['cliente_nombre']) ?> · <?= e($pedido['producto']) ?> · Total <?= money($pedido['total']) ?></p>
        </aside>
        <?php if (!$soloLectura): ?>
        <label>Motivo de cancelación
            <select name="motivo_cancelacion" required>
                <option value="">Selecciona un motivo</option>
                <?php foreach (motivosCancelacion() as $motivo): ?>
                <option <?= post('motivo_cancelacion') === $motivo ? 'selected' : '' ?>><?= $motivo ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <?php endif; ?>
        <div class="actions">
            <a class="button outline" href="editar-pedido.php?id=<?= $id ?>">Conservar pedido</a>
            <?php if (!$soloLectura): ?>
            <button class="button red-button">Confirmar cancelación</button>
            <?php else: ?>
            <span class="badge neutral"><?= e($pedido['estado']) ?></span>
            <?php endif; ?>
        </div>
    </form>
    <?php else: ?>
    <a class="back-link" href="pedidos.php">← Volver a pedidos</a>
    <?php if ($esEdicion): ?>
    <p><span class="badge <?= badge($pedido['estado']) ?>">#<?= $id ?> · <?= e($pedido['estado']) ?></span></p>
    <?php endif; ?>
    <?php if (!$opcionesProductos): ?>
    <div class="notice neutral">
        Agregá productos al catálogo antes de registrar un pedido.
    </div>
    <?php endif; ?>
    <form class="form-grid" method="post" data-order-form>
        <?php csrf_field(); ?>
        <input type="hidden" name="id_pedido" value="<?= $id ?>">
        <input type="hidden" name="pedido_nonce" value="<?= e($nonce) ?>">
        <input type="hidden" name="pedido_version" value="<?= e($versionFormulario) ?>">
        <section class="card stack">
            <h2>1. Cliente y retiro</h2>
            <fieldset class="stack" <?= $soloLectura ? 'disabled' : '' ?>>
                <label>Nombre del cliente *
                    <input name="cliente_nombre" value="<?= e($valores['cliente_nombre']) ?>" placeholder="Nombre y apellido" maxlength="120" required>
                </label>
                <label>Teléfono *
                    <input name="telefono" type="tel" value="<?= e($valores['telefono']) ?>" placeholder="Número de contacto" maxlength="30" required>
                    <small>Para coordinar el retiro.</small>
                </label>
                <label>Fecha de retiro *
                    <input name="fecha_retiro" type="date" value="<?= e($valores['fecha_retiro']) ?>" <?= !$esEdicion ? 'min="' . date('Y-m-d') . '"' : '' ?> required>
                </label>
                <label>Hora de retiro *
                    <input name="hora_retiro" type="time" value="<?= e(substr($valores['hora_retiro'], 0, 5)) ?>" required>
                </label>
                <hr>
                <h2>2. Productos</h2>
                <div data-order-lines class="stack">
                    <?php foreach ($lineas as $linea): ?>
                    <div class="order-line stack">
                        <label>Producto *
                            <select name="producto_id[]" data-order-product required>
                                <option value="">Selecciona un producto</option>
                                <?php foreach ($opcionesProductos as $opcion): ?>
                                <option
                                    value="<?= e($opcion['id']) ?>"
                                    data-price="<?= e($opcion['precio']) ?>"
                                    data-name="<?= e($opcion['nombre']) ?>"
                                    <?= (string) $linea['id_producto'] === $opcion['id'] ? 'selected' : '' ?>
                                ><?= e($opcion['texto']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </label>
                        <label>Cantidad *
                            <input name="cantidad[]" type="number" min="1" max="9999" value="<?= e($linea['cantidad']) ?>" data-order-quantity required>
                        </label>
                        <?php if (!$soloLectura): ?>
                        <button type="button" class="text-button red remove-line" data-remove-line>Quitar línea</button>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php if (!$soloLectura): ?>
                <button type="button" class="button outline full" data-add-line>+ Agregar otra línea</button>
                <?php endif; ?>
                <label>Notas del pedido<textarea name="notas" rows="4" maxlength="2000" placeholder="Indicaciones para preparar el pedido"><?= e($valores['notas']) ?></textarea><small>Opcional.</small></label>
                <?php if ($esEdicion): ?>
                <label>Estado
                    <select name="estado">
                        <?php foreach (states() as $estado): if ($estado === 'Cancelado' && !$soloLectura) { continue; } ?>
                        <option <?= $valores['estado'] === $estado ? 'selected' : '' ?>><?= $estado ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <?php endif; ?>
            </fieldset>
        </section>
        <aside class="card summary">
            <h2>Resumen</h2>
            <div data-order-summary aria-live="polite">
            </div>
            <hr>
            <h2>Total <span data-order-total><?= money($pedido['total'] ?? 0) ?></span></h2>
            <p data-order-date>Retiro · <?= e($valores['fecha_retiro']) ?>, <?= e(substr($valores['hora_retiro'], 0, 5)) ?></p>
            <?php if (!$soloLectura && $opcionesProductos): ?>
            <button class="button gold full"><?= $esEdicion ? 'Guardar cambios' : 'Registrar pedido' ?></button>
            <?php endif; ?>
            <a class="button outline full" href="pedidos.php">Volver sin guardar</a>
            <?php if ($esEdicion && !$soloLectura): ?>
            <a class="button red-button full" href="eliminar-pedido.php?id=<?= $id ?>">Cancelar pedido</a>
            <?php endif; ?>
            <?php if ($soloLectura): ?>
            <p class="small">Este pedido se conserva como historial. <?= e($pedido['motivo_cancelacion']) ?></p>
            <?php else: ?>
            <p class="small"><?= $esEdicion ? 'Los cambios se aplicarán al pedido #' . $id . '.' : 'El número de pedido se asigna automáticamente.' ?></p>
            <?php endif; ?>
        </aside>
    </form>
    <?php endif; ?>
</main>
<?php require __DIR__ . '/../partials/admin-footer.php'; ?>
