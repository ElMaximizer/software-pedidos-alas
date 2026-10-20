<?php require_once __DIR__ . '/partials/layout-header.php'; ?>
<main id="contenido" class="container section">
    <a class="back-link" href="<?= e(base_url('app/Views/cliente-login.php')) ?>">← Volver al acceso de clientes</a>
    <div class="auth-grid">
        <section class="auth-story">
            <span class="eyebrow">ACTIVACIÓN DE CUENTA</span>
            <h1>Un paso más<br>y ya está.</h1>
            <p class="lead">Te enviamos un código de seis dígitos a tu correo. Ingresalo para activar tu cuenta.</p>
        </section>
        <section class="card auth-card">
            <span class="eyebrow">VERIFICAR CORREO</span>
            <h1>Activá tu cuenta</h1>
            <?php show_flash(); ?>
            <?php if ($error): ?>
            <div class="notice danger" role="alert"><strong>No pudimos verificar la cuenta</strong><p><?= e($error) ?></p></div>
            <?php endif; ?>
            <form method="post" class="stack">
                <?php csrf_field(); ?>
                <label>Correo electrónico
                    <input type="email" name="email" value="<?= e($emailVerificacion) ?>" maxlength="190" autocomplete="email" required>
                </label>
                <label>Código de verificación
                    <input name="codigo" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" autocomplete="one-time-code" placeholder="123456" required>
                </label>
                <button class="button gold full" name="accion" value="verificar">Verificar y activar</button>
            </form>
            <form method="post" class="stack verification-resend">
                <?php csrf_field(); ?>
                <input type="hidden" name="email" value="<?= e($emailVerificacion) ?>">
                <button class="button outline full" name="accion" value="reenviar">Reenviar código</button>
            </form>
        </section>
    </div>
</main>
<?php require __DIR__ . '/partials/layout-footer.php'; ?>
