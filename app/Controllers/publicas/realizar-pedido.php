<?php
require_once __DIR__ . '/../../Services/carrito.php';
require_once __DIR__ . '/../../Services/pedidos.php';
$error = '';
$tituloPagina = 'Realizar pedido';
$articulos = [];
$puedeConfirmar = false;
$contextoFormulario = 'pedido:carrito';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nonce = post('pedido_nonce');
    try {
        check_csrf();
        $formulario = validarNonceFormulario($nonce, $contextoFormulario);
        $articulos = $formulario['articulos'];
        $articulosActuales = obtenerCarrito(true);
        if (!hash_equals(firmaCarrito($articulos), firmaCarrito($articulosActuales))) {
            throw new RuntimeException('El carrito o sus precios cambiaron. El pedido no se registró. Volvé al carrito para revisar los productos.');
        }
        $puedeConfirmar = true;
        $datos = [
            'cliente_nombre' => post('cliente_nombre'),
            'telefono' => post('telefono'),
            'fecha_retiro' => post('fecha_retiro'),
            'hora_retiro' => post('hora_retiro'),
            'notas' => post('notas'),
            'precios_esperados' => array_column($articulos, 'precio', 'id_producto'),
        ];
        $id = guardarPedido($datos, array_column($articulos, 'id_producto'), array_column($articulos, 'cantidad_carrito'));
        $_SESSION['pedido_confirmado'] = $id;
        $_SESSION['carrito'] = [];
        consumirNonceFormulario($nonce);
        redirect('public/pedido-confirmado.php');
    } catch (Throwable $exception) {
        $error = error_text($exception);
    }
} else {
    $articulos = obtenerCarrito();
    if (!$articulos) {
        redirect('public/carrito.php');
    }
    // Guarda lo que vio el cliente para detectar cambios antes de confirmar.
    $resumen = [];
    foreach ($articulos as $articulo) {
        $resumen[] = [
            'id_producto' => $articulo['id_producto'],
            'nombre' => $articulo['nombre'],
            'precio' => $articulo['precio'],
            'cantidad_carrito' => $articulo['cantidad_carrito'],
        ];
    }
    $nonce = crearNonceFormulario($contextoFormulario, ['articulos' => $resumen]);
    $puedeConfirmar = true;
}
// Se calcula antes de la vista para que la plantilla solo muestre los importes.
$total = 0;
foreach ($articulos as $articulo) {
    $total += (int) round((float) $articulo['precio'] * 100) * $articulo['cantidad_carrito'];
}
$total /= 100;
