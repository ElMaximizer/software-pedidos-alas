<?php require __DIR__ . '/../partials/admin-header.php'; ?>
<main id="contenido" class="container section">
    <a class="back-link" href="principal.php">← Volver al panel</a>
    <div class="page-heading">
        <span class="eyebrow">SOLO DUEÑO</span>
        <h1>Gestión del personal</h1>
        <p>Creá cuentas de empleado o administrador y quitá accesos. Los registros anteriores se conservan.</p>
    </div>
    <?php show_flash(); ?>
    <?php if ($error): ?><div class="notice danger" role="alert"><?= e($error) ?></div><?php endif; ?>

    <section class="card">
        <h2>Agregar empleado o administrador</h2>
        <form method="post" class="form-grid">
            <?php csrf_field(); ?>
            <label>Nombre completo<input name="nombre" maxlength="120" autocomplete="name" required></label>
            <label>Cédula<input name="cedula" inputmode="numeric" pattern="[0-9]{6,8}" minlength="6" maxlength="8" required><small>Ingresala sin puntos ni guiones.</small></label>
            <label>Correo (opcional)<input type="email" name="email" maxlength="190" autocomplete="email"></label>
            <label>Rol<select name="rol" required><option value="empleado">Empleado</option><option value="administrador">Administrador</option></select></label>
            <label>Contraseña inicial<input type="password" name="password" minlength="8" maxlength="72" autocomplete="new-password" required></label>
            <label>Confirmar contraseña<input type="password" name="confirm_password" minlength="8" maxlength="72" autocomplete="new-password" required></label>
            <div class="actions"><button class="button gold" name="accion" value="crear">Crear empleado</button></div>
        </form>
    </section>

        <h2 class="list-title">Personal registrado</h2>
    <div class="stack">
        <?php foreach ($empleados as $empleado): ?>
        <article class="card access-row">
            <div>
                <h3><?= e($empleado['nombre']) ?></h3>
                <p><?= e(ucfirst($empleado['rol'])) ?> · Cédula <?= e($empleado['cedula']) ?><?= $empleado['email'] ? ' · ' . e($empleado['email']) : '' ?></p>
                <span class="badge <?= badge($empleado['estado']) ?>"><?= e($empleado['estado']) ?></span>
            </div>
            <?php if ($empleado['estado'] === 'aprobado'): ?>
            <form method="post" onsubmit="return confirm('¿Quitar el acceso de esta cuenta? Sus registros se conservarán.');">
                <?php csrf_field(); ?>
                <input type="hidden" name="id_empleado" value="<?= (int) $empleado['id_sol'] ?>">
                <button class="button red-button" name="accion" value="desactivar">Quitar acceso</button>
            </form>
            <?php endif; ?>
        </article>
        <?php endforeach; ?>
        <?php if (!$empleados): ?><section class="card empty"><h2>Aún no hay personal</h2><p>Agregá la primera cuenta desde el formulario.</p></section><?php endif; ?>
    </div>
</main>
<?php require __DIR__ . '/../partials/admin-footer.php'; ?>

