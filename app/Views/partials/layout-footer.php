<?php if ($esAdministracion ?? false): ?>
<footer class="admin-footer container">
    <small>PANADERÍA ALAS · GESTIÓN INTERNA</small><a href="<?= e(base_url('public/index.php')) ?>">Ver sitio público →</a>
</footer>
<?php else: ?>
<footer class="site-footer">
    <div class="container">
        <div class="footer-grid">
            <div>
                <span class="eyebrow">PANADERÍA ARTESANAL</span>
                <h2>PANADERÍA ALAS</h2>
                <p>El sabor de siempre,<br>hecho para compartir.</p>
            </div>
            <nav aria-label="Explora Alas">
                <span class="eyebrow">EXPLORA ALAS</span>
                <div class="footer-links">
                    <a href="<?= e(base_url('public/index.php')) ?>">Inicio</a><a href="<?= e(base_url('public/productos.php')) ?>">Productos</a><a href="<?= e(base_url('public/nosotros.php')) ?>">Nosotros</a><a href="<?= e(base_url('public/contacto.php')) ?>">Contacto</a>
                </div>
            </nav>
            <div>
                <span class="eyebrow">CONVERSEMOS</span>
                <h3>¿Tienes un pedido en mente?</h3>
                <p class="small">Consulta por disponibilidad, productos y preparaciones especiales.</p>
                <a class="button gold" href="<?= e(base_url('public/contacto.php')) ?>">Hacer una consulta →</a>
            </div>
        </div>
        <div class="footer-bottom">
            <small>© <?= date('Y') ?> Panadería Alas</small><a href="<?= e(base_url('app/Views/login.php')) ?>">Acceso del equipo →</a>
        </div>
    </div>
</footer>
<?php endif; ?>
</body>
</html>
