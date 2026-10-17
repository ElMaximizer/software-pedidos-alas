<?php
require_once __DIR__ . '/../../Core/auth.php';
require_user(true);
$esAdministracion = true;
$seccionActiva = 'resumen';
$tituloPagina = 'Bandeja de correo local';
$correos = rows('SELECT * FROM correos_locales ORDER BY id_correo DESC LIMIT 100');
