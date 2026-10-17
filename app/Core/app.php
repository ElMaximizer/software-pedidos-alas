<?php
declare(strict_types=1);
// Inicio común de la aplicación. Cada petición lo carga una sola vez.
date_default_timezone_set('America/Montevideo');
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_set_cookie_params([
            'httponly' => true,
            'samesite' => 'Lax',
            'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        ]);
    session_start();
}
require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/base-datos.php';
require_once __DIR__ . '/http.php';
require_once __DIR__ . '/seguridad.php';
require_once __DIR__ . '/presentacion.php';
require_once __DIR__ . '/validacion.php';
require_once __DIR__ . '/correo.php';
