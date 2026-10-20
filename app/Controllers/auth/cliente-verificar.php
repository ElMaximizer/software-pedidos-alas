<?php
require_once __DIR__ . '/../../Services/clientes.php';
$emailVerificacion = strtolower(trim(post('email', $_SESSION['cliente_verificacion_email'] ?? '')));
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        check_csrf();
        if (post('accion') === 'reenviar') {
            enviarCodigoVerificacionCliente($emailVerificacion);
            $_SESSION['cliente_verificacion_email'] = $emailVerificacion;
            flash('Si la cuenta está pendiente, te enviamos un nuevo código. Revisá tu correo.');
        } else {
            verificarCodigoCliente($emailVerificacion, post('codigo'));
            unset($_SESSION['cliente_verificacion_email']);
            flash('Tu cuenta quedó verificada. Ya podés iniciar sesión como cliente.');
            redirect('app/Views/cliente-login.php');
        }
    } catch (Throwable $exception) {
        $error = error_text($exception);
    }
}
