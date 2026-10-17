<?php
// Usa una tabla temporal en esta conexión; no cambia el catálogo real.
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit;
}
ini_set('session.save_path', sys_get_temp_dir());
require_once __DIR__ . '/../app/Core/app.php';

$administrador = one("SELECT id_sol FROM solicitud_usuario WHERE estado='aprobado' AND rol='administrador' LIMIT 1");
if (!$administrador) {
    throw new RuntimeException('Se necesita una cuenta de administrador para probar el controlador.');
}
$_SESSION['user_id'] = (int) $administrador['id_sol'];
$_SERVER['REQUEST_METHOD'] = 'GET';
$conexion->query(
    'CREATE TEMPORARY TABLE productos (
        id_producto INT UNSIGNED PRIMARY KEY,
        nombre VARCHAR(120) NOT NULL,
        descripcion VARCHAR(280) NOT NULL,
        precio DECIMAL(10,2) NOT NULL,
        activo TINYINT NOT NULL DEFAULT 1,
        eliminado_en DATETIME NULL
    )'
);
try {
    $identificadores = [1, 99, 999, 1000, 10000];
    foreach ($identificadores as $identificador) {
        sql(
            "INSERT INTO productos (id_producto, nombre, descripcion, precio) VALUES (?, ?, 'Prueba temporal', 10)",
            'is',
            [$identificador, 'Producto temporal ' . $identificador]
        );
    }
    foreach ($identificadores as $identificador) {
        $_GET = ['q' => 'PRD-' . str_pad((string) $identificador, 3, '0', STR_PAD_LEFT)];
        require __DIR__ . '/../app/Controllers/admin/productos.php';
        if (!in_array($identificador, array_column($productos, 'id_producto'))) {
            throw new RuntimeException('No se encontró el código del producto ' . $identificador);
        }
    }
    echo "OK: búsqueda por códigos de tres, cuatro y cinco dígitos sin recortes.\n";
} finally {
    $conexion->query('DROP TEMPORARY TABLE productos');
    session_destroy();
}
