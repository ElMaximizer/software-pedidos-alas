<?php
require_once __DIR__ . '/../Core/app.php';
function actualizarCarrito(string $accion, int $idProducto, string $cantidadRecibida): void
{
    if ($accion === 'vaciar') {
        $_SESSION['carrito'] = [];
        return;
    }
    if ($accion === 'quitar') {
        unset($_SESSION['carrito'][$idProducto]);
        return;
    }
    if (!in_array($accion, ['agregar', 'actualizar'], true)) {
        throw new RuntimeException('Acción no válida.');
    }
    $producto = one(
        'SELECT id_producto FROM productos WHERE id_producto=? AND activo=1 AND eliminado_en IS NULL',
        'i',
        [$idProducto]
    );
    $cantidad = filter_var($cantidadRecibida, FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 1, 'max_range' => 99],
        ]);
    if (!$producto || !$cantidad) {
        throw new RuntimeException('Revisá el producto y la cantidad (entre 1 y 99).');
    }
    $cantidadFinal = $accion === 'agregar'
    ? ($_SESSION['carrito'][$idProducto] ?? 0) + $cantidad
    : $cantidad;
    if ($cantidadFinal > 99) {
        throw new RuntimeException('El máximo por producto es 99 unidades.');
    }
    $_SESSION['carrito'][$idProducto] = $cantidadFinal;
}

function obtenerCarrito(bool $exigirDisponibilidad = false): array
{
    $articulos = [];
    foreach ($_SESSION['carrito'] ?? [] as $id => $cantidad) {
        $producto = one('SELECT * FROM productos WHERE id_producto = ? AND activo = 1 AND eliminado_en IS NULL', 'i', [(int) $id]);
        if ($producto) {
            $producto['cantidad_carrito'] = (int) $cantidad;
            $articulos[] = $producto;
        } else {
            if ($exigirDisponibilidad) {
                throw new RuntimeException('Uno de los productos ya no está disponible. El pedido no se registró. Volvé al carrito para revisarlo.');
            }
            unset($_SESSION['carrito'][$id]);
        }
    }
    return $articulos;
}

function firmaCarrito(array $articulos): string
{
    $resumen = [];
    foreach ($articulos as $articulo) {
        $resumen[(int) $articulo['id_producto']] = [
            (int) $articulo['cantidad_carrito'],
            (int) round((float) $articulo['precio'] * 100),
        ];
    }
    ksort($resumen, SORT_NUMERIC);
    return hash('sha256', json_encode($resumen));
}
