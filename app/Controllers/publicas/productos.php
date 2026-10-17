<?php
require_once __DIR__ . '/../../Core/app.php';
$seccionActiva = 'productos';
$tituloPagina = 'Productos';
$busqueda = get('q');
$categoria = get('categoria');
$condiciones = 'activo = 1 AND eliminado_en IS NULL';
$parametros = [];
$tipos = '';
if ($busqueda !== '') {
    $condiciones .= ' AND (nombre LIKE ? OR descripcion LIKE ? OR ingredientes LIKE ?)';
    $tipos .= 'sss';
    $parametros = array_fill(0, 3, '%' . $busqueda . '%');
}
if (in_array($categoria, categories(), true)) {
    $condiciones .= ' AND categoria = ?';
    $tipos .= 's';
    $parametros[] = $categoria;
}
$productos = rows("SELECT * FROM productos WHERE $condiciones ORDER BY destacado DESC, id_producto", $tipos, $parametros);
