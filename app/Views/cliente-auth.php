<?php
require_once __DIR__ . '/../Core/app.php';
require_once __DIR__ . '/../Core/presentacion.php';
require_once __DIR__ . '/../Core/seguridad.php';
require_once __DIR__ . '/../Core/validacion.php';
require_once __DIR__ . '/../Core/http.php';
require_once __DIR__ . '/../Services/clientes.php';
$modoCliente = $modoCliente ?? 'login';
$esRegistro = $modoCliente === 'registro';
$tituloPagina = $esRegistro ? 'Crear cuenta de cliente' : 'Acceso para clientes';
$error = '';
if (!in_array($modoCliente, ['login', 'registro'], true)) {
    http_response_code(404);
    exit('Página no encontrada.');
}
require __DIR__ . '/../Controllers/auth/cliente-' . $modoCliente . '.php';
require_once __DIR__ . '/partials/layout-header.php';
?>
<main id="contenido" class="container section">
    <a class="back-link" href="<?= e(base_url('public/index.php')) ?>">← Volver al sitio</a>
    <div class="auth-grid">
        <section class="auth-story">
            <span class="eyebrow">PANADERÍA ALAS</span>
            <h1><?= $esRegistro ? 'Tu panadería,<br>más cerca.' : 'Acceso para<br>clientes.' ?></h1>
            <p class="lead"><?= $esRegistro ? 'Creá tu cuenta y confirmá tu correo con el código que te enviaremos.' : 'Iniciá sesión para identificarte como cliente y continuar con tus compras.' ?></p>
            <img class="bakery-picture" src="<?= e(base_url('public/images/panes.png')) ?>" width="648" height="350" alt="Elaboraciones artesanales de Panadería Alas">
        </section>
        <section class="card auth-card">
            <span class="eyebrow">CUENTA DE CLIENTE</span>
            <h1><?= e($tituloPagina) ?></h1>
            <?php show_flash(); ?>
            <?php if ($error): ?>
            <div class="notice danger" role="alert"><strong>No pudimos procesar tus datos</strong><p><?= e($error) ?></p></div>
            <?php endif; ?>
            <?php if (!$esRegistro && !empty($_SESSION['cliente_verificacion_email'])): ?>
            <a class="button outline full" href="cliente-verificar.php">Verificar mi cuenta con un código</a>
            <?php endif; ?>
            <form method="post" class="stack">
                <?php csrf_field(); ?>
                <?php if ($esRegistro): ?>
                <label>Nombre completo
                    <input name="nombre" value="<?= e(post('nombre')) ?>" maxlength="120" autocomplete="name" required>
                </label>
                <?php endif; ?>
                <label>Correo electrónico
                    <input type="email" name="email" value="<?= e(post('email')) ?>" maxlength="190" autocomplete="email" required>
                </label>
                <label>Contraseña<span class="password-control">
                    <input id="password" type="password" name="password" minlength="8" maxlength="72" autocomplete="<?= $esRegistro ? 'new-password' : 'current-password' ?>" required>
                    <button type="button" class="text-button" data-toggle-password="password" aria-label="Mostrar contraseña" aria-pressed="false">Mostrar</button></span>
                </label>
                <?php if ($esRegistro): ?>
                <label>Confirmar contraseña
                    <input type="password" name="confirm_password" minlength="8" maxlength="72" autocomplete="new-password" required>
                </label>
                <?php endif; ?>
                <button class="button gold full"><?= $esRegistro ? 'Crear cuenta' : 'Ingresar' ?></button>
                <?php if ($esRegistro): ?>
                <a class="text-link" href="cliente-login.php">Ya tengo cuenta · Iniciar sesión</a>
                <?php else: ?>
                <p class="small">¿Todavía no tenés cuenta?</p>
                <a class="button outline full" href="cliente-registro.php">Crear cuenta de cliente</a>
                <?php endif; ?>
            </form>
        </section>
    </div>
</main>
<?php require __DIR__ . '/partials/layout-footer.php'; ?>
