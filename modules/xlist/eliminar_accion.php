<?php
require_once '../../config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_acciones_borrar']) && isset($_POST['id_lista'])) {
    $idAccion = filter_var($_POST['id_acciones_borrar'], FILTER_VALIDATE_INT);
    $idLista = filter_var($_POST['id_lista'], FILTER_VALIDATE_INT);
    
    if ($idAccion > 0 && $idLista > 0) {
        try {
            $db = getDbConnection();
            $stmt = $db->prepare("DELETE FROM tacciones WHERE id_acciones = :id");
            $stmt->bindParam(':id', $idAccion, PDO::PARAM_INT);
            
            if ($stmt->execute()) {
                header("Location: acciones.php?id_lista=$idLista&borrado=true");
                exit;
            } else {
                header("Location: acciones.php?id_lista=$idLista&borrado=false");
                exit;
            }
        } catch (PDOException $e) {
            header("Location: acciones.php?id_lista=$idLista&borrado=false&error=" . urlencode($e->getMessage()));
            exit;
        }
    }
}

header("Location: index.php");
exit;
?>