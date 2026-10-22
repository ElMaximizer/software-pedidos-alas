<?php
require_once __DIR__ . '/../Core/app.php';

function validarDatosCliente(string $nombre, string $email, string $contrasena, string $confirmacion): array
{
    validate_text($nombre, 'tu nombre', 120);
    $email = strtolower(trim($email));
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 190) {
        throw new RuntimeException('Ingresá un correo electrónico válido.');
    }
    if (strlen($contrasena) < 8 || strlen($contrasena) > 72 || $contrasena !== $confirmacion) {
        throw new RuntimeException('Usá entre 8 y 72 caracteres y confirmá la misma contraseña.');
    }
    return [$nombre, $email];
}

function registrarCliente(string $nombre, string $email, string $contrasena, string $confirmacion): void
{
    [$nombre, $email] = validarDatosCliente($nombre, $email, $contrasena, $confirmacion);
    $existente = one('SELECT id_cliente, verificado FROM clientes WHERE email=?', 's', [$email]);
    if ($existente) {
        if (!(int) $existente['verificado']) {
            return;
        }
        throw new RuntimeException('Ya existe una cuenta con ese correo. Iniciá sesión.');
    }
    try {
        sql('INSERT INTO clientes (nombre, email, password, verificado) VALUES (?, ?, ?, 0)', 'sss',
            [$nombre, $email, password_hash($contrasena, PASSWORD_DEFAULT)]);
    } catch (mysqli_sql_exception $error) {
        if ((int) $error->getCode() === 1062) {
            $existente = one('SELECT verificado FROM clientes WHERE email=?', 's', [$email]);
            if ($existente && !(int) $existente['verificado']) {
                return;
            }
            throw new RuntimeException('Ya existe una cuenta con ese correo. Iniciá sesión.');
        }
        throw $error;
    }
}

function enviarCodigoVerificacionCliente(string $email): void
{
    $email = strtolower(trim($email));
    $cliente = one('SELECT id_cliente, nombre, verificado, ultimo_codigo_en, ultimo_codigo_en > DATE_SUB(NOW(), INTERVAL 1 MINUTE) AS enfriamiento FROM clientes WHERE email=?', 's', [$email]);
    if (!$cliente || (int) $cliente['verificado']) {
        throw new RuntimeException('No hay una cuenta pendiente de verificación para ese correo.');
    }
    if ($cliente['enfriamiento']) {
        throw new RuntimeException('Esperá un minuto antes de pedir otro código.');
    }

    $codigo = (string) random_int(100000, 999999);
    $hash = password_hash($codigo, PASSWORD_DEFAULT);
    sql('UPDATE clientes SET codigo_verificacion_hash=?, codigo_verificacion_vence=DATE_ADD(NOW(), INTERVAL 10 MINUTE), intentos_verificacion=0, ultimo_codigo_en=NOW() WHERE id_cliente=?',
        'si', [$hash, (int) $cliente['id_cliente']]);

    $cuerpo = "Hola {$cliente['nombre']},\n\nTu código para verificar la cuenta de Panadería Alas es: {$codigo}\n\nEl código vence en 10 minutos. Si no solicitaste esta cuenta, podés ignorar este mensaje.";
    try {
        enviarCorreoGmail($email, 'Código para verificar tu cuenta · Panadería Alas', $cuerpo);
    } catch (Throwable $error) {
        sql('UPDATE clientes SET codigo_verificacion_hash=NULL, codigo_verificacion_vence=NULL, intentos_verificacion=0, ultimo_codigo_en=NULL WHERE id_cliente=?',
            'i', [(int) $cliente['id_cliente']]);
        throw $error;
    }
}

function verificarCodigoCliente(string $email, string $codigo): void
{
    $email = strtolower(trim($email));
    if (!preg_match('/^\d{6}$/', $codigo)) {
        throw new RuntimeException('Ingresá el código de seis dígitos que te enviamos.');
    }

    global $conexion;
    $conexion->begin_transaction();
    $errorVerificacion = '';
    try {
        $cliente = one('SELECT id_cliente, verificado, codigo_verificacion_hash, codigo_verificacion_vence, codigo_verificacion_vence > NOW() AS codigo_vigente, intentos_verificacion FROM clientes WHERE email=? FOR UPDATE', 's', [$email]);
        if (!$cliente || (int) $cliente['verificado']) {
            $errorVerificacion = 'No encontramos una verificación pendiente para ese correo.';
        } elseif (!$cliente['codigo_verificacion_hash'] || !$cliente['codigo_vigente']
            || (int) $cliente['intentos_verificacion'] >= 5) {
            sql('UPDATE clientes SET codigo_verificacion_hash=NULL, codigo_verificacion_vence=NULL, intentos_verificacion=0 WHERE id_cliente=?', 'i', [(int) $cliente['id_cliente']]);
            $errorVerificacion = 'El código venció o alcanzaste el límite de intentos. Pedí uno nuevo.';
        } elseif (!password_verify($codigo, $cliente['codigo_verificacion_hash'])) {
            sql('UPDATE clientes SET intentos_verificacion=intentos_verificacion+1 WHERE id_cliente=?', 'i', [(int) $cliente['id_cliente']]);
            $errorVerificacion = 'El código no coincide. Revisalo e intentá nuevamente.';
        } else {
            sql('UPDATE clientes SET verificado=1, codigo_verificacion_hash=NULL, codigo_verificacion_vence=NULL, intentos_verificacion=0 WHERE id_cliente=?',
                'i', [(int) $cliente['id_cliente']]);
        }
        $conexion->commit();
    } catch (Throwable $error) {
        $conexion->rollback();
        throw $error;
    }
    if ($errorVerificacion !== '') {
        throw new RuntimeException($errorVerificacion);
    }
}

function iniciarSesionCliente(string $email, string $contrasena): void
{
    global $conexion;
    $email = strtolower(trim($email));
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 190) {
        $email = '';
    }
    $mensaje = 'Revisá tu correo y contraseña e intentá nuevamente.';
    $cliente = null;
    $errorAcceso = '';

    $conexion->begin_transaction();
    try {
        sql('INSERT INTO intentos_acceso_cliente (email) VALUES (?) ON DUPLICATE KEY UPDATE email=VALUES(email)', 's', [$email]);
        $registro = one('SELECT *, bloqueado_hasta > NOW() AS bloqueado FROM intentos_acceso_cliente WHERE email=? FOR UPDATE', 's', [$email]);
        if ($registro['bloqueado']) {
            $errorAcceso = 'Esperá un minuto antes de volver a intentarlo.';
        } else {
            $cliente = $email === '' ? null : one('SELECT id_cliente, nombre, email, password, verificado FROM clientes WHERE email=?', 's', [$email]);
            if (!$cliente || !password_verify($contrasena, $cliente['password'])) {
                $intentos = $registro['bloqueado_hasta'] !== null ? 1 : (int) $registro['intentos'] + 1;
                sql('UPDATE intentos_acceso_cliente SET intentos=?, bloqueado_hasta=IF(? >= 6, DATE_ADD(NOW(), INTERVAL 1 MINUTE), NULL) WHERE email=?',
                    'iis', [$intentos, $intentos, $email]);
                $errorAcceso = $mensaje;
            } elseif (!(int) $cliente['verificado']) {
                $errorAcceso = 'Tu correo todavía no está verificado. Ingresá el código que te enviamos.';
            } else {
                sql('DELETE FROM intentos_acceso_cliente WHERE email=?', 's', [$email]);
            }
        }
        $conexion->commit();
    } catch (Throwable $error) {
        $conexion->rollback();
        throw $error;
    }

    if ($errorAcceso !== '') {
        if ($cliente && !(int) $cliente['verificado']) {
            $_SESSION['cliente_verificacion_email'] = $email;
        }
        throw new RuntimeException($errorAcceso);
    }
    $usuarioEquipo = current_user();
    $conservarDueno = $usuarioEquipo
        && $usuarioEquipo['rol'] === 'dueno'
        && $usuarioEquipo['estado'] === 'aprobado';
    session_regenerate_id(true);
    if (!$conservarDueno) {
        unset($_SESSION['user_id']);
    }
    $_SESSION['cliente_id'] = (int) $cliente['id_cliente'];
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    unset($_SESSION['login_intentos'], $_SESSION['login_bloqueado_hasta']);
}

