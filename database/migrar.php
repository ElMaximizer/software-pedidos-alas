<?php
// Ejecutar: C:\xampp\php\php.exe database/migrar.php
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit;
}
require __DIR__ . '/../app/Core/conexion.php';
date_default_timezone_set('America/Montevideo');
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$additions = [
    'solicitud_usuario' => [
        'nombre' => "VARCHAR(120) NOT NULL DEFAULT ''", 'email' => 'VARCHAR(190) NULL',
        'estado' => "VARCHAR(20) NOT NULL DEFAULT 'aprobado'", 'rol' => "VARCHAR(20) NOT NULL DEFAULT 'administrador'",
        'creado_en' => 'TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP',
    ],
    'productos' => [
        'categoria' => "VARCHAR(30) NOT NULL DEFAULT 'Panes'", 'presentacion' => "VARCHAR(80) NOT NULL DEFAULT 'Unidad'",
        'ingredientes' => 'TEXT NULL', 'alergenos' => "VARCHAR(500) NOT NULL DEFAULT ''", 'imagen' => "VARCHAR(255) NOT NULL DEFAULT ''",
        'destacado' => 'TINYINT(1) NOT NULL DEFAULT 0', 'eliminado_en' => 'DATETIME NULL',
    ],
    'pedidos' => [
        'cliente_nombre' => "VARCHAR(120) NOT NULL DEFAULT 'Cliente de mostrador'", 'telefono' => "VARCHAR(30) NOT NULL DEFAULT ''",
        'fecha_retiro' => 'DATE NULL', 'hora_retiro' => "TIME NOT NULL DEFAULT '10:00:00'", 'notas' => 'TEXT NULL',
        'estado' => "VARCHAR(30) NOT NULL DEFAULT 'Pendiente'", 'total' => 'DECIMAL(10,2) UNSIGNED NOT NULL DEFAULT 0',
        'motivo_cancelacion' => "VARCHAR(255) NOT NULL DEFAULT ''",
        'version' => 'INT UNSIGNED NOT NULL DEFAULT 1',
    ],
];
foreach ($additions as $table => $fields) {
    $columns = array_column($conexion->query("SHOW COLUMNS FROM `$table`")->fetch_all(MYSQLI_ASSOC), 'Field');
    foreach ($fields as $field => $definition) {
        if (!in_array($field, $columns, true)) {
            $conexion->query("ALTER TABLE `$table` ADD COLUMN `$field` $definition");
            echo "Agregado $table.$field\n";
        }
    }
}
$conexion->query('ALTER TABLE pedidos MODIFY id_pedido INT UNSIGNED NOT NULL AUTO_INCREMENT');
// CREATE IF NOT EXISTS mantiene las tablas y los datos previos.
$conexion->multi_query(file_get_contents(__DIR__ . '/estructura.sql'));
do {
    if ($result = $conexion->store_result()) {
        $result->free();
    }
}
while ($conexion->more_results() && $conexion->next_result());
$conexion->query("UPDATE productos SET categoria = CASE WHEN LOWER(nombre) LIKE '%bizco%' OR LOWER(nombre) LIKE '%croissant%' THEN 'Bizcochos' WHEN LOWER(nombre) LIKE '%torta%' THEN 'Tortas' ELSE 'Panes' END WHERE ingredientes IS NULL");
$conexion->query("UPDATE pedidos SET fecha_retiro = DATE(creado_en) WHERE fecha_retiro IS NULL");
$conexion->query("INSERT INTO pedido_detalles (id_pedido, id_producto, nombre_producto, cantidad, precio_unitario)
    SELECT p.id_pedido, (SELECT MIN(id_producto) FROM productos WHERE nombre = p.producto), p.producto, p.cantidad,
    COALESCE((SELECT precio FROM productos WHERE nombre = p.producto ORDER BY id_producto LIMIT 1), 0)
    FROM pedidos p WHERE NOT EXISTS (SELECT 1 FROM pedido_detalles d WHERE d.id_pedido = p.id_pedido)");
$conexion->query('UPDATE pedidos p SET total = (SELECT COALESCE(SUM(cantidad * precio_unitario), 0) FROM pedido_detalles WHERE id_pedido = p.id_pedido) WHERE total = 0');
$conexion->query("UPDATE solicitud_usuario SET nombre = 'Equipo de prueba', email = 'demo@alas.local', rol = 'administrador' WHERE cedula = 99999999 AND email IS NULL");
$catalog = [
    ['Bizcocho de chocolate', 'Un toque dulce para la hora de la merienda.', 65, 'Bizcochos', 'Unidad', 'Harina de trigo, chocolate, manteca, huevos y azúcar.', 'Contiene gluten, leche y huevo.'],
    ['Torta de cumpleaños', 'Una celebración hecha para compartir.', 1200, 'Tortas', '8 porciones', 'Harina de trigo, huevos, crema y azúcar.', 'Contiene gluten, leche y huevo.'],
    ['Croissant clásico', 'Capas delicadas y un exterior crujiente.', 80, 'Bizcochos', 'Unidad', 'Harina de trigo, manteca, levadura y leche.', 'Contiene gluten y leche.'],
    ['Torta de frutos rojos', 'Fruta, crema y un momento especial.', 1350, 'Especiales', '8 porciones', 'Frutos rojos, crema, harina, huevos y azúcar.', 'Contiene gluten, leche y huevo.'],
];
foreach ($catalog as [$nombre, $description, $price, $categoria, $unit, $ingredients, $allergens]) {
    $sentencia = $conexion->prepare('INSERT INTO productos (nombre, descripcion, precio, categoria, presentacion, ingredientes, alergenos, destacado) SELECT ?, ?, ?, ?, ?, ?, ?, 1 WHERE NOT EXISTS (SELECT 1 FROM productos WHERE nombre = ?)');
    $sentencia->bind_param('ssdsssss', $nombre, $description, $price, $categoria, $unit, $ingredients, $allergens, $nombre);
    $sentencia->execute();
}
$conexion->query("UPDATE productos SET ingredientes = 'Consultar los ingredientes con el equipo.', alergenos = 'Consultá los alérgenos antes de realizar tu pedido.' WHERE ingredientes IS NULL");
echo "Migración completada; usuarios, productos y pedidos anteriores conservados.\n";
