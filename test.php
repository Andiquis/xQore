<?php
/*
// test_licencia_free_activa.php - Simula una licencia gratuita activa
try {
    $licencia_db = new PDO('sqlite:C:\xampp\htdocs\xqoredesk1\app\xqore_licencias.db');
    $licencia_db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Eliminar licencias existentes
    $licencia_db->exec("DELETE FROM licencias");

    // Insertar licencia gratuita con fecha actual
    $licencia_stmt = $licencia_db->prepare("INSERT INTO licencias (licencia_clave, licencia_fecha_activacion) VALUES (?, ?)");
    $licencia_stmt->execute([null, date('Y-m-d')]);

    echo "Licencia gratuita activa creada (fecha: " . date('Y-m-d') . ").<br>";
    echo "Visita <a href='index.php'>index.php</a> para verificar (no debería mostrar popup).";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}*/






/*
// test_licencia_free_expirada.php - Simula una licencia gratuita expirada
try {
    $licencia_db = new PDO('sqlite:C:\xampp\htdocs\xqoredesk1\app\xqore_licencias.db');
    $licencia_db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Eliminar licencias existentes
    $licencia_db->exec("DELETE FROM licencias");

    // Insertar licencia gratuita con fecha de hace 6 días
    $licencia_fecha_expirada = date('Y-m-d', strtotime('-6 days'));
    $licencia_stmt = $licencia_db->prepare("INSERT INTO licencias (licencia_clave, licencia_fecha_activacion) VALUES (?, ?)");
    $licencia_stmt->execute([null, $licencia_fecha_expirada]);

    echo "Licencia gratuita expirada creada (fecha: $licencia_fecha_expirada).<br>";
    echo "Visita <a href='index.php'>index.php</a> para verificar (debería mostrar popup).";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}*/




/*
// test_licencia_mensual_activa.php - Simula una licencia mensual activa
try {
    $licencia_db = new PDO('sqlite:C:\xampp\htdocs\xqoredesk1\app\xqore_licencias.db');
    $licencia_db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Eliminar licencias existentes
    $licencia_db->exec("DELETE FROM licencias");

    // Insertar licencia mensual con fecha actual
    $licencia_stmt = $licencia_db->prepare("INSERT INTO licencias (licencia_clave, licencia_fecha_activacion) VALUES (?, ?)");
    $licencia_stmt->execute(['xqore30', date('Y-m-d')]);

    echo "Licencia mensual activa creada (clave: xqore30, fecha: " . date('Y-m-d') . ").<br>";
    echo "Visita <a href='index.php'>index.php</a> para verificar (no debería mostrar popup).";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}*/



/*
// test_licencia_mensual_expirada.php - Simula una licencia mensual expirada
try {
    $licencia_db = new PDO('sqlite:C:\xampp\htdocs\xqoredesk1\app\xqore_licencias.db');
    $licencia_db->setAttribute(PDO::parent::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Eliminar licencias existentes
    $licencia_db->exec("DELETE FROM licencias");

    // Insertar licencia mensual con fecha de hace 31 días
    $licencia_fecha_expirada = date('Y-m-d', strtotime('-31 days'));
    $licencia_stmt = $licencia_db->prepare("INSERT INTO licencias (licencia_clave, licencia_fecha_activacion) VALUES (?, ?)");
    $licencia_stmt->execute(['xqore30', $licencia_fecha_expirada]);

    echo "Licencia mensual expirada creada (clave: xqore30, fecha: $licencia_fecha_expirada).<br>";
    echo "Visita <a href='index.php'>index.php</a> para verificar (debería mostrar popup).";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}*/




/*
// test_licencia_anual_activa.php - Simula una licencia anual activa
try {
    $licencia_db = new PDO('sqlite:C:\xampp\htdocs\xqoredesk1\app\xqore_licencias.db');
    $licencia_db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Eliminar licencias existentes
    $licencia_db->exec("DELETE FROM licencias");

    // Insertar licencia anual con fecha actual
    $licencia_stmt = $licencia_db->prepare("INSERT INTO licencias (licencia_clave, licencia_fecha_activacion) VALUES (?, ?)");
    $licencia_stmt->execute(['xqore365', date('Y-m-d')]);

    echo "Licencia anual activa creada (clave: xqore365, fecha: " . date('Y-m-d') . ").<br>";
    echo "Visita <a href='index.php'>index.php</a> para verificar (no debería mostrar popup).";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}*/



/*
// test_licencia_anual_expirada.php - Simula una licencia anual expirada
try {
    $licencia_db = new PDO('sqlite:C:\xampp\htdocs\xqoredesk1\app\xqore_licencias.db');
    $licencia_db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Eliminar licencias existentes
    $licencia_db->exec("DELETE FROM licencias");

    // Insertar licencia anual con fecha de hace 366 días
    $licencia_fecha_expirada = date('Y-m-d', strtotime('-366 days'));
    $licencia_stmt = $licencia_db->prepare("INSERT INTO licencias (licencia_clave, licencia_fecha_activacion) VALUES (?, ?)");
    $licencia_stmt->execute(['xqore365', $licencia_fecha_expirada]);

    echo "Licencia anual expirada creada (clave: xqore365, fecha: $licencia_fecha_expirada).<br>";
    echo "Visita <a href='index.php'>index.php</a> para verificar (debería mostrar popup).";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
*/




/*
// test_licencia_ilimitada.php - Simula una licencia ilimitada
try {
    $licencia_db = new PDO('sqlite:C:\xampp\htdocs\xqoredesk1\app\xqore_licencias.db');
    $licencia_db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Eliminar licencias existentes
    $licencia_db->exec("DELETE FROM licencias");

    // Insertar licencia ilimitada con fecha actual
    $licencia_stmt = $licencia_db->prepare("INSERT INTO licencias (licencia_clave, licencia_fecha_activacion) VALUES (?, ?)");
    $licencia_stmt->execute(['xqore00', date('Y-m-d')]);

    echo "Licencia ilimitada creada (clave: xqore00, fecha: " . date('Y-m-d') . ").<br>";
    echo "Visita <a href='index.php'>index.php</a> para verificar (no debería mostrar popup).";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}*/







// limpiar_licencias.php - Elimina todas las licencias y reinicia con una gratuita
try {
    $licencia_db = new PDO('sqlite:C:\xampp\htdocs\xqoredesk1\app\xqore_licencias.db');
    $licencia_db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Eliminar licencias existentes
    $licencia_db->exec("DELETE FROM licencias");

    // Insertar licencia gratuita con fecha actual
    $licencia_stmt = $licencia_db->prepare("INSERT INTO licencias (licencia_clave, licencia_fecha_activacion) VALUES (?, ?)");
    $licencia_stmt->execute([null, date('Y-m-d')]);

    echo "Base de datos de licencias limpiada. Licencia gratuita creada (fecha: " . date('Y-m-d') . ").<br>";
    echo "Visita <a href='index.php'>index.php</a> para verificar (no debería mostrar popup).";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>