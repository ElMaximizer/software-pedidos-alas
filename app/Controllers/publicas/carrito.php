<?php
require_once __DIR__ . '/../../Services/carrito.php';
$tituloPagina = 'Tu carrito';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        check_csrf();
        actualizarCarrito(post('accion'), (int) post('id_producto'), post('cantidad'));
        redirect('public/carrito.php');
    } catch (Throwable $exception) {
        $error = error_text($exception);
    }
}
$articulos = obtenerCarrito();
$total = 0;
foreach ($articulos as $articulo) {
    $total += (int) round((float) $articulo['precio'] * 100) * $articulo['cantidad_carrito'];
}
$total /= 100;
