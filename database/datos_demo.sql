-- Solo para desarrollo local. Ejecutar despues de estructura.sql.
-- Acceso de prueba: cedula 99999999 / clave AlasDemo2026!
-- La clave se almacena con password_hash() de PHP.
USE alas_bd;
SET NAMES utf8mb4;

START TRANSACTION;

INSERT INTO solicitud_usuario (cedula, password, nombre, email, rol)
SELECT 99999999, '$2y$10$GzsA0I2mhGYE4.slNcsrk.WRyrAqIL1OsSxBIDz9x1sfAHq.eSzaW', 'Equipo de prueba', 'demo@alas.local', 'administrador'
WHERE NOT EXISTS (
    SELECT 1 FROM solicitud_usuario WHERE cedula = 99999999
);

INSERT INTO productos (nombre, descripcion, precio, categoria, presentacion)
SELECT 'Pan integral', 'Pan artesanal de harina integral, por unidad.', 95.00, 'Panes', 'Unidad'
WHERE NOT EXISTS (SELECT 1 FROM productos WHERE nombre = 'Pan integral');

INSERT INTO productos (nombre, descripcion, precio, categoria, presentacion)
SELECT 'Bizcochos surtidos', 'Surtido de bizcochos dulces y salados, por kilo.', 420.00, 'Bizcochos', 'Kilo'
WHERE NOT EXISTS (SELECT 1 FROM productos WHERE nombre = 'Bizcochos surtidos');

INSERT INTO productos (nombre, descripcion, precio, categoria, presentacion)
SELECT 'Torta de chocolate', 'Torta de chocolate para compartir, por unidad.', 850.00, 'Tortas', 'Unidad'
WHERE NOT EXISTS (SELECT 1 FROM productos WHERE nombre = 'Torta de chocolate');

INSERT INTO productos (nombre, descripcion, precio, categoria, presentacion)
SELECT 'Pan de centeno', 'Pan artesanal de centeno, por unidad.', 120.00, 'Panes', 'Unidad'
WHERE NOT EXISTS (SELECT 1 FROM productos WHERE nombre = 'Pan de centeno');

INSERT INTO pedidos (id_pedido, producto, cantidad)
SELECT 1001, 'Pan integral', 3
WHERE NOT EXISTS (SELECT 1 FROM pedidos WHERE id_pedido = 1001);

INSERT INTO pedidos (id_pedido, producto, cantidad)
SELECT 1002, 'Bizcochos surtidos', 2
WHERE NOT EXISTS (SELECT 1 FROM pedidos WHERE id_pedido = 1002);

COMMIT;
