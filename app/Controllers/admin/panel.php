<?php
require_once __DIR__ . '/../../Core/auth.php';
$esAdministracion = true;
$seccionActiva = 'resumen';
$tituloPagina = 'Panel de administración';
$hoy = date('Y-m-d');
$pedidosHoy = one("SELECT COUNT(*) n, SUM(estado='Pendiente') pendientes FROM pedidos WHERE fecha_retiro=? AND estado<>'Cancelado'", 's', [$hoy]);
$productosActivos = one('SELECT COUNT(*) n, COUNT(DISTINCT categoria) categorias FROM productos WHERE activo=1 AND eliminado_en IS NULL');
$pedidosPendientes = one("SELECT COUNT(*) n FROM pedidos WHERE estado NOT IN ('Cancelado','Entregado')");
$pedidos = rows("SELECT * FROM pedidos WHERE fecha_retiro >= ? AND estado NOT IN ('Cancelado','Entregado') ORDER BY fecha_retiro, hora_retiro LIMIT 5", 's', [$hoy]);
$solicitudesPendientes = (int) one("SELECT COUNT(*) n FROM solicitud_usuario WHERE estado='pendiente'")['n'];
$consultasNuevas = (int) one('SELECT COUNT(*) n FROM mensajes_contacto WHERE leido=0')['n'];
