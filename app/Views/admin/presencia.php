<?php require __DIR__ . '/../partials/admin-header.php'; ?>
<main id="contenido" class="container section">
    <a class="back-link" href="principal.php">← Volver al panel</a>
    <div class="page-heading">
        <span class="eyebrow">SOLO DUEÑO</span>
        <h1>Actividad del personal</h1>
        <p>Consultá la última conexión y si hay una sesión activa de empleados o administradores.</p>
    </div>
    <p class="notice">El estado “Conectado” indica actividad en los últimos 2 minutos. Puede tardar ese tiempo en cambiar si alguien cierra el navegador sin salir de su cuenta. La última conexión se empieza a registrar desde que se instala esta función.</p>
    <div class="stack">
        <?php foreach ($personal as $persona): ?>
        <article class="card access-row">
            <div>
                <h2><?= e($persona['nombre']) ?></h2>
                <p><?= e(ucfirst($persona['rol'])) ?> · CI <?= e($persona['cedula']) ?> · <?= e(ucfirst($persona['estado'])) ?></p>
                <p>Último inicio de sesión:
                    <strong><?= $persona['ultimo_acceso'] ? e(date('d/m/Y H:i', strtotime($persona['ultimo_acceso']))) : 'Sin datos anteriores; se registrará en el próximo inicio de sesión' ?></strong>
                </p>
            </div>
            <?php if ((int) $persona['sesiones_activas'] > 0 && $persona['estado'] === 'aprobado'): ?>
            <span class="badge success">Conectado</span>
            <?php else: ?>
            <span class="badge neutral">Desconectado</span>
            <?php endif; ?>
        </article>
        <?php endforeach; ?>
        <?php if (!$personal): ?><section class="card empty"><h2>No hay personal registrado</h2><p>Cuando crees empleados o administradores, su actividad aparecerá aquí.</p></section><?php endif; ?>
    </div>
</main>
<?php require __DIR__ . '/../partials/admin-footer.php'; ?>

