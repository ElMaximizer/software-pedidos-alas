<?php
require_once __DIR__ . '/../Core/app.php';
function motivosContacto(): array
{
    return [
        'Consulta sobre productos',
        'Disponibilidad',
        'Pedidos para compartir',
        'Tortas y celebraciones',
        'Otra consulta',
    ];
}

function guardarConsulta(string $nombre, string $correo, string $motivo, string $mensaje): void
{
    validate_text($nombre, 'tu nombre', 120);
    validarCorreo($correo);
    if (!in_array($motivo, motivosContacto(), true)) {
        throw new RuntimeException('Seleccioná un motivo válido.');
    }
    validate_text($mensaje, 'el mensaje', 5000);
    if (time() - ($_SESSION['ultimo_contacto'] ?? 0) < 30) {
        throw new RuntimeException('Esperá unos segundos antes de enviar otra consulta.');
    }
    sql(
        'INSERT INTO mensajes_contacto (nombre, email, motivo, mensaje) VALUES (?, ?, ?, ?)',
        'ssss',
        [$nombre, $correo, $motivo, $mensaje]
    );
    $_SESSION['ultimo_contacto'] = time();
    $_SESSION['consulta_enviada'] = true;
}
