<?php
require_once __DIR__ . '/../../Services/contacto.php';
$seccionActiva = 'contacto';
$tituloPagina = 'Contacto';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        check_csrf();
        guardarConsulta(post('nombre'), post('email'), post('motivo'), post('mensaje'));
        redirect('public/mensaje-enviado.php');
    } catch (Throwable $exception) {
        $error = error_text($exception);
    }
}
