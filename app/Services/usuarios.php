<?php
require_once __DIR__ . '/../Core/app.php';
require_once __DIR__ . '/registro.php';
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
    $_SESSION['user_id'] = (int) $usuario['id_sol'];
    $_SESSION['usuario'] = (string) $usuario['cedula'];
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    unset($_SESSION['login_intentos'], $_SESSION['login_bloqueado_hasta']);
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

function revisarSolicitud(int $idUsuario, string $accion, int $idAdministrador): void
{
    global $conexion;
    $estado = match ($accion) {
        'aprobar' => 'aprobado',
        'rechazar' => 'rechazado',
        default => '',
    };
    if ($estado === '' || $idUsuario === $idAdministrador) {
        throw new RuntimeException('Acción no válida.');
    }
    $conexion->begin_transaction();
    try {
        $solicitud = one("SELECT * FROM solicitud_usuario WHERE id_sol=? AND estado='pendiente' FOR UPDATE", 'i', [$idUsuario]);
        if (!$solicitud) {
            throw new RuntimeException('La solicitud ya fue revisada.');
        }
        sql('UPDATE solicitud_usuario SET estado=? WHERE id_sol=?', 'si', [$estado, $idUsuario]);
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
