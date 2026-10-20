<?php
// Bandeja persistente de desarrollo. No envía correos por Internet.
function queue_mail(string $correo, string $asunto, string $cuerpo): void
{
    // Bandeja local persistente: no simula un envío por SMTP.
    sql('INSERT INTO correos_locales (destinatario, asunto, cuerpo) VALUES (?, ?, ?)', 'sss', [$correo, $asunto, $cuerpo]);
}

function enviarCorreoGmail(string $destinatario, string $asunto, string $cuerpo): void
{
    $configPath = __DIR__ . '/gmail-config.local.php';
    $config = is_file($configPath) ? require $configPath : [];
    $usuario = trim((string) ($config['from_email'] ?? (getenv('ALAS_GMAIL_USER') ?: '')));
    $clave = preg_replace('/\s+/', '', (string) ($config['app_password'] ?? (getenv('ALAS_GMAIL_APP_PASSWORD') ?: '')));
    $nombre = trim((string) ($config['from_name'] ?? 'Panadería Alas'));
    if (!filter_var($usuario, FILTER_VALIDATE_EMAIL) || $clave === '') {
        throw new RuntimeException('Falta configurar el correo de Gmail para poder enviar el código.');
    }

    $contexto = stream_context_create(['ssl' => [
        'verify_peer' => true,
        'verify_peer_name' => true,
        'peer_name' => 'smtp.gmail.com',
    ]]);
    $errno = 0;
    $errstr = '';
    $socket = @stream_socket_client('ssl://smtp.gmail.com:465', $errno, $errstr, 20, STREAM_CLIENT_CONNECT, $contexto);
    if (!$socket) {
        throw new RuntimeException('No se pudo conectar con Gmail para enviar el código.');
    }
    stream_set_timeout($socket, 20);

    try {
        gmail_smtp_check($socket, [220]);
        gmail_smtp_command($socket, 'EHLO localhost', [250]);
        gmail_smtp_command($socket, 'AUTH LOGIN', [334]);
        gmail_smtp_command($socket, base64_encode($usuario), [334]);
        gmail_smtp_command($socket, base64_encode($clave), [235]);
        gmail_smtp_command($socket, 'MAIL FROM:<' . $usuario . '>', [250]);
        gmail_smtp_command($socket, 'RCPT TO:<' . $destinatario . '>', [250, 251]);
        gmail_smtp_command($socket, 'DATA', [354]);

        $asuntoCodificado = '=?UTF-8?B?' . base64_encode($asunto) . '?=';
        $nombreCodificado = '=?UTF-8?B?' . base64_encode($nombre) . '?=';
        $mensaje = "From: {$nombreCodificado} <{$usuario}>\r\n"
            . "To: <{$destinatario}>\r\n"
            . "Subject: {$asuntoCodificado}\r\n"
            . "MIME-Version: 1.0\r\n"
            . "Content-Type: text/plain; charset=UTF-8\r\n"
            . "Content-Transfer-Encoding: base64\r\n\r\n"
            . chunk_split(base64_encode($cuerpo), 76, "\r\n");
        $mensaje = preg_replace('/(^|\r\n)\./', '$1..', $mensaje);
        if (fwrite($socket, $mensaje . "\r\n.\r\n") === false) {
            throw new RuntimeException('No se pudo enviar el mensaje a Gmail.');
        }
        gmail_smtp_check($socket, [250]);
        gmail_smtp_command($socket, 'QUIT', [221]);
    } catch (Throwable $error) {
        fclose($socket);
        if ($error instanceof RuntimeException && str_starts_with($error->getMessage(), 'Falta configurar')) {
            throw $error;
        }
        throw new RuntimeException('Gmail no aceptó el envío del código. Revisá la configuración de la cuenta.');
    }
    fclose($socket);
}

function gmail_smtp_command($socket, string $comando, array $codigos): string
{
    if (fwrite($socket, $comando . "\r\n") === false) {
        throw new RuntimeException('No se pudo enviar el mensaje a Gmail.');
    }
    return gmail_smtp_check($socket, $codigos);
}

function gmail_smtp_check($socket, array $codigos): string
{
    $respuesta = '';
    do {
        $linea = fgets($socket, 515);
        if ($linea === false) {
            throw new RuntimeException('Gmail cerró la conexión durante el envío.');
        }
        $respuesta .= $linea;
        $continuar = isset($linea[3]) && $linea[3] === '-';
    } while ($continuar);

    $codigo = (int) substr($respuesta, 0, 3);
    if (!in_array($codigo, $codigos, true)) {
        throw new RuntimeException('Gmail respondió con un error.');
    }
    return $respuesta;
}
