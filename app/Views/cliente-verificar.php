<?php
require_once __DIR__ . '/../Core/app.php';
$tituloPagina = 'Verificar cuenta de cliente';
$error = '';
require __DIR__ . '/../Controllers/auth/cliente-verificar.php';
require __DIR__ . '/cliente-verificar-pagina.php';
