<?php
// Validaciones comunes y mensajes de error que se pueden mostrar al usuario.
function validarCorreo(string $correo): void
{
    if (!filter_var($correo, FILTER_VALIDATE_EMAIL) || strlen($correo) > 190) {
        throw new RuntimeException('Ingresá un correo electrónico válido.');
    }
}

function error_text(Throwable $error): string
{
    if ($error instanceof RuntimeException && !($error instanceof mysqli_sql_exception)) {
        return $error->getMessage();
    }
    error_log((string) $error);
    return 'No pudimos guardar los cambios. Revisá los datos e intentá nuevamente.';
}

function validate_text(string $valor, string $etiqueta, int $maximo, bool $obligatorio = true): string
{
    if (($obligatorio && $valor === '') || mb_strlen($valor) > $maximo) {
        throw new RuntimeException("Revisá $etiqueta (máximo $maximo caracteres).");
    }
    return $valor;
}
