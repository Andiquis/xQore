<?php
require_once '../../config.php';

// Configuración inicial
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Conexión a la base de datos
$db = getDbConnection();

// Procesar formulario
$errorMessage = '';
$successMessage = '';

// Procesar la eliminación por AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $id = isset($_POST['id']) ? filter_var($_POST['id'], FILTER_VALIDATE_INT) : null;
    if ($id && $id > 0) {
        try {
            $stmt = $db->prepare("DELETE FROM tmotivaus WHERE Id_motivaus = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            if ($stmt->rowCount() > 0) {
                echo json_encode(['status' => 'success', 'message' => 'Frase eliminada con éxito.']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'No se encontró la frase con ID ' . htmlspecialchars($id) . '.']);
            }
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => 'Error al eliminar la frase: ' . htmlspecialchars($e->getMessage())]);
        }
        exit;
    } else {
        echo json_encode(['status' => 'error', 'message' => 'ID inválido para eliminación.']);
        exit;
    }
}

// Procesar formularios normales
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_phrase'])) {
        // Agregar nueva frase
        $frase = isset($_POST['frase']) ? trim($_POST['frase']) : '';
        if (!empty($frase)) {
            try {
                $stmt = $db->prepare("INSERT INTO tmotivaus (Frase, estado) VALUES (:frase, '1')");
                $stmt->bindParam(':frase', $frase);
                $stmt->execute();
                $successMessage = 'Frase agregada con éxito.';
            } catch (PDOException $e) {
                $errorMessage = 'Error al agregar la frase: ' . htmlspecialchars($e->getMessage());
            }
        } else {
            $errorMessage = 'La frase no puede estar vacía.';
        }
    } elseif (isset($_POST['edit_phrase'])) {
        // Editar frase existente
        $id = isset($_POST['id_motivaus']) ? filter_var($_POST['id_motivaus'], FILTER_VALIDATE_INT) : null;
        $frase = isset($_POST['frase']) ? trim($_POST['frase']) : '';
        $estado = isset($_POST['estado']) ? ($_POST['estado'] === '1' ? '1' : '0') : '1';
        if ($id && !empty($frase)) {
            try {
                $stmt = $db->prepare("UPDATE tmotivaus SET Frase = :frase, estado = :estado WHERE Id_motivaus = :id");
                $stmt->bindParam(':frase', $frase);
                $stmt->bindParam(':estado', $estado);
                $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                $stmt->execute();
                $successMessage = 'Frase actualizada con éxito.';
            } catch (PDOException $e) {
                $errorMessage = 'Error al actualizar la frase: ' . htmlspecialchars($e->getMessage());
            }
        } else {
            $errorMessage = 'La frase no puede estar vacía o ID inválido.';
        }
    }
}

// Obtener todas las frases
try {
    $stmt = $db->prepare("SELECT Id_motivaus, Frase, fecha_registro, estado FROM tmotivaus ORDER BY fecha_registro DESC");
    $stmt->execute();
    $frases = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $errorMessage = 'Error al obtener las frases: ' . htmlspecialchars($e->getMessage());
}

// Cerrar conexión
$db = null;
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administrar Frases Motivacionales - XQ0R3</title>
    <link rel="stylesheet" href="../../css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        /* Estilos adicionales para compatibilidad con el tema cyberpunk */
        .modal-content {
            background-color: var(--bg-darker);
            border: 1px solid var(--primary-color);
            box-shadow: 0 0 20px var(--shadow-color);
            font-family: 'Share Tech Mono', monospace;
        }
        .modal-content h2 {
            color: var(--secondary-color);
            margin-bottom: 20px;
        }
        .modal-content select {
            width: 100%;
            padding: 10px;
            background-color: rgba(0, 0, 0, 0.3);
            border: 1px solid var(--border-color);
            color: var(--text-color);
            font-family: 'Share Tech Mono', monospace;
            border-radius: 3px;
        }
        .modal-content select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 10px var(--shadow-color);
        }
        .modal-content p {
            color: var(--text-color);
            margin-bottom: 20px;
        }
        #notificacion {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px;
            border-radius: 5px;
            color: #fff;
            font-family: 'Share Tech Mono', monospace;
            z-index: 1000;
            display: none;
            max-width: 300px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.7);
        }
        .alert-success {
            background-color: rgba(40, 167, 69, 0.9);
            border-left: 5px solid #28a745;
        }
        .alert-danger {
            background-color: rgba(220, 53, 69, 0.9);
            border-left: 5px solid #dc3545;
        }
        .alert-warning {
            background-color: rgba(255, 193, 7, 0.09);
            border-left: 5px solid #ffc107;
        }
        .action-btn.delete {
            cursor: pointer;
        }
        .action-btn.delete:hover {
            background-color: #dc3545;
        }
    </style>
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
                <li><a href="../xlist/index.php"><i class="fas fa-list"></i> XList</a></li>
                <li><a href="../bitacore/pass.php"><i class="fas fa-book"></i> Bitacore</a></li>
                <li class="active"><a href="index.php"><i class="fas fa-bolt"></i> Motivaus</a></li>
                
            </ul>
        </nav>

        <main class="content">
            <header>
                <div class="header-content">
                    <h1><i class="fas fa-bolt"></i> Administrar Frases Motivacionales</h1>
                    <div class="date-time" id="dateTime">Cargando...</div>
                </div>
            </header>

            <!-- Contenedor para notificaciones dinamicas -->
            <div id="notificacion"></div>

            <div class="form-container">
                <h2 class="form-title">Agregar Nueva Frase</h2>
                <form method="POST" id="addForm" onsubmit="return validateForm()">
                    <div class="form-group">
                        
                        <input type="text" name="frase" id="frase" class="form-control" required maxlength="255">
                    </div>
                    <button type="submit" name="add_phrase" class="btn"><i class="fas fa-plus"></i> Agregar</button>
                </form>
            </div>

            <div class="table-container">
                <h2 class="form-title">Frases Existentes</h2>
                <?php if ($frases): ?>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Frase</th>
                                <th>Fecha de Registro</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($frases as $frase): ?>
                                <tr id="frase-<?php echo htmlspecialchars($frase['Id_motivaus']); ?>">
                                    <td><?php echo htmlspecialchars($frase['Id_motivaus']); ?></td>
                                    <td><?php echo htmlspecialchars($frase['Frase']); ?></td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($frase['fecha_registro'])); ?></td>
                                    <td><?php echo $frase['estado'] === '1' ? 'Activo' : 'Inactivo'; ?></td>
                                    <td class="actions">
                                        <button onclick="editPhrase(<?php echo $frase['Id_motivaus']; ?>, '<?php echo htmlspecialchars(addslashes($frase['Frase'])); ?>', '<?php echo $frase['estado']; ?>')" class="action-btn edit"><i class="fas fa-edit"></i></button>
                                        <button onclick="deletePhrase(<?php echo $frase['Id_motivaus']; ?>)" class="action-btn delete"><i class="fas fa-trash"></i></button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p class="alert alert-warning">No hay frases motivacionales disponibles.</p>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <!-- Modal para editar frase -->
    <div class="modal" id="editPopup">
        <div class="modal-content">
            <span class="close-button" onclick="closeEditPopup()">×</span>
            <h2>Editar Frase</h2>
            <form method="POST" id="editForm" onsubmit="return validateEditForm()">
                <input type="hidden" name="id_motivaus" id="edit_id">
                <div class="form-group">
                    <label for="edit_frase">Frase Motivacional</label>
                    <input type="text" name="frase" id="edit_frase" class="form-control" required maxlength="255">
                </div>
                <div class="form-group">
                    <label for="edit_estado">Estado</label>
                    <select name="estado" id="edit_estado" class="form-control">
                        <option value="1">Activo</option>
                        <option value="0">Inactivo</option>
                    </select>
                </div>
                <div class="modal-buttons">
                    <button type="submit" name="edit_phrase" class="modal-button-confirm">Guardar</button>
                    <button type="button" onclick="closeEditPopup()" class="modal-button-cancel">Cancelar</button>
                </div>
            </form>
        </div>
    </div>

    <script src="../../js/scripts.js"></script>
    <script>
        // Cargar mensajes dinámicamente
        window.onload = function() {
            <?php if (isset($successMessage)): ?>
                mostrarNotificacion('<?php echo addslashes($successMessage); ?>', 'success');
            <?php endif; ?>
            <?php if (isset($errorMessage)): ?>
                mostrarNotificacion('<?php echo addslashes($errorMessage); ?>', 'danger');
            <?php endif; ?>
        };

        function mostrarNotificacion(mensaje, tipo) {
            const notificacion = document.getElementById('notificacion');
            notificacion.textContent = mensaje;
            notificacion.className = 'alert-' + tipo;
            notificacion.style.display = 'block';
            
            // Ocultar después de 5 segundos
            setTimeout(function() {
                notificacion.style.display = 'none';
            }, 5000);
        }

        function editPhrase(id, frase, estado) {
            document.getElementById('edit_id').value = id;
            document.getElementById('edit_frase').value = frase;
            document.getElementById('edit_estado').value = estado;
            document.getElementById('editPopup').style.display = 'block';
        }

        function closeEditPopup() {
            document.getElementById('editPopup').style.display = 'none';
        }

        function deletePhrase(id) {
            // Crear el objeto FormData para enviar con AJAX
            let formData = new FormData();
            formData.append('action', 'delete');
            formData.append('id', id);

            // Realizar solicitud AJAX
            fetch('index.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    // Eliminar la fila de la tabla inmediatamente
                    const row = document.getElementById('frase-' + id);
                    if (row) {
                        row.remove();
                    }
                    mostrarNotificacion(data.message, 'success');
                } else {
                    mostrarNotificacion(data.message, 'danger');
                }
            })
            .catch(error => {
                mostrarNotificacion('Error al procesar la solicitud: ' + error, 'danger');
            });
        }

        function validateForm() {
            const frase = document.getElementById('frase').value.trim();
            if (frase === '') {
                mostrarNotificacion('La frase no puede estar vacía.', 'danger');
                return false;
            }
            return true;
        }

        function validateEditForm() {
            const frase = document.getElementById('edit_frase').value.trim();
            if (frase === '') {
                mostrarNotificacion('La frase no puede estar vacía.', 'danger');
                return false;
            }
            return true;
        }

        function actualizarFechaHora() {
            const ahora = new Date();
            const opciones = { 
                weekday: 'long', 
                year: 'numeric', 
                month: 'long', 
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit'
            };
            document.getElementById('dateTime').textContent = ahora.toLocaleDateString('es-ES', opciones);
        }

        actualizarFechaHora();
        setInterval(actualizarFechaHora, 60000);
    </script>
</body>
</html>