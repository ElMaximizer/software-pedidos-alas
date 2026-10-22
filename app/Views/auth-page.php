<?php
require_once __DIR__ . '/../Services/usuarios.php';
$modo = $modoAcceso ?? 'login';
$titulos = [
    'login' => 'Acceso del equipo',
    'register' => 'Solicitar usuario',
    'recover' => '¿Olvidaste tu contraseña?',
    'sent' => 'Revisa tu correo',
    'reset' => 'Nueva contraseña',
];
if (!isset($titulos[$modo])) {
    http_response_code(404);
    exit('Página no encontrada.');
}
$usuarioAutenticado = current_user();
if ($usuarioAutenticado && in_array($modo, ['login', 'register'], true)) {
    if ($modo === 'login' || !in_array($usuarioAutenticado['rol'], ['administrador', 'dueno'], true)) {
        redirect('app/Views/principal.php');
    }
}
$error = '';
$tituloPagina = $titulos[$modo];
$seccionActiva = '';
// Lista cerrada: el nombre del controlador no viene de la URL.
require __DIR__ . '/../Controllers/auth/' . $modo . '.php';
require __DIR__ . '/auth/pagina.php';

