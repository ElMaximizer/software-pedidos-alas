"""Genera el manual a partir de guia-codigo.md. Requiere Python y reportlab.

Ejecutar desde cualquier carpeta: python docs/generar_manual.py
"""
from pathlib import Path
from html import escape
import re

from reportlab.lib import colors
from reportlab.lib.pagesizes import A4
from reportlab.lib.styles import ParagraphStyle, getSampleStyleSheet
from reportlab.pdfbase import pdfmetrics
from reportlab.pdfbase.ttfonts import TTFont
from reportlab.platypus import (
    SimpleDocTemplate, Paragraph, Spacer, PageBreak, Preformatted,
    Table, TableStyle, Flowable,
)

RAIZ = Path(__file__).resolve().parent.parent
SALIDA = RAIZ / 'output/pdf/guia-del-codigo-panaderia-alas.pdf'
AZUL = colors.HexColor('#162D3B')
ORO = colors.HexColor('#AD7D31')
GRIS = colors.HexColor('#52616B')
FONDO = colors.HexColor('#F1F4F5')
BORDE = colors.HexColor('#DCE3E6')


def configurar_fuentes():
    fuentes = Path('C:/Windows/Fonts')
    if (fuentes / 'arial.ttf').exists():
        pdfmetrics.registerFont(TTFont('Alas', str(fuentes / 'arial.ttf')))
        pdfmetrics.registerFont(TTFont('Alas-Bold', str(fuentes / 'arialbd.ttf')))
        pdfmetrics.registerFontFamily('Alas', normal='Alas', bold='Alas-Bold')
        return 'Alas', 'Alas-Bold'
    return 'Helvetica', 'Helvetica-Bold'


NORMAL, NEGRITA = configurar_fuentes()
MONO = 'Courier'
fuente_codigo = Path('C:/Windows/Fonts/consola.ttf')
if fuente_codigo.exists():
    pdfmetrics.registerFont(TTFont('AlasMono', str(fuente_codigo)))
    MONO = 'AlasMono'
estilos = getSampleStyleSheet()
estilos.add(ParagraphStyle('TextoAlas', fontName=NORMAL, fontSize=9.7,
    leading=14, textColor=AZUL, spaceAfter=8))
estilos.add(ParagraphStyle('TituloAlas', fontName=NEGRITA, fontSize=29,
    leading=34, textColor=AZUL, spaceAfter=16))
estilos.add(ParagraphStyle('SeccionAlas', fontName=NEGRITA, fontSize=20,
    leading=25, textColor=AZUL, spaceAfter=14))
estilos.add(ParagraphStyle('SubtituloAlas', fontName=NEGRITA, fontSize=12,
    leading=16, textColor=AZUL, spaceBefore=8, spaceAfter=7, keepWithNext=True))
estilos.add(ParagraphStyle('ListaAlas', parent=estilos['TextoAlas'],
    leftIndent=15, firstLineIndent=0, bulletIndent=0, spaceAfter=5))
estilos.add(ParagraphStyle('CeldaAlas', fontName=NORMAL, fontSize=8.5,
    leading=11.5, textColor=AZUL))
estilos.add(ParagraphStyle('CabeceraAlas', parent=estilos['CeldaAlas'],
    fontName=NEGRITA, textColor=colors.white))
estilos.add(ParagraphStyle('CodigoAlas', fontName=MONO, fontSize=9,
    leading=12.5, textColor=AZUL, backColor=FONDO, borderPadding=9,
    spaceBefore=5, spaceAfter=13))
estilos.add(ParagraphStyle('PasoTitulo', fontName=NEGRITA, fontSize=9.5,
    leading=12, textColor=AZUL))
estilos.add(ParagraphStyle('PasoTexto', fontName=NORMAL, fontSize=8.8,
    leading=12, textColor=GRIS))


def texto_enriquecido(texto):
    texto = escape(texto)
    texto = re.sub(r'`([^`]+)`', r'<font name="Courier">\1</font>', texto)
    texto = re.sub(r'\*\*([^*]+)\*\*', r'<b>\1</b>', texto)
    return texto


class Flujo(Flowable):
    def __init__(self, pasos):
        super().__init__()
        self.pasos = pasos

    def wrap(self, ancho, alto):
        self.width = ancho
        self.bloques = []
        for titulo, detalle in self.pasos:
            p_titulo = Paragraph(escape(titulo), estilos['PasoTitulo'])
            p_texto = Paragraph(escape(detalle), estilos['PasoTexto'])
            h_titulo = p_titulo.wrap(ancho - 66, 100)[1]
            h_texto = p_texto.wrap(ancho - 66, 100)[1]
            self.bloques.append((p_titulo, p_texto, h_titulo, h_texto, h_titulo + h_texto + 17))
        self.height = sum(b[4] for b in self.bloques) + 13 * (len(self.bloques) - 1) + 12
        return self.width, self.height

    def draw(self):
        lienzo = self.canv
        y = self.height - 3
        for numero, (titulo, texto, ht, hd, alto) in enumerate(self.bloques, 1):
            y -= alto
            lienzo.setFillColor(FONDO)
            lienzo.setStrokeColor(BORDE)
            lienzo.roundRect(0, y, self.width, alto, 6, fill=1, stroke=1)
            lienzo.setFillColor(ORO)
            lienzo.circle(22, y + alto / 2, 11, fill=1, stroke=0)
            lienzo.setFillColor(colors.white)
            lienzo.setFont(NEGRITA, 9)
            lienzo.drawCentredString(22, y + alto / 2 - 3, str(numero))
            titulo.drawOn(lienzo, 45, y + alto - 8 - ht)
            texto.drawOn(lienzo, 45, y + 8)
            if numero < len(self.bloques):
                lienzo.setStrokeColor(ORO)
                lienzo.line(22, y - 1, 22, y - 10)
                lienzo.line(19, y - 7, 22, y - 10)
                lienzo.line(25, y - 7, 22, y - 10)
            y -= 13


class Relaciones(Flowable):
    def wrap(self, ancho, alto):
        self.width, self.height = ancho, 220
        return self.width, self.height

    def draw(self):
        c = self.canv
        ancho = self.width
        def caja(x, y, w, titulo, detalle):
            c.setFillColor(FONDO)
            c.setStrokeColor(BORDE)
            c.roundRect(x, y, w, 43, 6, fill=1, stroke=1)
            c.setFillColor(AZUL)
            c.setFont(NEGRITA, 9.5)
            c.drawCentredString(x + w / 2, y + 25, titulo)
            c.setFont(NORMAL, 8)
            c.setFillColor(GRIS)
            c.drawCentredString(x + w / 2, y + 11, detalle)
        caja(0, 164, 165, 'productos', 'Precio y disponibilidad actuales')
        caja(ancho - 165, 164, 165, 'pedidos', 'Cliente, retiro, estado y total')
        caja(ancho / 2 - 110, 80, 220, 'pedido_detalles', 'Nombre y precio históricos de cada línea')
        c.setStrokeColor(ORO)
        c.line(82, 164, ancho / 2 - 58, 123)
        c.line(ancho - 82, 164, ancho / 2 + 58, 123)
        c.setFont(NORMAL, 8)
        c.setFillColor(GRIS)
        c.drawString(30, 140, 'Producto opcional')
        c.drawRightString(ancho - 15, 140, 'Un pedido, varias líneas')
        caja(0, 7, 190, 'solicitud_usuario', 'Cuenta, aprobación y rol')
        caja(ancho - 190, 7, 190, 'recuperaciones', 'Token, vencimiento y uso')
        c.setDash(3, 3)
        c.line(190, 28, ancho - 190, 28)
        c.setDash()
        c.setFont(NORMAL, 7.8)
        c.drawCentredString(ancho / 2, 44, 'id_usuario')


class Manual(SimpleDocTemplate):
    def afterFlowable(self, elemento):
        if isinstance(elemento, Paragraph) and elemento.style.name in ['TituloAlas', 'SeccionAlas']:
            clave = 'seccion-' + str(self.page)
            self.canv.bookmarkPage(clave)
            self.canv.addOutlineEntry(elemento.getPlainText(), clave, 0, False)


def pie_pagina(c, doc):
    c.saveState()
    c.setFillColor(ORO)
    c.rect(46, A4[1] - 34, 23, 3, fill=1, stroke=0)
    c.setFont(NEGRITA, 7.5)
    c.setFillColor(GRIS)
    c.drawString(77, A4[1] - 34, 'PANADERÍA ALAS / GUÍA DEL CÓDIGO')
    c.setStrokeColor(BORDE)
    c.line(46, 39, A4[0] - 46, 39)
    c.setFont(NORMAL, 7.5)
    c.drawString(46, 26, 'Equipo de desarrollo | 16 de octubre de 2026')
    c.drawRightString(A4[0] - 46, 26, str(doc.page))
    c.restoreState()


def crear_tabla(lineas, ancho):
    filas = [[c.strip() for c in linea.strip().strip('|').split('|')] for linea in lineas]
    filas = [fila for fila in filas if not all(re.fullmatch(r'[:\- ]+', c) for c in fila)]
    columnas = len(filas[0])
    proporcion = {2: [0.43, 0.57], 3: [0.10, 0.34, 0.56], 4: [0.43, 0.17, 0.20, 0.20]}[columnas]
    celdas = [[Paragraph(texto_enriquecido(c), estilos['CabeceraAlas' if i == 0 else 'CeldaAlas'])
               for c in fila] for i, fila in enumerate(filas)]
    tabla = Table(celdas, colWidths=[ancho * p for p in proporcion], repeatRows=1, hAlign='LEFT')
    tabla.setStyle(TableStyle([
        ('BACKGROUND', (0, 0), (-1, 0), AZUL),
        ('ROWBACKGROUNDS', (0, 1), (-1, -1), [colors.white, FONDO]),
        ('VALIGN', (0, 0), (-1, -1), 'TOP'),
        ('LEFTPADDING', (0, 0), (-1, -1), 8),
        ('RIGHTPADDING', (0, 0), (-1, -1), 8),
        ('TOPPADDING', (0, 0), (-1, -1), 6),
        ('BOTTOMPADDING', (0, 0), (-1, -1), 6),
        ('LINEBELOW', (0, -1), (-1, -1), 0.5, BORDE),
    ]))
    return [tabla, Spacer(1, 10)]


def convertir_pagina(texto, ancho):
    lineas = texto.strip().splitlines()
    elementos = []
    i = 0
    while i < len(lineas):
        linea = lineas[i].strip()
        if not linea:
            i += 1
            continue
        if linea.startswith('```'):
            codigo = []
            i += 1
            while i < len(lineas) and not lineas[i].startswith('```'):
                codigo.append(lineas[i])
                i += 1
            elementos.append(Preformatted('\n'.join(codigo), estilos['CodigoAlas']))
        elif linea == ':::flujo':
            pasos = []
            i += 1
            while lineas[i].strip() != ':::':
                pasos.append(tuple(p.strip() for p in lineas[i].split('|', 1)))
                i += 1
            if pasos[-2][0] == 'Resultado correcto':
                elementos.append(Flujo(pasos[:-2]))
                elementos.append(Paragraph('El servicio da uno de estos dos resultados:', estilos['TextoAlas']))
                celdas = [[Paragraph(escape(t), estilos['PasoTitulo']) for t, _ in pasos[-2:]],
                          [Paragraph(escape(d), estilos['PasoTexto']) for _, d in pasos[-2:]]]
                tabla = Table(celdas, colWidths=[ancho / 2, ancho / 2])
                tabla.setStyle(TableStyle([
                    ('BACKGROUND', (0, 0), (-1, -1), FONDO),
                    ('VALIGN', (0, 0), (-1, -1), 'TOP'),
                    ('BOX', (0, 0), (-1, -1), 0.5, BORDE),
                    ('INNERGRID', (0, 0), (-1, -1), 0.5, BORDE),
                    ('LEFTPADDING', (0, 0), (-1, -1), 10),
                    ('RIGHTPADDING', (0, 0), (-1, -1), 10),
                    ('TOPPADDING', (0, 0), (-1, -1), 7),
                    ('BOTTOMPADDING', (0, 0), (-1, -1), 7),
                ]))
                elementos.extend([tabla, Spacer(1, 10)])
            else:
                elementos.append(Flujo(pasos))
        elif linea == ':::relaciones':
            elementos.append(Relaciones())
            i += 1
        elif linea.startswith('|'):
            tabla = []
            while i < len(lineas) and lineas[i].strip().startswith('|'):
                tabla.append(lineas[i])
                i += 1
            elementos.extend(crear_tabla(tabla, ancho))
            continue
        elif linea.startswith('#'):
            nivel = len(linea) - len(linea.lstrip('#'))
            estilo = {1: 'TituloAlas', 2: 'SeccionAlas', 3: 'SubtituloAlas'}[nivel]
            elementos.append(Paragraph(texto_enriquecido(linea.lstrip('#').strip()), estilos[estilo]))
        elif linea.startswith('- ') or re.match(r'^\d+\. ', linea):
            etiqueta, contenido = ('-', linea[2:]) if linea.startswith('- ') else linea.split(' ', 1)
            elementos.append(Paragraph(texto_enriquecido(contenido), estilos['ListaAlas'], bulletText=etiqueta))
        else:
            parrafo = [linea]
            while i + 1 < len(lineas) and lineas[i + 1].strip():
                siguiente = lineas[i + 1].strip()
                if siguiente.startswith(('#', '|', '```', ':::', '- ')) or re.match(r'^\d+\. ', siguiente):
                    break
                i += 1
                parrafo.append(siguiente)
            elementos.append(Paragraph(texto_enriquecido(' '.join(parrafo)), estilos['TextoAlas']))
        i += 1
    return elementos


def main():
    SALIDA.parent.mkdir(parents=True, exist_ok=True)
    documento = Manual(str(SALIDA), pagesize=A4, leftMargin=46, rightMargin=46,
        topMargin=53, bottomMargin=52,
        title='Panadería Alas - Guía del código y del flujo del programa',
        author='Equipo de desarrollo - Panadería Alas',
        subject='Estructura PHP, reglas del negocio, flujos y pruebas de integración')
    paginas = (RAIZ / 'docs/guia-codigo.md').read_text(encoding='utf-8').split('<!-- pagina -->')
    elementos = []
    for indice, pagina in enumerate(paginas):
        if indice:
            elementos.append(PageBreak())
        elementos.extend(convertir_pagina(pagina, A4[0] - 92))
    documento.build(elementos, onFirstPage=pie_pagina, onLaterPages=pie_pagina)
    print(SALIDA)


if __name__ == '__main__':
    main()
