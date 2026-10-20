# Gestión del proyecto — Panadería Alas

## Product Backlog
Prioridad: P1 = imprescindible; P2 = importante.

| ID | Historia de usuario | Prioridad | Criterio de aceptación |
|---|---|---|---|
| HU-01 | Como cliente, quiero ver el catálogo para elegir productos. | P1 | Se muestran productos activos con precio y detalle. |
| HU-02 | Como cliente, quiero armar un carrito para indicar cantidades. | P1 | Puedo agregar, editar y quitar líneas. |
| HU-03 | Como cliente, quiero confirmar un pedido con mis datos y retiro. | P1 | Se guarda nombre, teléfono, fecha/hora, líneas y total correcto. |
| HU-04 | Como empleado, quiero ver pedidos por fecha/estado. | P1 | Los filtros muestran los pedidos correspondientes. |
| HU-05 | Como empleado, quiero cambiar el estado para organizar producción. | P1 | Se visualiza el estado actualizado. |
| HU-06 | Como administrador, quiero mantener el catálogo. | P1 | Puedo crear, editar y desactivar productos. |
| HU-07 | Como encargado, quiero ver cifras del panel. | P1 | El panel presenta estadísticas calculadas desde datos reales. |
| HU-08 | Como administrador, quiero controlar accesos. | P2 | Puedo revisar solicitudes y administrar accesos. |
| HU-09 | Como cliente, quiero enviar una consulta. | P2 | La consulta queda guardada y visible en administración. |
| HU-10 | Como equipo, queremos validar el sistema. | P1 | Se registran casos, resultados y correcciones. |

## Propuesta de Sprint Backlog
### Sprint 1 — Análisis y base del sistema
- Confirmar usuarios, problema, alcance y requisitos.
- Definir navegación y diagramas UML.
- Preparar repositorio GitHub, estructura del proyecto y base de datos.
- **Criterio de salida:** requisitos revisados, repositorio operativo y esquema importable.

### Sprint 2 — Flujo principal de pedidos
- Revisar catálogo, búsqueda y carrito.
- Verificar registro de pedidos con fecha/hora y datos del cliente.
- Verificar panel de pedidos y cambios de estado.
- **Criterio de salida:** recorrido completo de pedido probado localmente.

### Sprint 3 — Administración, seguridad y entrega
- Verificar alta/edición/desactivación de productos.
- Verificar permisos, validaciones, filtros y estadísticas.
- Ejecutar pruebas, registrar errores y corregirlos.
- Preparar manual, presentación y defensa.
- **Criterio de salida:** requisitos mínimos demostrables y documentación actualizada.

## Cronograma tipo Gantt (plan orientativo 2026)
| Actividad | Ago | Set | Oct | Nov |
|---|:---:|:---:|:---:|:---:|
| Relevamiento y requisitos | ■ | | | |
| Repositorio y estructura | ■ | | | |
| Diseño y diagramas UML | | ■ | | |
| Product/Sprint Backlog | | ■ | | |
| Desarrollo del flujo de pedidos | | | ■ | |
| Administración y seguridad | | | ■ | |
| Pruebas y correcciones | | | ■ | ■ |
| Documentación y defensa | | | | ■ |

## Distribución de tareas
Ajustar los nombres a los integrantes reales del equipo.
| Rol propuesto | Responsabilidades |
|---|---|
| Integrante A — análisis/documentación | requisitos, historias de usuario, actas y documentación |
| Integrante B — backend/base de datos | PHP, consultas, reglas de negocio y esquema SQL |
| Integrante C — interfaz | HTML/CSS/JavaScript, navegación y accesibilidad |
| Integrante D — testing/integración | casos de prueba, revisión, GitHub y registro de errores |

Todos los integrantes revisan el trabajo y participan en la defensa. En un equipo más pequeño se pueden combinar roles.

## Registro de cambios relevantes
Completar durante el desarrollo; no inventar commits retrospectivos.
| Fecha | Cambio | Motivo | Responsable | Evidencia |
|---|---|---|---|---|
| ____ | ____ | ____ | ____ | enlace a commit/PR |
