<?php
require_once __DIR__ . '/../Core/app.php';
function validarContrasena(string $contrasena, string $confirmacion): void
{
    if (strlen($contrasena) < 8 || strlen($contrasena) > 72 || $contrasena !== $confirmacion) {
        throw new RuntimeException('Usá entre 8 y 72 caracteres y confirmá la misma contraseña.');
    }
}

function iniciarSesion(string $cedula, string $contrasena): void
{
    global $conexion;

    $mensajeCredenciales = 'Revisá tu cédula y contraseña e intentá nuevamente.';
    if (!ctype_digit($cedula) || strlen($cedula) < 6 || strlen($cedula) > 8) {
        throw new RuntimeException($mensajeCredenciales);
    }

    $usuario = null;
    $errorAcceso = '';
    $conexion->begin_transaction();
    try {
        // El bloqueo de fila también evita perder intentos de peticiones simultáneas.
        sql(
            'INSERT INTO intentos_acceso (cedula) VALUES (?)
             ON DUPLICATE KEY UPDATE cedula=VALUES(cedula)',
            'i',
            [(int) $cedula]
        );
        $registro = one(
            'SELECT *, bloqueado_hasta > NOW() AS bloqueado
             FROM intentos_acceso WHERE cedula=? FOR UPDATE',
            'i',
            [(int) $cedula]
        );

        if ($registro['bloqueado']) {
            $errorAcceso = 'Esperá un minuto antes de volver a intentarlo.';
        } else {
            $usuario = one('SELECT * FROM solicitud_usuario WHERE cedula=?', 'i', [(int) $cedula]);
            if (!$usuario || !password_verify($contrasena, $usuario['password'])) {
                // Al vencer el bloqueo empieza una nueva cuenta de intentos.
                $intentos = $registro['bloqueado_hasta'] !== null ? 1 : (int) $registro['intentos'] + 1;
                sql(
                    'UPDATE intentos_acceso SET intentos=?,
                        bloqueado_hasta=IF(? >= 6, DATE_ADD(NOW(), INTERVAL 1 MINUTE), NULL)
                     WHERE cedula=?',
                    'iii',
                    [$intentos, $intentos, (int) $cedula]
                );
                $errorAcceso = $mensajeCredenciales;
            } elseif (!in_array($usuario['rol'], ['empleado', 'administrador', 'dueno'], true)) {
                $errorAcceso = $mensajeCredenciales;
            } else {
                sql('DELETE FROM intentos_acceso WHERE cedula=?', 'i', [(int) $cedula]);
                if ($usuario['estado'] !== 'aprobado') {
                    $errorAcceso = 'Tu solicitud de acceso todavía no fue aprobada. Consultá con el administrador.';
                }
            }
        }
        $conexion->commit();
    } catch (Throwable $error) {
        $conexion->rollback();
        throw $error;
    }

    // Se informa el error después del commit para conservar el intento fallido.
    if ($errorAcceso !== '') {
        throw new RuntimeException($errorAcceso);
    }
    session_regenerate_id(true);
    if ($usuario['rol'] !== 'dueno') {
        unset($_SESSION['cliente_id']);
    }
    $_SESSION['user_id'] = (int) $usuario['id_sol'];
    $_SESSION['usuario'] = (string) $usuario['cedula'];
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    registrarIngresoEquipo($usuario);
    unset($_SESSION['login_intentos'], $_SESSION['login_bloqueado_hasta']);
}

function solicitarUsuario(string $nombre, string $cedula, string $correo, string $contrasena, string $confirmacion): void
{
    global $conexion;
    validate_text($nombre, 'tu nombre', 120);
    if (!ctype_digit($cedula) || strlen($cedula) < 6 || strlen($cedula) > 8) {
        throw new RuntimeException('Ingresá una cédula de 6 a 8 dígitos, sin puntos ni guiones.');
    }
    validarCorreo($correo);
    validarContrasena($contrasena, $confirmacion);
    // Conserva la exclusión de duplicados en instalaciones antiguas sin índice único.
    $bloqueo = one("SELECT GET_LOCK('alas_solicitud_usuario', 5) AS obtenido");
    if ((int) $bloqueo['obtenido'] !== 1) {
        throw new RuntimeException('Intentá nuevamente en unos segundos.');
    }
    try {
        if (one('SELECT id_sol FROM solicitud_usuario WHERE cedula=? OR email=?', 'is', [(int) $cedula, $correo])) {
            throw new RuntimeException('Ya existe una cuenta o solicitud con esos datos.');
        }
        $conexion->begin_transaction();
        try {
            sql(
                "INSERT INTO solicitud_usuario (nombre, cedula, email, password, estado, rol)
                 VALUES (?, ?, ?, ?, 'pendiente', 'empleado')",
                'siss',
                [$nombre, (int) $cedula, $correo, password_hash($contrasena, PASSWORD_DEFAULT)]
            );
            queue_mail($correo, 'Solicitud de acceso recibida',
                "Hola $nombre. Recibimos tu solicitud. El dueño de Panadería Alas debe aprobarla antes de que puedas iniciar sesión.");
            $conexion->commit();
        } catch (Throwable $error) {
            $conexion->rollback();
            throw $error;
        }
    } finally {
        sql("SELECT RELEASE_LOCK('alas_solicitud_usuario')");
    }
}

/** Crea directamente una cuenta aprobada de empleado; el controlador limita esto al dueño. */
function crearEmpleado(string $nombre, string $cedula, string $correo, string $contrasena, string $confirmacion, string $rol): void
{
    validate_text($nombre, 'el nombre del empleado', 120);
    if (!ctype_digit($cedula) || strlen($cedula) < 6 || strlen($cedula) > 8) {
        throw new RuntimeException('Ingresá una cédula de 6 a 8 dígitos, sin puntos ni guiones.');
    }
    if ($correo !== '') {
        validarCorreo($correo);
    }
    if (!in_array($rol, ['empleado', 'administrador'], true)) {
        throw new RuntimeException('Elegí un rol válido para la cuenta.');
    }
    validarContrasena($contrasena, $confirmacion);
    if (one('SELECT id_sol FROM solicitud_usuario WHERE cedula=? OR (? <> \'\' AND email=?)', 'iss', [(int) $cedula, $correo, $correo])) {
        throw new RuntimeException('Ya existe una cuenta con esa cédula o correo.');
    }
    sql(
        "INSERT INTO solicitud_usuario (nombre, cedula, email, password, estado, rol)
         VALUES (?, ?, ?, ?, 'aprobado', ?)",
        'sisss',
        [$nombre, (int) $cedula, $correo !== '' ? $correo : null, password_hash($contrasena, PASSWORD_DEFAULT), $rol]
    );
}

/** Revoca el acceso del empleado conservando sus registros históricos. */
function desactivarEmpleado(int $idEmpleado): void
{
    if ($idEmpleado < 1) {
        throw new RuntimeException('Seleccioná un empleado válido.');
    }
    $empleado = one("SELECT id_sol FROM solicitud_usuario WHERE id_sol=? AND rol IN ('empleado','administrador') FOR UPDATE", 'i', [$idEmpleado]);
    if (!$empleado) {
        throw new RuntimeException('No se encontró un empleado activo para quitar.');
    }
    sql("UPDATE solicitud_usuario SET estado='rechazado' WHERE id_sol=? AND rol IN ('empleado','administrador')", 'i', [$idEmpleado]);
}

function buscarRecuperacion(string $token): ?array
{
    if (!preg_match('/^[a-f0-9]{64}$/', $token)) {
        return null;
    }
    return one(
        'SELECT * FROM recuperaciones WHERE token_hash=? AND usado=0 AND vence_en > NOW()',
        's',
        [hash('sha256', $token)]
    );
}

function solicitarRecuperacion(string $correo): void
{
    global $conexion;
    validarCorreo($correo);
    if (time() - ($_SESSION['ultima_recuperacion'] ?? 0) < 60) {
        throw new RuntimeException('Esperá un minuto antes de volver a solicitar instrucciones.');
    }
    $usuario = one("SELECT * FROM solicitud_usuario WHERE email=? AND estado='aprobado'", 's', [$correo]);
    if ($usuario) {
        $token = bin2hex(random_bytes(32));
        $vencimiento = date('Y-m-d H:i:s', time() + 3600);
        $conexion->begin_transaction();
        try {
            sql('UPDATE recuperaciones SET usado=1 WHERE id_usuario=?', 'i', [$usuario['id_sol']]);
            sql('INSERT INTO recuperaciones (id_usuario, token_hash, vence_en) VALUES (?, ?, ?)',
                'iss', [$usuario['id_sol'], hash('sha256', $token), $vencimiento]);
            $enlace = base_url('app/Views/restablecer.php?token=' . $token);
            queue_mail($correo, 'Recuperar acceso a Panadería Alas',
                "Usá este enlace para elegir una nueva contraseña (vence en una hora):\n$enlace\nSi no solicitaste este cambio, ignorá el mensaje.");
            $conexion->commit();
        } catch (Throwable $error) {
            $conexion->rollback();
            throw $error;
        }
    }
    // La respuesta no revela si el correo pertenece a una cuenta.
    $_SESSION['ultima_recuperacion'] = time();
}

function restablecerContrasena(?array $recuperacion, string $contrasena, string $confirmacion): void
{
    global $conexion;
    if (!$recuperacion) {
        throw new RuntimeException('El enlace venció o ya fue utilizado. Solicitá uno nuevo.');
    }
    validarContrasena($contrasena, $confirmacion);
    $conexion->begin_transaction();
    try {
        $registro = one(
            'SELECT * FROM recuperaciones WHERE id_recuperacion=? AND usado=0 AND vence_en > NOW() FOR UPDATE',
            'i', [$recuperacion['id_recuperacion']]
        );
        if (!$registro) {
            throw new RuntimeException('El enlace venció o ya fue utilizado.');
        }
        sql('UPDATE solicitud_usuario SET password=? WHERE id_sol=?',
            'si', [password_hash($contrasena, PASSWORD_DEFAULT), $registro['id_usuario']]);
        sql(
            'DELETE FROM intentos_acceso WHERE cedula=(SELECT cedula FROM solicitud_usuario WHERE id_sol=?)',
            'i',
            [$registro['id_usuario']]
        );
        sql('UPDATE recuperaciones SET usado=1 WHERE id_usuario=?', 'i', [$registro['id_usuario']]);
        $conexion->commit();
    } catch (Throwable $error) {
        $conexion->rollback();
        throw $error;
    }
}

function revisarSolicitud(int $idUsuario, string $accion, string $rolAprobado, int $idAdministrador): void
{
    global $conexion;
    $estado = match ($accion) {
        'aprobar' => 'aprobado',
        'rechazar' => 'rechazado',
        default => '',
    };
    if ($estado === '' || $idUsuario === $idAdministrador
        || ($estado === 'aprobado' && !in_array($rolAprobado, ['empleado', 'administrador'], true))) {
        throw new RuntimeException('Acción no válida.');
    }
    $conexion->begin_transaction();
    try {
        $solicitud = one("SELECT * FROM solicitud_usuario WHERE id_sol=? AND estado='pendiente' FOR UPDATE", 'i', [$idUsuario]);
        if (!$solicitud) {
            throw new RuntimeException('La solicitud ya fue revisada.');
        }
        sql('UPDATE solicitud_usuario SET estado=?, rol=? WHERE id_sol=?', 'ssi', [$estado, $estado === 'aprobado' ? $rolAprobado : $solicitud['rol'], $idUsuario]);
        if ($solicitud['email']) {
            $respuesta = $estado === 'aprobado'
            ? 'Tu acceso fue aprobado. Ya podés iniciar sesión con tu cédula y contraseña.'
            : 'Tu solicitud no fue aprobada. Contactá al administrador para más información.';
            queue_mail($solicitud['email'], 'Respuesta a tu solicitud de acceso', $respuesta);
        }
        $conexion->commit();
    } catch (Throwable $error) {
        $conexion->rollback();
        throw $error;
    }
}

