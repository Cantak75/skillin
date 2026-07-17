-- =====================================================================
-- SKILLIN - Plataforma web gamificada para evaluación y entrenamiento
-- de competencias profesionales en empresas.
-- Script de creación de Base de Datos (MySQL / MariaDB)
-- Proyecto Intermodular DAW - IES Albarregas
-- Autor: Constantino Alexopoulos Real
-- =====================================================================

DROP DATABASE IF EXISTS skillindb;
CREATE DATABASE skillindb
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE skillindb;

-- Fuerza el charset de la SESIÓN de importación a utf8mb4, independientemente
-- del locale del cliente que ejecute el script (evita problemas con acentos/ñ).
SET NAMES utf8mb4;

-- ---------------------------------------------------------------------
-- Tabla: empresa
-- ---------------------------------------------------------------------
CREATE TABLE empresa (
    id_empresa      INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre          VARCHAR(150)    NOT NULL,
    sector          VARCHAR(100)    DEFAULT NULL,
    fecha_registro  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- Tabla: usuario
-- rol: 'trabajador' | 'rrhh' | 'administrador'
--   - trabajador: realiza los serious games asignados.
--   - rrhh: gestiona plantilla, catálogo, asignaciones e informes de SU empresa.
--   - administrador: igual que rrhh pero sin restricción de empresa (multi-empresa).
-- ---------------------------------------------------------------------
CREATE TABLE usuario (
    id_usuario      INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre          VARCHAR(100)    NOT NULL,
    apellidos       VARCHAR(150)    NOT NULL,
    email           VARCHAR(150)    NOT NULL UNIQUE,
    contrasena      VARCHAR(255)    NOT NULL,          -- hash bcrypt
    rol             ENUM('trabajador','rrhh','administrador') NOT NULL DEFAULT 'trabajador',
    departamento    VARCHAR(100)    DEFAULT NULL,
    foto            VARCHAR(255)    DEFAULT NULL,       -- nombre de fichero en public/uploads/avatars/
    activo          TINYINT(1)      NOT NULL DEFAULT 1, -- RF6: activar/desactivar
    fecha_alta      DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    id_empresa      INT UNSIGNED    NOT NULL,
    CONSTRAINT fk_usuario_empresa
        FOREIGN KEY (id_empresa) REFERENCES empresa(id_empresa)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE INDEX idx_usuario_empresa ON usuario(id_empresa);
CREATE INDEX idx_usuario_rol ON usuario(rol);

-- ---------------------------------------------------------------------
-- Tabla: password_reset
-- Tokens de un solo uso para recuperar contraseña por email.
-- Se guarda el HASH del token (nunca el token en claro).
-- ---------------------------------------------------------------------
CREATE TABLE password_reset (
    id_reset    INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_usuario  INT UNSIGNED    NOT NULL,
    token_hash  VARCHAR(255)    NOT NULL,
    expira_en   DATETIME        NOT NULL,
    usado       TINYINT(1)      NOT NULL DEFAULT 0,
    creado_en   DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_reset_usuario
        FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE INDEX idx_reset_usuario ON password_reset(id_usuario);
CREATE INDEX idx_reset_token ON password_reset(token_hash);

-- ---------------------------------------------------------------------
-- Tabla: juego (catálogo de serious games)
-- ---------------------------------------------------------------------
CREATE TABLE juego (
    id_juego            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    titulo              VARCHAR(150)    NOT NULL,
    descripcion         TEXT            DEFAULT NULL,
    tipo_competencia    VARCHAR(100)    DEFAULT NULL,   -- ej: atención, memoria, lógica...
    dificultad          ENUM('facil','media','dificil') NOT NULL DEFAULT 'facil',
    slug                VARCHAR(50)     NOT NULL UNIQUE, -- identifica el motor JS del juego
    imagen              VARCHAR(255)    DEFAULT NULL,     -- nombre de fichero en public/uploads/juegos/
    activo              TINYINT(1)      NOT NULL DEFAULT 1
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- Tabla: asignacion_juego (entidad intermedia N:M usuario <-> juego)
-- ---------------------------------------------------------------------
CREATE TABLE asignacion_juego (
    id_asignacion       INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    fecha_asignacion    DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    fecha_limite        DATE            DEFAULT NULL,
    estado              ENUM('pendiente','en_progreso','completado','caducado') NOT NULL DEFAULT 'pendiente',
    id_usuario          INT UNSIGNED    NOT NULL,
    id_juego            INT UNSIGNED    NOT NULL,
    asignado_por        INT UNSIGNED    DEFAULT NULL,   -- id_usuario (rrhh) que hizo la asignación
    CONSTRAINT fk_asignacion_usuario
        FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_asignacion_juego
        FOREIGN KEY (id_juego) REFERENCES juego(id_juego)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_asignacion_rrhh
        FOREIGN KEY (asignado_por) REFERENCES usuario(id_usuario)
        ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT uq_asignacion UNIQUE (id_usuario, id_juego, fecha_asignacion)
) ENGINE=InnoDB;

CREATE INDEX idx_asignacion_usuario ON asignacion_juego(id_usuario);
CREATE INDEX idx_asignacion_juego ON asignacion_juego(id_juego);
CREATE INDEX idx_asignacion_estado ON asignacion_juego(estado);

-- ---------------------------------------------------------------------
-- Tabla: resultado (histórico de partidas jugadas)
-- ---------------------------------------------------------------------
CREATE TABLE resultado (
    id_resultado        INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    puntuacion           INT UNSIGNED    NOT NULL DEFAULT 0,
    tiempo_empleado       INT UNSIGNED    NOT NULL DEFAULT 0, -- segundos
    fecha_realizacion     DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    id_usuario            INT UNSIGNED    NOT NULL,
    id_juego               INT UNSIGNED    NOT NULL,
    id_asignacion           INT UNSIGNED    DEFAULT NULL,
    CONSTRAINT fk_resultado_usuario
        FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_resultado_juego
        FOREIGN KEY (id_juego) REFERENCES juego(id_juego)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_resultado_asignacion
        FOREIGN KEY (id_asignacion) REFERENCES asignacion_juego(id_asignacion)
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE INDEX idx_resultado_usuario ON resultado(id_usuario);
CREATE INDEX idx_resultado_juego ON resultado(id_juego);

-- ---------------------------------------------------------------------
-- Tabla: informe (informes generados por RRHH - RF7)
-- ---------------------------------------------------------------------
CREATE TABLE informe (
    id_informe          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    fecha_generacion     DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    tipo                 VARCHAR(50)     NOT NULL DEFAULT 'rendimiento', -- rendimiento, participacion...
    observaciones         TEXT            DEFAULT NULL,
    id_usuario_rrhh        INT UNSIGNED    NOT NULL,
    id_empresa             INT UNSIGNED    NOT NULL,
    CONSTRAINT fk_informe_rrhh
        FOREIGN KEY (id_usuario_rrhh) REFERENCES usuario(id_usuario)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_informe_empresa
        FOREIGN KEY (id_empresa) REFERENCES empresa(id_empresa)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

-- =====================================================================
-- DATOS DE PRUEBA (seed)
-- =====================================================================

INSERT INTO empresa (nombre, sector) VALUES
('Alba Construcciones S.L.', 'Construcción'),
('Innotech S.L.', 'Tecnología'),
('Skillin', 'Formación');

-- Contraseña de prueba para todas las cuentas: 1234
-- (hash bcrypt real, generado con password_hash('1234', PASSWORD_BCRYPT);
-- no hace falta ejecutar seed_passwords.php para poder iniciar sesión,
-- aunque también está actualizado por si se prefiere regenerarlo).
-- El admin se inserta el último para no desplazar los id_usuario (1-6)
-- que referencian los INSERT de asignacion_juego más abajo.
INSERT INTO usuario (nombre, apellidos, email, contrasena, rol, departamento, id_empresa) VALUES
('Laura', 'Martín Sánchez', 'laura.martin@albaconstrucciones.com', '$2y$10$rAYipubgvvhpJ9VidFGqxux1pH5l1L3l0lmpmAE2EQd.vG.wwXYEa', 'rrhh', 'Recursos humanos', 1),
('Carlos', 'Ruiz Pérez', 'carlos.ruiz@innotech.com', '$2y$10$rAYipubgvvhpJ9VidFGqxux1pH5l1L3l0lmpmAE2EQd.vG.wwXYEa', 'rrhh', 'Recursos humanos', 2),
('Ana', 'García López', 'ana.garcia@innotech.com', '$2y$10$rAYipubgvvhpJ9VidFGqxux1pH5l1L3l0lmpmAE2EQd.vG.wwXYEa', 'trabajador', 'Administración', 2),
('Juan', 'Chacón Paz', 'juan.chacon@albaconstrucciones.com', '$2y$10$rAYipubgvvhpJ9VidFGqxux1pH5l1L3l0lmpmAE2EQd.vG.wwXYEa', 'trabajador', 'Comercial', 1),
('Eva', 'Flores Rojo', 'eva.flores@albaconstrucciones.com', '$2y$10$rAYipubgvvhpJ9VidFGqxux1pH5l1L3l0lmpmAE2EQd.vG.wwXYEa', 'trabajador', 'Marketing', 1),
('Elsa', 'Cantero Fernández', 'elsa.cantero@albaconstrucciones.com', '$2y$10$rAYipubgvvhpJ9VidFGqxux1pH5l1L3l0lmpmAE2EQd.vG.wwXYEa', 'trabajador', 'Logística', 1),
('Constantino', 'Alexopoulos Real', 'tak@tagtak.com', '$2y$10$rAYipubgvvhpJ9VidFGqxux1pH5l1L3l0lmpmAE2EQd.vG.wwXYEa', 'administrador', 'Administración', 3);

INSERT INTO juego (titulo, descripcion, tipo_competencia, dificultad, slug) VALUES
('Quiz de Seguridad Laboral', 'Preguntas tipo test sobre protocolos y competencias de seguridad en el puesto de trabajo.', 'Conocimiento normativo', 'facil', 'quiz'),
('Memoria de Procesos', 'Juego de memoria por parejas para entrenar la retención de pasos de un proceso.', 'Memoria y atención', 'media', 'memoria'),
('Tiempo de Reacción', 'Mide la rapidez de respuesta ante estímulos, útil para puestos que requieren reacción rápida.', 'Rapidez y atención', 'facil', 'reaccion');

-- Asignaciones de ejemplo, respetando que cada RRHH solo asigna dentro de su propia empresa
INSERT INTO asignacion_juego (id_usuario, id_juego, fecha_limite, estado, asignado_por) VALUES
(3, 1, DATE_ADD(CURDATE(), INTERVAL 15 DAY), 'pendiente', 2),
(4, 1, DATE_ADD(CURDATE(), INTERVAL 15 DAY), 'pendiente', 1),
(5, 2, DATE_ADD(CURDATE(), INTERVAL 15 DAY), 'pendiente', 1);

-- =====================================================================
-- Nota: el hash de contraseña de las cuentas de prueba ya corresponde a
-- "1234" (bcrypt real), así que se puede iniciar sesión nada más importar
-- este script. /database/seed_passwords.php se mantiene disponible por si
-- se prefiere regenerar los hashes o añadir/editar cuentas de prueba.
-- =====================================================================
