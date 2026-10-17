<?php
require_once __DIR__ . '/../../Core/app.php';
$seccionActiva = 'inicio';
$tituloPagina = 'Inicio';
$productos = rows(
    "SELECT * FROM productos WHERE activo = 1 AND eliminado_en IS NULL
     ORDER BY FIELD(nombre, 'Pan integral', 'Bizcocho de chocolate', 'Torta de cumpleaños') = 0,
              FIELD(nombre, 'Pan integral', 'Bizcocho de chocolate', 'Torta de cumpleaños'),
              destacado DESC, id_producto LIMIT 3"
);
$categoriasInicio = [
    ['Panes', '01 / PANADERÍA', 'Integral, centeno y los clásicos de cada mesa.'],
    ['Bizcochos', '02 / DULCERÍA', 'Para el desayuno, la merienda y el antojo.'],
    ['Tortas', '03 / CELEBRACIONES', 'El detalle especial para compartir.'],
];
