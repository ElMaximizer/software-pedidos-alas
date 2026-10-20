<?php
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}
require_once __DIR__ . '/../app/Core/conexion.php';

$conexion->query("CREATE TABLE IF NOT EXISTS clientes (
    id_cliente INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(120) NOT NULL,
    email VARCHAR(190) NOT NULL,
    password VARCHAR(255) NOT NULL,
    verificado TINYINT(1) NOT NULL DEFAULT 1,
    codigo_verificacion_hash VARCHAR(255) NULL,
    codigo_verificacion_vence DATETIME NULL,
    intentos_verificacion TINYINT UNSIGNED NOT NULL DEFAULT 0,
    ultimo_codigo_en DATETIME NULL,
    creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_clientes_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

$conexion->query("CREATE TABLE IF NOT EXISTS intentos_acceso_cliente (
    email VARCHAR(190) PRIMARY KEY,
    intentos TINYINT UNSIGNED NOT NULL DEFAULT 0,
    bloqueado_hasta DATETIME NULL,
    actualizado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

$columnas = [
    'verificado' => 'TINYINT(1) NOT NULL DEFAULT 0',
    'codigo_verificacion_hash' => 'VARCHAR(255) NULL',
    'codigo_verificacion_vence' => 'DATETIME NULL',
    'intentos_verificacion' => 'TINYINT UNSIGNED NOT NULL DEFAULT 0',
    'ultimo_codigo_en' => 'DATETIME NULL',
];
$consulta = $conexion->prepare('SELECT COUNT(*) AS cantidad FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name=? AND column_name=?');
foreach ($columnas as $columna => $definicion) {
    $tabla = 'clientes';
    $consulta->bind_param('ss', $tabla, $columna);
    $consulta->execute();
    $existe = (int) $consulta->get_result()->fetch_assoc()['cantidad'] > 0;
    if (!$existe) {
        $conexion->query("ALTER TABLE clientes ADD COLUMN `$columna` $definicion");
    }
}
$consulta->close();

echo "Migración de cuentas y verificación de clientes completada.\n";
