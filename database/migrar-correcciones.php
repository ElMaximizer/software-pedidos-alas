<?php
// Actualización puntual de una instalación existente. No agrega datos demo.
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit;
}
require __DIR__ . '/../app/Core/conexion.php';

$columnas = array_column($conexion->query('SHOW COLUMNS FROM pedidos')->fetch_all(MYSQLI_ASSOC), 'Field');
if (!in_array('version', $columnas, true)) {
    $conexion->query('ALTER TABLE pedidos ADD COLUMN version INT UNSIGNED NOT NULL DEFAULT 1');
}

$conexion->query(
    'CREATE TABLE IF NOT EXISTS intentos_acceso (
        cedula INT UNSIGNED PRIMARY KEY,
        intentos TINYINT UNSIGNED NOT NULL DEFAULT 0,
        bloqueado_hasta DATETIME NULL,
        actualizado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
);
echo "Actualización completada: versiones de pedidos y bloqueo persistente de acceso.\n";
