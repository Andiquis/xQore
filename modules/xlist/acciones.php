<?php
require_once '../../config.php';

// Habilitar depuración (quitar en producción)
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_GET['id_lista']) || !filter_var($_GET['id_lista'], FILTER_VALIDATE_INT)) {
    header("Location: administracion.php");
    exit;
}

$idLista = $_GET['id_lista'];
$db = getDbConnection();

// Obtener información de la lista
try {
    $stmtLista = $db->prepare("SELECT nombre_lista FROM tlistas WHERE Id_lista = :id");
    $stmtLista->bindParam(':id', $idLista, PDO::PARAM_INT);
    $stmtLista->execute();
    $lista = $stmtLista->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Error al obtener la lista: " . $e->getMessage();
    $lista = false;
}

if (!$lista) {
    header("Location: administracion.php?error=Lista no encontrada");
    exit;
}

// Inicialización de variables para el formulario de inserción
$mensaje = "";
$error = "";
$mostrarModalDuplicado = false;
$nombreDuplicado = "";
$idAccionDuplicada = 0;
$nombreAccion = "";
$estadoAccion = "1";

// Procesar el formulario de inserción
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['insertar_accion'])) {
    // Sanitizar nombre_accion
    $nombreAccion = trim(strip_tags($_POST['nombre_accion']));
    $nombreAccion = htmlspecialchars($nombreAccion, ENT_QUOTES, 'UTF-8');
    
    // Validar estado_accion
    $estadoAccion = $_POST['estado_accion'] === '0' ? '0' : '1';

    if (!empty($nombreAccion)) {
        try {
            // Verificar duplicados
            $stmt = $db->prepare("SELECT id_acciones, nombre_accion FROM tacciones WHERE nombre_accion = :nombre AND Id_lista = :id_lista");
            $stmt->bindParam(':nombre', $nombreAccion);
            $stmt->bindParam(':id_lista', $idLista, PDO::PARAM_INT);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($row) {
                $mostrarModalDuplicado = true;
                $nombreDuplicado = htmlspecialchars($nombreAccion);
                $idAccionDuplicada = $row['id_acciones'];
            } else {
                $db->beginTransaction();
                $stmt = $db->prepare("INSERT INTO tacciones (Id_lista, nombre_accion, estado_accion) VALUES (:id_lista, :nombre, :estado)");
                $stmt->bindParam(':id_lista', $idLista, PDO::PARAM_INT);
                $stmt->bindParam(':nombre', $nombreAccion);
                $stmt->bindParam(':estado', $estadoAccion);

                if ($stmt->execute()) {
                    $db->commit();
                    $mensaje = "<div class='alert alert-success'>Acción registrada correctamente.</div>";
                    $nombreAccion = "";
                    $estadoAccion = "1";
                } else {
                    $db->rollBack();
                    $error = "<div class='alert alert-danger'>Error al registrar la acción: " . implode(" ", $stmt->errorInfo()) . "</div>";
                }
            }
        } catch (PDOException $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            $error = "<div class='alert alert-danger'>Error de base de datos: " . $e->getMessage() . "</div>";
        }
    } else {
        $error = "<div class='alert alert-danger'>El nombre de la acción es obligatorio.</div>";
    }
}

// Manejar confirmación de duplicado
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirmar_reemplazo'])) {
    $idAccionConfirmada = filter_var($_POST['id_accion_duplicada'], FILTER_VALIDATE_INT);
    $nombreAccion = trim(strip_tags($_POST['nombre_accion']));
    $nombreAccion = htmlspecialchars($nombreAccion, ENT_QUOTES, 'UTF-8');
    $estadoAccion = $_POST['estado_accion'] === '0' ? '0' : '1';

    if ($idAccionConfirmada > 0) {
        try {
            $db->beginTransaction();
            $stmtDelete = $db->prepare("DELETE FROM tacciones WHERE id_acciones = :id");
            $stmtDelete->bindParam(':id', $idAccionConfirmada, PDO::PARAM_INT);
            if ($stmtDelete->execute()) {
                // Insertar la nueva acción
                $stmtInsert = $db->prepare("INSERT INTO tacciones (Id_lista, nombre_accion, estado_accion) VALUES (:id_lista, :nombre, :estado)");
                $stmtInsert->bindParam(':id_lista', $idLista, PDO::PARAM_INT);
                $stmtInsert->bindParam(':nombre', $nombreAccion);
                $stmtInsert->bindParam(':estado', $estadoAccion);
                if ($stmtInsert->execute()) {
                    $db->commit();
                    $mensaje = "<div class='alert alert-success'>Acción reemplazada con éxito.</div>";
                    $nombreAccion = "";
                    $estadoAccion = "1";
                } else {
                    $db->rollBack();
                    $error = "<div class='alert alert-danger'>Error al reemplazar la acción: " . implode(" ", $stmtInsert->errorInfo()) . "</div>";
                }
            } else {
                $db->rollBack();
                $error = "<div class='alert alert-danger'>Error al eliminar la acción existente.</div>";
            }
        } catch (PDOException $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            $error = "<div class='alert alert-danger'>Error de base de datos: " . $e->getMessage() . "</div>";
        }
    } else {
        $error = "<div class='alert alert-danger'>Identificador de acción inválido.</div>";
    }
}

// Obtener acciones de la lista (después de posibles inserciones)
try {
    $stmtAcciones = $db->prepare("SELECT * FROM tacciones WHERE Id_lista = :id ORDER BY nombre_accion ASC");
    $stmtAcciones->bindParam(':id', $idLista, PDO::PARAM_INT);
    $stmtAcciones->execute();
    $acciones = $stmtAcciones->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "<div class='alert alert-danger'>Error al obtener acciones: " . $e->getMessage() . "</div>";
    $acciones = [];
}

// Manejar mensajes de eliminación
if (isset($_GET['borrado']) && $_GET['borrado'] === 'true') {
    $mensaje = "<div class='alert alert-success'>Acción borrada correctamente.</div>";
} elseif (isset($_GET['borrado']) && $_GET['borrado'] === 'false') {
    $mensaje = "<div class='alert alert-danger'>Error al borrar la acción.</div>";
}

// Cerrar conexión
$db = null;
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acciones de <?php echo htmlspecialchars($lista['nombre_lista']); ?></title>
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
                <li><a href="../cingles/index.php"><i class="fas fa-language"></i> Cingles</a></li>
                <li class="active"><a href="index.php"><i class="fas fa-list"></i> XList</a></li>
                <li><a href="../bitacore/pass.php"><i class="fas fa-book"></i> Bitacore</a></li>
                <li><a href="../motivaus/index.php"><i class="fas fa-bolt"></i> Motivaus</a></li>
            </ul>
        </nav>

        <main class="content">
            <header>
                <div class="header-content">
                    <h1><i class="fas fa-tasks"></i> Acciones de <?php echo htmlspecialchars($lista['nombre_lista']); ?></h1>
                    <div class="date-time" id="dateTime">Cargando...</div>
                </div>
            </header>

            <div class="cingles-container">
                <div class="modules-grid">
                    <button class="module-card" onclick="mostrarModalInsertar()">
                        <div class="module-icon"><i class="fas fa-plus"></i></div>
                        <h3>Nueva Acción</h3>
                        <p>Agregar una nueva acción a la lista</p>
                    </button>
                </div>

                <div class="table-container">
                    <h2 class="form-title">Acciones Registradas</h2>
                    <?php if (!empty($mensaje)): ?>
                        <?php echo $mensaje; ?>
                    <?php endif; ?>
                    <?php if (!empty($error)): ?>
                        <?php echo $error; ?>
                    <?php endif; ?>
                    <?php if (empty($acciones)): ?>
                        <div class="alert alert-warning">No hay acciones registradas para esta lista.</div>
                    <?php else: ?>
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nombre</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($acciones as $accion): ?>
                                    <tr>
                                        <td><?php echo $accion['id_acciones']; ?></td>
                                        <td><?php echo htmlspecialchars($accion['nombre_accion']); ?></td>
                                        <td><?php echo $accion['estado_accion'] == '1' ? 'Activa' : 'Inactiva'; ?></td>
                                        <td class="actions">
                                            <button class="action-btn delete" onclick="mostrarModalBorrar(<?php echo $accion['id_acciones']; ?>, '<?php echo htmlspecialchars($accion['nombre_accion']); ?>')"><i class="fas fa-trash-alt"></i></button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                    <a href="administracion.php" class="back-link"><i class="fas fa-arrow-left"></i> Volver</a>
                </div>
            </div>

            <!-- Modal para insertar acción -->
            <div id="insertarModal" class="modal">
                <div class="modal-content">
                    <span class="close-button" onclick="cerrarModalInsertar()">×</span>
                    <h3 style="color: var(--secondary-color); margin-bottom: 15px; font-family: 'Share Tech Mono', monospace;">NUEVA ACCIÓN</h3>
                    <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']) . '?id_lista=' . $idLista; ?>" autocomplete="off">
                        <input type="hidden" name="insertar_accion" value="1">
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
            </div>

            <!-- Modal para confirmar duplicado -->
            <div id="duplicateModal" class="modal" style="<?php echo $mostrarModalDuplicado ? 'display:block;' : ''; ?>">
                <div class="modal-content">
                    <span class="close-button" onclick="cerrarModalDuplicado()">×</span>
                    <h3 style="color: var(--secondary-color); margin-bottom: 15px; font-family: 'Share Tech Mono', monospace;">ACCIÓN DUPLICADA DETECTADA</h3>
                    <p>La acción "<strong class="resalt-text"><?php echo $nombreDuplicado; ?></strong>" ya está registrada en esta lista.</p>
                    <p style="margin-top: 10px;">Selecciona una acción:</p>
                    <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']) . '?id_lista=' . $idLista; ?>">
                        <input type="hidden" name="confirmar_reemplazo" value="1">
                        <input type="hidden" name="id_accion_duplicada" value="<?php echo $idAccionDuplicada; ?>">
                        <input type="hidden" name="nombre_accion" value="<?php echo htmlspecialchars($nombreAccion); ?>">
                        <input type="hidden" name="estado_accion" value="<?php echo $estadoAccion; ?>">
                        <div class="modal-buttons">
                            <button type="submit" class="modal-button-confirm"><i class="fas fa-sync-alt"></i> Reemplazar</button>
                            <button type="button" class="modal-button-cancel" onclick="cerrarModalDuplicado()"><i class="fas fa-times"></i> Cancelar</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Modal para borrar acción -->
            <div id="borrarModal" class="modal">
                <div class="modal-content">
                    <span class="close-button" onclick="cerrarModalBorrar()">×</span>
                    <p>¿Estás seguro de que deseas borrar la acción "<strong id="accionABorrar" class="resalt-text"></strong>"?</p>
                    <div class="modal-buttons">
                        <button class="modal-button-confirm" onclick="borrarAccion()">Borrar</button>
                        <button class="modal-button-cancel" onclick="cerrarModalBorrar()">Cancelar</button>
                    </div>
                </div>
            </div>

            <form id="formBorrar" method="POST" action="eliminar_accion.php" style="display: none;">
                <input type="hidden" name="id_acciones_borrar" id="idAccionBorrar">
                <input type="hidden" name="id_lista" value="<?php echo $idLista; ?>">
            </form>
        </main>
    </div>

    <script src="../../js/scripts.js"></script>
    <script>
        var modalBorrar = document.getElementById("borrarModal");
        var accionABorrarSpan = document.getElementById("accionABorrar");
        var idAccionBorrarInput = document.getElementById("idAccionBorrar");
        var formBorrar = document.getElementById("formBorrar");
        var modalInsertar = document.getElementById("insertarModal");
        var modalDuplicado = document.getElementById("duplicateModal");

        function mostrarModalBorrar(id, nombre) {
            idAccionBorrarInput.value = id;
            accionABorrarSpan.textContent = nombre;
            modalBorrar.style.display = "block";
        }

        function cerrarModalBorrar() {
            modalBorrar.style.display = "none";
        }

        function borrarAccion() {
            formBorrar.submit();
        }

        function mostrarModalInsertar() {
            modalInsertar.style.display = "block";
            document.getElementById("nombre_accion").focus();
        }

        function cerrarModalInsertar() {
            modalInsertar.style.display = "none";
        }

        function cerrarModalDuplicado() {
            modalDuplicado.style.display = "none";
        }

        window.onclick = function(event) {
            if (event.target == modalBorrar) {
                cerrarModalBorrar();
            } else if (event.target == modalInsertar) {
                cerrarModalInsertar();
            } else if (event.target == modalDuplicado) {
                cerrarModalDuplicado();
            }
        }

        // Efectos de enfoque para inputs
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
    </script>
</body>
</html>