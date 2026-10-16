<?php
require_once __DIR__ . '/../Core/auth.php';
require_once __DIR__ . '/../Core/conexion.php';

$configuraciones = [
    'agregar-producto' => [
        'titulo' => 'Agregar producto',
        'boton' => 'Agregar',
        'operacion' => 'crear_producto',
        'campos' => [
            ['nombre' => 'nombre', 'etiqueta' => 'Nombre', 'tipo' => 'text', 'placeholder' => 'Ingresa el nombre del producto'],
            ['nombre' => 'descripcion', 'etiqueta' => 'Descripción', 'tipo' => 'text', 'placeholder' => 'Ingresa la descripción del producto'],
            ['nombre' => 'precio', 'etiqueta' => 'Precio', 'tipo' => 'number', 'placeholder' => 'Ingresa el precio del producto', 'extra' => 'min="0" step="0.01"'],
        ],
    ],
    'editar-producto' => [
        'titulo' => 'Editar producto',
        'boton' => 'Editar',
        'operacion' => 'buscar_producto',
        'campos' => [
            ['nombre' => 'id_producto', 'etiqueta' => 'ID producto', 'tipo' => 'number', 'placeholder' => 'Ingresa el ID del producto', 'extra' => 'min="1"'],
        ],
    ],
    'eliminar-producto' => [
        'titulo' => 'Eliminar producto',
        'boton' => 'Eliminar',
        'operacion' => 'eliminar_producto',
        'campos' => [
            ['nombre' => 'id_producto', 'etiqueta' => 'ID producto', 'tipo' => 'number', 'placeholder' => 'Ingresa el ID del producto', 'extra' => 'min="1"'],
        ],
    ],
    'agregar-pedido' => [
        'titulo' => 'Agregar pedido',
        'boton' => 'Agregar',
        'operacion' => 'crear_pedido',
        'campos' => [
            ['nombre' => 'id_pedido', 'etiqueta' => 'ID pedido', 'tipo' => 'number', 'placeholder' => 'Ingresa el ID del pedido', 'extra' => 'min="1"'],
            ['nombre' => 'producto', 'etiqueta' => 'Producto', 'tipo' => 'text', 'placeholder' => 'Ingresa el producto'],
            ['nombre' => 'cantidad', 'etiqueta' => 'Cantidad', 'tipo' => 'number', 'placeholder' => 'Ingresa la cantidad', 'extra' => 'min="1"'],
        ],
    ],
    'editar-pedido' => [
        'titulo' => 'Editar pedido',
        'boton' => 'Editar',
        'operacion' => 'buscar_pedido',
        'campos' => [
            ['nombre' => 'id_pedido', 'etiqueta' => 'ID pedido', 'tipo' => 'number', 'placeholder' => 'Ingresa el ID del pedido', 'extra' => 'min="1"'],
        ],
    ],
    'eliminar-pedido' => [
        'titulo' => 'Eliminar pedido',
        'boton' => 'Eliminar',
        'operacion' => 'eliminar_pedido',
        'campos' => [
            ['nombre' => 'id_pedido', 'etiqueta' => 'ID pedido', 'tipo' => 'number', 'placeholder' => 'Ingresa el ID del pedido', 'extra' => 'min="1"'],
        ],
    ],
];

if (!isset($tipoGestion, $configuraciones[$tipoGestion])) {
    http_response_code(404);
    exit('Página no encontrada.');
}

$configuracion = $configuraciones[$tipoGestion];
$tituloPagina = $configuracion['titulo'];
$mensaje = '';
$tipoMensaje = 'exito';
$registroEditar = null;

function valorPost(string $campo): string
{
    return trim((string) ($_POST[$campo] ?? ''));
}

function validarCsrf(): bool
{
    return isset($_POST['csrf_token'], $_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], (string) $_POST['csrf_token']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validarCsrf()) {
        $mensaje = 'La sesión del formulario venció. Volvé a intentarlo.';
        $tipoMensaje = 'error';
    } else {
        try {
            $operacion = valorPost('operacion');

            if ($operacion === 'crear_producto') {
                $nombre = valorPost('nombre');
                $descripcion = valorPost('descripcion');
                $precio = (float) valorPost('precio');

                if ($nombre === '' || $descripcion === '' || $precio <= 0) {
                    throw new RuntimeException('Completá todos los datos del producto.');
                }

                $stmt = $conexion->prepare('INSERT INTO productos (nombre, descripcion, precio) VALUES (?, ?, ?)');
                $stmt->bind_param('ssd', $nombre, $descripcion, $precio);
                $stmt->execute();
                $mensaje = 'Producto agregado correctamente.';
                $_POST = [];
            } elseif ($operacion === 'eliminar_producto') {
                $id = (int) valorPost('id_producto');
                $stmt = $conexion->prepare('DELETE FROM productos WHERE id_producto = ?');
                $stmt->bind_param('i', $id);
                $stmt->execute();
                if ($stmt->affected_rows === 0) {
                    throw new RuntimeException('No encontramos un producto con ese ID.');
                }
                $mensaje = 'Producto eliminado correctamente.';
                $_POST = [];
            } elseif ($operacion === 'buscar_producto') {
                $id = (int) valorPost('id_producto');
                $stmt = $conexion->prepare('SELECT id_producto, nombre, descripcion, precio FROM productos WHERE id_producto = ?');
                $stmt->bind_param('i', $id);
                $stmt->execute();
                $registroEditar = $stmt->get_result()->fetch_assoc();
                if (!$registroEditar) {
                    throw new RuntimeException('No encontramos un producto con ese ID.');
                }
            } elseif ($operacion === 'guardar_producto') {
                $id = (int) valorPost('id_producto');
                $nombre = valorPost('nombre');
                $descripcion = valorPost('descripcion');
                $precio = (float) valorPost('precio');
                if ($id <= 0 || $nombre === '' || $descripcion === '' || $precio <= 0) {
                    throw new RuntimeException('Completá todos los datos del producto.');
                }
                $stmt = $conexion->prepare('UPDATE productos SET nombre = ?, descripcion = ?, precio = ? WHERE id_producto = ?');
                $stmt->bind_param('ssdi', $nombre, $descripcion, $precio, $id);
                $stmt->execute();
                $mensaje = 'Producto actualizado correctamente.';
                $_POST = [];
            } elseif ($operacion === 'crear_pedido') {
                $id = (int) valorPost('id_pedido');
                $producto = valorPost('producto');
                $cantidad = (int) valorPost('cantidad');
                if ($id <= 0 || $producto === '' || $cantidad <= 0) {
                    throw new RuntimeException('Completá todos los datos del pedido.');
                }
                $stmt = $conexion->prepare('INSERT INTO pedidos (id_pedido, producto, cantidad) VALUES (?, ?, ?)');
                $stmt->bind_param('isi', $id, $producto, $cantidad);
                $stmt->execute();
                $mensaje = 'Pedido agregado correctamente.';
                $_POST = [];
            } elseif ($operacion === 'eliminar_pedido') {
                $id = (int) valorPost('id_pedido');
                $stmt = $conexion->prepare('DELETE FROM pedidos WHERE id_pedido = ?');
                $stmt->bind_param('i', $id);
                $stmt->execute();
                if ($stmt->affected_rows === 0) {
                    throw new RuntimeException('No encontramos un pedido con ese ID.');
                }
                $mensaje = 'Pedido eliminado correctamente.';
                $_POST = [];
            } elseif ($operacion === 'buscar_pedido') {
                $id = (int) valorPost('id_pedido');
                $stmt = $conexion->prepare('SELECT id_pedido, producto, cantidad FROM pedidos WHERE id_pedido = ?');
                $stmt->bind_param('i', $id);
                $stmt->execute();
                $registroEditar = $stmt->get_result()->fetch_assoc();
                if (!$registroEditar) {
                    throw new RuntimeException('No encontramos un pedido con ese ID.');
                }
            } elseif ($operacion === 'guardar_pedido') {
                $id = (int) valorPost('id_pedido');
                $producto = valorPost('producto');
                $cantidad = (int) valorPost('cantidad');
                if ($id <= 0 || $producto === '' || $cantidad <= 0) {
                    throw new RuntimeException('Completá todos los datos del pedido.');
                }
                $stmt = $conexion->prepare('UPDATE pedidos SET producto = ?, cantidad = ? WHERE id_pedido = ?');
                $stmt->bind_param('sii', $producto, $cantidad, $id);
                $stmt->execute();
                $mensaje = 'Pedido actualizado correctamente.';
                $_POST = [];
            }
        } catch (Throwable $error) {
            $mensaje = $error instanceof RuntimeException
                ? $error->getMessage()
                : 'No pudimos completar la operación. Revisá la base de datos e intentá nuevamente.';
            $tipoMensaje = 'error';
        }
    }
}

require __DIR__ . '/partials/admin-header.php';
?>
    <main class="contenido-administracion">
        <section class="tarjeta-gestion<?= $registroEditar ? ' tarjeta-gestion--extendida' : '' ?>" aria-labelledby="titulo-gestion">
            <h1 id="titulo-gestion"><?= htmlspecialchars($configuracion['titulo'], ENT_QUOTES, 'UTF-8') ?></h1>

            <?php if ($mensaje !== ''): ?>
                <div class="mensaje-formulario mensaje-formulario--<?= $tipoMensaje ?>" role="status">
                    <?= htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8') ?>
                </div>
            <?php endif; ?>

            <?php if ($registroEditar): ?>
                <?php $esProducto = isset($registroEditar['id_producto']); ?>
                <form class="formulario-gestion" method="post">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">
                    <input type="hidden" name="operacion" value="<?= $esProducto ? 'guardar_producto' : 'guardar_pedido' ?>">
                    <input type="hidden" name="<?= $esProducto ? 'id_producto' : 'id_pedido' ?>" value="<?= (int) ($registroEditar[$esProducto ? 'id_producto' : 'id_pedido']) ?>">

                    <?php if ($esProducto): ?>
                        <label class="campo-gestion">Nombre<input type="text" name="nombre" value="<?= htmlspecialchars($registroEditar['nombre'], ENT_QUOTES, 'UTF-8') ?>" required></label>
                        <label class="campo-gestion">Descripción<input type="text" name="descripcion" value="<?= htmlspecialchars($registroEditar['descripcion'], ENT_QUOTES, 'UTF-8') ?>" required></label>
                        <label class="campo-gestion">Precio<input type="number" name="precio" min="0" step="0.01" value="<?= htmlspecialchars((string) $registroEditar['precio'], ENT_QUOTES, 'UTF-8') ?>" required></label>
                    <?php else: ?>
                        <label class="campo-gestion">Producto<input type="text" name="producto" value="<?= htmlspecialchars($registroEditar['producto'], ENT_QUOTES, 'UTF-8') ?>" required></label>
                        <label class="campo-gestion">Cantidad<input type="number" name="cantidad" min="1" value="<?= (int) $registroEditar['cantidad'] ?>" required></label>
                    <?php endif; ?>

                    <button class="boton-principal" type="submit">Guardar cambios</button>
                </form>
            <?php else: ?>
                <form class="formulario-gestion" method="post">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">
                    <input type="hidden" name="operacion" value="<?= htmlspecialchars($configuracion['operacion'], ENT_QUOTES, 'UTF-8') ?>">

                    <?php foreach ($configuracion['campos'] as $campo): ?>
                        <label class="campo-gestion" for="<?= htmlspecialchars($campo['nombre'], ENT_QUOTES, 'UTF-8') ?>">
                            <?= htmlspecialchars($campo['etiqueta'], ENT_QUOTES, 'UTF-8') ?>
                            <input
                                type="<?= htmlspecialchars($campo['tipo'], ENT_QUOTES, 'UTF-8') ?>"
                                id="<?= htmlspecialchars($campo['nombre'], ENT_QUOTES, 'UTF-8') ?>"
                                name="<?= htmlspecialchars($campo['nombre'], ENT_QUOTES, 'UTF-8') ?>"
                                value="<?= htmlspecialchars(valorPost($campo['nombre']), ENT_QUOTES, 'UTF-8') ?>"
                                placeholder="<?= htmlspecialchars($campo['placeholder'], ENT_QUOTES, 'UTF-8') ?>"
                                <?= $campo['extra'] ?? '' ?>
                                required
                            >
                        </label>
                    <?php endforeach; ?>

                    <button class="boton-principal" type="submit"><?= htmlspecialchars($configuracion['boton'], ENT_QUOTES, 'UTF-8') ?></button>
                </form>
            <?php endif; ?>
        </section>
    </main>
<?php require __DIR__ . '/partials/admin-footer.php'; ?>

