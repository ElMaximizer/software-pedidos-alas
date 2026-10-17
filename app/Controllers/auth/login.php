<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        check_csrf();
        $contrasena = is_string($_POST['password'] ?? null) ? $_POST['password'] : '';
        iniciarSesion(post('cedula'), $contrasena);
        redirect('app/Views/principal.php');
    } catch (Throwable $exception) {
        $error = error_text($exception);
    }
}
