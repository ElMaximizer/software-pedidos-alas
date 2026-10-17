<?php
// Funciones para mostrar valores escapados, importes, estados e imágenes.
function e(mixed $valor): string
{
    return htmlspecialchars((string) $valor, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function show_flash(): void
{
    if (isset($_SESSION['flash'])) {
        echo '<div class="notice success" role="status">' . e($_SESSION['flash']) . '</div>';
        unset($_SESSION['flash']);
    }
}

function money(mixed $importe): string
{
    $numero = (float) $importe;
    $decimales = $numero === floor($numero) ? 0 : 2;
    return '$ ' . number_format($numero, $decimales, ',', '.');
}

function categories(): array
{
    return ['Panes', 'Bizcochos', 'Tortas', 'Especiales'];
}

function states(): array
{
    return ['Pendiente', 'En preparación', 'Listo para retirar', 'Entregado', 'Cancelado'];
}

function badge(string $estado): string
{
    if (in_array($estado, ['Activo', 'Listo para retirar', 'Entregado', 'aprobado'], true)) {
        return 'success';
    }
    if (in_array($estado, ['Cancelado', 'rechazado'], true)) {
        return 'danger';
    }
    return 'neutral';
}

function product_image(array $producto): string
{
    if (!empty($producto['imagen'])) {
        return base_url('public/' . $producto['imagen']);
    }
    $archivo = match ($producto['categoria'] ?? 'Panes') {
        'Bizcochos' => 'croissant.svg',
        'Tortas', 'Especiales' => 'torta.svg',
        default => 'pan.svg',
    };
    return base_url('public/images/' . $archivo);
}
