<?php
// Compatibilidad con las rutas anteriores: las reglas están en Services.
require_once __DIR__ . '/../Services/pedidos.php';
function save_order(array $data, array $idsProductos, array $cantidades, ?int $id = null, ?int $versionEsperada = null): int
{
    return guardarPedido($data, $idsProductos, $cantidades, $id, $versionEsperada);
}

function order_lines(int $id): array
{
    return obtenerLineasPedido($id);
}
