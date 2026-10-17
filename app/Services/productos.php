<?php
require_once __DIR__ . '/../Core/app.php';
function validarProducto(array $datos): array
{
    $nombre = validate_text($datos['nombre'], 'el nombre', 120);
    $descripcion = validate_text($datos['descripcion'], 'la descripción', 280, false);
    if (!in_array($datos['categoria'], categories(), true)) {
        throw new RuntimeException('Seleccioná una categoría válida.');
    }
    $precio = str_replace(',', '.', $datos['precio']);
    if (!preg_match('/^\d{1,8}(\.\d{1,2})?$/', $precio) || (float) $precio <= 0) {
        throw new RuntimeException('Ingresá un precio mayor que cero, con hasta dos decimales.');
    }
    if (!in_array((string) $datos['activo'], ['0', '1'], true)) {
        throw new RuntimeException('Seleccioná un estado válido.');
    }
    return [
        'nombre' => $nombre,
        'descripcion' => $descripcion,
        'precio' => (float) $precio,
        'categoria' => $datos['categoria'],
        'presentacion' => validate_text($datos['presentacion'], 'la presentación', 80),
        'ingredientes' => validate_text($datos['ingredientes'], 'los ingredientes', 5000, false),
        'alergenos' => validate_text($datos['alergenos'], 'los alérgenos', 500, false),
        'activo' => (int) $datos['activo'],
    ];
}

/** Devuelve la ruta relativa a public, o null si no se seleccionó un archivo. */
function guardarImagenProducto(?array $archivo): ?string
{
    if (!$archivo || $archivo['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    if ($archivo['error'] !== UPLOAD_ERR_OK || $archivo['size'] > 4 * 1024 * 1024) {
        throw new RuntimeException('Elegí una imagen JPG o PNG de hasta 4 MB.');
    }
    $tipo = (new finfo(FILEINFO_MIME_TYPE))->file($archivo['tmp_name']);
    $dimensiones = getimagesize($archivo['tmp_name']);
    if (!in_array($tipo, ['image/jpeg', 'image/png'], true) || !$dimensiones
        || $dimensiones[0] > 8000 || $dimensiones[1] > 8000) {
        throw new RuntimeException('El archivo debe ser una imagen JPG o PNG válida de hasta 8000 píxeles.');
    }
    $carpeta = __DIR__ . '/../../public/uploads/productos';
    if (!is_dir($carpeta)) {
        mkdir($carpeta, 0755, true);
    }
    $extension = $tipo === 'image/png' ? '.png' : '.jpg';
    $ruta = 'uploads/productos/' . bin2hex(random_bytes(16)) . $extension;
    if (!move_uploaded_file($archivo['tmp_name'], __DIR__ . '/../../public/' . $ruta)) {
        throw new RuntimeException('No pudimos guardar la imagen.');
    }
    return $ruta;
}

function guardarProducto(array $datos, ?array $archivo, ?array $productoAnterior): void
{
    $producto = validarProducto($datos);
    $imagenNueva = null;
    try {
        $imagenNueva = guardarImagenProducto($archivo);
        $imagen = $imagenNueva ?? ($productoAnterior['imagen'] ?? '');
        // El orden coincide con las columnas del INSERT y del UPDATE.
        $parametros = [
            $producto['nombre'],
            $producto['descripcion'],
            $producto['precio'],
            $producto['categoria'],
            $producto['presentacion'],
            $producto['ingredientes'],
            $producto['alergenos'],
            $producto['activo'],
            $imagen,
        ];
        if ($productoAnterior) {
            $parametros[] = (int) $productoAnterior['id_producto'];
            sql(
                'UPDATE productos SET nombre=?, descripcion=?, precio=?, categoria=?, presentacion=?,
                    ingredientes=?, alergenos=?, activo=?, imagen=?
                 WHERE id_producto=? AND eliminado_en IS NULL',
                'ssdssssisi',
                $parametros
            );
        } else {
            sql(
                'INSERT INTO productos
                    (nombre, descripcion, precio, categoria, presentacion, ingredientes, alergenos, activo, imagen)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)',
                'ssdssssis',
                $parametros
            );
        }
    } catch (Throwable $error) {
        // Si falla MySQL se retira solamente el archivo que acaba de subirse.
        if ($imagenNueva) {
            unlink(__DIR__ . '/../../public/' . $imagenNueva);
        }
        throw $error;
    }
}

function archivarProducto(int $idProducto): void
{
    // No se borra la fila: los pedidos anteriores siguen teniendo su referencia.
    sql(
        'UPDATE productos SET activo=0, eliminado_en=NOW() WHERE id_producto=? AND eliminado_en IS NULL',
        'i',
        [$idProducto]
    );
}
