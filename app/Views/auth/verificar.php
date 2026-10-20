<?php require __DIR__ . '/../partials/layout-header.php'; ?>
<main id="contenido" class="container section">
    <a class="back-link" href="<?= e(base_url('public/index.php')) ?>">← Volver al sitio</a>
    <section class="card auth-card">
        <span class="eyebrow">ACCESO DEL EQUIPO</span>
        <h1>Verificá tu correo</h1>
        <p>Enviamos un código de seis dígitos a <strong><?= e($registro['correo']) ?></strong>.</p>
        <p>Revisá también spam. El código vence en diez minutos y debés ingresarlo en esta misma ventana.</p>
        <p>Al verificar el correo se creará tu solicitud. El administrador deberá aprobarla para que puedas iniciar sesión.</p>
        <?php show_flash(); ?>
        <?php if ($error): ?>
        <div class="notice danger" role="alert"><p><?= e($error) ?></p></div>
        <?php endif; ?>
        <form method="post" class="stack">
            <?php csrf_field(); ?>
            <input type="hidden" name="accion" value="confirmar">
            <input type="hidden" name="registro_id" value="<?= e($registro['id']) ?>">
            <label>Código de verificación
                <input name="codigo" inputmode="numeric" autocomplete="one-time-code"
                    pattern="[0-9]{6}" minlength="6" maxlength="6" placeholder="123456" required>
            </label>
            <button class="button gold full">Verificar y crear solicitud</button>
        </form>
        <hr>
        <form method="post" class="stack">
            <?php csrf_field(); ?>
            <input type="hidden" name="registro_id" value="<?= e($registro['id']) ?>">
            <button name="accion" value="reenviar" class="button outline full">Reenviar código</button>
            <small>Podés solicitar otro código después de un minuto. Solo sirve el último enviado.</small>
            <button name="accion" value="cancelar" class="text-button">Cambiar mis datos o cancelar</button>
        </form>
    </section>
</main>
<?php require __DIR__ . '/../partials/layout-footer.php'; ?>
