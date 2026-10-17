<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        check_csrf();
        $contrasena = is_string($_POST['password'] ?? null) ? $_POST['password'] : '';
        $confirmacion = is_string($_POST['confirm_password'] ?? null) ? $_POST['confirm_password'] : '';
        solicitarUsuario(post('nombre'), post('cedula'), post('email'), $contrasena, $confirmacion);
        $_SESSION['solicitud_enviada'] = 'registro';
        redirect('app/Views/solicitud-enviada.php');
    } catch (Throwable $exception) {
        $error = error_text($exception);
    }
}
