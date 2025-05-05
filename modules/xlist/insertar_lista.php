<?php
// Iniciar el buffer de salida al principio del script
ob_start();

require_once '../../config.php';

// Inicialización de variables
$mensaje = "";
$error = "";
$mostrarModal = false;
$nombreDuplicado = "";
$idListaDuplicada = 0;
$nombreLista = "";
$descripcionLista = "";
$estadoLista = "1";

// Manejar la confirmación de duplicado y reemplazo
if (isset($_GET['confirm']) && $_GET['confirm'] === 'true' && isset($_GET['id'])) {
    $idListaConfirmada = filter_var($_GET['id'], FILTER_VALIDATE_INT);
    if ($idListaConfirmada > 0) {
        try {
            $db = getDbConnection();
            $stmtDelete = $db->prepare("DELETE FROM tlistas WHERE Id_lista = :id");
            $stmtDelete->bindParam(':id', $idListaConfirmada, PDO::PARAM_INT);
            if ($stmtDelete->execute()) {
                $mensaje = "Lista reemplazada con éxito.";
            } else {
                $error = "Error al reemplazar la lista.";
            }
        } catch (PDOException $e) {
            $error = "Error de base de datos: " . htmlspecialchars($e->getMessage());
        }
    } else {
        $error = "Identificador de lista inválido.";
    }
}

// Procesar el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Usar htmlspecialchars en lugar de FILTER_SANITIZE_STRING
    $nombreLista = trim(htmlspecialchars($_POST['nombre_lista'] ?? ''));
    $descripcionLista = trim(htmlspecialchars($_POST['descripcion_lista'] ?? ''));
    $estadoLista = htmlspecialchars($_POST['estado_lista'] ?? '1');

    if (!empty($nombreLista)) {
        try {
            $db = getDbConnection();
            $stmt = $db->prepare("SELECT Id_lista, nombre_lista FROM tlistas WHERE nombre_lista = :nombre");
            $stmt->bindParam(':nombre', $nombreLista);
            $stmt->execute();

            if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $mostrarModal = true;
                $nombreDuplicado = htmlspecialchars($nombreLista);
                $idListaDuplicada = $row['Id_lista'];
            } else {
                $stmt = $db->prepare("INSERT INTO tlistas (nombre_lista, descripcion_lista, Estado_lista) VALUES (:nombre, :descripcion, :estado)");
                $stmt->bindParam(':nombre', $nombreLista);
                $stmt->bindParam(':descripcion', $descripcionLista);
                $stmt->bindParam(':estado', $estadoLista);

                if ($stmt->execute()) {
                    $mensaje = "Lista registrada correctamente.";
                    $nombreLista = $descripcionLista = "";
                    $estadoLista = "1";
                    
                    // Redirigir sin intentar limpiar el buffer
                    header("Location: administracion.php");
                    exit;
                } else {
                    $error = "Error al registrar la lista.";
                }
            }
        } catch (PDOException $e) {
            $error = "Error de base de datos: " . htmlspecialchars($e->getMessage());
        }
    } else {
        $error = "El nombre de la lista es obligatorio.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Insertar Nueva Lista</title>
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
                <li><a href="../cingles/index.php"><i class="fas fa-language"></i> Cingles</a></li>
                <li class="active"><a href="index.php"><i class="fas fa-list"></i> XList</a></li>
                <li><a href="../bitacore/pass.php"><i class="fas fa-book"></i> Bitacore</a></li>
                <li><a href="../motivaus/index.php"><i class="fas fa-bolt"></i> Motivaus</a></li>
            </ul>
        </nav>

        <main class="content">
            <header>
                <div class="header-content">
                    <h1><i class="fas fa-plus"></i> Insertar Nueva Lista</h1>
                    <div class="date-time" id="dateTime">Cargando...</div>
                </div>
            </header>

            <div class="insert-container">
                <div class="matrix-overlay"></div>
                <div class="form-container">
                    <?php if (!empty($mensaje)): ?>
                        <p class="message"><i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($mensaje); ?> Redirigiendo en 2 segundos...</p>
                    <?php endif; ?>
                    <?php if (!empty($error)): ?>
                        <p class="error"><i class="fas fa-exclamation-triangle"></i> <?php echo htmlspecialchars($error); ?></p>
                    <?php endif; ?>
                    <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" autocomplete="off">
                        <div class="form-group">
                            <label for="nombre_lista"><i class="fas fa-keyboard"></i> Nombre de la Lista:</label>
                            <input type="text" id="nombre_lista" name="nombre_lista" value="<?php echo htmlspecialchars($nombreLista); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="descripcion_lista"><i class="fas fa-comment"></i> Descripción (Opcional):</label>
                            <textarea id="descripcion_lista" name="descripcion_lista" class="form-control"><?php echo htmlspecialchars($descripcionLista); ?></textarea>
                        </div>
                        <div class="form-group">
                            <label for="estado_lista"><i class="fas fa-toggle-on"></i> Estado:</label>
                            <select id="estado_lista" name="estado_lista" class="form-control">
                                <option value="1" <?php echo $estadoLista == '1' ? 'selected' : ''; ?>>Activa</option>
                                <option value="0" <?php echo $estadoLista == '0' ? 'selected' : ''; ?>>Inactiva</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <button type="submit" class="btn"><i class="fas fa-save"></i> Guardar</button>
                        </div>
                    </form>
                </div>
                <a href="index.php" class="back-link"><i class="fas fa-arrow-left"></i> Volver</a>
            </div>

            <div id="duplicateModal" class="modal" style="<?php echo $mostrarModal ? 'display:block;' : ''; ?>">
                <div class="modal-content">
                    <span class="close-button" onclick="cerrarModal()">×</span>
                    <h3 style="color: var(--secondary-color); margin-bottom: 15px; font-family: 'Share Tech Mono', monospace;">LISTA DUPLICADA DETECTADA</h3>
                    <p>La lista "<strong class="resalt-text"><?php echo $nombreDuplicado; ?></strong>" ya está registrada.</p>
                    <p style="margin-top: 10px;">Selecciona una acción:</p>
                    <div class="modal-buttons">
                        <button class="modal-button-confirm" onclick="window.location.href='insertar_lista.php?confirm=true&id=<?php echo $idListaDuplicada; ?>'"><i class="fas fa-sync-alt"></i> Reemplazar</button>
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
            var inputNombre = document.getElementById('nombre_lista');
            if (inputNombre) {
                inputNombre.focus();
            }

            window.onclick = function(event) {
                const modal = document.getElementById('duplicateModal');
                if (event.target == modal) {
                    modal.style.display = "none";
                }
            }

            const inputs = document.querySelectorAll('input[type="text"], textarea, select');
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