<?php
if (registroPendiente()) {
    redirect('app/Views/verificar-correo.php');
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        check_csrf();
        $contrasena = is_string($_POST['password'] ?? null) ? $_POST['password'] : '';
        $confirmacion = is_string($_POST['confirm_password'] ?? null) ? $_POST['confirm_password'] : '';
        solicitarUsuario(post('nombre'), post('cedula'), post('email'), $contrasena, $confirmacion);
        redirect('app/Views/verificar-correo.php');
    } catch (Throwable $exception) {
        $error = error_text($exception);
    }
}
