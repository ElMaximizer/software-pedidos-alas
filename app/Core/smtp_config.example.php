<?php
// Copiar como smtp_config.php SOLO en el hosting. No subir credenciales a Git.
// Es compatible con el archivo usado en la prueba manual de PHPMailer.
return [
    'host' => 'smtp.gmail.com',
    'port' => 587,
    'seguridad' => 'tls',
    'usuario' => 'tu-cuenta@gmail.com',
    'password' => 'CONTRASENA_DE_APLICACION',
    'remitente' => 'tu-cuenta@gmail.com',
    'nombre' => 'Panadería Alas',
];
