<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        check_csrf();
        solicitarRecuperacion(post('email'));
        $_SESSION['solicitud_enviada'] = 'recuperacion';
        redirect('app/Views/solicitud-enviada.php');
    } catch (Throwable $exception) {
        $error = error_text($exception);
    }
}
