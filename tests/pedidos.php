<?php
// Pruebas de integración: requieren MySQL local y el esquema de desarrollo.
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit;
}
ini_set('session.save_path', sys_get_temp_dir());
set_error_handler(function ($nivel, $mensaje, $archivo, $linea) {
    throw new ErrorException($mensaje, 0, $nivel, $archivo, $linea);
});
require_once __DIR__ . '/../app/Services/pedidos.php';
require_once __DIR__ . '/../app/Services/carrito.php';

function comprobar(bool $condicion, string $mensaje): void
{
    if (!$condicion) {
        throw new RuntimeException($mensaje);
    }
}

function debeRechazar(callable $operacion, string $mensajeEsperado): void
{
    try {
        $operacion();
    } catch (RuntimeException $error) {
        comprobar(str_contains($error->getMessage(), $mensajeEsperado), 'Error inesperado: ' . $error->getMessage());
        return;
    }
    throw new RuntimeException('Se aceptó una operación que debía rechazarse.');
}

function versionActualPedido(int $idPedido): int
{
    return (int) one('SELECT version FROM pedidos WHERE id_pedido=?', 'i', [$idPedido])['version'];
}

function formularioHistoricoSinCatalogo(int $idPedido): string
{
    $pedido = one('SELECT * FROM pedidos WHERE id_pedido=?', 'i', [$idPedido]);
    $detalle = obtenerLineasPedido($idPedido)[0];
    $id = $idPedido;
    $esEdicion = true;
    $esCancelacion = false;
    $soloLectura = false;
    $tituloPagina = 'Editar pedido';
    $error = '';
    $productos = [];
    $valores = $pedido;
    $valorHistorico = 'historico:' . $detalle['id_detalle'];
    $lineas = [['id_producto' => $valorHistorico, 'cantidad' => $detalle['cantidad']]];
    $opcionesProductos = [[
        'id' => $valorHistorico, 'nombre' => $detalle['nombre_producto'],
        'precio' => $detalle['precio_unitario'], 'texto' => $detalle['nombre_producto'],
    ]];
    $nonce = crearNonceFormulario('pedido:editar-pedido:' . $id);
    $versionFormulario = (string) $pedido['version'];
    ob_start();
    require __DIR__ . '/../app/Views/admin/pedido-form.php';
    return ob_get_clean();
}

$marca = 'QA_REFACTOR_' . bin2hex(random_bytes(6));
$idProducto = null;
$idPedido = null;
$datos = [
    'cliente_nombre' => $marca,
    'telefono' => '099000000',
    'fecha_retiro' => date('Y-m-d', strtotime('+1 day')),
    'hora_retiro' => '10:30',
    'notas' => 'Datos temporales de prueba',
];
try {
    sql('INSERT INTO productos (nombre, descripcion, precio) VALUES (?, ?, 10.25)', 'ss', [$marca, 'Prueba temporal']);
    $idProducto = (int) $conexion->insert_id;
    debeRechazar(
        fn() => guardarPedido($datos + ['precios_esperados' => [$idProducto => '9.00']], [$idProducto], [1]),
        'precio de un producto cambió'
    );
    $idPedido = guardarPedido($datos, [$idProducto], [2]);
    comprobar(one('SELECT total FROM pedidos WHERE id_pedido=?', 'i', [$idPedido])['total'] === '20.50', 'Total inicial incorrecto.');

    $versionAnterior = versionActualPedido($idPedido);
    guardarPedido($datos, [$idProducto], [3], $idPedido, $versionAnterior);
    debeRechazar(fn() => guardarPedido($datos, [$idProducto], [1], $idPedido, $versionAnterior), 'fue modificado');
    comprobar((int) obtenerLineasPedido($idPedido)[0]['cantidad'] === 3, 'Un formulario anterior borró los cambios de otra persona.');
    comprobar(versionActualPedido($idPedido) === $versionAnterior + 1, 'No avanzó la versión del pedido.');
    debeRechazar(fn() => cancelarPedido($idPedido, 'Solicitud del cliente', $versionAnterior), 'fue modificado');
    foreach (['------', '++++++', '123', '1234567890123456'] as $telefonoInvalido) {
        debeRechazar(fn() => guardarPedido(array_replace($datos, ['telefono' => $telefonoInvalido]), [$idProducto], [1]), 'teléfono');
    }

    sql("UPDATE productos SET nombre='Nombre cambiado', precio=88, activo=0, eliminado_en=NOW() WHERE id_producto=?", 'i', [$idProducto]);
    guardarPedido($datos, [$idProducto], [3], $idPedido, versionActualPedido($idPedido));
    $detalle = obtenerLineasPedido($idPedido)[0];
    comprobar($detalle['nombre_producto'] === $marca && $detalle['precio_unitario'] === '10.25', 'Se perdió el precio o nombre histórico.');

    debeRechazar(fn() => guardarPedido($datos, [$idProducto], [1]), 'no está disponible');
    debeRechazar(fn() => guardarPedido($datos, [$idProducto], [0], $idPedido, versionActualPedido($idPedido)), 'cantidades enteras');
    debeRechazar(fn() => guardarPedido($datos, [$idProducto, 0], [4, 2], $idPedido, versionActualPedido($idPedido)), 'Seleccioná productos');
    comprobar((int) obtenerLineasPedido($idPedido)[0]['cantidad'] === 3, 'Una edición fallida alteró el pedido.');

    // Simula un detalle importado que ya no está vinculado con el catálogo.
    sql('UPDATE pedido_detalles SET id_producto=NULL WHERE id_pedido=?', 'i', [$idPedido]);
    $detalle = obtenerLineasPedido($idPedido)[0];
    guardarPedido($datos, ['historico:' . $detalle['id_detalle']], [4], $idPedido, versionActualPedido($idPedido));
    comprobar(one('SELECT total FROM pedidos WHERE id_pedido=?', 'i', [$idPedido])['total'] === '41.00', 'Falló el pedido antiguo sin producto.');

    // Comprueba también que la pantalla conserve seleccionada la opción histórica.
    $_SERVER['REQUEST_METHOD'] = 'GET';
    $_SERVER['DOCUMENT_ROOT'] = dirname(__DIR__, 2);
    $_GET['id'] = (string) $idPedido;
    $administrador = one("SELECT id_sol FROM solicitud_usuario WHERE rol='administrador' AND estado='aprobado' LIMIT 1");
    comprobar($administrador !== null, 'Se necesita un administrador de prueba para verificar la pantalla.');
    $_SESSION['user_id'] = (int) $administrador['id_sol'];
    ob_start();
    require __DIR__ . '/../app/Views/editar-pedido.php';
    $html = ob_get_clean();
    comprobar((bool) preg_match('/value="historico:\d+"\s+data-price="10\.25"\s+data-name="[^"]+"\s+selected/s', $html), 'La vista perdió la selección histórica.');
    $htmlSinCatalogo = formularioHistoricoSinCatalogo($idPedido);
    comprobar(str_contains($htmlSinCatalogo, '>Guardar cambios</button>'), 'Falta guardar un pedido histórico cuando no hay catálogo activo.');

    $_SESSION['carrito'] = [$idProducto => 1];
    debeRechazar(fn() => obtenerCarrito(true), 'ya no está disponible');
    comprobar(isset($_SESSION['carrito'][$idProducto]), 'La confirmación eliminó silenciosamente un producto del carrito.');

    $noncePrimero = crearNonceFormulario('pedido:crear');
    $nonceSegundo = crearNonceFormulario('pedido:crear');
    comprobar($noncePrimero !== $nonceSegundo, 'Dos pestañas recibieron el mismo identificador.');
    consumirNonceFormulario($noncePrimero);
    validarNonceFormulario($nonceSegundo, 'pedido:crear');
    debeRechazar(fn() => validarNonceFormulario($noncePrimero, 'pedido:crear'), 'ya fue procesado');
    debeRechazar(fn() => validarNonceFormulario($nonceSegundo, 'pedido:otro'), 'ya fue procesado');

    cancelarPedido($idPedido, 'Solicitud del cliente', versionActualPedido($idPedido));
    debeRechazar(fn() => guardarPedido($datos, [$idProducto], [1], $idPedido, versionActualPedido($idPedido)), 'ya no se puede modificar');
    debeRechazar(fn() => cancelarPedido($idPedido, 'Solicitud del cliente', versionActualPedido($idPedido)), 'ya no se puede modificar');
    debeRechazar(fn() => guardarPedido(array_replace($datos, ['fecha_retiro' => '2020-01-01']), [$idProducto], [1]), 'debe ser futura');

    sql('UPDATE productos SET precio=99999999.99, activo=1, eliminado_en=NULL WHERE id_producto=?', 'i', [$idProducto]);
    debeRechazar(fn() => guardarPedido($datos, [$idProducto], [2]), 'supera el máximo');
    comprobar(count(rows('SELECT id_pedido FROM pedidos WHERE cliente_nombre=?', 's', [$marca])) === 1, 'Un rechazo dejó un pedido parcial.');
    echo "OK: versiones, pestañas independientes, historial sin catálogo, carrito, teléfono, cantidades y transacciones.\n";
} finally {
    if (ob_get_level()) {
        ob_end_clean();
    }
    // Solo se eliminan los registros con la marca aleatoria de esta ejecución.
    sql('DELETE FROM pedidos WHERE cliente_nombre=?', 's', [$marca]);
    if ($idProducto !== null) {
        sql('DELETE FROM productos WHERE id_producto=?', 'i', [$idProducto]);
    }
    session_destroy();
}
