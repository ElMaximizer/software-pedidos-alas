<?php
require_once __DIR__ . '/../../Core/auth.php';
$esAdministracion = true;
$seccionActiva = 'pedidos';
$tituloPagina = 'Gestión de pedidos';
$busqueda = get('q');
$estado = get('estado');
$fecha = get('fecha');
$condiciones = '1=1';
$tipos = '';
$parametros = [];
if ($busqueda !== '') {
    $condiciones .= ' AND (cliente_nombre LIKE ? OR CAST(id_pedido AS CHAR) LIKE ?)';
    $tipos .= 'ss';
    $parametros = ['%' . $busqueda . '%', '%' . ltrim($busqueda, '#') . '%'];
}
if (in_array($estado, states(), true)) {
    $condiciones .= ' AND estado=?';
    $tipos .= 's';
    $parametros[] = $estado;
}
if ($fecha === 'hoy') {
    $condiciones .= ' AND fecha_retiro=?';
    $tipos .= 's';
    $parametros[] = date('Y-m-d');
} elseif ($fecha === 'proximos') {
    $condiciones .= " AND fecha_retiro >= ? AND estado NOT IN ('Cancelado','Entregado')";
    $tipos .= 's';
    $parametros[] = date('Y-m-d');
}
$total = (int) one("SELECT COUNT(*) AS n FROM pedidos WHERE $condiciones", $tipos, $parametros)['n'];
$porPagina = 20;
$ultimaPagina = max(1, (int) ceil($total / $porPagina));
$paginaSolicitada = filter_var(get('pagina', '1'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$pagina = min($paginaSolicitada ?: 1, $ultimaPagina);
$desplazamiento = ($pagina - 1) * $porPagina;
$pedidos = rows("SELECT * FROM pedidos WHERE $condiciones ORDER BY fecha_retiro DESC, hora_retiro, id_pedido DESC LIMIT $porPagina OFFSET $desplazamiento", $tipos, $parametros);
