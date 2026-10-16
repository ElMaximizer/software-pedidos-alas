<?php
session_start();

if (isset($_SESSION['user_id'])) {
    header('Location: principal.php');
    exit();
}

require_once __DIR__ . '/../Core/conexion.php';

if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$error = '';
$cedula = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cedula = trim((string) ($_POST['cedula'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');
    $confirmPassword = (string) ($_POST['confirm_password'] ?? '');
    $csrfValido = isset($_POST['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], (string) $_POST['csrf_token']);

    if (!$csrfValido) {
        $error = 'La sesión del formulario venció. Volvé a intentarlo.';
    } elseif ($cedula === '' || $password === '' || $confirmPassword === '') {
        $error = 'Todos los campos son obligatorios.';
    } elseif (!ctype_digit($cedula)) {
        $error = 'Ingresá una cédula válida.';
    } elseif (strlen($password) < 6) {
        $error = 'La contraseña debe tener al menos 6 caracteres.';
    } elseif ($password !== $confirmPassword) {
        $error = 'Las contraseñas no coinciden.';
    } else {
        try {
            $cedulaNumerica = (int) $cedula;
            $stmtExiste = $conexion->prepare('SELECT id_sol FROM solicitud_usuario WHERE cedula = ?');
            $stmtExiste->bind_param('i', $cedulaNumerica);
            $stmtExiste->execute();

            if ($stmtExiste->get_result()->num_rows > 0) {
                $error = 'Ya existe un usuario con esa cédula.';
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmtInsert = $conexion->prepare('INSERT INTO solicitud_usuario (cedula, password) VALUES (?, ?)');
                $stmtInsert->bind_param('is', $cedulaNumerica, $hash);
                $stmtInsert->execute();
                header('Location: login.php?registrado=1');
                exit();
            }
        } catch (Throwable $excepcion) {
            $error = 'No pudimos completar el registro. Intentá nuevamente.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../public/css/css.css?v=20260920-1">
    <title>Registro | Panadería Alas</title>
</head>
<body class="pagina-inicio-sesion pagina-registro">
<?php require __DIR__ . '/partials/site-header.php'; ?>
    <main class="contenido-principal">
        <section class="tarjeta-inicio-sesion" aria-labelledby="titulo-registro">
            <a class="marca marca--tarjeta" href="../../public/index.php" aria-label="Panadería Alas, inicio">
                <span class="marca-texto">
                    <span class="marca-nombre">Panadería Alas</span>
                    <span class="marca-descripcion">Sistema de registro de pedidos</span>
                </span>
            </a>

            <div class="encabezado-formulario">
                <h1 id="titulo-registro">Registro</h1>
            </div>

            <?php if ($error !== ''): ?>
                <div class="mensaje-formulario mensaje-formulario--error" role="alert">
                    <img class="icono" src="../../public/images/icono-error.svg" alt="">
                    <span><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></span>
                </div>
            <?php endif; ?>

            <form class="formulario-inicio-sesion" method="post">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">

                <label class="campo-formulario" for="cedula">
                    Cédula
                    <span class="control-formulario">
                        <img class="icono icono--campo" src="../../public/images/icono-usuario.svg" alt="">
                        <input type="text" inputmode="numeric" id="cedula" name="cedula" value="<?= htmlspecialchars($cedula, ENT_QUOTES, 'UTF-8') ?>" placeholder="Número de cédula" autocomplete="username" required>
                    </span>
                </label>

                <label class="campo-formulario" for="password">
                    Contraseña
                    <span class="control-formulario">
                        <img class="icono icono--campo" src="../../public/images/icono-candado.svg" alt="">
                        <input type="password" id="password" name="password" placeholder="••••••••" autocomplete="new-password" required>
                        <button class="boton-visibilidad" type="button" data-toggle-password="password" aria-label="Mostrar contraseña"><img src="../../public/images/icono-contrasena-oculta.svg" alt=""></button>
                    </span>
                </label>

                <label class="campo-formulario" for="confirm_password">
                    Confirmar contraseña
                    <span class="control-formulario">
                        <img class="icono icono--campo" src="../../public/images/icono-candado.svg" alt="">
                        <input type="password" id="confirm_password" name="confirm_password" placeholder="••••••••" autocomplete="new-password" required>
                        <button class="boton-visibilidad" type="button" data-toggle-password="confirm_password" aria-label="Mostrar contraseña"><img src="../../public/images/icono-contrasena-oculta.svg" alt=""></button>
                    </span>
                </label>

                <button class="boton-principal" type="submit">Registrar</button>
            </form>

            <div class="marca-qba">
                <img class="marca-qba-imagen" src="../../public/images/QBA.png" alt="QBA, solo desarrolladores">
            </div>
        </section>
    </main>

<?php require __DIR__ . '/partials/site-footer.php'; ?>
    <script src="../../public/js/auth.js" defer></script>
</body>
</html>
