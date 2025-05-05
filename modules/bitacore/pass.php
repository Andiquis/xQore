<?php
require_once '../../config.php';

// Configuración inicial
error_reporting(E_ALL); // Quitar en producción
ini_set('display_errors', 1);

// Archivo para almacenar la contraseña
$passFile = __DIR__ . '/pass.json';

// Verificar si el archivo de contraseña existe y cargar la contraseña
$password = null;
if (file_exists($passFile)) {
    $passData = json_decode(file_get_contents($passFile), true);
    $password = isset($passData['password']) ? $passData['password'] : null;
}

// Procesar formulario de contraseña y restablecimiento
$showContent = false;
$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['reset_password'])) {
        // Restablecer contraseña (borrar pass.json)
        if (file_exists($passFile)) {
            unlink($passFile);
        }
        $password = null;
    } elseif (isset($_POST['set_password'])) {
        // Establecer nueva contraseña
        $inputPassword = isset($_POST['password']) ? trim($_POST['password']) : '';
        if (!empty($inputPassword)) {
            $passData = ['password' => password_hash($inputPassword, PASSWORD_DEFAULT)];
            file_put_contents($passFile, json_encode($passData));
            $password = $passData['password'];
            $showContent = true;
            // Redirigir a index.php tras establecer la contraseña
            header('Location: index.php');
            exit;
        } else {
            $errorMessage = 'La contraseña no puede estar vacía';
        }
    } elseif (isset($_POST['verify_password'])) {
        // Verificar contraseña existente
        $inputPassword = isset($_POST['password']) ? trim($_POST['password']) : '';
        if ($password && password_verify($inputPassword, $password)) {
            $showContent = true;
            // Redirigir a index.php tras verificar la contraseña
            header('Location: index.php');
            exit;
        } else {
            $errorMessage = 'Contraseña incorrecta';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificación - Bitacora</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;500&family=IM+Fell+English&display=swap" rel="stylesheet">
    <style>
        body {
            margin: 0;
            padding: 0;
            background: #1a1a1a;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            font-family: 'Cormorant Garamond', serif;
        }

        .popup {
            display: flex;
            position: fixed;
            top: tweeted: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }

        .popup-content {
            background: #f2e8c9;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.7);
            max-width: 400px;
            width: 90%;
            font-family: 'IM Fell English', serif;
            color: #3a2c1d;
        }

        .popup-content h2 {
            margin-top: 0;
            color: #8b4513;
        }

        .popup-content input {
            width: 100%;
            padding: 8px;
            margin: 10px 0;
            border: 1px solid #b89f65;
            border-radius: 4px;
            font-family: 'Cormorant Garamond', serif;
        }

        .popup-content button {
            padding: 8px 15px;
            background: linear-gradient(45deg, rgba(212, 175, 55, 0.4), rgba(0, 255, 255, 0.4));
            color: #3a2c1d;
            border: 1px solid #d4af37;
            border-radius: 5px;
            cursor: pointer;
            font-family: 'Share Tech Mono', monospace;
            margin-right: 10px;
        }

        .popup-content button:hover {
            background: linear-gradient(45deg, rgba(212, 175, 55, 0.6), rgba(0, 255, 255, 0.6));
        }

        .error-message {
            color: #8b0000;
            font-size: 14px;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <?php if (!$showContent && $password): ?>
        <!-- Popup para verificar contraseña -->
        <div class="popup" id="verifyPopup">
            <div class="popup-content">
                <h2>Verificar Contraseña</h2>
                <form method="POST">
                    <input type="password" name="password" placeholder="Ingrese la contraseña" required autofocus>
                    <input type="hidden" name="verify_password" value="1">
                    <button type="submit">Verificar</button>
                    <button type="submit" name="reset_password" value="1">Restablecer Contraseña</button>
                    <?php if ($errorMessage): ?>
                        <div class="error-message"><?php echo htmlspecialchars($errorMessage); ?></div>
                    <?php endif; ?>
                </form>
            </div>
        </div>
    <?php elseif (!$password && !$showContent): ?>
        <!-- Popup para establecer contraseña -->
        <div class="popup" id="setPopup">
            <div class="popup-content">
                <h2>Establecer Contraseña</h2>
                <form method="POST">
                    <input type="password" name="password" placeholder="Ingrese nueva contraseña" required autofocus>
                    <input type="hidden" name="set_password" value="1">
                    <button type="submit">Establecer</button>
                    <?php if ($errorMessage): ?>
                        <div class="error-message"><?php echo htmlspecialchars($errorMessage); ?></div>
                    <?php endif; ?>
                </form>
            </div>
        </div>
    <?php endif; ?>

    <script>
        // Enfocar el campo de contraseña al cargar el popup
        document.addEventListener('DOMContentLoaded', function() {
            const passwordInput = document.querySelector('input[name="password"]');
            if (passwordInput) {
                passwordInput.focus();
            }
        });
    </script>
</body>
</html>