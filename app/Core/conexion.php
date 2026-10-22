<?php

$datos = require __DIR__ . '/db_credentials.php';
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conexion = new mysqli(
        $datos['host'],
        $datos['user'],
        $datos['password'],
        $datos['database']
    );
    $conexion->set_charset('utf8mb4');
} catch (mysqli_sql_exception $error) {
    error_log('No se pudo conectar con la base de datos. Código: ' . $error->getCode());
    http_response_code(500);
    exit('Error interno al conectar con la base de datos.');
}

