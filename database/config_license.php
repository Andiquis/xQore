<?php
// config_license.php - Configuración para el manejo de licencias
session_start();

try {
    $dbLicense = new PDO('sqlite:xqore.db');
    $dbLicense->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error de conexión a la base de datos de licencias: " . $e->getMessage());
}

// Incluir el manejador de licencias
require_once 'license_manager.php';

// Inicializar el manejador de licencias
$licenseManager = new LicenseManager();

// Obtener la licencia actual del usuario (la más reciente)
$stmt = $dbLicense->query("SELECT license_key, activation_date FROM licenses ORDER BY created_at DESC LIMIT 1");
$licenseData = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$licenseData) {
    // Si no hay licencia, establecer plan free por defecto
    $stmt = $dbLicense->prepare("INSERT INTO licenses (license_key, activation_date) VALUES (?, ?)");
    $stmt->execute([null, date('Y-m-d')]);
    $licenseData = ['license_key' => null, 'activation_date' => date('Y-m-d')];
}

// Procesar la licencia actual
$licenseStatus = $licenseManager->processLicense(
    $licenseData['license_key'],
    $licenseData['activation_date']
);

// Variable global para determinar si se muestra el popup
$showActivationPopup = $licenseStatus['show_popup'];
?>