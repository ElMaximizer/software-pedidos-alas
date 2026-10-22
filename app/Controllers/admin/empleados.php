<?php
require_once __DIR__ . '/../../Core/auth.php';
require_once __DIR__ . '/../../Services/usuarios.php';
$usuarioActual = require_owner();
$esAdministracion = true;
$seccionActiva = 'empleados';
$tituloPagina = 'Gestión de empleados';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        check_csrf();
        if (post('accion') === 'crear') {
            crearEmpleado(post('nombre'), post('cedula'), post('email'), post('password'), post('confirm_password'), post('rol'));
            flash('La cuenta de personal fue creada y ya puede ingresar al sistema.');
        } elseif (post('accion') === 'desactivar') {
            desactivarEmpleado((int) post('id_empleado'));
            flash('Se quitó el acceso del empleado. Sus registros históricos se conservaron.');
        } else {
            throw new RuntimeException('Acción no válida.');
        }
        redirect('app/Views/empleados.php');
    } catch (Throwable $exception) {
        $error = error_text($exception);
    }
}

$empleados = rows("SELECT id_sol, nombre, cedula, email, estado, rol, creado_en FROM solicitud_usuario
    WHERE rol IN ('empleado','administrador') ORDER BY estado='aprobado' DESC, rol, nombre, id_sol DESC LIMIT 200");

