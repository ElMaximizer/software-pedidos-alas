<?php
require_once __DIR__ . '/../Core/app.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('app/Views/principal.php');
}
try {
    check_csrf();
} catch (RuntimeException $error) {
    http_response_code(403);
    exit(e($error->getMessage()));
}
$_SESSION = [];
if (ini_get('session.use_cookies')) {
    $parametros = session_get_cookie_params();
    setcookie(session_name(), '', ['expires' => time() - 3600, 'path' => $parametros['path'], 'secure' => $parametros['secure'], 'httponly' => true, 'samesite' => 'Lax']);
}
session_destroy();
redirect('app/Views/login.php');
