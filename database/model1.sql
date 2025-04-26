-- Crear base de datos
CREATE DATABASE IF NOT EXISTS xqore;
USE xqore;

-- Tabla de palabras en inglés
CREATE TABLE tword_ingles (
    Id_palabra INT AUTO_INCREMENT PRIMARY KEY,
    Palabraen VARCHAR(255) NOT NULL,
    Palabraes VARCHAR(255) NOT NULL,
    Fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabla de conteo de palabras
CREATE TABLE tcount_palabras (
    Id_tcount INT AUTO_INCREMENT PRIMARY KEY,
    Id_palabra INT NOT NULL,
    Contador INT DEFAULT 1,
    Estado_count ENUM('1', '0') DEFAULT '1',
    Fecha_intento date,
    FOREIGN KEY (Id_palabra) REFERENCES tword_ingles(Id_palabra) ON DELETE CASCADE
);

-- Tabla de listas
CREATE TABLE tlistas (
    Id_lista INT AUTO_INCREMENT PRIMARY KEY,
    nombre_lista VARCHAR(255) NOT NULL,
    descripcion_lista VARCHAR(255) NULL DEFAULT NULL,
    Estado_lista ENUM('1', '0') DEFAULT '1'
);

-- Tabla de acciones relacionadas a listas
CREATE TABLE tacciones (
    id_acciones INT AUTO_INCREMENT PRIMARY KEY,
    Id_lista INT NOT NULL,
    nombre_accion VARCHAR(255) NOT NULL,
    estado_accion ENUM('1', '0') DEFAULT '1',
    FOREIGN KEY (Id_lista) REFERENCES tlistas(Id_lista) ON DELETE CASCADE
);

-- Tabla de bitácora
CREATE TABLE tbitacora (
    Id_bitacora INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(255) NOT NULL,
    contenido TEXT NOT NULL,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    estado ENUM('1', '0') DEFAULT '1'
);

-- Tabla motivacional de usuarios
CREATE TABLE tmotivaus (
    Id_motivaus INT AUTO_INCREMENT PRIMARY KEY,
    Frase TEXT NOT NULL,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    estado ENUM('1', '0') DEFAULT '1'
);

-- Estructura de módulos de xqore
-- xqore es una aplicaion que tiene varias aplicaiones dentro de una sola. cada aplicacion tiene su propia funcionalidad y estructura de base de datos.:

-- Cingles -> tword_ingles, tcount_palabras
-- Es para aprender inglés. Cada día se debe traducir palabras del inglés al español. Si se acierta, se debe aumentar el contador; de lo contrario, se detiene el contador. Solo se puede hacer un intento por día. La aplicación debe incluir una opción de traductor al momento de registrar las palabras.

-- Xlist -> tlistas, tacciones
-- Es para crear listas de palabras, frases o acciones. Cada lista puede tener una o varias acciones. Cada acción puede ser una palabra o frase, y tiene un estado: si es 1, la acción se ejecutó; si es 0, no se ejecutó.

-- bitacore -> tbitacora
-- Es para llevar un registro de las acciones realizadas. Cada acción tiene un título y un contenido. El contenido son anotaciones del usuario, funcionando como una bitácora.

-- Motivaus -> tmotivaus
-- Es para motivar al usuario. Cada frase tiene un estado: si es 1, la frase se muestra; si es 0, no se muestra. Cada frase también tiene una fecha de registro.
-- La aplicación debe mostrar una frase motivacional aleatorio al iniciar sesión. Si el usuario no ha registrado ninguna frase, se debe mostrar una frase predeterminada. Si el usuario registra una nueva frase, la anterior se desactiva y la nueva se activa. Se debe permitir al usuario eliminar frases motivacionales.