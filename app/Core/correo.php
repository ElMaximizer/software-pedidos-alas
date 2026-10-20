<?php
use PHPMailer\PHPMailer\PHPMailer;

// SMTP real para verificar el correo antes de crear una solicitud de usuario.
function enviarCorreoSmtp(string $destinatario, string $asunto, string $cuerpo): void
{
    $archivo = __DIR__ . '/smtp_config.php';
    if (!is_file($archivo)) {
        throw new RuntimeException('El envío de correo todavía no está configurado. Contactá al administrador.');
    }
    $config = require $archivo;
    if (!is_array($config)) {
        throw new RuntimeException('La configuración de correo no es válida. Contactá al administrador.');
    }
    foreach (['host', 'usuario', 'password', 'remitente'] as $campo) {
        if (!is_string($config[$campo] ?? null) || trim($config[$campo]) === '') {
            throw new RuntimeException('La configuración de correo está incompleta. Contactá al administrador.');
        }
    }

    require_once __DIR__ . '/../Services/PHPMailer/src/Exception.php';
    require_once __DIR__ . '/../Services/PHPMailer/src/PHPMailer.php';
    require_once __DIR__ . '/../Services/PHPMailer/src/SMTP.php';

    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = $config['host'];
        $mail->Port = (int) ($config['port'] ?? 587);
        $mail->SMTPAuth = true;
        $mail->Username = $config['usuario'];
        $mail->Password = $config['password'];
        $mail->SMTPSecure = ($config['seguridad'] ?? 'tls') === 'ssl'
            ? PHPMailer::ENCRYPTION_SMTPS : PHPMailer::ENCRYPTION_STARTTLS;
        $mail->SMTPDebug = 0;
        $mail->Timeout = 20;
        $mail->Timelimit = 20;
        $mail->CharSet = 'UTF-8';
        $mail->setFrom($config['remitente'], $config['nombre'] ?? 'Panadería Alas');
        $mail->addAddress($destinatario);
        $mail->isHTML(false);
        $mail->Subject = $asunto;
        $mail->Body = $cuerpo;
        $mail->send();
    } catch (Throwable $error) {
        // No registrar la conversación SMTP, la contraseña ni el código enviado.
        error_log('No se pudo completar el envío SMTP de Panadería Alas.');
        throw new RuntimeException('No pudimos enviar el correo. Esperá un minuto y volvé a intentarlo.');
    }
}

// Bandeja local conservada para las notificaciones anteriores del proyecto.
function queue_mail(string $correo, string $asunto, string $cuerpo): void
{
    // Bandeja local persistente: no simula un envío por SMTP.
    sql('INSERT INTO correos_locales (destinatario, asunto, cuerpo) VALUES (?, ?, ?)', 'sss', [$correo, $asunto, $cuerpo]);
}
