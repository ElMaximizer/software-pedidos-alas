<?php
require_once __DIR__ . '/../../Core/app.php';
$producto = one('SELECT * FROM productos WHERE id_producto = ? AND activo = 1 AND eliminado_en IS NULL', 'i', [(int) get('id')]);
$seccionActiva = 'productos';
$tituloPagina = $producto['nombre'] ?? 'Producto no disponible';
if (!$producto) {
    http_response_code(404);
}
