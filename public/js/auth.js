document.querySelectorAll('[data-toggle-password]').forEach((boton) => {
    boton.addEventListener('click', () => {
        const campo = document.getElementById(boton.dataset.togglePassword);
        if (!campo) return;

        const mostrar = campo.type === 'password';
        campo.type = mostrar ? 'text' : 'password';
        boton.setAttribute('aria-label', mostrar ? 'Ocultar contraseña' : 'Mostrar contraseña');
    });
});

