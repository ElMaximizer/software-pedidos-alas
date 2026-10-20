<?php
$registro = registroPendiente();
if (!$registro) {
    flash('Volvé a completar tus datos para recibir un código de verificación.');
    redirect('app/Views/register.php');
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        check_csrf();
        $id = post('registro_id');
        if (!hash_equals($registro['id'], $id)) {
            throw new RuntimeException('Este formulario corresponde a otro registro. Actualizá la página.');
        }
        switch (post('accion')) {
            case 'confirmar':
                confirmarRegistro(trim(post('codigo')), $id);
                redirect('app/Views/solicitud-enviada.php');
            case 'reenviar':
                reenviarCodigoRegistro($id);
                flash('Enviamos un nuevo código. Usá el último que recibiste.');
                redirect('app/Views/verificar-correo.php');
            case 'cancelar':
                unset($_SESSION['registro_pendiente']);
                redirect('app/Views/register.php');
            default:
                throw new RuntimeException('Acción no válida.');
        }
    } catch (Throwable $exception) {
        $error = error_text($exception);
    }
}
$registro = registroPendiente();
if (!$registro) {
    redirect('app/Views/register.php');
}
