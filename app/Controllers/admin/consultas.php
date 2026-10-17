<?php
require_once __DIR__ . '/../../Core/auth.php';
$esAdministracion = true;
$seccionActiva = 'resumen';
$tituloPagina = 'Consultas recibidas';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        check_csrf();
        sql('UPDATE mensajes_contacto SET leido=1 WHERE id_mensaje=?', 'i', [(int) post('id_mensaje')]);
        flash('Consulta marcada como leída.');
        redirect('app/Views/consultas.php');
    } catch (Throwable $exception) {
        $error = error_text($exception);
    }
}
$mensajes = rows('SELECT * FROM mensajes_contacto ORDER BY creado_en DESC LIMIT 100');
