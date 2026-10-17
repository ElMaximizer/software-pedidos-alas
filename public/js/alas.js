// Cada función se ocupa de una interacción. Si la pantalla no tiene ese
// elemento, no se registra ningún evento. Los selectores data-* son el contrato
// entre este archivo y las plantillas PHP.
iniciarBotonesContrasena();
iniciarVistaPreviaProducto();
iniciarFormularioPedido();

function iniciarBotonesContrasena() {
    document.querySelectorAll('[data-toggle-password]').forEach((boton) => {
        boton.addEventListener('click', () => {
            const campo = document.getElementById(boton.dataset.togglePassword);
            if (!campo) return;

            const mostrar = campo.type === 'password';
            campo.type = mostrar ? 'text' : 'password';
            boton.textContent = mostrar ? 'Ocultar' : 'Mostrar';
            boton.setAttribute('aria-label', mostrar ? 'Ocultar contraseña' : 'Mostrar contraseña');
            boton.setAttribute('aria-pressed', String(mostrar));
        });
    });
}

function iniciarVistaPreviaProducto() {
    const archivo = document.querySelector('[data-image-input]');
    const vistaPrevia = document.querySelector('[data-image-preview]');
    const categoria = document.querySelector('[data-product-category]');
    if (!archivo || !vistaPrevia) return;

    const imagenInicial = vistaPrevia.src;
    let urlTemporal;
    archivo.addEventListener('change', () => {
        archivo.setCustomValidity('');
        if (urlTemporal) {
            URL.revokeObjectURL(urlTemporal);
            urlTemporal = undefined;
        }
        vistaPrevia.src = imagenInicial;
        actualizarIlustracionCategoria();

        const imagen = archivo.files?.[0];
        if (!imagen) return;

        const formatoValido = ['image/jpeg', 'image/png'].includes(imagen.type);
        const tamanoValido = imagen.size <= 4 * 1024 * 1024;
        if (!formatoValido || !tamanoValido) {
            archivo.setCustomValidity('Elegí una imagen JPG o PNG de hasta 4 MB.');
            archivo.reportValidity();
            return;
        }

        urlTemporal = URL.createObjectURL(imagen);
        vistaPrevia.src = urlTemporal;
    });

    function actualizarIlustracionCategoria() {
        if (!categoria) return;
        if (!/\/(pan|croissant|torta)\.svg$/.test(vistaPrevia.src)) return;

        let ilustracion = 'pan.svg';
        if (categoria.value === 'Bizcochos') ilustracion = 'croissant.svg';
        if (['Tortas', 'Especiales'].includes(categoria.value)) ilustracion = 'torta.svg';
        vistaPrevia.src = vistaPrevia.src.replace(/[^/]+$/, ilustracion);
    }

    categoria?.addEventListener('change', () => {
        if (archivo.files?.length) return;
        actualizarIlustracionCategoria();
    });
}

function formatearImporte(importe) {
    return '$ ' + importe.toLocaleString('es-UY', { maximumFractionDigits: 2 });
}

function actualizarResumenPedido(formulario) {
    const resumen = formulario.querySelector('[data-order-summary]');
    const lineas = formulario.querySelectorAll('.order-line');
    resumen.replaceChildren();
    let totalCentavos = 0;

    lineas.forEach((linea) => {
        const producto = linea.querySelector('select').selectedOptions[0];
        const cantidad = Number(linea.querySelector('input').value) || 0;
        const precio = Number(producto?.dataset.price) || 0;
        totalCentavos += Math.round(precio * 100) * cantidad;

        if (producto?.value) {
            const detalle = document.createElement('p');
            detalle.textContent = `${producto.dataset.name} × ${cantidad} · ${formatearImporte(precio)} por unidad`;
            detalle.className = 'small';
            detalle.style.marginBottom = '16px';
            resumen.append(detalle);
        }
    });

    // Esta suma es una vista previa. PHP vuelve a calcular el total al guardar.
    formulario.querySelector('[data-order-total]').textContent = formatearImporte(totalCentavos / 100);
    const fecha = formulario.querySelector('[name=fecha_retiro]').value;
    const hora = formulario.querySelector('[name=hora_retiro]').value;
    formulario.querySelector('[data-order-date]').textContent = `Retiro · ${fecha || 'A coordinar'}, ${hora}`;
}

function agregarLineaPedido(contenedor) {
    if (contenedor.children.length >= 50) return;
    const linea = contenedor.firstElementChild.cloneNode(true);
    linea.querySelector('select').selectedIndex = 0;
    linea.querySelector('input').value = '1';
    contenedor.append(linea);
    linea.querySelector('select').focus();
}

function iniciarFormularioPedido() {
    const formulario = document.querySelector('[data-order-form]');
    if (!formulario) return;

    const lineas = formulario.querySelector('[data-order-lines]');
    const actualizar = () => actualizarResumenPedido(formulario);
    formulario.querySelector('[data-add-line]')?.addEventListener('click', () => {
        agregarLineaPedido(lineas);
        actualizar();
    });
    lineas.addEventListener('click', (evento) => {
        if (evento.target.closest('[data-remove-line]') && lineas.children.length > 1) {
            evento.target.closest('.order-line').remove();
            actualizar();
        }
    });
    formulario.addEventListener('input', actualizar);
    formulario.addEventListener('change', actualizar);
    actualizar();
}
