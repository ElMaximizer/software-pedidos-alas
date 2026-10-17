# Implementación del mockup Panadería Alas

Referencia: https://www.figma.com/design/8HP59jpjLDkzd8tkSbZ1ND/Mockup-Panadria-Alas

Se revisaron las páginas de escritorio y móvil desde el navegador, con autorización
del usuario, porque la conexión MCP alcanzó el límite del plan Starter. Las
exportaciones del mismo archivo que ya estaban en Descargas se usaron únicamente
como referencias visuales. Las capturas no forman parte de los recursos del sitio.

## Correspondencia de pantallas

| Figma | Implementación |
| --- | --- |
| 01 Inicio | public/index.php, public/inicio.php |
| 02 Productos y 20 Sin resultados | public/productos.php |
| 03 Detalle de producto | public/producto.php |
| 04 Nosotros | public/nosotros.php |
| 05 Contacto y 06 Mensaje enviado | public/contacto.php, public/mensaje-enviado.php |
| 07 Iniciar sesión y 21 Error de acceso | app/Views/login.php, app/Views/auth-page.php |
| 08 Solicitar usuario | app/Views/register.php |
| 09 Recuperar acceso y 10 Solicitud enviada | app/Views/recuperar.php, app/Views/solicitud-enviada.php |
| 11 Panel de administración | app/Views/principal.php, app/Views/panel.php |
| 12 Gestión de productos | app/Views/productos.php |
| 13 Agregar y 14 Editar producto | app/Views/producto-form.php |
| 15 Eliminar producto | app/Views/eliminar-producto.php |
| 16 Gestión de pedidos | app/Views/pedidos.php |
| 17 Agregar y 18 Editar pedido | app/Views/pedido-form.php |
| 19 Cancelar pedido | app/Views/eliminar-pedido.php |
| 22 Carrito y 23 Carrito vacío | public/carrito.php |

El retiro y la confirmación pública del carrito se completan con
`public/realizar-pedido.php` y `public/pedido-confirmado.php`. La revisión de accesos,
las consultas y la bandeja local se abren desde el panel. Son pantallas adicionales
necesarias para que los formularios del mockup tengan un destino funcional.

## Recursos y estilos

- Paleta: azul `#0B1F3B`, dorado `#C3A052`, crema `#F4F1ED`.
- Estilos activos: `public/css/alas.css`. El CSS anterior se conserva.
- Se reutiliza la fotografía existente `public/images/panes.png`.
- Las ilustraciones de productos se reconstruyeron manualmente como archivos
  SVG separados (`pan.svg`, `croissant.svg`, `torta.svg`); no se generaron imágenes
  raster ni se incrustaron SVG en PHP, HTML, CSS o JavaScript.
- Las imágenes subidas por el usuario reemplazan la ilustración de la categoría.
- Los datos visibles son los reales de MySQL. Los importes, cantidades e IDs del
  mockup son ejemplos; no se reemplazaron los registros existentes para imitarlos.
- Los diseños se adaptan a 412 px y a escritorio mediante las mismas vistas.

## Correo en desarrollo

No hay SMTP configurado en esta instalación. Los mensajes de aprobación y los
enlaces de recuperación se guardan en `correos_locales`, visible exclusivamente
para administradores en `app/Views/correos.php`. No se realiza ningún envío externo.
Los enlaces de recuperación vencen en una hora y solo se pueden usar una vez.
