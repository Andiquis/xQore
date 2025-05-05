<?php
require_once '../../config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_palabra_borrar'])) {
    $idPalabraBorrar = $_POST['id_palabra_borrar'];
    $db = getDbConnection();
    $stmt = $db->prepare("DELETE FROM tword_ingles WHERE Id_palabra = :id");
    $stmt->bindParam(':id', $idPalabraBorrar);

    if ($stmt->execute()) {
        header("Location: index.php?borrado=true");
        exit();
    } else {
        header("Location: index.php?borrado=false");
        exit();
    }
} else {
    // Si se accede directamente a este archivo o no se recibe el ID, redirigir
    header("Location: index.php");
    exit();
}
?>