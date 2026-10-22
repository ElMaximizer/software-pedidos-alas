<?php
// Sesión del equipo, permisos y protección de formularios CSRF.
function csrf_token(): string
{
    return $_SESSION['csrf_token'] ??= bin2hex(random_bytes(32));
}

function csrf_field(): void
{
    echo '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

function check_csrf(): void
{
    if (!hash_equals(csrf_token(), post('csrf_token'))) {
        throw new RuntimeException('La sesión del formulario venció. Volvé a intentarlo.');
    }
}

/** Cada pestaña recibe su propio formulario; procesar uno no invalida los demás. */
function crearNonceFormulario(string $contexto, array $datos = []): string
{
    foreach ($_SESSION['formularios'] ?? [] as $nonce => $formulario) {
        if ($formulario['creado_en'] < time() - 7200) {
            unset($_SESSION['formularios'][$nonce]);
        }
    }
    // Limita el tamaño de la sesión si se actualiza una pantalla muchas veces.
    if (count($_SESSION['formularios'] ?? []) >= 100) {
        $masAntiguo = array_key_first($_SESSION['formularios']);
        unset($_SESSION['formularios'][$masAntiguo]);
    }

    $nonce = bin2hex(random_bytes(24));
    $_SESSION['formularios'][$nonce] = [
        'contexto' => $contexto,
        'creado_en' => time(),
        'datos' => $datos,
    ];
    return $nonce;
}

function validarNonceFormulario(string $nonce, string $contexto): array
{
    $formulario = $_SESSION['formularios'][$nonce] ?? null;
    if (!$formulario || $formulario['contexto'] !== $contexto
        || $formulario['creado_en'] < time() - 7200) {
        throw new RuntimeException('Este formulario venció o ya fue procesado. Volvé a abrir la pantalla.');
    }
    return $formulario['datos'];
}

function consumirNonceFormulario(string $nonce): void
{
    unset($_SESSION['formularios'][$nonce]);
}

function current_user(): ?array
{
    if (empty($_SESSION['user_id'])) {
        return null;
    }
    $usuario = one('SELECT id_sol, cedula, nombre, email, estado, rol, ultimo_acceso FROM solicitud_usuario WHERE id_sol = ?', 'i', [(int) $_SESSION['user_id']]);
    if ($usuario && $usuario['estado'] === 'aprobado') {
        actualizarPresenciaEquipo($usuario);
    }
    return $usuario;
}

/** Marca actividad periódica sin guardar el identificador real de la sesión. */
function actualizarPresenciaEquipo(array $usuario, bool $nuevoIngreso = false): void
{
    if (empty($_SESSION['user_id']) || (int) $_SESSION['user_id'] !== (int) $usuario['id_sol']) {
        return;
    }
    $ahora = time();
    if (!$nuevoIngreso && isset($_SESSION['_equipo_actividad']) && $ahora - (int) $_SESSION['_equipo_actividad'] < 25) {
        return;
    }
    $huella = hash('sha256', session_id());
    if ($nuevoIngreso) {
        sql('UPDATE solicitud_usuario SET ultimo_acceso=NOW() WHERE id_sol=?', 'i', [(int) $usuario['id_sol']]);
    }
    sql(
        'INSERT INTO sesiones_equipo (id_usuario, huella_sesion, ultima_actividad)
         VALUES (?, ?, NOW())
         ON DUPLICATE KEY UPDATE id_usuario=VALUES(id_usuario), ultima_actividad=NOW()',
        'is',
        [(int) $usuario['id_sol'], $huella]
    );
    $_SESSION['_equipo_actividad'] = $ahora;
}

function registrarIngresoEquipo(array $usuario): void
{
    $_SESSION['_equipo_actividad'] = 0;
    actualizarPresenciaEquipo($usuario, true);
}

function cerrarPresenciaEquipo(): void
{
    if (!empty($_SESSION['user_id'])) {
        sql('DELETE FROM sesiones_equipo WHERE huella_sesion=?', 's', [hash('sha256', session_id())]);
    }
}

/** Cuenta autenticada de cliente; se guarda en una sesión distinta a la del equipo. */
function current_client(): ?array
{
    if (empty($_SESSION['cliente_id'])) {
        return null;
    }
    return one('SELECT id_cliente, nombre, email FROM clientes WHERE id_cliente = ?', 'i', [(int) $_SESSION['cliente_id']]);
}

function require_user(bool $esAdministracion = false): array
{
    $usuario = current_user();
    if (!$usuario || $usuario['estado'] !== 'aprobado'
        || !in_array($usuario['rol'], ['empleado', 'administrador', 'dueno'], true)) {
        unset($_SESSION['user_id']);
        redirect('app/Views/login.php');
    }
    if ($esAdministracion && !in_array($usuario['rol'], ['administrador', 'dueno'], true)) {
        http_response_code(403);
        exit('No tenés permiso para administrar accesos.');
    }
    return $usuario;
}

/** Acciones reservadas al dueño de la panadería. */
function require_owner(): array
{
    $usuario = require_user();
    if ($usuario['rol'] !== 'dueno') {
        http_response_code(403);
        exit('Esta acción está disponible únicamente para el dueño.');
    }
    return $usuario;
}

