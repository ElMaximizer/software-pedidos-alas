<?php
session_start();
include '../Core/conexion.php';

$error =
$usuario = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = trim($_POST['usuario'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($usuario) || empty($password)) {
        $error = 'Todos los campos son obligatorios.';
    } else {
        $sql = "SELECT id, usuario, password FROM usuarios WHERE usuario = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("s", $usuario);
        $stmt->execute();

        $resultado = $stmt->get_result();
        $user = $resultado->fetch_assoc();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['usuario'] = $user['usuario'];
            header('Location: principal.php');
            exit();
        }

        $error = 'Usuario o contraseña incorrectos.';
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Inicio de sesión del sistema de registro de pedidos de Panadería Alas">
    <link rel="stylesheet" href="../../public/css/css.css">
    <title>Iniciar sesión | Panadería Alas</title>
</head>
<body class="pagina-inicio-sesion">
    <header class="encabezado-sitio">
        <div class="contenedor encabezado-contenido">
            <a class="marca marca--encabezado" href="login.php" aria-label="Panadería Alas, inicio">
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
                <h1 id="titulo-inicio-sesion">Inicio de Sesión</h1>
            </div>

            <?php if ($error !== ''): ?>
                <div class="mensaje-error" role="alert">
                    <img class="icono" src="../../public/images/icono-error.svg" alt="">
                    <span><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></span>
                </div>
            <?php endif; ?>

            <form class="formulario-inicio-sesion" action="login.php" method="post">
                <div class="campo-formulario">
                    <label for="usuario">Usuario</label>
                    <div class="control-formulario">
                        <img class="icono icono--campo" src="../../public/images/icono-usuario.svg" alt="">
                        <input
                            type="text"
                            id="usuario"
                            name="usuario"
                            value="<?= htmlspecialchars($usuario, ENT_QUOTES, 'UTF-8') ?>"
                            placeholder="juan.perez"
                            autocomplete="username"
                            required
                        >
                    </div>
                </div>

                <div class="campo-formulario">
                    <label for="password">Contraseña</label>
                    <div class="control-formulario">
                        <img class="icono icono--campo" src="../../public/images/icono-candado.svg" alt="">
                        <input
                            type="password"
                            id="password"
                            name="password"
                            placeholder="••••••••"
                            autocomplete="current-password"
                            required
                        >
                        <button class="boton-mostrar-contrasena" type="button" aria-label="Mostrar contraseña" aria-pressed="false">
                            <img class="icono icono--mostrar" src="../../public/images/icono-contrasena-oculta.svg" alt="">
                        </button>
                    </div>
                </div>

                <button class="boton-principal" type="submit">Iniciar sesión</button>

                <nav class="enlaces-formulario" aria-label="Enlaces de acceso">
                    <a class="enlace-formulario enlace-formulario--olvido" href="#">¿Olvidaste tu contraseña?</a>
                    <a class="enlace-formulario enlace-formulario--solicitud" href="#">Solicitar usuario</a>
                </nav>
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
                    <p>Av. Italia 1234<br>Salto, Uruguay</p>
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
                    <p>+598 473 12345<br>pedidos@panaderiaalas.uy</p>
                </section>
            </div>

            <div class="separador-pie"></div>
            <p class="derechos-reservados">© 2026 Panadería Alas. Todos los derechos reservados.</p>
        </div>
    </footer>

    <script>
        const botonMostrarContrasena = document.querySelector('.boton-mostrar-contrasena');
        const campoContrasena = document.querySelector('#password');

        botonMostrarContrasena?.addEventListener('click', () => {
            const mostrarContrasena = campoContrasena.type === 'password';
            campoContrasena.type = mostrarContrasena ? 'text' : 'password';
            botonMostrarContrasena.setAttribute('aria-pressed', String(mostrarContrasena));
            botonMostrarContrasena.setAttribute('aria-label', mostrarContrasena ? 'Ocultar contraseña' : 'Mostrar contraseña');
        });
    </script>
</body>
</html>