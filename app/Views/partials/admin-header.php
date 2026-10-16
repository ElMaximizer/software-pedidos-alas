<?php
$tituloPagina = $tituloPagina ?? 'Administración';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../public/css/css.css?v=20260920-1">
    <title><?= htmlspecialchars($tituloPagina, ENT_QUOTES, 'UTF-8') ?> | Panadería Alas</title>
</head>
<body class="pagina-administracion">
    <header class="encabezado-sitio encabezado-sitio--administracion">
        <div class="contenedor encabezado-contenido encabezado-contenido--administracion">
            <a class="marca marca--encabezado" href="principal.php" aria-label="Panadería Alas, panel principal">
                <span class="marca-texto">
                    <span class="marca-nombre">Panadería Alas</span>
                    <span class="marca-descripcion">Sistema de registro de pedidos</span>
                </span>
            </a>

            <nav class="navegacion-administracion" aria-label="Navegación principal">
                <a href="principal.php">Inicio</a>
                <a href="principal.php#productos">Productos</a>
                <a href="../../public/index.php#nosotros">Nosotros</a>
                <a href="../../public/index.php#contacto">Contacto</a>
            </nav>
        </div>
    </header>
