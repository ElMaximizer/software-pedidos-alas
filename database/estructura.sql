-- Base provisional compatible con el acceso y los formularios actuales.
-- Las tablas existentes y sus registros se conservan.
CREATE DATABASE IF NOT EXISTS alas_bd
    CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE alas_bd;
SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS solicitud_usuario (
    id_sol INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    cedula INT UNSIGNED NOT NULL,
    password VARCHAR(255) NOT NULL,
    nombre VARCHAR(120) NOT NULL DEFAULT '',
    email VARCHAR(190) NULL,
    estado VARCHAR(20) NOT NULL DEFAULT 'aprobado',
    rol VARCHAR(20) NOT NULL DEFAULT 'empleado',
    creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_solicitud_usuario_cedula (cedula)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS productos (
    id_producto INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(120) NOT NULL,
    descripcion VARCHAR(280) NOT NULL,
    precio DECIMAL(10,2) UNSIGNED NOT NULL,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    categoria VARCHAR(30) NOT NULL DEFAULT 'Panes',
    presentacion VARCHAR(80) NOT NULL DEFAULT 'Unidad',
    ingredientes TEXT NULL,
    alergenos VARCHAR(500) NOT NULL DEFAULT '',
    imagen VARCHAR(255) NOT NULL DEFAULT '',
    destacado TINYINT(1) NOT NULL DEFAULT 0,
    eliminado_en DATETIME NULL,
    creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    actualizado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS pedidos (
    id_pedido INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    -- Resumen conservado para compatibilidad con los pedidos anteriores.
    producto VARCHAR(120) NOT NULL,
    cantidad INT UNSIGNED NOT NULL,
    cliente_nombre VARCHAR(120) NOT NULL DEFAULT 'Cliente de mostrador',
    telefono VARCHAR(30) NOT NULL DEFAULT '',
    fecha_retiro DATE NULL,
    hora_retiro TIME NOT NULL DEFAULT '10:00:00',
    notas TEXT NULL,
    estado VARCHAR(30) NOT NULL DEFAULT 'Pendiente',
    total DECIMAL(10,2) UNSIGNED NOT NULL DEFAULT 0,
    motivo_cancelacion VARCHAR(255) NOT NULL DEFAULT '',
    version INT UNSIGNED NOT NULL DEFAULT 1,
    creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    actualizado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS pedido_detalles (
    id_detalle INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_pedido INT UNSIGNED NOT NULL,
    id_producto INT UNSIGNED NULL,
    nombre_producto VARCHAR(120) NOT NULL,
    cantidad INT UNSIGNED NOT NULL,
    precio_unitario DECIMAL(10,2) UNSIGNED NOT NULL,
    CONSTRAINT fk_detalle_pedido FOREIGN KEY (id_pedido) REFERENCES pedidos (id_pedido) ON DELETE CASCADE,
    CONSTRAINT fk_detalle_producto FOREIGN KEY (id_producto) REFERENCES productos (id_producto) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS mensajes_contacto (
    id_mensaje INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(120) NOT NULL,
    email VARCHAR(190) NOT NULL,
    motivo VARCHAR(80) NOT NULL,
    mensaje TEXT NOT NULL,
    leido TINYINT(1) NOT NULL DEFAULT 0,
    creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS recuperaciones (
    id_recuperacion INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    token_hash CHAR(64) NOT NULL UNIQUE,
    vence_en DATETIME NOT NULL,
    usado TINYINT(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS correos_locales (
    id_correo INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    destinatario VARCHAR(190) NOT NULL,
    asunto VARCHAR(160) NOT NULL,
    cuerpo TEXT NOT NULL,
    creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- El bloqueo pertenece a la cédula y se conserva al cambiar de sesión.
CREATE TABLE IF NOT EXISTS intentos_acceso (
    cedula INT UNSIGNED PRIMARY KEY,
    intentos TINYINT UNSIGNED NOT NULL DEFAULT 0,
    bloqueado_hasta DATETIME NULL,
    actualizado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

