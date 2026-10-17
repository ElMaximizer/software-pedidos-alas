<?php
require_once __DIR__ . '/../../Core/app.php';
if (empty($_SESSION['consulta_enviada'])) {
    redirect('public/contacto.php');
}
$seccionActiva = 'contacto';
$tituloPagina = 'Mensaje enviado';
