# Registro con verificación de correo

La solicitud de usuario se crea únicamente después de introducir el código recibido por SMTP.
Mantiene la aprobación posterior del administrador y el rol de empleado.

## Configuración del hosting

El archivo privado `app/Core/smtp_config.php` ya usado en la prueba manual es compatible:
`host`, `port`, `usuario`, `password`, `remitente` y `nombre`.
`seguridad` es opcional: `tls` (STARTTLS, predeterminado) o `ssl` (TLS implícito).
Ver el ejemplo sin credenciales en `app/Core/smtp_config.example.php`.
Para Gmail usar puerto 587, STARTTLS y una contraseña de aplicación.

PHPMailer 7.1.1 y su licencia se incluyen en `app/Services/PHPMailer`.
No hace falta Composer en InfinityFree. La verificación usa la sesión existente:
no hay tablas nuevas ni migraciones automáticas.

Las credenciales SMTP y MySQL están excluidas tanto de Git como del despliegue FTP.
El despliegue no crea ni reemplaza `smtp_config.php` ni `db_credentials.php`.
Nunca publicar contraseñas reales ni habilitar trazas SMTP en la página de registro.
La página opcional `public/prueba-correo.php` usa el mismo transporte; queda deshabilitada
si no existe `clave_prueba` privada de al menos 20 caracteres en la configuración.
Su destinatario es fijo: `destinatario` o, si falta, el remitente configurado.

## Comportamiento

1. Validar nombre, cédula, correo y contraseña; comprobar duplicados.
2. Enviar un código aleatorio de seis dígitos. Ninguna cuenta se inserta todavía.
3. Guardar en la sesión los datos pendientes, el hash de contraseña y el hash del código.
4. Confirmar desde `app/Views/verificar-correo.php` en la misma sesión del navegador.
5. Al acertar, insertar la solicitud pendiente una sola vez, sin iniciar sesión automáticamente.

Cada código vence en diez minutos y todo el registro pendiente vence a los treinta minutos.
Hay cinco intentos de verificación y un máximo de cinco intentos de envío por hora y sesión,
separados por un minuto. Cancelar no borra este límite. Estas limitaciones son por sesión,
no sustituyen límites de tráfico por IP o destinatario en un servicio de mayor escala.
Reenviar invalida el código anterior y conserva los intentos fallidos.
Si SMTP falla, no se crea la cuenta y no se reemplaza un código anterior.
No se conserva la contraseña en texto plano ni se publica el código en HTML o logs.
Los formularios mantienen CSRF y un identificador que vincula la confirmación al registro.
Se comprueban nuevamente los duplicados bajo bloqueo MySQL al insertar.

Los avisos anteriores de aprobación y recuperación conservan su bandeja local
`correos_locales`; el correo SMTP integrado en este cambio es el código de registro.

## Pruebas

Ejecutar `php tests/registro.php`: no requiere MySQL ni envía correo real.
Comprueba ausencia de cuentas antes de verificar, expiración, intentos, reenvío,
errores de SMTP, duplicados, rollback, un solo uso y mantenimiento de permisos.
La prueba HTTP anterior `tests/integracion.ps1` necesita ahora un SMTP de pruebas
y el código de verificación; ver sus parámetros de prueba de correo.
