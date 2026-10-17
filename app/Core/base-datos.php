<?php
// Consultas preparadas: sql modifica datos; rows devuelve una lista; one devuelve una fila.
function sql(string $consulta, string $tipos = '', array $parametros = []): mysqli_stmt
{
    global $conexion;
    $sentencia = $conexion->prepare($consulta);
    if ($tipos !== '') {
        $sentencia->bind_param($tipos, ...$parametros);
    }
    $sentencia->execute();
    return $sentencia;
}

function rows(string $consulta, string $tipos = '', array $parametros = []): array
{
    return sql($consulta, $tipos, $parametros)->get_result()->fetch_all(MYSQLI_ASSOC);
}

function one(string $consulta, string $tipos = '', array $parametros = []): ?array
{
    return sql($consulta, $tipos, $parametros)->get_result()->fetch_assoc();
}
