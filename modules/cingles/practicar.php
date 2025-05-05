<?php
require_once '../../config.php';

$db = getDbConnection();
$hoy = date('Y-m-d');

// Seleccionar palabras para practicar:
// 1. Contador menor a 10.
// 2. Fecha_intento es NULL o diferente al día de hoy.
$stmt = $db->prepare("SELECT Id_palabra, Palabraen, Palabraes FROM tword_ingles 
                       WHERE Contador < 20 AND (Fecha_intento IS NULL OR Fecha_intento != :hoy)
                       ORDER BY RANDOM() LIMIT 1");
$stmt->bindParam(':hoy', $hoy);
$stmt->execute();
$palabraActual = $stmt->fetch(PDO::FETCH_ASSOC);

$mensaje = "";
$practicadoHoy = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idPalabra = $_POST['id_palabra'];

    // Verificar si ya se practicó hoy
    $stmtCheck = $db->prepare("SELECT Id_palabra FROM tword_ingles WHERE Id_palabra = :id AND Fecha_intento = :hoy");
    $stmtCheck->bindParam(':id', $idPalabra);
    $stmtCheck->bindParam(':hoy', $hoy);
    $stmtCheck->execute();

    if ($stmtCheck->rowCount() > 0) {
        $mensaje = "<div class='alert alert-warning'>Ya has practicado esta palabra hoy. Intenta con otra mañana.</div>";
        $practicadoHoy = true;
    } else {
        $respuesta = trim($_POST['respuesta']);
        $palabraCorrectaEs = $_POST['palabra_correcta_es'];
        $palabraCorrectaEn = $_POST['palabra_correcta_en'];

        $stmtUpdateFecha = $db->prepare("UPDATE tword_ingles SET Fecha_intento = :hoy WHERE Id_palabra = :id");
        $stmtUpdateFecha->bindParam(':hoy', $hoy);
        $stmtUpdateFecha->bindParam(':id', $idPalabra);
        $stmtUpdateFecha->execute();

        if (strtolower($respuesta) === strtolower($palabraCorrectaEs)) {
            $mensaje = "<div class='alert alert-success'>¡Correcto! La traducción de '" . htmlspecialchars($palabraCorrectaEn) . "' es '" . htmlspecialchars($palabraCorrectaEs) . "'.</div>";
            $stmtUpdateContador = $db->prepare("UPDATE tword_ingles SET Contador = Contador + 1 WHERE Id_palabra = :id");
            $stmtUpdateContador->bindParam(':id', $idPalabra);
            $stmtUpdateContador->execute();
        } else {
            $mensaje = "<div class='alert alert-danger'>Incorrecto. La traducción de '" . htmlspecialchars($palabraCorrectaEn) . "' es '" . htmlspecialchars($palabraCorrectaEs) . "'. Tu respuesta fue '" . htmlspecialchars($respuesta) . "'.</div>";
        }
        // Después de la respuesta, volvemos a seleccionar una nueva palabra para la siguiente práctica
        $stmtNuevaPalabra = $db->prepare("SELECT Id_palabra, Palabraen, Palabraes FROM tword_ingles 
                                       WHERE Contador < 10 AND (Fecha_intento IS NULL OR Fecha_intento != :hoy)
                                       ORDER BY RANDOM() LIMIT 1");
        $stmtNuevaPalabra->bindParam(':hoy', $hoy);
        $stmtNuevaPalabra->execute();
        $palabraActual = $stmtNuevaPalabra->fetch(PDO::FETCH_ASSOC);
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Practicar Inglés</title>
    <link rel="stylesheet" href="../../css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" crossorigin="anonymous">
</head>
<body>
    <div class="container">
        <nav class="sidebar">
        <div class="matrix-nav-bg">
                <canvas id="matrixNavCanvas"></canvas>
            </div>
            <div class="logo" data-text="XQ0R3">XQ0R3</div>
            
            <ul class="menu">
                <li><a href="../../index.php"><i class="fas fa-home"></i> Dashboard</a></li>
                <li class="active"><a href="index.php"><i class="fas fa-language"></i> Cingles</a></li>
                <li><a href="../xlist/index.php"><i class="fas fa-list"></i> XList</a></li>
                <li><a href="../bitacore/pass.php"><i class="fas fa-book"></i> Bitacore</a></li>
                <li><a href="../motivaus/index.php"><i class="fas fa-bolt"></i> Motivaus</a></li>
            </ul>
        </nav>

        <main class="content">
            <header>
                <div class="header-content">
                    <h1><i class="fas fa-play"></i> Practicar Inglés</h1>
                    <div class="date-time" id="dateTime">Cargando...</div>
                </div>
            </header>

            <div class="insert-container">
                <h2 class="form-title">Practicar Traducción</h2>
                <div class="matrix-overlay"></div>
                <?php if (!empty($palabraActual)): ?>
                    <div class="form-container">
                        <div class="form-group">
                            <label for="respuesta">¿Cuál es la traducción de <span class="resalt-text"><?php echo htmlspecialchars($palabraActual['Palabraen']); ?></span>?</label>
                            <form method="POST" action="">
                                <input type="hidden" name="id_palabra" value="<?php echo $palabraActual['Id_palabra']; ?>">
                                <input type="hidden" name="palabra_correcta_es" value="<?php echo htmlspecialchars($palabraActual['Palabraes']); ?>">
                                <input type="hidden" name="palabra_correcta_en" value="<?php echo htmlspecialchars($palabraActual['Palabraen']); ?>">
                                <input autocomplete="off" type="text" name="respuesta" id="respuestaInput" class="form-control" placeholder="Tu respuesta en español" required>
                                <br><br>
                                <button type="submit" class="btn">Verificar</button>
                            </form>
                        </div>
                        <?php if (!empty($mensaje)): ?>
                            <?php echo $mensaje; ?>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <?php if (!empty($mensaje)): ?>
                        <?php echo $mensaje; ?>
                    <?php else: ?>
                        <div class="alert alert-success">¡Has practicado todas las palabras por hoy o ya las has dominado!</div>
                    <?php endif; ?>
                <?php endif; ?>
                <a href="index.php" class="back-link"><i class="fas fa-arrow-left"></i> Volver</a>
            </div>
        </main>
    </div>

    <script src="../../js/scripts.js"></script>
    <script>
        // Enfocar el input al cargar la página
        document.addEventListener('DOMContentLoaded', function() {
            var input = document.getElementById('respuestaInput');
            if (input) {
                input.focus();
            }
        });

        // Enfocar el input después de enviar el formulario
        document.querySelector('form').addEventListener('submit', function() {
            setTimeout(function() {
                var input = document.getElementById('respuestaInput');
                if (input) {
                    input.focus();
                }
            }, 100); // Pequeño retraso para asegurar que el DOM esté actualizado
        });
    </script>
</body>
</html>