<?php
// Confirmación de una solicitud guardada en esta sesión.
if (empty($_SESSION['solicitud_enviada'])) {
    redirect('app/Views/login.php');
}
