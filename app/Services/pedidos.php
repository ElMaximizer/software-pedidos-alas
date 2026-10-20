<?php
require_once __DIR__ . '/../Core/app.php';
function obtenerPedidosCliente(int $idCliente): array
{
    return rows(
        "SELECT p.id_pedido, p.creado_en, p.fecha_retiro, p.hora_retiro, p.estado, p.total, p.producto,
                GROUP_CONCAT(CONCAT(d.nombre_producto, ' × ', d.cantidad) ORDER BY d.id_detalle SEPARATOR ', ') AS productos
         FROM pedidos p
         LEFT JOIN pedido_detalles d ON d.id_pedido = p.id_pedido
         WHERE p.id_cliente = ?
         GROUP BY p.id_pedido
         ORDER BY p.creado_en DESC, p.id_pedido DESC
         LIMIT 50",
        'i',
        [$idCliente]
    );
}

function obtenerLineasPedido(int $idPedido): array
{
    return rows(
        'SELECT * FROM pedido_detalles WHERE id_pedido = ? ORDER BY id_detalle',
        'i',
        [$idPedido]
    );
}

function validarDatosPedido(array $datos, bool $esNuevo): void
{
    validate_text($datos['cliente_nombre'] ?? '', 'el nombre del cliente', 120);
    $telefono = validate_text($datos['telefono'] ?? '', 'el teléfono', 30);
    $digitosTelefono = preg_replace('/\D/', '', $telefono);
    if (!preg_match('/^\+?[\d\s()\-]+$/', $telefono)
        || strlen($digitosTelefono) < 6 || strlen($digitosTelefono) > 15) {
        throw new RuntimeException('Ingresá un teléfono de contacto válido.');
    }
    $fechaHora = ($datos['fecha_retiro'] ?? '') . ' ' . ($datos['hora_retiro'] ?? '');
    $retiro = DateTimeImmutable::createFromFormat('!Y-m-d H:i', $fechaHora);
    if (!$retiro || $retiro->format('Y-m-d H:i') !== $fechaHora) {
        throw new RuntimeException('Ingresá una fecha y hora de retiro válidas.');
    }
    if ($esNuevo && $retiro < new DateTimeImmutable('now -1 minute')) {
        throw new RuntimeException('La fecha de retiro debe ser futura.');
    }
    validate_text($datos['notas'] ?? '', 'las notas', 2000, false);
    $estado = $datos['estado'] ?? 'Pendiente';
    if (!in_array($estado, states(), true)) {
        throw new RuntimeException('Seleccioná un estado válido.');
    }
    if ($estado === 'Cancelado') {
        throw new RuntimeException('Usá la opción Cancelar pedido para indicar el motivo.');
    }
}

/** Se llama dentro de la transacción para comprobar la versión bajo bloqueo. */
function obtenerPedidoEditable(int $idPedido, ?int $versionEsperada): array
{
    $pedido = one('SELECT * FROM pedidos WHERE id_pedido = ? FOR UPDATE', 'i', [$idPedido]);
    if (!$pedido || in_array($pedido['estado'], ['Cancelado', 'Entregado'], true)) {
        throw new RuntimeException('Este pedido ya no se puede modificar.');
    }
    if ($versionEsperada === null || $versionEsperada !== (int) $pedido['version']) {
        throw new RuntimeException('Este pedido fue modificado o el formulario es anterior. Volvé al listado y abrilo nuevamente antes de guardar.');
    }
    return $pedido;
}

function cargarHistorialPedido(?int $idPedido, ?int $versionEsperada): array
{
    if ($idPedido === null) {
        return [];
    }
    obtenerPedidoEditable($idPedido, $versionEsperada);
    $historial = [];
    foreach (obtenerLineasPedido($idPedido) as $linea) {
        // Los datos antiguos pueden no tener una referencia al catálogo actual.
        $clave = $linea['id_producto'] ?: 'historico:' . $linea['id_detalle'];
        $historial[$clave] = $linea;
    }
    return $historial;
}

function validarCantidadPedido(mixed $valor): int
{
    $cantidad = filter_var($valor, FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 1, 'max_range' => 9999],
        ]);
    if (!$cantidad) {
        throw new RuntimeException('Seleccioná productos y cantidades enteras entre 1 y 9999.');
    }
    return $cantidad;
}

/** Obtiene el precio en el servidor; nunca toma un precio enviado por el navegador. */
function prepararLineaPedido(mixed $idRecibido, mixed $cantidadRecibida, array $historial): array
{
    $cantidad = validarCantidadPedido($cantidadRecibida);
    $anterior = is_scalar($idRecibido) ? ($historial[$idRecibido] ?? null) : null;
    if (is_string($idRecibido) && str_starts_with($idRecibido, 'historico:') && $anterior) {
        return [
            'id_producto' => null,
            'nombre_producto' => $anterior['nombre_producto'],
            'cantidad' => $cantidad,
            'precio_unitario' => $anterior['precio_unitario'],
        ];
    }
    $idProducto = filter_var($idRecibido, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    if (!$idProducto) {
        throw new RuntimeException('Seleccioná productos y cantidades enteras entre 1 y 9999.');
    }
    $producto = one('SELECT * FROM productos WHERE id_producto = ? FOR UPDATE', 'i', [$idProducto]);
    $disponible = $producto && $producto['activo'] && !$producto['eliminado_en'];
    if (!$producto || (!$disponible && !$anterior)) {
        throw new RuntimeException('Uno de los productos ya no está disponible.');
    }
    // Al editar conservamos el nombre y el precio originales de ese producto.
    return [
        'id_producto' => $idProducto,
        'nombre_producto' => $anterior['nombre_producto'] ?? $producto['nombre'],
        'cantidad' => $cantidad,
        'precio_unitario' => $anterior['precio_unitario'] ?? $producto['precio'],
    ];
}

function calcularTotalPedido(array $lineas): float
{
    $centavos = 0;
    foreach ($lineas as $linea) {
        $precioCentavos = (int) round((float) $linea['precio_unitario'] * 100);
        $centavos += $precioCentavos * $linea['cantidad'];
    }
    if ($centavos > 9999999999) {
        throw new RuntimeException('El total del pedido supera el máximo permitido.');
    }
    return $centavos / 100;
}

function guardarCabeceraPedido(array $datos, array $lineas, float $total, ?int $idPedido): int
{
    global $conexion;
    $resumen = [];
    foreach ($lineas as $linea) {
        $resumen[] = $linea['nombre_producto'] . ' × ' . $linea['cantidad'];
    }
    $parametros = [
        mb_substr(implode(', ', $resumen), 0, 120),
        array_sum(array_column($lineas, 'cantidad')),
        $datos['cliente_nombre'],
        $datos['telefono'],
        $datos['fecha_retiro'],
        $datos['hora_retiro'],
        $datos['notas'] ?? '',
        $datos['estado'] ?? 'Pendiente',
        $total,
    ];
    if ($idPedido === null) {
        $parametros[] = $datos['id_cliente'] ?? null;
        sql(
            'INSERT INTO pedidos
                (producto, cantidad, cliente_nombre, telefono, fecha_retiro, hora_retiro, notas, estado, total, id_cliente)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
            'sissssssdi',
            $parametros
        );
        return (int) $conexion->insert_id;
    }
    $parametros[] = $idPedido;
    sql(
        'UPDATE pedidos SET producto=?, cantidad=?, cliente_nombre=?, telefono=?,
            fecha_retiro=?, hora_retiro=?, notas=?, estado=?, total=?, version=version+1 WHERE id_pedido=?',
        'sissssssdi',
        $parametros
    );
    sql('DELETE FROM pedido_detalles WHERE id_pedido = ?', 'i', [$idPedido]);
    return $idPedido;
}

/** Cabecera y líneas se guardan juntas: ante un error se revierte toda la operación. */
function guardarPedido(array $datos, array $idsProductos, array $cantidades, ?int $idPedido = null, ?int $versionEsperada = null): int
{
    global $conexion;
    validarDatosPedido($datos, $idPedido === null);
    if (!$idsProductos || count($idsProductos) !== count($cantidades) || count($idsProductos) > 50) {
        throw new RuntimeException('Agregá entre 1 y 50 líneas al pedido.');
    }
    $preciosEsperados = $datos['precios_esperados'] ?? null;
    if ($preciosEsperados !== null && !is_array($preciosEsperados)) {
        throw new RuntimeException('Revisá los precios del pedido antes de confirmar.');
    }
    $conexion->begin_transaction();
    try {
        $historial = cargarHistorialPedido($idPedido, $versionEsperada);
        $lineas = [];
        foreach ($idsProductos as $indice => $idProducto) {
            $linea = prepararLineaPedido($idProducto, $cantidades[$indice], $historial);
            // La confirmación pública comprueba su precio con el producto ya bloqueado.
            if ($preciosEsperados !== null) {
                $precioEsperado = $preciosEsperados[$linea['id_producto']] ?? null;
                if ($precioEsperado === null
                    || (int) round((float) $precioEsperado * 100) !== (int) round((float) $linea['precio_unitario'] * 100)) {
                    throw new RuntimeException('El precio de un producto cambió. El pedido no se registró. Volvé al carrito para revisarlo.');
                }
            }
            $lineas[] = $linea;
        }
        $total = calcularTotalPedido($lineas);
        $idPedido = guardarCabeceraPedido($datos, $lineas, $total, $idPedido);
        foreach ($lineas as $linea) {
            sql(
                'INSERT INTO pedido_detalles
                    (id_pedido, id_producto, nombre_producto, cantidad, precio_unitario)
                 VALUES (?, ?, ?, ?, ?)',
                'iisid',
                [$idPedido, $linea['id_producto'], $linea['nombre_producto'], $linea['cantidad'], $linea['precio_unitario']]
            );
        }
        $conexion->commit();
        return $idPedido;
    } catch (Throwable $error) {
        $conexion->rollback();
        throw $error;
    }
}

function motivosCancelacion(): array
{
    return ['Solicitud del cliente', 'Sin disponibilidad', 'Error de registro', 'Otro motivo'];
}

function cancelarPedido(int $idPedido, string $motivo, ?int $versionEsperada = null): void
{
    global $conexion;
    if (!in_array($motivo, motivosCancelacion(), true)) {
        throw new RuntimeException('Seleccioná un motivo de cancelación.');
    }
    $conexion->begin_transaction();
    try {
        obtenerPedidoEditable($idPedido, $versionEsperada);
        sql(
            "UPDATE pedidos SET estado='Cancelado', motivo_cancelacion=?, version=version+1 WHERE id_pedido=?",
            'si',
            [$motivo, $idPedido]
        );
        $conexion->commit();
    } catch (Throwable $error) {
        $conexion->rollback();
        throw $error;
    }
}
