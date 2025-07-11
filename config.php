<?php

// Obtener la ruta persistente desde una variable de entorno
$dbPath = getenv("XQORE_DB_PATH");

if (!$dbPath) {
    // Por compatibilidad en desarrollo, usar ruta por defecto
    $dbPath = __DIR__ . '/database/xqore.db';
}

define('DB_FILE', $dbPath);

// Función para conectar a la base de datos
function getDbConnection() {
    try {
        $db = new PDO('sqlite:' . DB_FILE);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $db->exec("PRAGMA busy_timeout = 5000");
        return $db;
    } catch (PDOException $e) {
        die("Error de conexión: " . $e->getMessage());
    }
}

// Inicializar la base de datos si no existe
function initializeDatabase() {
    if (!file_exists(dirname(DB_FILE))) {
        mkdir(dirname(DB_FILE), 0777, true);
    }
    
    $db = getDbConnection();
    
    // Tabla de palabras en inglés
    $db->exec("CREATE TABLE IF NOT EXISTS tword_ingles (
        Id_palabra INTEGER PRIMARY KEY AUTOINCREMENT,
        Palabraen VARCHAR(255) NOT NULL,
        Palabraes VARCHAR(255) NOT NULL,
        Contador INTEGER DEFAULT 1,
        Estado_count TEXT DEFAULT '1' CHECK(Estado_count IN ('0','1')),
        Fecha_intento DATE,
        Fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    
    // Tabla de listas
    $db->exec("CREATE TABLE IF NOT EXISTS tlistas (
        Id_lista INTEGER PRIMARY KEY AUTOINCREMENT,
        nombre_lista VARCHAR(255) NOT NULL,
        descripcion_lista VARCHAR(255) DEFAULT NULL,
        Estado_lista TEXT DEFAULT '1' CHECK(Estado_lista IN ('0','1'))
    )");
    
    // Tabla de acciones relacionadas a listas
    $db->exec("CREATE TABLE IF NOT EXISTS tacciones (
        id_acciones INTEGER PRIMARY KEY AUTOINCREMENT,
        Id_lista INTEGER NOT NULL,
        nombre_accion VARCHAR(255) NOT NULL,
        estado_accion TEXT DEFAULT '1' CHECK(estado_accion IN ('0','1')),
        FOREIGN KEY (Id_lista) REFERENCES tlistas(Id_lista) ON DELETE CASCADE
    )");
    
    // Tabla de bitácora
    $db->exec("CREATE TABLE IF NOT EXISTS tbitacora (
        Id_bitacora INTEGER PRIMARY KEY AUTOINCREMENT,
        titulo VARCHAR(255) NOT NULL,
        contenido TEXT NOT NULL,
        fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        estado TEXT DEFAULT '1' CHECK(estado IN ('0','1'))
    )");
    
    // Tabla motivacional de usuarios
    $db->exec("CREATE TABLE IF NOT EXISTS tmotivaus (
        Id_motivaus INTEGER PRIMARY KEY AUTOINCREMENT,
        Frase TEXT NOT NULL,
        fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        estado TEXT DEFAULT '1' CHECK(estado IN ('0','1'))
    )");
    /*
    $db->exec("INSERT OR IGNORE INTO tmotivaus (Frase, estado) VALUES 
        ('El éxito es la suma de pequeños esfuerzos repetidos día tras día.', '1'),
        ('Los sueños no funcionan a menos que tú lo hagas.', '1'),
        ('La disciplina es el puente entre metas y logros.', '1'),
        ('No cuentes los días, haz que los días cuenten.', '1'),
        ('La constancia convierte lo ordinario en extraordinario.', '1'),
        ('El único modo de hacer un gran trabajo es amar lo que haces.', '1'),
        ('Cada error te acerca más al éxito si aprendes de él.', '1'),
        ('La actitud determina la dirección.', '1'),
        ('Tu potencial es ilimitado. Ve por ello.', '1'),
        ('El fracaso es la oportunidad de comenzar de nuevo con más inteligencia.', '1'),
        ('La motivación te pone en marcha, el hábito te mantiene.', '1'),
        ('Las dificultades preparan a personas comunes para destinos extraordinarios.', '1'),
        ('Lo que haces hoy puede mejorar todos tus mañanas.', '1'),
        ('No hay ascensor hacia el éxito, hay que tomar las escaleras.', '1'),
        ('El optimismo es la fe que conduce al logro.', '1'),
        ('Cuando todo parece ir en contra, recuerda que el avión despega contra el viento.', '1'),
        ('Nunca es tarde para ser lo que podrías haber sido.', '1'),
        ('Ser el mejor no es lo importante, ser mejor que ayer sí lo es.', '1'),
        ('Tu única limitación es la que tú mismo te impones.', '1'),
        ('El camino al éxito está siempre en construcción.', '1')");
*/
    // Cerrar conexión explícitamente
    $db = null;
}

// Inicializar la base de datos
initializeDatabase();
?>