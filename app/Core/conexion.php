<?php
$conexion = new mysqli("localhost", "root", "", "login_php");
if ($conexion->connect_errno) {
    die("Error de conexión: " . $conexion->connect_error);
}
?>