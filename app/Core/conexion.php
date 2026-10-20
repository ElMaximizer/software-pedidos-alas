<?php
$conexion = new mysqli("sql306.infinityfree.com", "if0_43221024", "cQzFkySwVUTUEk4", "alas_bd");
if ($conexion->connect_errno) {
    die("Error de conexión: " . $conexion->connect_error);
}
$conexion->set_charset('utf8mb4');
