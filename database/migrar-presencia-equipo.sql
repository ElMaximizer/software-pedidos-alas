-- Ejecutar en phpMyAdmin sobre la base alas_bd. Se puede volver a ejecutar.
USE alas_bd;

SET @existe_ultimo_acceso := (
    SELECT COUNT(*)
    FROM information_schema.columns
    WHERE table_schema = DATABASE()
      AND table_name = 'solicitud_usuario'
      AND column_name = 'ultimo_acceso'
);
SET @sql_ultimo_acceso := IF(
    @existe_ultimo_acceso = 0,
    'ALTER TABLE solicitud_usuario ADD COLUMN ultimo_acceso DATETIME NULL AFTER creado_en',
    'SELECT 1'
);
PREPARE agregar_ultimo_acceso FROM @sql_ultimo_acceso;
EXECUTE agregar_ultimo_acceso;
DEALLOCATE PREPARE agregar_ultimo_acceso;

CREATE TABLE IF NOT EXISTS sesiones_equipo (
    id_sesion BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT UNSIGNED NOT NULL,
    huella_sesion CHAR(64) NOT NULL,
    ultima_actividad DATETIME NOT NULL,
    UNIQUE KEY uq_sesiones_huella (huella_sesion),
    KEY idx_sesiones_usuario_actividad (id_usuario, ultima_actividad),
    CONSTRAINT fk_sesiones_equipo_usuario FOREIGN KEY (id_usuario)
        REFERENCES solicitud_usuario(id_sol) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

