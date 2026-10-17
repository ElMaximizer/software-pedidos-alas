<?php
require_once __DIR__ . '/../Core/auth.php';
if (!isset($tipoGestion) || !in_array($tipoGestion, ['agregar-producto','editar-producto','eliminar-producto','agregar-pedido','editar-pedido','eliminar-pedido'], true)) {
    http_response_code(404);
    exit('Página no encontrada.');
}
require __DIR__ . (str_ends_with($tipoGestion, 'producto') ? '/producto-form.php' : '/pedido-form.php');
