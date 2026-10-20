# Plan de pruebas — Panadería Alas

## Entorno
Windows con XAMPP; Apache y MySQL activos; PHP 8.2+ con `mysqli`; navegador actualizado; base `alas_bd`, esquema `database/estructura.sql` y datos demo opcionales. Ejecutar en una base local de desarrollo, nunca sobre datos reales.

## Casos de prueba
| ID | Caso | Pasos / datos | Resultado esperado |
|---|---|---|---|
| CP-01 | Abrir catálogo | Entrar a `public/index.php` y navegar a Productos. | Se muestran productos activos. |
| CP-02 | Buscar producto | Buscar por nombre o ingredientes. | Se listan coincidencias o se informa que no hay. |
| CP-03 | Carrito | Agregar un producto, cambiar cantidad y quitarlo. | Cantidades y subtotal se actualizan. |
| CP-04 | Pedido válido | Completar nombre, teléfono y retiro futuro; confirmar. | Se guarda el pedido y aparece confirmación. |
| CP-05 | Pedido incompleto | Confirmar sin datos requeridos. | Se valida y no se registra un pedido inválido. |
| CP-06 | Total confiable | Manipular un precio desde el navegador. | El servidor usa precios del catálogo. |
| CP-07 | Filtro por fecha | En panel, filtrar pedidos por fecha. | Aparecen los pedidos correspondientes. |
| CP-08 | Cambio de estado | Cambiar a En preparación, Listo y Entregado. | Cada estado se guarda y se visualiza. |
| CP-09 | Producto | Crear, editar y desactivar un producto. | Cambios visibles; inactivo no aparece al público. |
| CP-10 | Permisos | Abrir ruta administrativa sin sesión autorizada. | Acceso denegado o redirección. |
| CP-11 | CSRF | Enviar formulario sin token válido. | La operación se rechaza. |
| CP-12 | Contacto | Enviar consulta válida. | Queda guardada y visible en administración. |
| CP-13 | Responsive | Revisar páginas en pantalla estrecha. | Contenido legible y usable. |

## Resultados ejecutados en el entorno de revisión
- Sintaxis PHP: todos los archivos PHP revisados sin errores de sintaxis.
- Pruebas JavaScript de imágenes (`node --test tests/imagenes.test.js`): 3 pruebas aprobadas.
- Pruebas que requieren MySQL/Apache: pendientes de ejecutar en una instalación XAMPP activa. No se declaran aprobadas hasta correrlas allí.

## Registro de incidencias
Completar con incidencias reales detectadas durante las pruebas; describir el error, la corrección aplicada, el estado y la evidencia.
