<?php
require_once '../../config.php';

// Configuración inicial
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Conexión a la base de datos
$db = getDbConnection();

try {
    // Ejecutar la consulta para vaciar la tabla tmotivaus
    $db->exec("DELETE FROM tmotivaus");
    
    // Reiniciar el contador de autoincremento (opcional, para que los nuevos IDs comiencen desde 1)
    $db->exec("DELETE FROM sqlite_sequence WHERE name='tmotivaus'");
    
    echo "La tabla tmotivaus ha sido vaciada con éxito.";
} catch (PDOException $e) {
    echo "Error al vaciar la tabla: " . htmlspecialchars($e->getMessage());
}

// Cerrar conexión
$db = null;
?>