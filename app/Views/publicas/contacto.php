<?php require __DIR__ . '/../partials/layout-header.php'; ?>
<main id="contenido" class="container section">
    <div class="page-heading">
        <span class="eyebrow">CONVERSEMOS</span>
        <h1>¿Qué te gustaría compartir?</h1>
        <p>Consulta por productos, disponibilidad o preparaciones especiales.</p>
    </div>
    <div class="contact-grid">
        <form class="card stack" method="post">
            <?php csrf_field(); ?>
            <?php if ($error): ?>
            <div class="notice danger" role="alert">
                <?= e($error) ?>
            </div>
            <?php endif; ?>
            <label>Nombre
                <input name="nombre" placeholder="Tu nombre" value="<?= e(post('nombre')) ?>" maxlength="120" required autocomplete="name">
            </label>
            <label>Correo electrónico
                <input type="email" name="email" value="<?= e(post('email')) ?>" placeholder="nombre@ejemplo.com" maxlength="190" autocomplete="email" required>
                <small>Usaremos este correo para responder tu consulta.</small>
            </label>
            <label>Motivo
                <select name="motivo">
                    <?php foreach (motivosContacto() as $motivo): ?>
                    <option <?= post('motivo') === $motivo ? 'selected' : '' ?>><?= $motivo ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>Mensaje<textarea name="mensaje" rows="5" placeholder="Cuéntanos qué necesitas..." maxlength="5000" required><?= e(post('mensaje', get('producto') ? 'Consulta por ' . get('producto') . ': ' : '')) ?></textarea><small>Si es para un evento, incluye fecha y cantidad de personas.</small></label>
            <button class="button gold full">Enviar consulta</button><small>Al enviar, tus datos se utilizarán únicamente para responder esta consulta.</small>
        </form>
        <aside class="card navy-panel">
            <h2>Estamos para ayudarte.</h2>
            <p class="lead">Productos y disponibilidad<br>Pedidos para compartir<br>Tortas y celebraciones</p>
            <hr>
            <p>¿Ya sabes qué te gustaría pedir?</p>
            <a class="button gold" href="productos.php">Ver el catálogo</a>
        </aside>
    </div>
</main>
<?php require __DIR__ . '/../partials/layout-footer.php'; ?>
