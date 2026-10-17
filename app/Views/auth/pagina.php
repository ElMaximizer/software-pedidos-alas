<?php require __DIR__ . '/../partials/layout-header.php'; ?>
<main id="contenido" class="container section">
    <a class="back-link" href="<?= e(base_url('public/index.php')) ?>">← Volver al sitio</a>
    <div class="auth-grid">
        <section class="auth-story">
            <span class="eyebrow">TODO EN SU LUGAR</span>
            <h1>Más tiempo para<br>lo que hacemos bien.</h1>
            <p class="lead">Gestiona productos y pedidos desde un solo lugar.</p>
            <img class="bakery-picture" src="<?= e(base_url('public/images/panes.png')) ?>" width="648" height="350" alt="Elaboraciones artesanales de Panadería Alas">
        </section>
        <section class="card auth-card">
            <?php if ($modo === 'sent'): ?>
            <span class="badge success">Solicitud recibida</span>
            <?php endif; ?>
            <span class="eyebrow"><?= $modo === 'recover' || $modo === 'reset' ? 'RECUPERAR ACCESO' : 'ACCESO DEL EQUIPO' ?></span>
            <h1><?= e($tituloPagina) ?></h1>
            <?php show_flash(); ?>
            <?php if ($modo === 'sent'): ?>
            <p>Si el correo corresponde a una cuenta registrada, las instrucciones estarán en la bandeja de correo de esta instalación. Las solicitudes de usuario deben ser aprobadas por un administrador.</p>
            <aside class="callout">
                <h4>Solicitud registrada</h4>
                <p>El equipo revisará tus datos para continuar.</p>
            </aside>
            <a class="button gold full" href="login.php">Volver a iniciar sesión</a>
            <?php else: ?>
            <p><?= match ($modo) { 'login' => 'Ingresa con tu usuario autorizado.', 'register' => 'El administrador revisará tu solicitud.', 'recover' => 'Escribe el correo asociado a tu cuenta.', default => 'Elige una contraseña para recuperar tu acceso.' } ?></p>
            <?php if ($error): ?>
            <div class="notice danger" role="alert">
                <strong><?= $modo === 'login' ? 'No pudimos iniciar sesión' : 'Revisá los datos' ?></strong>
                <p><?= e($error) ?></p>
            </div>
            <?php endif; ?>
            <?php if ($modo === 'reset' && !$recuperacion): ?>
            <div class="notice danger">
                El enlace venció o ya fue utilizado.
            </div>
            <a class="button gold" href="recuperar.php">Solicitar otro enlace</a>
            <?php else: ?>
            <form method="post" class="stack">
                <?php csrf_field(); ?>
                <?php if ($modo === 'register'): ?>
                <label>Nombre completo
                    <input name="nombre" value="<?= e(post('nombre')) ?>" placeholder="Tu nombre y apellido" maxlength="120" autocomplete="name" required>
                </label>
                <?php endif; ?>
                <?php if (in_array($modo, ['login', 'register'], true)): ?>
                <label>Cédula
                    <input name="cedula" value="<?= e(post('cedula')) ?>" inputmode="numeric" pattern="[0-9]{6,8}" maxlength="8" placeholder="<?= $modo === 'login' ? 'Ingresa tu número de cédula' : 'Número de cédula' ?>" autocomplete="username" required>
                    <small>Sin puntos ni guiones.</small>
                </label>
                <?php endif; ?>
                <?php if (in_array($modo, ['register', 'recover'], true)): ?>
                <label>Correo electrónico
                    <input type="email" name="email" value="<?= e(post('email')) ?>" maxlength="190" placeholder="nombre@ejemplo.com" autocomplete="email" required>
                    <?php if ($modo === 'register'): ?>
                    <small>Recibirás aquí la respuesta a tu solicitud.</small>
                    <?php endif; ?>
                </label>
                <?php endif; ?>
                <?php if ($modo !== 'recover'): ?>
                <label>Contraseña<span class="password-control">
                    <input id="password" type="password" name="password" placeholder="<?= $modo === 'login' ? '••••••••' : 'Crea una contraseña' ?>" autocomplete="<?= $modo === 'login' ? 'current-password' : 'new-password' ?>" <?= $modo !== 'login' ? 'minlength="8" maxlength="72"' : '' ?> required>
                    <button type="button" class="text-button" data-toggle-password="password" aria-label="Mostrar contraseña" aria-pressed="false">Mostrar</button></span>
                    <?php if ($modo !== 'login'): ?>
                    <small>Usa al menos 8 caracteres.</small>
                    <?php endif; ?>
                </label>
                <?php endif; ?>
                <?php if ($modo === 'register' || $modo === 'reset'): ?>
                <label>Confirmar contraseña
                    <input type="password" name="confirm_password" placeholder="Repite la contraseña" minlength="8" maxlength="72" autocomplete="new-password" required>
                </label>
                <?php endif; ?>
                <?php if ($modo === 'reset'): ?>
                <input type="hidden" name="token" value="<?= e($token) ?>">
                <?php endif; ?>
                <?php if ($modo === 'login'): ?>
                <a class="text-link" href="recuperar.php">¿Olvidaste tu contraseña?</a>
                <?php endif; ?>
                <button class="button gold full"><?= match ($modo) { 'login' => 'Iniciar sesión', 'register' => 'Enviar solicitud', 'recover' => 'Enviar instrucciones', default => 'Guardar contraseña' } ?></button>
                <?php if ($modo === 'login'): ?>
                <hr>
                <p class="small">¿Todavía no tienes acceso?</p>
                <a class="button outline full" href="register.php">Solicitar usuario</a>
                <?php else: ?>
                <a class="text-link" href="login.php"><?= $modo === 'register' ? 'Ya tengo usuario · Iniciar sesión' : 'Volver a iniciar sesión' ?></a>
                <?php endif; ?>
            </form>
            <?php endif; ?>
            <?php endif; ?>
        </section>
    </div>
</main>
<?php require __DIR__ . '/../partials/layout-footer.php'; ?>
