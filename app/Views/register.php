<?php
session_start();
if (isset($_SESSION['usuario'])) {
    header('Location: principal.php');
    exit();
}

include '../Core/conexion.php';

$error = '';
$success = '';
$usuario = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = trim($_POST['usuario'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if (empty($usuario) || empty($password) || empty($confirmPassword)) {
        $error = 'Todos los campos son obligatorios.';
    } elseif ($password !== $confirmPassword) {
        $error = 'Las contraseñas no coinciden.';
    } else {
        $sqlExiste = "SELECT id FROM solicitud_usuario WHERE nombre = ?";
        $stmtExiste = $conexion->prepare($sqlExiste);
        $stmtExiste->bind_param("s", $usuario);
        $stmtExiste->execute();
        $resultadoExiste = $stmtExiste->get_result();

        if ($resultadoExiste->num_rows > 0) {
            $error = 'Este usuario ya está registrado.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);

            $sqlInsert = "INSERT INTO solicitud_usuario (nombre, password) VALUES (?, ?)";
            $stmtInsert = $conexion->prepare($sqlInsert);
            $stmtInsert->bind_param("ss", $usuario, $hash);

            if ($stmtInsert->execute()) {
                $success = 'Solicitud exitosa. Espera a que un administrador acepte tu solicitud.';
            } else {
                $error = 'Error al solicitar usuario. Intenta de nuevo.';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../public/css/css.css">
    <title>Solicitar Usuario | Panadería Alas</title>
</head>
<body class="pagina-inicio-sesion">
    <header class="encabezado-sitio">
        <div class="contenedor encabezado-contenido">
            <a class="marca marca--encabezado" href="register.php" aria-label="Panadería Alas, inicio">
                <span class="marca-texto">
                    <span class="marca-nombre">Panadería Alas</span>
                    <span class="marca-descripcion">Sistema de registro de pedidos</span>
                </span>
            </a>
        </div>
    </header>

    <main class="contenido-principal">
        <section class="tarjeta-inicio-sesion" aria-labelledby="titulo-inicio-sesion">
            <div class="marca marca--tarjeta" aria-label="Panadería Alas">
                <span class="marca-texto">
                    <span class="marca-nombre">Panadería Alas</span>
                    <span class="marca-descripcion">Sistema de registro de pedidos</span>
                </span>
            </div>

            <div class="encabezado-formulario">
                <h1 id="titulo-inicio-sesion">Solicitar Usuario</h1>
            </div>

            <?php if ($error !== ''): ?>
                <div class="mensaje-error">
                    <img class="icono" src="../../public/images/icono-error.svg" alt="">
                    <span><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></span>
                </div>
            <?php endif; ?>

            <form class="formulario-inicio-sesion" action="register.php" method="post">
                <div class="campo-formulario">
                    <label for="usuario">Usuario</label>
                    <div class="control-formulario">
                        <img class="icono icono--campo" src="../../public/images/icono-usuario.svg" alt="">
                        <input type="text" id="usuario" name="usuario" value="<?= htmlspecialchars($usuario, ENT_QUOTES, 'UTF-8') ?>" placeholder="mia.carpanessi" required
                        >
                    </div>
                </div>

                <div class="campo-formulario">
                    <label for="password">Contraseña</label>
                    <div class="control-formulario">
                        <img class="icono icono--campo" src="../../public/images/icono-candado.svg" alt="">
                        <input type="password" id="password" name="password" placeholder="••••••••" required>
                    </div>
                </div>

                <div class="campo-formulario">
                    <label for="confirm_password">Confirmar Contraseña</label>
                    <div class="control-formulario">
                        <img class="icono icono--campo" src="../../public/images/icono-candado.svg" alt="">
                        <input type="password" id="confirm_password" name="confirm_password" placeholder="••••••••" required>
                    </div>
                </div>

                <button class="boton-principal" type="submit">Solicitar Usuario</button>

            </form>

            <div class="marca-qba">
                <img class="marca-qba-imagen" src="../../public/images/QBA.png" alt="QBA">
            </div>
        </section>
    </main>

    <footer class="pie-sitio">
        <div class="contenedor">
            <div class="informacion-pie">
                <section class="bloque-pie" aria-labelledby="titulo-direccion">
                    <h2 id="titulo-direccion">
                        <img class="icono icono--pie" src="../../public/images/icono-ubicacion.svg" alt="">
                        Panadería Alas
                    </h2>
                    <p>Agraciada 1299 Esquina, Dr Luis Alberto de Herrera<br>Salto, Uruguay</p>
                </section>

                <section class="bloque-pie" aria-labelledby="titulo-horario">
                    <h2 id="titulo-horario">
                        <img class="icono icono--pie" src="../../public/images/icono-horario.svg" alt="">
                        Horario de Atención
                    </h2>
                    <p>Lunes a Viernes: 06:00 - 18:00<br>Sábados: 06:00 - 13:00</p>
                </section>

                <section class="bloque-pie" aria-labelledby="titulo-contacto">
                    <h2 id="titulo-contacto">
                        <img class="icono icono--pie" src="../../public/images/icono-telefono.svg" alt="">
                        Contáctanos
                    </h2>
                    <p>098 788 807<br>pedidos@panaderiaalas.uy</p>
                </section>
            </div>

            <div class="separador-pie"></div>
            <p class="derechos-reservados">© 2026 Panadería Alas. Todos los derechos reservados.</p>
        </div>
    </footer>
</body>
</html>