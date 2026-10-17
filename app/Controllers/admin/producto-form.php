<?php
require_once __DIR__ . '/../../Core/auth.php';
require_once __DIR__ . '/../../Services/productos.php';
if (!in_array($tipoGestion ?? '', ['agregar-producto', 'editar-producto', 'eliminar-producto'], true)) {
    http_response_code(404);
    exit('Página no encontrada.');
}
$esAdministracion = true;
$seccionActiva = 'productos';
$esEdicion = $tipoGestion === 'editar-producto';
$esEliminacion = $tipoGestion === 'eliminar-producto';
$id = (int) get('id', post('id_producto'));
$producto = null;
if ($esEdicion || $esEliminacion) {
    $producto = one('SELECT * FROM productos WHERE id_producto=? AND eliminado_en IS NULL', 'i', [$id]);
    if (!$producto) {
        flash('Seleccioná un producto del listado.');
        redirect('app/Views/productos.php');
    }
}
$tituloPagina = $esEliminacion ? 'Eliminar producto' : ($esEdicion ? 'Editar producto' : 'Agregar producto');
$error = '';
$valores = $producto ?? [
    'nombre' => '', 'categoria' => 'Panes', 'descripcion' => '', 'precio' => '',
    'presentacion' => 'Unidad', 'ingredientes' => '', 'alergenos' => '',
    'activo' => 1, 'imagen' => '',
];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        check_csrf();
        if ($esEliminacion) {
            archivarProducto($id);
            flash('Producto eliminado del catálogo. Los pedidos anteriores conservan su información.');
            redirect('app/Views/productos.php');
        }
        // Solo se aceptan los campos editables; la imagen la decide el servidor.
        foreach (['nombre', 'categoria', 'descripcion', 'precio', 'presentacion', 'ingredientes', 'alergenos', 'activo'] as $clave) {
            $valores[$clave] = post($clave);
        }
        guardarProducto($valores, $_FILES['imagen'] ?? null, $producto);
        flash($esEdicion ? 'Producto actualizado correctamente.' : 'Producto agregado correctamente.');
        redirect('app/Views/productos.php');
    } catch (Throwable $exception) {
        $error = error_text($exception);
    }
}
