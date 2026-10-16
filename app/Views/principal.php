<?php
require_once __DIR__ . '/../Core/auth.php';
$tituloPagina = 'Administración';
require __DIR__ . '/partials/admin-header.php';
?>
    <main class="contenido-administracion contenido-administracion--panel">
        <div class="panel-administracion">
            <section class="grupo-administracion" id="productos" aria-labelledby="titulo-productos">
                <h1 id="titulo-productos">Administrar<br>productos</h1>
                <div class="acciones-administracion">
                    <a href="editar-producto.php">Editar<br>producto</a>
                    <a href="agregar-producto.php">Agregar<br>producto</a>
                    <a href="eliminar-producto.php">Eliminar<br>producto</a>
                </div>
            </section>

            <section class="grupo-administracion" id="pedidos" aria-labelledby="titulo-pedidos">
                <h2 id="titulo-pedidos">Administrar<br>pedidos</h2>
                <div class="acciones-administracion">
                    <a href="editar-pedido.php">Editar<br>pedido</a>
                    <a href="agregar-pedido.php">Agregar<br>pedido</a>
                    <a href="eliminar-pedido.php">Eliminar<br>pedido</a>
                </div>
            </section>
        </div>
    </main>
<?php require __DIR__ . '/partials/admin-footer.php'; ?>
