<?php
require_once __DIR__ . '/../../Services/usuarios.php';

// El enlace llega por GET y el formulario conserva el token en un campo oculto.
$token = get('token', post('token'));
$recuperacion = buscarRecuperacion($token);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        check_csrf();
        $contrasena = is_string($_POST['password'] ?? null) ? $_POST['password'] : '';
        $confirmacion = is_string($_POST['confirm_password'] ?? null) ? $_POST['confirm_password'] : '';
        restablecerContrasena($recuperacion, $contrasena, $confirmacion);
        flash('Contraseña actualizada. Ya podés iniciar sesión.');
        redirect('app/Views/login.php');
    } catch (Throwable $exception) {
        $error = error_text($exception);
    }
}
