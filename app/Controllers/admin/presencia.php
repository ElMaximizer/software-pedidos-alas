<?php
require_once __DIR__ . '/../../Core/auth.php';
$usuarioActual = require_owner();
$esAdministracion = true;
$seccionActiva = 'presencia';
$tituloPagina = 'Actividad del personal';
$personal = rows(
    "SELECT u.id_sol, u.nombre, u.cedula, u.rol, u.estado, u.ultimo_acceso,
            COUNT(s.id_sesion) AS sesiones_activas
     FROM solicitud_usuario u
     LEFT JOIN sesiones_equipo s
       ON s.id_usuario = u.id_sol
      AND s.ultima_actividad >= DATE_SUB(NOW(), INTERVAL 2 MINUTE)
     WHERE u.rol IN ('empleado', 'administrador')
     GROUP BY u.id_sol, u.nombre, u.cedula, u.rol, u.estado, u.ultimo_acceso
     ORDER BY u.rol, u.nombre, u.id_sol"
);

