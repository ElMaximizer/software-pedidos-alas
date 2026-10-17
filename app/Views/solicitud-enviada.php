<?php
require_once __DIR__ . '/../Core/app.php';
if (empty($_SESSION['solicitud_enviada'])) {
    redirect('app/Views/login.php');
}
$modoAcceso = 'sent';
require __DIR__ . '/auth-page.php';
