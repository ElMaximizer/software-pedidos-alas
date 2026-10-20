<?php
// Prueba manual protegida. No depende de MySQL ni de la sesión del proyecto.
require_once __DIR__ . '/../app/Core/correo.php';
header('Content-Type: text/html; charset=UTF-8');
header('Cache-Control: no-store');
header('Referrer-Policy: no-referrer');
$archivo = __DIR__ . '/../app/Core/smtp_config.php';
$config = is_file($archivo) ? require $archivo : [];
$claveEsperada = is_array($config) ? ($config['clave_prueba'] ?? '') : '';
if (!is_string($claveEsperada) || strlen($claveEsperada) < 20 || $claveEsperada === 'CAMBIAR_POR_UNA_CLAVE_LARGA') {
    http_response_code(403);
    exit('La prueba de correo está deshabilitada. Configurá una clave_prueba privada de al menos 20 caracteres para habilitarla.');
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $clave = $_POST['clave'] ?? '';
    if (!is_string($clave) || !hash_equals($claveEsperada, $clave)) {
        http_response_code(403);
        exit('Clave de prueba incorrecta.');
    }
    try {
        enviarCorreoSmtp($config['destinatario'] ?? $config['remitente'], 'Prueba SMTP - Panadería Alas', 'El envío de correo SMTP funciona.');
        echo '<p>El servidor SMTP aceptó el correo. Revisá la bandeja de entrada y spam.</p>';
    } catch (Throwable $error) {
        http_response_code(500);
        echo '<p>' . htmlspecialchars($error->getMessage(), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</p>';
    }
}
?>
<!doctype html>
<html lang="es"><meta charset="UTF-8"><title>Prueba de correo</title>
<h1>Probar PHPMailer</h1>
<form method="post"><label>Clave de prueba <input type="password" name="clave" required autocomplete="off"></label>
<button>Enviar correo de prueba</button></form></html>
