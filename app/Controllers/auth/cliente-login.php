<?php
require_once __DIR__ . '/../../Services/clientes.php';
if (current_client()) {
    redirect('public/index.php');
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        check_csrf();
        $contrasena = is_string($_POST['password'] ?? null) ? $_POST['password'] : '';
        iniciarSesionCliente(post('email'), $contrasena);
        flash('Iniciaste sesión como cliente. Ya podés continuar con tu compra.');
        redirect('public/index.php');
    } catch (Throwable $exception) {
        $error = error_text($exception);
    }
}
