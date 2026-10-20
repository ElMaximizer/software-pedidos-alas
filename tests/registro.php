<?php
// Pruebas de reglas de registro: dobles de SMTP y MySQL, sin servicios externos.
error_reporting(E_ALL);
set_error_handler(static function ($nivel, $mensaje) { throw new ErrorException($mensaje, 0, $nivel); });
require __DIR__ . '/../app/Core/validacion.php';
require __DIR__ . '/../app/Services/registro.php';

class ConexionRegistroPrueba
{
    public array $filas = [];
    public array $pendientes = [];
    public bool $fallar = false;
    public function begin_transaction(): void { $this->pendientes = []; }
    public function commit(): void { $this->filas = array_merge($this->filas, $this->pendientes); $this->pendientes = []; }
    public function rollback(): void { $this->pendientes = []; }
}

function one(string $consulta, string $tipos = '', array $parametros = []): ?array
{
    global $conexion;
    if (str_contains($consulta, 'GET_LOCK')) return ['obtenido' => 1];
    foreach ($conexion->filas as $fila) {
        if ($fila['cedula'] === $parametros[0] || $fila['email'] === $parametros[1]) return ['id_sol' => 1];
    }
    return null;
}

function sql(string $consulta, string $tipos = '', array $parametros = []): void
{
    global $conexion;
    if (str_contains($consulta, 'RELEASE_LOCK')) return;
    if (!str_contains($consulta, 'INSERT INTO solicitud_usuario')) throw new RuntimeException('Consulta inesperada');
    if ($conexion->fallar) throw new RuntimeException('Fallo SQL simulado');
    $conexion->pendientes[] = ['nombre' => $parametros[0], 'cedula' => $parametros[1], 'email' => $parametros[2],
        'password' => $parametros[3], 'estado' => 'pendiente', 'rol' => 'empleado'];
}

function enviarCorreoSmtp(string $correo, string $asunto, string $cuerpo): void
{
    global $buzon, $fallarSmtp;
    if ($fallarSmtp) throw new RuntimeException('SMTP no disponible');
    $buzon[] = compact('correo', 'asunto', 'cuerpo');
}

function comprobar(bool $condicion, string $mensaje): void
{
    if (!$condicion) throw new RuntimeException($mensaje);
}
function falla(callable $accion, string $texto): void
{
    try { $accion(); } catch (RuntimeException $error) {
        comprobar(str_contains($error->getMessage(), $texto), 'Error inesperado: ' . $error->getMessage());
        return;
    }
    throw new RuntimeException('Se esperaba rechazo: ' . $texto);
}
function preparar(): void
{
    global $conexion, $buzon, $fallarSmtp;
    $_SESSION = [];
    $conexion = new ConexionRegistroPrueba(); $buzon = []; $fallarSmtp = false;
}
function solicitar(): void { solicitarUsuario('Equipo QA', '81234567', 'qa@example.invalid', 'AlasTest2026!', 'AlasTest2026!'); }
function codigoEnviado(): string
{
    global $buzon;
    preg_match('/es: ([0-9]{6})/', $buzon[count($buzon) - 1]['cuerpo'], $coincidencia);
    return $coincidencia[1];
}
function habilitarReenvio(): void { $_SESSION['registro_envios'][count($_SESSION['registro_envios']) - 1] = time() - 61; }

preparar();
falla(fn () => solicitarUsuario('', '81234567', 'qa@example.invalid', 'AlasTest2026!', 'AlasTest2026!'), 'tu nombre');
falla(fn () => solicitarUsuario('QA', 'x', 'qa@example.invalid', 'AlasTest2026!', 'AlasTest2026!'), 'cédula');
falla(fn () => solicitarUsuario('QA', '81234567', 'sin-correo', 'AlasTest2026!', 'AlasTest2026!'), 'correo');
falla(fn () => solicitarUsuario('QA', '81234567', 'qa@example.invalid', 'corta', 'corta'), 'caracteres');
comprobar(!$buzon && !$conexion->filas, 'Validación envió o guardó datos');
echo "PASS validación de datos previa al envío\n";

solicitar(); $registro = registroPendiente(); $codigo = codigoEnviado();
comprobar(count($buzon) === 1 && !$conexion->filas, 'Cuenta creada antes de verificar');
comprobar(password_verify('AlasTest2026!', $registro['password_hash']), 'Hash de contraseña inválido');
comprobar(!str_contains(json_encode($_SESSION), 'AlasTest2026!') && !str_contains(json_encode($_SESSION), $codigo), 'Secretos en texto plano');
falla(fn () => confirmarRegistro($codigo, 'otro-registro'), 'venció');
falla(fn () => solicitar(), 'pendiente');
confirmarRegistro($codigo, $registro['id']);
comprobar(count($conexion->filas) === 1 && !registroPendiente(), 'No se consumió el registro');
comprobar($conexion->filas[0]['estado'] === 'pendiente' && $conexion->filas[0]['rol'] === 'empleado', 'Se elevaron permisos');
comprobar(empty($_SESSION['user_id']), 'Inicio de sesión automático');
falla(fn () => confirmarRegistro($codigo, $registro['id']), 'venció');
comprobar(count($conexion->filas) === 1, 'Código reutilizado');
echo "PASS cuenta solo después del código correcto, un solo uso y aprobación preservada\n";

preparar(); solicitar(); $id = registroPendiente()['id']; $codigo = codigoEnviado();
for ($i = 0; $i < 5; $i++) falla(fn () => confirmarRegistro('000000', $id), 'no es correcto');
falla(fn () => confirmarRegistro($codigo, $id), 'Agotaste');
habilitarReenvio(); falla(fn () => reenviarCodigoRegistro($id), 'Agotaste');
comprobar(!$conexion->filas, 'Se omitió límite de intentos');
echo "PASS cinco intentos y bloqueo conservado al reenviar\n";

preparar(); solicitar(); $id = registroPendiente()['id']; $anterior = codigoEnviado();
falla(fn () => reenviarCodigoRegistro($id), 'un minuto');
habilitarReenvio(); reenviarCodigoRegistro($id); $nuevo = codigoEnviado();
comprobar($nuevo !== $anterior, 'Reenvío repitió el código');
falla(fn () => confirmarRegistro($anterior, $id), 'no es correcto');
confirmarRegistro($nuevo, $id);
echo "PASS reenvío invalida código anterior y conserva sesión\n";

preparar(); solicitar(); $id = registroPendiente()['id'];
$_SESSION['registro_pendiente']['vence_en'] = time() - 1;
falla(fn () => confirmarRegistro(codigoEnviado(), $id), 'venció');
$_SESSION['registro_pendiente']['creado_en'] = time() - 1801;
comprobar(registroPendiente() === null, 'Registro no venció');
echo "PASS expiración del código y del registro completo\n";

preparar(); $fallarSmtp = true;
falla(fn () => solicitar(), 'SMTP');
comprobar(!registroPendiente() && !$conexion->filas, 'Fallo SMTP creó registro');
$fallarSmtp = false; falla(fn () => solicitar(), 'un minuto');
habilitarReenvio(); solicitar(); $antes = registroPendiente();
habilitarReenvio(); $fallarSmtp = true;
falla(fn () => reenviarCodigoRegistro($antes['id']), 'SMTP');
comprobar(registroPendiente() === $antes, 'Fallo SMTP reemplazó código anterior');
echo "PASS fallo SMTP sin crear cuenta ni reemplazar código, con límite de envíos\n";

preparar(); solicitar();
for ($i = 0; $i < 4; $i++) { habilitarReenvio(); reenviarCodigoRegistro(registroPendiente()['id']); }
habilitarReenvio(); unset($_SESSION['registro_pendiente']);
falla(fn () => solicitar(), 'límite de envíos');
echo "PASS cancelar no evita el límite horario\n";

preparar(); solicitar(); $registro = registroPendiente(); $codigo = codigoEnviado();
$conexion->fallar = true; falla(fn () => confirmarRegistro($codigo, $registro['id']), 'SQL');
comprobar(!$conexion->filas && !$conexion->pendientes && registroPendiente() !== null, 'Rollback incorrecto');
$conexion->fallar = false; confirmarRegistro($codigo, $registro['id']);
echo "PASS fallo SQL conserva verificación pendiente y permite reintento\n";

preparar(); solicitar(); $registro = registroPendiente();
$conexion->filas[] = ['cedula' => 81234567, 'email' => 'otro@example.invalid'];
falla(fn () => confirmarRegistro(codigoEnviado(), $registro['id']), 'Ya existe');
comprobar(count($conexion->filas) === 1, 'Duplicado en confirmación');
echo "PASS duplicados revisados otra vez al confirmar\n";
