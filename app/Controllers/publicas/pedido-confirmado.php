<?php
require_once __DIR__ . '/../../Core/app.php';
if (empty($_SESSION['pedido_confirmado'])) {
    redirect('public/productos.php');
}
$tituloPagina = 'Pedido registrado';
