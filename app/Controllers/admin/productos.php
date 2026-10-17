<?php
require_once __DIR__ . '/../../Core/auth.php';
$esAdministracion = true;
$seccionActiva = 'productos';
$tituloPagina = 'Gestión de productos';
$busqueda = get('q');
$estado = get('estado');
$condiciones = 'eliminado_en IS NULL';
$tipos = '';
$parametros = [];
if ($busqueda !== '') {
    $condiciones .= " AND (nombre LIKE ?
        OR CONCAT('PRD-', LPAD(id_producto, GREATEST(3, CHAR_LENGTH(id_producto)), '0')) LIKE ?)";
    $tipos .= 'ss';
    $parametros = ['%' . $busqueda . '%', '%' . $busqueda . '%'];
}
if (in_array($estado, ['0', '1'], true)) {
    $condiciones .= ' AND activo = ?';
    $tipos .= 'i';
    $parametros[] = (int) $estado;
}
$productos = rows("SELECT * FROM productos WHERE $condiciones ORDER BY id_producto", $tipos, $parametros);
