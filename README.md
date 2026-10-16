# Panadería Alas

Sistema PHP/MySQL para registrar usuarios, productos y pedidos.

## Puesta en marcha

1. Crear la base `alas_bd` en MySQL.
2. Mantener la tabla existente `solicitud_usuario` con las columnas `id_sol`,
   `cedula` y `password`.
3. Ejecutar `database/estructura.sql` para crear las tablas de productos y
   pedidos.
4. Abrir `app/Views/login.php` desde el servidor local.

## Pantallas

- Acceso y registro adaptables a escritorio y móvil.
- Panel principal de administración.
- Alta, edición y eliminación de productos.
- Alta, edición y eliminación de pedidos.

El diseño de estas vistas sigue los frames terminados del archivo de Figma
“Mockup Panadria Alas”. El frame de inicio público queda pendiente por decisión
de diseño.
