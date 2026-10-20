<?php
// Datos pendientes en la sesión: nunca se guarda la contraseña en texto plano.
// No requiere tablas nuevas ni cambios automáticos de esquema en el hosting.
function validarContrasena(string $contrasena, string $confirmacion): void
{
    if (strlen($contrasena) < 8 || strlen($contrasena) > 72 || $contrasena !== $confirmacion) {
        throw new RuntimeException('Usá entre 8 y 72 caracteres y confirmá la misma contraseña.');
    }
}

function registroPendiente(): ?array
{
    $registro = $_SESSION['registro_pendiente'] ?? null;
    if (!is_array($registro) || ($registro['creado_en'] ?? 0) + 1800 <= time()) {
        unset($_SESSION['registro_pendiente']);
        return null;
    }
    return $registro;
}

function comprobarDisponibilidadRegistro(string $cedula, string $correo): void
{
    if (one('SELECT id_sol FROM solicitud_usuario WHERE cedula=? OR email=?', 'is', [(int) $cedula, $correo])) {
        throw new RuntimeException('Ya existe una cuenta o solicitud con esos datos.');
    }
}

function enviarCodigoRegistro(array $registro): void
{
    // Límites por sesión, conservados incluso al cancelar o fallar SMTP.
    $ahora = time();
    $envios = array_values(array_filter($_SESSION['registro_envios'] ?? [], static fn ($fecha) => $fecha > $ahora - 3600));
    if ($envios && $ahora - $envios[count($envios) - 1] < 60) {
        throw new RuntimeException('Esperá un minuto antes de solicitar otro código.');
    }
    if (count($envios) >= 5) {
        throw new RuntimeException('Alcanzaste el límite de envíos. Volvé a intentarlo en una hora.');
    }
    $envios[] = $ahora;
    $_SESSION['registro_envios'] = $envios;

    do {
        $codigo = (string) random_int(100000, 999999);
    } while (isset($registro['codigo_hash']) && password_verify($codigo, $registro['codigo_hash']));
    $registro['codigo_hash'] = password_hash($codigo, PASSWORD_DEFAULT);
    $registro['vence_en'] = $ahora + 600;
    $registro['ultimo_envio'] = $ahora;

    enviarCorreoSmtp(
        $registro['correo'],
        'Código de verificación - Panadería Alas',
        "Hola {$registro['nombre']}.\n\nTu código de verificación es: $codigo\n\n" .
        "Ingresalo en la misma ventana donde solicitaste tu usuario. Vence en 10 minutos.\n" .
        'Si no solicitaste este registro, ignorá el mensaje.'
    );
    // Un fallo SMTP no reemplaza ni valida un código anterior.
    $_SESSION['registro_pendiente'] = $registro;
}

function solicitarUsuario(string $nombre, string $cedula, string $correo, string $contrasena, string $confirmacion): void
{
    $nombre = validate_text(trim($nombre), 'tu nombre', 120);
    $correo = trim($correo);
    if (!ctype_digit($cedula) || strlen($cedula) < 6 || strlen($cedula) > 8) {
        throw new RuntimeException('Ingresá una cédula de 6 a 8 dígitos, sin puntos ni guiones.');
    }
    validarCorreo($correo);
    validarContrasena($contrasena, $confirmacion);
    if (registroPendiente()) {
        throw new RuntimeException('Ya tenés una verificación pendiente. Ingresá el código o cancelá ese registro.');
    }
    comprobarDisponibilidadRegistro($cedula, $correo);
    enviarCodigoRegistro([
        'id' => bin2hex(random_bytes(16)),
        'nombre' => $nombre,
        'cedula' => $cedula,
        'correo' => $correo,
        'password_hash' => password_hash($contrasena, PASSWORD_DEFAULT),
        'creado_en' => time(),
        'intentos' => 0,
    ]);
}

function reenviarCodigoRegistro(string $id): void
{
    $registro = registroPendiente();
    if (!$registro || !hash_equals($registro['id'], $id)) {
        throw new RuntimeException('La verificación venció. Volvé a solicitar tu usuario.');
    }
    if ($registro['intentos'] >= 5) {
        throw new RuntimeException('Agotaste los intentos. Cancelá este registro y empezá nuevamente.');
    }
    // Reenviar invalida el código anterior y no reinicia los intentos fallidos.
    enviarCodigoRegistro($registro);
}

function confirmarRegistro(string $codigo, string $id): void
{
    global $conexion;
    $registro = registroPendiente();
    if (!$registro || !hash_equals($registro['id'], $id)) {
        throw new RuntimeException('La verificación venció. Volvé a solicitar tu usuario.');
    }
    if ($registro['intentos'] >= 5) {
        throw new RuntimeException('Agotaste los intentos. Cancelá este registro y empezá nuevamente.');
    }
    if ($registro['vence_en'] <= time()) {
        throw new RuntimeException('El código venció. Solicitá uno nuevo.');
    }
    $_SESSION['registro_pendiente']['intentos']++;
    if (!preg_match('/^[0-9]{6}$/D', $codigo) || !password_verify($codigo, $registro['codigo_hash'])) {
        throw new RuntimeException('El código no es correcto. Revisá el correo e intentá nuevamente.');
    }

    // Evita duplicados si dos sesiones verifican la misma cédula o correo.
    $bloqueo = one("SELECT GET_LOCK('alas_solicitud_usuario', 5) AS obtenido");
    if ((int) $bloqueo['obtenido'] !== 1) {
        throw new RuntimeException('Intentá nuevamente en unos segundos.');
    }
    try {
        comprobarDisponibilidadRegistro($registro['cedula'], $registro['correo']);
        $conexion->begin_transaction();
        try {
            sql(
                "INSERT INTO solicitud_usuario (nombre, cedula, email, password, estado, rol)
                 VALUES (?, ?, ?, ?, 'pendiente', 'empleado')",
                'siss',
                [$registro['nombre'], (int) $registro['cedula'], $registro['correo'], $registro['password_hash']]
            );
            $conexion->commit();
        } catch (Throwable $error) {
            $conexion->rollback();
            throw $error;
        }
    } finally {
        sql("SELECT RELEASE_LOCK('alas_solicitud_usuario')");
    }
    // Solo un commit exitoso consume el registro y permite mostrar confirmación.
    unset($_SESSION['registro_pendiente']);
    $_SESSION['solicitud_enviada'] = 'registro';
}
