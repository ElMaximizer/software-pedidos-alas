
<?php

$datos = require __DIR__ . '/db_credentials.php';

try {
    $conexion = new mysqli(
        $datos['host'],
        $datos['user'],
        $datos['password'],
        $datos['database']
    );

    $conexion->set_charset('utf8mb4');

} catch (mysqli_sql_exception $e) {
    error_log("Error de conexión MySQL: " . $e->getMessage());
    http_response_code(500);
    exit("Error interno al conectar con la base de datos.");
}
