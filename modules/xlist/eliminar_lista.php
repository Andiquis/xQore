<?php
require_once '../../config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_lista_borrar'])) {
    $idLista = filter_var($_POST['id_lista_borrar'], FILTER_VALIDATE_INT);
    
    if ($idLista > 0) {
        try {
            $db = getDbConnection();
            $stmt = $db->prepare("DELETE FROM tlistas WHERE Id_lista = :id");
            $stmt->bindParam(':id', $idLista, PDO::PARAM_INT);
            
            if ($stmt->execute()) {
                header("Location: administracion.php?borrado=true");
                exit;
            } else {
                header("Location: administracion.php?borrado=false");
                exit;
            }
        } catch (PDOException $e) {
            header("Location: administracion.php?borrado=false&error=" . urlencode($e->getMessage()));
            exit;
        }
    }
}

header("Location: administracion.php");
exit;
?>