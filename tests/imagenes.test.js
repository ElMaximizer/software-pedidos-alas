const { test } = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');

function abrirFormulario(imagenInicial = '/images/pan.svg') {
    const eventos = {};
    const revocadas = [];
    const archivo = {
        files: [], error: '',
        addEventListener: (nombre, accion) => { eventos[nombre] = accion; },
        setCustomValidity(mensaje) { this.error = mensaje; },
        reportValidity() {},
    };
    const imagen = { src: imagenInicial };
    const categoria = {
        value: 'Panes',
        addEventListener: (_, accion) => { eventos.categoria = accion; },
    };
    const elementos = {
        '[data-image-input]': archivo,
        '[data-image-preview]': imagen,
        '[data-product-category]': categoria,
    };
    const contexto = {
        document: { querySelectorAll: () => [], querySelector: (selector) => elementos[selector] ?? null },
        URL: { createObjectURL: () => 'blob:imagen-temporal', revokeObjectURL: (url) => revocadas.push(url) },
    };
    vm.runInNewContext(fs.readFileSync(path.join(__dirname, '../public/js/alas.js'), 'utf8'), contexto);
    return { archivo, imagen, categoria, eventos, revocadas };
}

test('retirar una imagen demasiado grande permite guardar sin imagen', () => {
    const formulario = abrirFormulario();
    formulario.archivo.files = [{ type: 'image/png', size: 5 * 1024 * 1024 }];
    formulario.eventos.change();
    assert.notEqual(formulario.archivo.error, '');
    formulario.archivo.files = [];
    formulario.eventos.change();
    assert.equal(formulario.archivo.error, '');
    assert.equal(formulario.imagen.src, '/images/pan.svg');
});

test('corregir un formato inválido muestra la imagen válida y libera su vista previa al retirarla', () => {
    const formulario = abrirFormulario();
    formulario.archivo.files = [{ type: 'text/plain', size: 100 }];
    formulario.eventos.change();
    assert.notEqual(formulario.archivo.error, '');
    formulario.archivo.files = [{ type: 'image/jpeg', size: 100 }];
    formulario.eventos.change();
    assert.equal(formulario.archivo.error, '');
    assert.equal(formulario.imagen.src, 'blob:imagen-temporal');
    formulario.archivo.files = [];
    formulario.eventos.change();
    assert.deepEqual(formulario.revocadas, ['blob:imagen-temporal']);
});

test('al retirar el archivo se respeta la categoría y la imagen anterior del producto', () => {
    const formulario = abrirFormulario();
    formulario.categoria.value = 'Tortas';
    formulario.eventos.categoria();
    assert.equal(formulario.imagen.src, '/images/torta.svg');
    formulario.archivo.files = [{ type: 'image/png', size: 100 }];
    formulario.eventos.change();
    formulario.archivo.files = [];
    formulario.eventos.change();
    assert.equal(formulario.imagen.src, '/images/torta.svg');

    const productoExistente = abrirFormulario('/uploads/productos/anterior.jpg');
    productoExistente.categoria.value = 'Tortas';
    productoExistente.eventos.categoria();
    assert.equal(productoExistente.imagen.src, '/uploads/productos/anterior.jpg');
});
