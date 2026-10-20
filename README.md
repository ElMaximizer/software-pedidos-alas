# Panadería Alas

Aplicación PHP 8.2/MySQL compatible con MariaDB de XAMPP. Implementa el sitio
público y la administración del mockup de Figma, con datos persistentes.

## Iniciar esta instalación

1. Iniciar Apache y MySQL en XAMPP.
2. Abrir <http://localhost/software-pedidos-alas/public/index.php>.
3. Entrar al equipo con la cédula `99999999` y contraseña `AlasDemo2026!`.

La cuenta es de prueba local. Su correo es `demo@alas.local`. Los usuarios que ya
existían se conservaron y mantienen su acceso. Las solicitudes nuevas requieren
aprobación de un administrador desde Panel → Solicitudes de acceso.

## Instalar desde cero

1. Importar `database/estructura.sql` desde phpMyAdmin.
2. Importar `database/datos_demo.sql` si se desean una cuenta y datos de ejemplo.
3. Ejecutar desde la raíz del proyecto:

```powershell
C:\xampp\php\php.exe database\migrar.php
```

La migración completa las columnas antiguas, convierte los pedidos a varias
líneas y agrega los ejemplos del catálogo de Figma sin reemplazar registros.
Puede ejecutarse nuevamente sin duplicar las líneas ni los ejemplos.
En una base existente, ejecutar la migración antes de importar `datos_demo.sql`.

Si la instalación ya estaba completa antes de las correcciones de pedidos y
acceso, basta con esta actualización puntual, que conserva los datos existentes:

```powershell
C:\xampp\php\php.exe database\migrar-correcciones.php
```

Agrega `pedidos.version` y la tabla `intentos_acceso`. Puede ejecutarse nuevamente.

La conexión está en `app/Core/conexion.php`: servidor `localhost`, usuario `root`,
contraseña vacía, base `alas_bd`. El archivo SQL crea la base si no existe.
El instalador y las migraciones solo se ejecutan por consola; no durante visitas.

## Funcionalidades

- Inicio, catálogo con búsqueda por nombre/ingredientes y filtro por categoría,
  detalle de producto, Nosotros, contacto y confirmación del mensaje.
- Carrito en sesión: agregar, cambiar cantidades, quitar, vaciar y registrar el
  pedido público con nombre, teléfono, fecha y hora de retiro.
- Solicitud de usuario, revisión por administrador, acceso, salida y recuperación
  de contraseña con enlace de un solo uso que vence en una hora.
- Panel con cifras reales y próximos retiros, consultas recibidas y accesos.
- Productos: listado, búsqueda por nombre/código, filtro de estado, alta, edición,
  imagen JPG/PNG de hasta 4 MB, categoría, presentación, ingredientes y alérgenos.
- Productos inactivos ocultos del catálogo; eliminación lógica que mantiene los
  pedidos anteriores.
- Pedidos: búsqueda por cliente/número, filtros por retiro/estado, varias líneas,
  total calculado en servidor, ID automático, notas, edición y cancelación motivada.
- Los precios y nombres de las líneas se conservan al cambiar el catálogo.
- Cada formulario tiene un identificador propio. Las ediciones comprueban la
  versión del pedido y rechazan formularios anteriores sin sobrescribir cambios.
- La confirmación pública exige revisar nuevamente el carrito si cambian sus
  productos, cantidades, precios o disponibilidad.
- Los intentos fallidos se cuentan por cédula en MySQL, incluso entre sesiones.
- Interfaz adaptable a escritorio y móvil, formularios con CSRF y consultas
  preparadas. La confirmación del pedido evita duplicados al reenviar.

## Verificación de correo y bandeja local

El registro envía por SMTP un código de seis dígitos y crea la solicitud solamente
al verificarlo. Luego el administrador debe aprobar el acceso. Configurar
`app/Core/smtp_config.php` en el hosting usando el ejemplo sin credenciales.
Ese archivo privado se conserva durante el despliegue y nunca se sube a Git.
PHPMailer está incluido y no requiere Composer. Ver [configuración y límites](docs/verificacion-correo.md).
Las notificaciones anteriores de aprobación y recuperación conservan su bandeja
local: Panel → Bandeja de correo local, disponible solo para administradores.
Las consultas de contacto se guardan en Panel → Consultas recibidas.

## Estructura del código

- `app/Core/app.php`: inicio común; carga sesión, conexión y utilidades por tema.
- `app/Controllers/publicas`, `admin` y `auth`: lectura de peticiones y preparación
  de los datos de cada pantalla.
- `app/Services`: reglas de usuarios, productos, pedidos, carrito y contacto.
- `app/Views/publicas`, `admin` y `auth`: plantillas HTML sin consultas SQL.
- `app/Views/partials`: cabeceras, pies, tarjetas y tabla compartidos.
- `public` y los archivos directos de `app/Views`: conservan las URL existentes y
  cargan el controlador y su plantilla. `gestion.php` selecciona el formulario.
- `public/js/alas.js`: funciones separadas para contraseñas, imágenes y pedidos.
- `database/estructura.sql`: esquema completo.
- `database/migrar.php`: actualización conservando la información anterior.
- `docs/figma-implementacion.md`: correspondencia de las 23 pantallas y decisiones.
- `docs/guia-codigo.md`: explicación editable de estructura, funciones y flujos.
- `output/pdf/guia-del-codigo-panaderia-alas.pdf`: manual de 13 páginas para el equipo.

El recorrido habitual es **entrada → controlador → servicio → vista**. Las
consultas de lectura simples pueden estar en el controlador; las escrituras y
reglas del negocio se reúnen en los servicios. No hace falta un framework ni
Composer. `app/Core/pedidos.php` conserva las funciones anteriores por compatibilidad.
Las carpetas internas tienen `.htaccess` y se incluyen desde PHP; no son páginas
para abrir directamente.

## Pruebas y manual

Con Apache y MySQL activos, una base local de desarrollo y los datos demo:

```powershell
pwsh -NoProfile -File tests/integracion.ps1 -BuzonSmtpPrueba C:\ruta\buzon-pruebas.jsonl
C:\xampp\php\php.exe tests\registro.php
C:\xampp\php\php.exe tests\pedidos.php
C:\xampp\php\php.exe tests\catalogo.php
node --test tests/imagenes.test.js
node --check public/js/alas.js
```

La prueba HTTP requiere PowerShell 7 y admite `-BaseUrl`, `-Mysql`,
`-CedulaAdministrador` y `-ContrasenaAdministrador`. `-ProbarImagen` agrega una
carga JPG/PNG de prueba y requiere permiso para retirar el archivo al finalizar.
Las pruebas crean sus propios registros con una marca aleatoria y los limpian.
Comprueban los flujos del sitio, permisos, CSRF, recuperación, transacciones,
precios históricos, versiones, pestañas independientes, pedidos antiguos sin
catálogo, carrito incompleto, bloqueo entre sesiones, imágenes y códigos largos.
La prueba del catálogo utiliza una tabla temporal y no modifica productos reales.

Para regenerar el PDF, instalar ReportLab en Python y ejecutar:

```powershell
python docs/generar_manual.py
```

## Diseño

Los estilos activos están en `public/css/alas.css`; los anteriores se conservan.
No se generaron imágenes raster. Se reutiliza `panes.png` y todas las ilustraciones
SVG están en archivos separados en `public/images`. Las subidas de imágenes no se
versionan. Los ejemplos del diseño no sustituyen los datos existentes de la base.
La tabla antigua `usuarios`, si existe, no participa en el acceso actual.
