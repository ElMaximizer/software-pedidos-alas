<?php
$conexion = new mysqli("localhost", "root", "", "alas_bd");
if ($conexion->connect_errno) {
    die("Error de conexión: " . $conexion->connect_error);
}
?>