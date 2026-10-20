# Documento de requisitos — Panadería Alas
**Proyecto:** Sistema web de pedidos y producción · **Asignatura:** Ingeniería del Software · **Curso:** 6.º Bachillerato Tecnológico en Informática · **Centro:** Escuela Técnica Catalina Harriague de Castaños · **Año:** 2026

## 1. Problema
La panadería recibe y organiza pedidos mediante papel, llamadas y mensajes. Esto puede provocar pérdida de datos, errores en cantidades o fechas y dificultades para conocer qué debe prepararse y qué pedidos están listos.

## 2. Objetivo del sistema
Centralizar el catálogo y los pedidos en una aplicación web, conservar la información y permitir que el personal administre productos, consulte pedidos y actualice su estado.

## 3. Stakeholders y usuarios
- **Clientes:** consultan productos y realizan pedidos con fecha/hora de retiro.
- **Empleados:** consultan los pedidos y actualizan su estado durante la producción.
- **Administrador:** administra productos, consulta pedidos, revisa estadísticas y gestiona accesos.
- **Propietario/encargado:** necesita información confiable para organizar el trabajo.
- **Equipo de desarrollo y docente:** construyen, validan y evalúan la solución.

## 4. Requerimientos funcionales
- **RF-01:** el visitante puede consultar el catálogo de productos activos.
- **RF-02:** el visitante puede buscar productos y filtrar por categoría.
- **RF-03:** el cliente puede agregar productos al carrito y cambiar cantidades o quitarlos.
- **RF-04:** el cliente puede confirmar un pedido ingresando nombre, teléfono y fecha/hora de retiro.
- **RF-05:** el sistema calcula el total en el servidor y registra las líneas del pedido.
- **RF-06:** el personal autorizado puede consultar y filtrar pedidos por fecha y estado.
- **RF-07:** el personal autorizado puede actualizar el estado del pedido: Pendiente, En preparación, Listo o Entregado.
- **RF-08:** el administrador puede dar de alta, editar y desactivar productos.
- **RF-09:** el administrador puede consultar estadísticas y próximos retiros desde el panel.
- **RF-10:** el sistema permite acceso de usuarios autorizados y revisión de solicitudes de acceso.
- **RF-11:** el sistema permite registrar consultas de contacto.
- **RF-12:** el sistema conserva el nombre y precio histórico de cada línea de pedido aunque cambie el catálogo.

## 5. Requerimientos no funcionales
- **RNF-01 Seguridad:** usar consultas preparadas, validación de entradas, protección CSRF y control de permisos.
- **RNF-02 Usabilidad:** interfaz adaptable a móviles y computadoras, con navegación clara.
- **RNF-03 Integridad:** calcular importes en el servidor y usar transacciones para operaciones relacionadas.
- **RNF-04 Persistencia:** almacenar productos y pedidos en MySQL/MariaDB.
- **RNF-05 Mantenibilidad:** separar controladores, servicios, vistas y utilidades comunes.
- **RNF-06 Compatibilidad:** PHP 8.2+ y MySQL/MariaDB incluidos habitualmente en XAMPP.
- **RNF-07 Trazabilidad:** gestionar el código mediante Git y GitHub durante el trabajo del equipo.

## 6. Restricciones y supuestos
- Requiere servidor Apache, PHP con extensión `mysqli` y MySQL/MariaDB.
- Configuración local prevista: base `alas_bd`, usuario `root`, contraseña vacía; ajustar `app/Core/conexion.php` si el entorno difiere.
- El correo es local para pruebas; no se envían mensajes por Internet sin configurar SMTP.
- El cliente no necesita crear una cuenta para realizar un pedido.
- Los datos de demostración son ficticios y no deben utilizarse como credenciales de producción.

## 7. Factibilidad
- **Técnica:** viable con PHP, MySQL/MariaDB, HTML, CSS y JavaScript, usando XAMPP.
- **Económica:** herramientas gratuitas y de código abierto.
- **Operativa:** reduce la dependencia de papel y mensajes dispersos; requiere capacitación breve.
- **Temporal:** puede desarrollarse por incrementos en tres sprints, priorizando pedidos, administración y pruebas.

## 8. Alcance
Incluye catálogo, carrito, registro de pedidos, fecha/hora de retiro, gestión de productos, consulta y actualización de pedidos, panel, accesos y contacto. La integración con pagos, control físico de stock, proveedores y envío de correo real no forma parte del alcance mínimo actual.
