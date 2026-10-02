<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panadería Alas</title>
    <link rel="stylesheet" href="../../public/css/css.css">
</head>
<body class="pagina-inicio">

    <!-- Encabezado del sitio -->
    <header class="encabezado-sitio">
        <div class="contenedor encabezado-contenido">
            <a href="index.php" class="marca marca--encabezado">
                <div class="marca-texto">
                    <span class="marca-nombre">PANADERÍA ALAS</span>
                    <span class="marca-descripcion">PANADERÍA ARTESANAL</span>
                </div>
            </a>
            
            <nav class="navegacion-principal">
                <ul>
                    <li><a href="#inicio">INICIO</a></li>
                    <li><a href="#productos">PRODUCTOS</a></li>
                    <li><a href="#nosotros">NOSOTROS</a></li>
                    <li><a href="#contacto">CONTACTO</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <!-- Contenido Principal -->
    <main class="contenido-principal-inicio">

        <!--  Bienvenida -->
        <section class="seccion-hero" id="inicio">
            <div class="contenedor contenedor-hero">
                <div class="hero-texto">
                    <span class="subtitulo-destacado">HORNEADO FRESCO CADA DÍA</span>
                    <h1>El sabor de siempre, hecho para compartir.</h1>
                    <p>Panes, bizcochos y tortas elaborados con dedicación, ingredientes seleccionados y ese aroma que convierte cualquier momento en casa.</p>
                    
                    <div class="hero-acciones">
                        <a href="#productos" class="boton-principal boton-hero">VER PRODUCTOS</a>
                        <a href="#nosotros" class="enlace-secundario">CONOCER LA PANADERÍA &rarr;</a>
                    </div>

                    <ul class="lista-atributos">
                        <li>HECHO CON DEDICACIÓN</li>
                        <li>INGREDIENTES SELECCIONADOS</li>
                    </ul>
                </div>

                <figure class="hero-imagen">
                    <img src="../../public/images/panes.png" alt="Panes y bizcochos recién horneados">
                    <figcaption class="etiqueta-imagen">
                        <strong>RECIÉN HORNEADO</strong>
                        <span>PANES · BIZCOCHOS · TORTAS</span>
                    </figcaption>
                </figure>
            </div>
        </section>

        <!-- Sección Categorías -->
        <section class="seccion-categorias" id="productos">
            <div class="contenedor">
                <h2 class="titulo-seccion">CATEGORÍAS</h2>

                <div class="grilla-categorias">
                    <article class="tarjeta-categoria">
                        <span class="categoria-tag">PANADERÍA</span>
                        <h3>Panes</h3>
                        <p>Integrales, blancos, centeno y especialidades del día.</p>
                    </article>

                    <article class="tarjeta-categoria">
                        <span class="categoria-tag">DULCERÍA</span>
                        <h3>Bizcochos</h3>
                        <p>Clásicos, rellenos y opciones para compartir.</p>
                    </article>

                    <article class="tarjeta-categoria">
                        <span class="categoria-tag">POSTRES</span>
                        <h3>Tortas</h3>
                        <p>Opciones para celebrar y disfrutar en casa.</p>
                    </article>

                    <article class="tarjeta-categoria">
                        <span class="categoria-tag">TEMPORADA</span>
                        <h3>Especiales</h3>
                        <p>Ediciones limitadas y creaciones de temporada.</p>
                    </article>
                </div>
            </div>
        </section>

        <!-- Sección Favoritos -->
        <section class="seccion-favoritos">
            <div class="contenedor">
                <header class="encabezado-seccion-favoritos">
                    <div>
                        <h2 class="titulo-seccion">FAVORITOS</h2>
                        <p>Los productos que más acompañan a Panadería Alas en cada mesa.</p>
                    </div>
                    <a href="#productos" class="enlace-ver-todo">Ver todo</a>
                </header>

                <div class="grilla-favoritos">
                    <article class="tarjeta-producto">
                        <figure class="producto-imagen">
                            <span class="tag-producto-imagen">Pan Integral</span>
                        </figure>
                        <h3>Pan integral</h3>
                        <p>Ideal para desayunar, merendar o acompañar tus platos favoritos.</p>
                    </article>

                    <article class="tarjeta-producto">
                        <figure class="producto-imagen">
                            <span class="tag-producto-imagen">Bizcocho de chocolate</span>
                        </figure>
                        <h3>Bizcocho de chocolate</h3>
                        <p>Un clásico con textura suave y el toque intenso para compartir.</p>
                    </article>

                    <article class="tarjeta-producto">
                        <figure class="producto-imagen">
                            <span class="tag-producto-imagen">Torta de cumpleaños</span>
                        </figure>
                        <h3>Torta de cumpleaños</h3>
                        <p>Una opción especial para celebrar con familia y amigos.</p>
                    </article>

                    <article class="tarjeta-producto">
                        <figure class="producto-imagen">
                            <span class="tag-producto-imagen">Pan de centeno</span>
                        </figure>
                        <h3>Pan de centeno</h3>
                        <p>Una opción más intensa y perfecta para acompañar platos caseros.</p>
                    </article>
                </div>
            </div>
        </section>

    </main>

    <!-- Pie de página -->
    <footer class="pie-sitio" id="contacto">
        <div class="contenedor">
            <div class="informacion-pie">
                <section class="bloque-pie">
                    <span class="subtitulo-pie">PANADERÍA ARTESANAL</span>
                    <h2>PANADERÍA ALAS</h2>
                    <p>El sabor de siempre, hecho para compartir.</p>
                </section>

                <nav class="bloque-pie">
                    <h2>EXPLORA ALAS</h2>
                    <ul class="menu-pie">
                        <li><a href="#inicio">Inicio</a></li>
                        <li><a href="#productos">Productos</a></li>
                        <li><a href="#nosotros">Nosotros</a></li>
                        <li><a href="#contacto">Contacto</a></li>
                    </ul>
                </nav>

                <aside class="bloque-pie">
                    <h2>¿Tienes un pedido en mente?</h2>
                    <p>Consulta por disponibilidad, productos y preparaciones especiales.</p>
                    <a href="#contacto" class="boton-principal boton-consulta">Hacer una consulta &rarr;</a>
                </aside>
            </div>

            <div class="separador-pie"></div>

            <div class="enlaces-pie-inferior">
                <p class="derechos-reservados">&copy; 2026 Panadería Alas</p>
                <a href="app/Views/login.php" class="enlace-equipo">Acceso del equipo &rarr;</a>
            </div>
        </div>
    </footer>

    <!-- Enlace al JS -->
    <script src="public/js/script.js"></script>
</body>
</html>