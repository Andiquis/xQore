<?php
// Iniciar el buffer de salida al principio del script
ob_start();

require_once '../../config.php';

// Inicialización de variables
$mensaje = "";
$error = "";
$mostrarModal = false;
$palabraEnDuplicada = "";
$palabraEsDuplicada = "";
$idPalabraDuplicada = 0;
$palabraEn = "";
$palabraEs = "";

// Manejar la confirmación de duplicado y reinicio del contador
if (isset($_GET['confirm']) && $_GET['confirm'] === 'true' && isset($_GET['id'])) {
    $idPalabraConfirmada = filter_var($_GET['id'], FILTER_VALIDATE_INT);
    if ($idPalabraConfirmada > 0) {
        try {
            $db = getDbConnection();
            $stmtUpdate = $db->prepare("UPDATE tword_ingles SET Contador = 0, Fecha_intento = NULL WHERE Id_palabra = :id");
            $stmtUpdate->bindParam(':id', $idPalabraConfirmada, PDO::PARAM_INT);
            if ($stmtUpdate->execute()) {
                $mensaje = "Progreso reiniciado con éxito. Palabra lista para nuevo ciclo de aprendizaje.";
            } else {
                $error = "Error en operación de reinicio. Código: " . implode(" ", $stmtUpdate->errorInfo());
            }
        } catch (PDOException $e) {
            $error = "Inconveniete crítico de base de datos: " . $e->getMessage();
        }
    } else {
        $error = "Identificador de palabra inválido. Operación abortada.";
    }
}

// Procesar el formulario cuando se envía
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validación y limpieza de entradas usando htmlspecialchars en lugar de FILTER_SANITIZE_STRING
    $palabraEn = trim(htmlspecialchars($_POST['palabra_en'] ?? ''));
    $palabraEs = trim(htmlspecialchars($_POST['palabra_es'] ?? ''));

    if (!empty($palabraEn) && !empty($palabraEs)) {
        try {
            $db = getDbConnection();
            // Verificar si alguna de las palabras ya existe
            $stmt = $db->prepare("SELECT Id_palabra, Palabraen, Palabraes FROM tword_ingles WHERE Palabraen = :en OR Palabraes = :es");
            $stmt->bindParam(':en', $palabraEn);
            $stmt->bindParam(':es', $palabraEs);
            $stmt->execute();

            if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                // La palabra ya existe, mostrar la modal de confirmación
                $mostrarModal = true;
                $palabraEnDuplicada = htmlspecialchars($palabraEn);
                $palabraEsDuplicada = htmlspecialchars($palabraEs);
                $idPalabraDuplicada = $row['Id_palabra'];
            } else {
                // Insertar la nueva palabra con protección contra SQL injection
                $stmt = $db->prepare("INSERT INTO tword_ingles (Palabraen, Palabraes, Contador, Fecha_intento) VALUES (:en, :es, 0, NULL)");
                $stmt->bindParam(':en', $palabraEn);
                $stmt->bindParam(':es', $palabraEs);

                if ($stmt->execute()) {
                    $mensaje = "Palabra Registrada correctamente.";
                    // Limpiar campos después de insertar
                    $palabraEn = $palabraEs = "";
                } else {
                    $error = "Error de inserción: " . implode(" ", $stmt->errorInfo());
                }
            }
        } catch (PDOException $e) {
            $error = "Estamos teniendo inconvenientes de base de datos: " . $e->getMessage();
        }
    } else {
        $error = "Los campos no pueden estar vacíos. Intenta de nuevo.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>[XQ0R3] :: Insertar Nueva Palabra</title>
    <meta name="description" content="Sistema de gestión de vocabulario inglés-español con estilo cyberpunk">
    <link rel="stylesheet" href="../../css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" crossorigin="anonymous">
    
</head>
<body>
    <div class="container">
        <nav class="sidebar">
        <div class="matrix-nav-bg">
                <canvas id="matrixNavCanvas"></canvas>
            </div>
            <div class="logo">XQ0R3</div>
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
                    <h1><i class="fas fa-plus"></i> Insertar Nueva Palabra</h1>
                    <div class="date-time" id="dateTime">Cargando...</div>
                </div>
            </header>

            <div class="insert-container">
                <div class="matrix-overlay"></div>
                <div class="form-container">
                    <?php if (!empty($mensaje)): ?>
                        <p class="message"><i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($mensaje); ?></p>
                    <?php endif; ?>
                    <?php if (!empty($error)): ?>
                        <p class="error"><i class="fas fa-exclamation-triangle"></i> <?php echo htmlspecialchars($error); ?></p>
                    <?php endif; ?>
                    <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" autocomplete="off">
                        <div class="form-group">
                            <label for="palabra_en"><i class="fas fa-keyboard"></i> Palabra en Inglés:</label>
                            <input type="text" id="palabra_en" name="palabra_en" value="<?php echo htmlspecialchars($palabraEn); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="palabra_es"><i class="fas fa-language"></i> Palabra en Español:</label>
                            <input type="text" id="palabra_es" name="palabra_es" value="<?php echo htmlspecialchars($palabraEs); ?>" required>
                        </div>
                        <div class="form-group">
                            <button type="submit"><i class="fas fa-save"></i> Guardar</button>
                        </div>
                    </form>
                </div>
                <a href="index.php" class="back-link"><i class="fas fa-arrow-left"></i> Volver</a>
            </div>

            <!-- Modal para palabras duplicadas -->
            <div id="duplicateModal" class="modal" style="<?php echo $mostrarModal ? 'display:block;' : ''; ?>">
                <div class="modal-content">
                    <span class="close-button" onclick="cerrarModal()">×</span>
                    <h3 style="color: var(--secondary-color); margin-bottom: 15px; font-family: 'Share Tech Mono', monospace;">ENTRADA DUPLICADA DETECTADA</h3>
                    <p>La palabra "<strong style="color: var(--primary-color);"><?php echo $palabraEnDuplicada; ?></strong>" (<strong style="color: var(--secondary-color);"><?php echo $palabraEsDuplicada; ?></strong>) ya fue registrado.</p>
                    <p style="margin-top: 10px;">Selecciona una acción:</p>
                    <div class="modal-buttons">
                        <button class="modal-button-confirm" onclick="window.location.href='insertar_palabra.php?confirm=true&id=<?php echo $idPalabraDuplicada; ?>'"><i class="fas fa-sync-alt"></i> Reiniciar Progreso</button>
                        <button class="modal-button-cancel" onclick="cerrarModal()"><i class="fas fa-times"></i> Cancelar</button>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
    function cerrarModal() {
        document.getElementById('duplicateModal').style.display = 'none';
    }
    
    document.addEventListener('DOMContentLoaded', function() {
        // Enfocar el primer input al cargar la página
        var inputPalabraEn = document.getElementById('palabra_en');
        if (inputPalabraEn) {
            inputPalabraEn.focus();
        }

        // Cerrar modal al hacer clic fuera de él
        window.onclick = function(event) {
            const modal = document.getElementById('duplicateModal');
            if (event.target == modal) {
                modal.style.display = "none";
            }
        }

        // Añadir efecto de tecleo a los inputs
        const inputs = document.querySelectorAll('input[type="text"]');
        inputs.forEach(input => {
            input.addEventListener('focus', function() {
                this.style.borderColor = 'var(--primary-color)';
                this.style.boxShadow = '0 0 10px rgba(0, 255, 0, 0.3)';
            });
            
            input.addEventListener('blur', function() {
                if (!this.value) {
                    this.style.borderColor = 'var(--border-color)';
                    this.style.boxShadow = 'none';
                }
            });
        });
    });
    </script>
    <script src="../../js/scripts.js"></script>

</body>
</html>