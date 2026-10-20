<?php
// Lectura de la petición, rutas, redirecciones y mensajes entre pantallas.
function base_url(string $ruta = ''): string
{
    $raiz = str_replace('\\', '/', dirname(__DIR__, 2));
    $documento = str_replace('\\', '/', rtrim($_SERVER['DOCUMENT_ROOT'] ?? dirname($raiz), '/\\'));
    $base = $raiz === $documento
        ? ''
        : (str_starts_with($raiz, $documento . '/')
            ? substr($raiz, strlen($documento))
            : '/software-pedidos-alas');
    return $base . '/' . ltrim($ruta, '/');
}

function redirect(string $ruta): never
{
    header('Location: ' . base_url($ruta));
    exit;
}

function post(string $clave, string $predeterminado = ''): string
{
    if (!isset($_POST[$clave]) || !is_scalar($_POST[$clave])) {
        return $predeterminado;
    }
    return trim((string) $_POST[$clave]);
}

function get(string $clave, string $predeterminado = ''): string
{
    if (!isset($_GET[$clave]) || !is_scalar($_GET[$clave])) {
        return $predeterminado;
    }
    return trim((string) $_GET[$clave]);
}

function flash(string $mensaje): void
{
    $_SESSION['flash'] = $mensaje;
}
