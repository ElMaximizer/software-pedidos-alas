<?php
session_start();
include '../Core/conexion.php';

$error = '';
$mensaje = '';
$usuario = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $usuario = trim($_POST['usuario'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmarPassword = $_POST['confirmar_password'] ?? '';

    // Validar campos vacíos
    if (empty($usuario) || empty($password) || empty($confirmarPassword)) {

        $error = 'Todos los campos son obligatorios.';

        // Comprobar que las contraseñas sean iguales
    } elseif ($password !== $confirmarPassword) {

        $error = 'Las contraseñas no coinciden.';

        // Longitud mínima de contraseña
    } elseif (strlen($password) < 6) {

        $error = 'La contraseña debe tener al menos 6 caracteres.';
    } else {

        // Buscar si el usuario ya existe
        $sql = "SELECT id FROM usuarios WHERE usuario = ?";

        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("s", $usuario);
        $stmt->execute();

        $resultado = $stmt->get_result();

        if ($resultado->num_rows > 0) {

            $error = 'El usuario ya existe.';
        } else {

            // Encriptar la contraseña
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);

            // Guardar usuario
            $sql = "INSERT INTO usuarios (usuario, password) VALUES (?, ?)";

            $stmt = $conexion->prepare($sql);
            $stmt->bind_param("ss", $usuario, $passwordHash);

            if ($stmt->execute()) {

                $mensaje = 'Usuario registrado correctamente.';
                $usuario = '';
            } else {

                $error = 'Error al registrar el usuario.';
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
    <meta name="description" content="Registro de nuevos usuarios en el sistema de registro de pedidos de Panadería Alas">
    <link rel="stylesheet" href="../../public/css/css.css">
    <title>Registrar | Panadería Alas</title>
</head>

<body class="pagina-registro">
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
        <section class="tarjeta-registro" aria-labelledby="titulo-registro">
            <div class="marca marca--tarjeta" aria-label="Panadería Alas">
                <span class="marca-texto">
                    <span class="marca-nombre">Panadería Alas</span>
                    <span class="marca-descripcion">Sistema de registro de pedidos</span>
                </span>
            </div>

            <div class="encabezado-formulario">
                <h1 id="titulo-registro">Registrar Usuario</h1>
            </div>

            <?php if ($error !== ''): ?>
                <div class="mensaje-error" role="alert">
                    <img class="icono" src="../../public/images/icono-error.svg" alt="">
                    <span><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></span>
                </div>
            <?php endif; ?>

            <form class="formulario-inicio-sesion" action="register.php" method="post">
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
                            required>
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
                            required>
                        <button class="boton-mostrar-contrasena" type="button" aria-label="Mostrar contraseña" aria-pressed="false">
                            <img class="icono icono--mostrar" src="../../public/images/icono-contrasena-oculta.svg" alt="">
                        </button>
                    </div>
                </div>

                <div class="campo-formulario">
                    <label for="password">Confirmar contraseña</label>
                    <div class="control-formulario">
                        <img class="icono icono--campo" src="../../public/images/icono-candado.svg" alt="">
                        <input
                            type="password"
                            id="password-confirm"
                            name="password_confirm"
                            placeholder="••••••••"
                            autocomplete="current-password"
                            required>
                        <button class="boton-mostrar-contrasena-confirm" type="button" aria-label="Mostrar contraseña" aria-pressed="false">
                            <img class="icono icono--mostrar" src="../../public/images/icono-contrasena-oculta.svg" alt="">
                        </button>
                    </div>
                </div>

                <button class="boton-principal" type="submit">Registrar</button>
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
        const botonMostrarContrasenaConfirm = document.querySelector('.boton-mostrar-contrasena-confirm');
        const campoContrasenaConfirm = document.querySelector('#password-confirm');

        botonMostrarContrasena.addEventListener('click', () => {
            const mostrarContrasena = campoContrasena.type === 'password';
            campoContrasena.type = mostrarContrasena ? 'text' : 'password';
            botonMostrarContrasena.setAttribute('aria-pressed', String(mostrarContrasena));
            botonMostrarContrasena.setAttribute('aria-label', mostrarContrasena ? 'Ocultar contraseña' : 'Mostrar contraseña');
        });
        botonMostrarContrasenaConfirm.addEventListener('click', () => {
            const mostrarContrasenaConfirm = campoContrasenaConfirm.type === 'password';
            campoContrasenaConfirm.type = mostrarContrasenaConfirm ? 'text' : 'password';
            botonMostrarContrasenaConfirm.setAttribute('aria-pressed', String(mostrarContrasenaConfirm));
            botonMostrarContrasenaConfirm.setAttribute('aria-label', mostrarContrasenaConfirm ? 'Ocultar contraseña' : 'Mostrar contraseña');
        });
    </script>