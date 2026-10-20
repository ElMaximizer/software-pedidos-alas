<?php
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}
require_once __DIR__ . '/../app/Core/conexion.php';

$consulta = $conexion->prepare("SELECT COUNT(*) AS cantidad FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name='pedidos' AND column_name='id_cliente'");
$consulta->execute();
$columnaExiste = (int) $consulta->get_result()->fetch_assoc()['cantidad'] > 0;
$consulta->close();
if (!$columnaExiste) {
    $conexion->query('ALTER TABLE pedidos ADD COLUMN id_cliente INT UNSIGNED NULL AFTER id_pedido');
}

$consulta = $conexion->prepare("SELECT COUNT(*) AS cantidad FROM information_schema.statistics WHERE table_schema=DATABASE() AND table_name='pedidos' AND index_name='idx_pedidos_cliente_creado'");
$consulta->execute();
$indiceExiste = (int) $consulta->get_result()->fetch_assoc()['cantidad'] > 0;
$consulta->close();
if (!$indiceExiste) {
    $conexion->query('ALTER TABLE pedidos ADD INDEX idx_pedidos_cliente_creado (id_cliente, creado_en)');
}
echo "Migración del historial de pedidos de clientes completada.\n";
