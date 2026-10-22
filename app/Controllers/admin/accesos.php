<?php
require_once __DIR__ . '/../../Core/auth.php';
require_once __DIR__ . '/../../Services/usuarios.php';
require_owner();
$esAdministracion = true;
$seccionActiva = 'resumen';
$tituloPagina = 'Solicitudes de acceso';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        check_csrf();
        revisarSolicitud((int) post('id_usuario'), post('accion'), post('rol'), (int) $usuarioActual['id_sol']);
        flash('Solicitud revisada correctamente.');
        redirect('app/Views/accesos.php');
    } catch (Throwable $exception) {
        $error = error_text($exception);
    }
}
$solicitudes = rows("SELECT id_sol, nombre, cedula, email, estado, rol, creado_en FROM solicitud_usuario
    ORDER BY estado='pendiente' DESC, id_sol DESC LIMIT 100");

