<?php
require_once __DIR__ . '/../../Services/clientes.php';
if (current_client()) {
    redirect('public/index.php');
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        check_csrf();
        $contrasena = is_string($_POST['password'] ?? null) ? $_POST['password'] : '';
        $confirmacion = is_string($_POST['confirm_password'] ?? null) ? $_POST['confirm_password'] : '';
        $email = strtolower(trim(post('email')));
        registrarCliente(post('nombre'), $email, $contrasena, $confirmacion);
        $_SESSION['cliente_verificacion_email'] = $email;
        try {
            enviarCodigoVerificacionCliente($email);
            flash('Te enviamos un código de seis dígitos. Revisá tu correo para activar la cuenta.');
        } catch (Throwable $errorEnvio) {
            flash($errorEnvio->getMessage() . ' La cuenta queda pendiente; podés volver a enviar el código desde esta página.');
        }
        redirect('app/Views/cliente-verificar.php');
    } catch (Throwable $exception) {
        $error = error_text($exception);
    }
}
