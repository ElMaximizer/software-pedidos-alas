<?php
require_once __DIR__ . '/../Services/usuarios.php';
$modo = $modoAcceso ?? 'login';
$titulos = [
    'login' => 'Iniciar sesión',
    'register' => 'Solicitar usuario',
    'verify' => 'Verificá tu correo',
    'recover' => '¿Olvidaste tu contraseña?',
    'sent' => 'Revisa tu correo',
    'reset' => 'Nueva contraseña',
];
if (!isset($titulos[$modo])) {
    http_response_code(404);
    exit('Página no encontrada.');
}
if (current_user() && in_array($modo, ['login', 'register', 'verify'], true)) {
    redirect('app/Views/principal.php');
}
$error = '';
$tituloPagina = $titulos[$modo];
$seccionActiva = '';
// Lista cerrada: el nombre del controlador no viene de la URL.
require __DIR__ . '/../Controllers/auth/' . $modo . '.php';
require __DIR__ . ($modo === 'verify' ? '/auth/verificar.php' : '/auth/pagina.php');
