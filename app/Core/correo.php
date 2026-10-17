<?php
// Bandeja persistente de desarrollo. No envía correos por Internet.
function queue_mail(string $correo, string $asunto, string $cuerpo): void
{
    // Bandeja local persistente: no simula un envío por SMTP.
    sql('INSERT INTO correos_locales (destinatario, asunto, cuerpo) VALUES (?, ?, ?)', 'sss', [$correo, $asunto, $cuerpo]);
}
