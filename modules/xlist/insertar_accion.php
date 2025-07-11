<?php
require_once '../../config.php';

// Habilitar depuración (quitar en producción)
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_GET['id_lista']) || !filter_var($_GET['id_lista'], FILTER_VALIDATE_INT)) {
    header("Location: index.php");
    exit;
}

$idLista = $_GET['id_lista'];
$db = getDbConnection();

// Verificar que la lista existe
try {
    $stmtLista = $db->prepare("SELECT nombre_lista FROM tlistas WHERE Id_lista = :id");
    $stmtLista->bindParam(':id', $idLista, PDO::PARAM_INT);
    $stmtLista->execute();
    $lista = $stmtLista->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Error al verificar la lista: " . $e->getMessage();
    $lista = false;
} finally {
    $db = null; // Cerrar conexión
}

if (!$lista) {
    header("Location: index.php?error=Lista no encontrada");
    exit;
}

// Inicialización de variables
$mensaje = "";
$error = "";
$mostrarModal = false;
$nombreDuplicado = "";
$idAccionDuplicada = 0;
$nombreAccion = "";
$estadoAccion = "1";

// Manejar la confirmación de duplicado y reemplazo
if (isset($_GET['confirm']) && $_GET['confirm'] === 'true' && isset($_GET['id'])) {
    $idAccionConfirmada = filter_var($_GET['id'], FILTER_VALIDATE_INT);
    if ($idAccionConfirmada > 0) {
        try {
            $db = getDbConnection();
            $db->beginTransaction(); // Iniciar transacción
            $stmtDelete = $db->prepare("DELETE FROM tacciones WHERE id_acciones = :id");
            $stmtDelete->bindParam(':id', $idAccionConfirmada, PDO::PARAM_INT);
            if ($stmtDelete->execute()) {
                $db->commit();
                $mensaje = "Acción reemplazada con éxito.";
            } else {
                $db->rollBack();
                $error = "Error al reemplazar la acción: " . implode(" ", $stmtDelete->errorInfo());
            }
        } catch (PDOException $e) {
            $db->rollBack();
            $error = "Error de base de datos al reemplazar: " . $e->getMessage();
        } finally {
            $db = null; // Cerrar conexión
        }
    } else {
        $error = "Identificador de acción inválido.";
    }
}

// Procesar el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombreAccion = trim(filter_input(INPUT_POST, 'nombre_accion', FILTER_SANITIZE_STRING));
    $estadoAccion = filter_input(INPUT_POST, 'estado_accion', FILTER_SANITIZE_STRING);

    // Validar estado_accion
    if (!in_array($estadoAccion, ['0', '1'])) {
        $estadoAccion = '1';
    }

    if (!empty($nombreAccion)) {
        try {
            $db = getDbConnection();
            // Verificar duplicados
            $stmt = $db->prepare("SELECT id_acciones, nombre_accion FROM tacciones WHERE nombre_accion = :nombre AND Id_lista = :id_lista");
            $stmt->bindParam(':nombre', $nombreAccion);
            $stmt->bindParam(':id_lista', $idLista, PDO::PARAM_INT);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $db = null; // Cerrar conexión

            if ($row) {
                $mostrarModal = true;
                $nombreDuplicado = htmlspecialchars($nombreAccion);
                $idAccionDuplicada = $row['id_acciones'];
            } else {
                $db = getDbConnection();
                $db->beginTransaction(); // Iniciar transacción
                $stmt = $db->prepare("INSERT INTO tacciones (Id_lista, nombre_accion, estado_accion) VALUES (:id_lista, :nombre, :estado)");
                $stmt->bindParam(':id_lista', $idLista, PDO::PARAM_INT);
                $stmt->bindParam(':nombre', $nombreAccion);
                $stmt->bindParam(':estado', $estadoAccion);

                if ($stmt->execute()) {
                    $db->commit();
                    header("Location: acciones.php?id_lista=$idLista&mensaje=Acción registrada correctamente");
                    exit;
                } else {
                    $db->rollBack();
                    $error = "Error al registrar la acción: " . implode(" ", $stmt->errorInfo());
                }
            }
        } catch (PDOException $e) {
            if (isset($db) && $db->inTransaction()) {
                $db->rollBack();
            }
            $error = "Error de base de datos al insertar: " . $e->getMessage();
        } finally {
            $db = null; // Cerrar conexión
        }
    } else {
        $error = "El nombre de la acción es obligatorio.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Insertar Nueva Acción</title>
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
                    <h1><i class="fas fa-plus"></i> Nueva Acción para <?php echo htmlspecialchars($lista['nombre_lista']); ?></h1>
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
                    <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']) . '?id_lista=' . $idLista; ?>" autocomplete="off">
                        <div class="form-group">
                            <label for="nombre_accion"><i class="fas fa-keyboard"></i> Nombre de la Acción:</label>
                            <input type="text" id="nombre_accion" name="nombre_accion" class="form-control" value="<?php echo htmlspecialchars($nombreAccion); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="estado_accion"><i class="fas fa-toggle-on"></i> Estado:</label>
                            <select id="estado_accion" name="estado_accion" class="form-control">
                                <option value="1" <?php echo $estadoAccion == '1' ? 'selected' : ''; ?>>Activa</option>
                                <option value="0" <?php echo $estadoAccion == '0' ? 'selected' : ''; ?>>Inactiva</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <button type="submit" class="btn"><i class="fas fa-save"></i> Guardar</button>
                        </div>
                    </form>
                </div>
                <a href="acciones.php?id_lista=<?php echo $idLista; ?>" class="back-link"><i class="fas fa-arrow-left"></i> Volver</a>
            </div>

            <div id="duplicateModal" class="modal" style="<?php echo $mostrarModal ? 'display:block;' : ''; ?>">
                <div class="modal-content">
                    <span class="close-button" onclick="cerrarModal()">×</span>
                    <h3 style="color: var(--secondary-color); margin-bottom: 15px; font-family: 'Share Tech Mono', monospace;">ACCIÓN DUPLICADA DETECTADA</h3>
                    <p>La acción "<strong class="resalt-text"><?php echo $nombreDuplicado; ?></strong>" ya está registrada en esta lista.</p>
                    <p style="margin-top: 10px;">Selecciona una acción:</p>
                    <div class="modal-buttons">
                        <button class="modal-button-confirm" onclick="window.location.href='insertar_accion.php?id_lista=<?php echo $idLista; ?>&confirm=true&id=<?php echo $idAccionDuplicada; ?>'"><i class="fas fa-sync-alt"></i> Reemplazar</button>
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
            var inputNombre = document.getElementById('nombre_accion');
            if (inputNombre) {
                inputNombre.focus();
            }

            window.onclick = function(event) {
                const modal = document.getElementById('duplicateModal');
                if (event.target == modal) {
                    modal.style.display = "none";
                }
            }

            const inputs = document.querySelectorAll('input[type="text"], select');
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