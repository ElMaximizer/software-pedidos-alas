<?php
require_once __DIR__ . '/../../Core/auth.php';
require_once __DIR__ . '/../../Services/pedidos.php';
if (!in_array($tipoGestion ?? '', ['agregar-pedido', 'editar-pedido', 'eliminar-pedido'], true)) {
    http_response_code(404);
    exit('Página no encontrada.');
}
$esAdministracion = true;
$seccionActiva = 'pedidos';
$esEdicion = $tipoGestion === 'editar-pedido';
$esCancelacion = $tipoGestion === 'eliminar-pedido';
$id = (int) get('id', post('id_pedido'));
$pedido = ($esEdicion || $esCancelacion) ? one('SELECT * FROM pedidos WHERE id_pedido=?', 'i', [$id]) : null;
if (($esEdicion || $esCancelacion) && !$pedido) {
    flash('Seleccioná un pedido del listado.');
    redirect('app/Views/pedidos.php');
}
$tituloPagina = $esCancelacion ? 'Cancelar pedido' : ($esEdicion ? 'Editar pedido' : 'Agregar pedido');
$error = '';
$soloLectura = $pedido && in_array($pedido['estado'], ['Cancelado', 'Entregado'], true);
$valores = $pedido ?? [
    'cliente_nombre' => '', 'telefono' => '',
    'fecha_retiro' => date('Y-m-d', strtotime('+1 day')), 'hora_retiro' => '10:00',
    'notas' => '', 'estado' => 'Pendiente',
];
$lineas = $pedido ? obtenerLineasPedido($id) : [['id_producto' => '', 'cantidad' => 1]];
$lineasHistoricas = [];
$historialPorProducto = [];
foreach ($lineas as $linea) {
    if (empty($linea['id_producto']) && isset($linea['id_detalle'])) {
        $lineasHistoricas[] = $linea;
    } elseif (!empty($linea['id_producto'])) {
        $historialPorProducto[$linea['id_producto']] = $linea;
    }
}
$productos = rows('SELECT * FROM productos WHERE eliminado_en IS NULL AND activo=1 ORDER BY nombre');
// Incluye opciones que ya estaban en el pedido, aunque hoy estén inactivas.
foreach ($historialPorProducto as $idProducto => $anterior) {
    if (!in_array($idProducto, array_column($productos, 'id_producto'))) {
        $productoAnterior = one('SELECT * FROM productos WHERE id_producto=?', 'i', [$idProducto]);
        if ($productoAnterior) {
            $productos[] = $productoAnterior;
        }
    }
}
$opcionesProductos = [];
foreach ($lineasHistoricas as $historico) {
    $opcionesProductos[] = [
        'id' => 'historico:' . $historico['id_detalle'],
        'nombre' => $historico['nombre_producto'],
        'precio' => $historico['precio_unitario'],
        'texto' => $historico['nombre_producto'] . ' (histórico)',
    ];
}
foreach ($productos as $producto) {
    $precio = $historialPorProducto[$producto['id_producto']]['precio_unitario'] ?? $producto['precio'];
    $esHistorico = !$producto['activo'] || $producto['eliminado_en'];
    $opcionesProductos[] = [
        'id' => (string) $producto['id_producto'],
        'nombre' => $producto['nombre'],
        'precio' => $precio,
        'texto' => $producto['nombre'] . ' · ' . money($precio) . ($esHistorico ? ' (histórico)' : ''),
    ];
}
// Las líneas antiguas usan el mismo valor que su opción del selector.
foreach ($lineas as &$linea) {
    if (empty($linea['id_producto']) && isset($linea['id_detalle'])) {
        $linea['id_producto'] = 'historico:' . $linea['id_detalle'];
    }
}
unset($linea);
$contextoFormulario = 'pedido:' . $tipoGestion . ':' . $id;
$esEnvio = $_SERVER['REQUEST_METHOD'] === 'POST';
$nonce = $esEnvio ? post('pedido_nonce') : crearNonceFormulario($contextoFormulario);
$versionFormulario = $esEnvio ? post('pedido_version') : (string) ($pedido['version'] ?? '');
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        check_csrf();
        validarNonceFormulario($nonce, $contextoFormulario);
        $versionEsperada = null;
        if ($esEdicion || $esCancelacion) {
            $versionEsperada = filter_var($versionFormulario, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
            if ($versionEsperada === false) {
                throw new RuntimeException('Volvé a abrir el pedido antes de guardar los cambios.');
            }
        }
        if ($soloLectura) {
            throw new RuntimeException('Los pedidos entregados o cancelados se conservan como historial.');
        }
        if ($esCancelacion) {
            cancelarPedido($id, post('motivo_cancelacion'), $versionEsperada);
            consumirNonceFormulario($nonce);
            flash('Pedido cancelado. Su registro se conserva en el historial.');
            redirect('app/Views/pedidos.php');
        }
        foreach (['cliente_nombre', 'telefono', 'fecha_retiro', 'hora_retiro', 'notas', 'estado'] as $clave) {
            $valores[$clave] = post($clave, (string) $valores[$clave]);
        }
        $idsProductos = $_POST['producto_id'] ?? [];
        $cantidades = $_POST['cantidad'] ?? [];
        if (!is_array($idsProductos) || !is_array($cantidades)) {
            throw new RuntimeException('Revisá las líneas del pedido.');
        }
        // Se conservan los valores del formulario si falla una validación.
        $lineas = [];
        foreach ($idsProductos as $indice => $idProducto) {
            $lineas[] = [
                'id_producto' => is_scalar($idProducto) ? (string) $idProducto : '',
                'cantidad' => isset($cantidades[$indice]) && is_scalar($cantidades[$indice]) ? (string) $cantidades[$indice] : '',
            ];
        }
        $idGuardado = guardarPedido($valores, $idsProductos, $cantidades, $esEdicion ? $id : null, $versionEsperada);
        consumirNonceFormulario($nonce);
        flash($esEdicion ? 'Pedido actualizado correctamente.' : "Pedido #$idGuardado registrado correctamente.");
        redirect('app/Views/pedidos.php');
    } catch (Throwable $exception) {
        $error = error_text($exception);
    }
}
