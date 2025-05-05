<?php
require_once '../../config.php';

// Configuración inicial
error_reporting(E_ALL); // Quitar en producción
ini_set('display_errors', 1);

// Inicializar contador
$contador = 1;

// Conexión a la base de datos
$db = getDbConnection();

// Obtener la última lista
$ultimaLista = false;
$error = '';
try {
    $stmt = $db->query("SELECT * FROM tlistas ORDER BY Id_lista DESC LIMIT 1");
    $ultimaLista = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "<div class='alert alert-danger'>Error al obtener la última lista: " . htmlspecialchars($e->getMessage()) . "</div>";
}

// Obtener acciones de la última lista
$accionesUltimaLista = [];
if ($ultimaLista) {
    try {
        $stmtAcciones = $db->prepare("SELECT id_acciones, nombre_accion, estado_accion FROM tacciones WHERE Id_lista = :id ORDER BY nombre_accion DESC");
        $stmtAcciones->bindParam(':id', $ultimaLista['Id_lista'], PDO::PARAM_INT);
        $stmtAcciones->execute();
        $accionesUltimaLista = $stmtAcciones->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $error = "<div class='alert alert-danger'>Error al obtener acciones: " . htmlspecialchars($e->getMessage()) . "</div>";
    }
}

// Procesar cambio de estado de una acción
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cambiar_estado_accion'])) {
    $idAccion = filter_var($_POST['id_accion'], FILTER_VALIDATE_INT);
    $nuevoEstado = $_POST['estado_accion'] === '1' ? '0' : '1';

    try {
        $db->beginTransaction();
        $stmt = $db->prepare("UPDATE tacciones SET estado_accion = :estado WHERE id_acciones = :id");
        $stmt->bindParam(':estado', $nuevoEstado);
        $stmt->bindParam(':id', $idAccion, PDO::PARAM_INT);
        if ($stmt->execute()) {
            $db->commit();
        } else {
            $db->rollBack();
            $error = "<div class='alert alert-danger'>Error al actualizar el estado.</div>";
        }
    } catch (PDOException $e) {
        if ($db->inTransaction()) {
            $db->rollBack();
        }
        $error = "<div class='alert alert-danger'>Error de base de datos: " . htmlspecialchars($e->getMessage()) . "</div>";
    }
}

// Procesar inserción de nueva acción
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['insertar_accion'])) {
    $idLista = filter_var($_POST['id_lista'], FILTER_VALIDATE_INT);
    $nombreAccion = trim(strip_tags($_POST['nombre_accion']));
    $nombreAccion = htmlspecialchars($nombreAccion, ENT_QUOTES, 'UTF-8');
    $estadoAccion = '0'; // Por defecto, activa

    if ($idLista && $nombreAccion) {
        try {
            $db->beginTransaction();
            $stmt = $db->prepare("INSERT INTO tacciones (Id_lista, nombre_accion, estado_accion) VALUES (:id_lista, :nombre, :estado)");
            $stmt->bindParam(':id_lista', $idLista, PDO::PARAM_INT);
            $stmt->bindParam(':nombre', $nombreAccion);
            $stmt->bindParam(':estado', $estadoAccion);
            if ($stmt->execute()) {
                $db->commit();
                // Enviar respuesta JSON para AJAX
                header('Content-Type: application/json');
                echo json_encode(['success' => true]);
                exit;
            } else {
                $db->rollBack();
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'error' => 'Error al insertar la acción.']);
                exit;
            }
        } catch (PDOException $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Error de base de datos: ' . htmlspecialchars($e->getMessage())]);
            exit;
        }
    } else {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'Nombre de la acción o ID de lista inválido.']);
        exit;
    }
}

// Cerrar conexión
$db = null;
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acciones de la Última Lista - XList</title>
    <link rel="stylesheet" href="../../css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" crossorigin="anonymous">
    <style>
        /* Estilos para filas, botón administrativo y botón de nueva acción */
        .accion-activa {
            background-color: rgba(0, 255, 0, 0.1);
            cursor: pointer;
        }
        .accion-inactiva {
            background-color: rgba(255, 0, 0, 0.1);
            cursor: pointer;
        }
        .table tr:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }
        .admin-button {
            display: inline-flex;
            align-items: center;
            padding: 10px 20px;
            background: linear-gradient(45deg, rgba(0, 255, 0, 0.2), rgba(0, 255, 255, 0.2));
            color: var(--text-color);
            text-decoration: none;
            font-size: 15px;
            font-family: 'Share Tech Mono', monospace;
            border: 2px solid var(--primary-color);
            border-radius: 4px;
            margin-top: 20px;
            transition: all 0.3s ease;
            box-shadow: 0 0 10px rgba(0, 255, 0, 0.5);
            position: relative;
            overflow: hidden;
        }
        .admin-button:hover {
            background: linear-gradient(45deg, rgba(0, 255, 255, 0.3), rgba(255, 0, 255, 0.3));
            border-color: var(--secondary-color);
            box-shadow: 0 0 15px rgba(0, 255, 255, 0.7);
            transform: translateY(-2px);
            animation: glitch 0.3s linear;
        }
        .add-action-button {
            display: inline-flex;
            align-items: center;
            padding: 6px 14px;
            background: linear-gradient(45deg, rgba(0, 255, 0, 0.2), rgba(0, 255, 255, 0.2));
            color: var(--text-color);
            text-decoration: none;
            font-size: 13px;
            font-family: 'Share Tech Mono', monospace;
            border: 2px solid var(--primary-color);
            border-radius: 3px;
            margin-bottom: 20px;
            transition: all 0.3s ease;
            box-shadow: 0 0 8px rgba(0, 255, 0, 0.5);
            position: relative;
            overflow: hidden;
            cursor: pointer;
        }
        .add-action-button:hover {
            background: linear-gradient(45deg, rgba(0, 255, 255, 0.3), rgba(255, 0, 255, 0.3));
            border-color: var(--secondary-color);
            box-shadow: 0 0 12px rgba(0, 255, 255, 0.7);
            transform: translateY(-2px);
            animation: glitch 0.3s linear;
        }
        .add-action-button i {
            margin-right: 4px;
            font-size: 12px;
        }
        .add-action-button.disabled {
            opacity: 0.5;
            cursor: not-allowed;
            pointer-events: none;
        }
        /* Estilos para el modal */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.7);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }
        .modal-content {
            background: linear-gradient(45deg, rgba(0, 0, 0, 0.9), rgba(20, 20, 20, 0.9));
            padding: 20px;
            border: 2px solid var(--primary-color);
            border-radius: 5px;
            box-shadow: 0 0 15px rgba(0, 255, 0, 0.7);
            width: 90%;
            max-width: 400px;
            font-family: 'Share Tech Mono', monospace;
            color: var(--text-color);
        }
        .modal-content h2 {
            margin-top: 0;
            color: var(--primary-color);
            text-shadow: 0 0 5px var(--primary-color);
        }
        .modal-content input[type="text"] {
            width: 100%;
            padding: 8px;
            margin: 10px 0;
            background: rgba(0, 0, 0, 0.5);
            border: 1px solid var(--primary-color);
            color: var(--text-color);
            font-family: 'Share Tech Mono', monospace;
            font-size: 14px;
            border-radius: 3px;
            transition: all 0.3s ease;
        }
        .modal-content input[type="text"]:focus {
            border-color: var(--secondary-color);
            box-shadow: 0 0 10px rgba(0, 255, 255, 0.5);
            outline: none;
        }
        .modal-content .button-container {
            display: flex;
            justify-content: space-between;
        }
        .modal-content button {
            padding: 8px 16px;
            background: linear-gradient(45deg, rgba(0, 255, 0, 0.2), rgba(0, 255, 255, 0.2));
            color: var(--text-color);
            border: 1px solid var(--primary-color);
            border-radius: 3px;
            font-family: 'Share Tech Mono', monospace;
            font-size: 13px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .modal-content button:hover {
            background: linear-gradient(45deg, rgba(0, 255, 255, 0.3), rgba(255, 0, 255, 0.3));
            box-shadow: 0 0 10px rgba(0, 255, 255, 0.7);
            transform: translateY(-2px);
        }
        .modal-content button.cancel {
            background: linear-gradient(45deg, rgba(255, 0, 0, 0.2), rgba(255, 50, 50, 0.2));
            border-color: var(--secondary-color);
        }
        .modal-content button.cancel:hover {
            background: linear-gradient(45deg, rgba(255, 50, 50, 0.3), rgba(255, 100, 100, 0.3));
            box-shadow: 0 0 10px rgba(255, 0, 0, 0.7);
        }
        .modal-content .error-message {
            color: var(--secondary-color);
            font-size: 12px;
            margin-top: 10px;
            display: none;
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
                <li class="active"><a href="index.php"><i class="fas fa-list"></i> XList</a></li>
                <li><a href="../bitacore/pass.php"><i class="fas fa-book"></i> Bitacore</a></li>
                <li><a href="../motivaus/index.php"><i class="fas fa-bolt"></i> Motivaus</a></li>
            </ul>
        </nav>

        <main class="content">
            <header>
                <div class="header-content">
                    <h1><i class="fas fa-tasks"></i> Lista - <?php echo $ultimaLista ? '' . htmlspecialchars($ultimaLista['nombre_lista']) . '' : ''; ?></h1>
                    <div class="date-time" id="dateTime">Cargando...</div>
                </div>
            </header>

            <div class="cingles-container">
                <div class="table-container">
                    <?php if ($ultimaLista): ?>
                        <button class="add-action-button" onclick="openModal()"><i class="fas fa-plus"></i> Nueva Acción</button>
                    <?php else: ?>
                        <button class="add-action-button disabled"><i class="fas fa-plus"></i> Nueva Acción</button>
                    <?php endif; ?>

                    <?php if ($error): ?>
                        <?php echo $error; ?>
                    <?php endif; ?>
                    <?php if (!$ultimaLista): ?>
                        <div class="alert alert-warning">No hay listas registradas para mostrar acciones.</div>
                    <?php elseif (empty($accionesUltimaLista)): ?>
                        <div class="alert alert-warning">No hay acciones registradas para la última lista.</div>
                    <?php else: ?>
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>N°</th>
                                    <th>Nombre</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($accionesUltimaLista as $accion): ?>
                                    <tr class="<?php echo $accion['estado_accion'] == '1' ? 'accion-activa' : 'accion-inactiva'; ?>" onclick="cambiarEstado(<?php echo $accion['id_acciones']; ?>, '<?php echo $accion['estado_accion']; ?>')">
                                        <td><?php echo $contador; ?></td>
                                        <td><?php echo htmlspecialchars($accion['nombre_accion']); ?></td>
                                    </tr>
                                    <?php $contador++; ?>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>

                <!-- Botón para redirigir al módulo administrativo -->
                <a href="administracion.php" class="admin-button"><i class="fas fa-cog"></i> Ir al Módulo Administrativo</a>
            </div>

            <!-- Modal para nueva acción -->
            <?php if ($ultimaLista): ?>
                <div id="actionModal" class="modal">
                    <div class="modal-content">
                        <h2>Nueva Acción</h2>
                        <form id="actionForm" method="POST">
                            <input type="hidden" name="insertar_accion" value="1">
                            <input type="hidden" name="id_lista" value="<?php echo $ultimaLista['Id_lista']; ?>">
                            <label for="nombre_accion">Nombre de la Acción:</label>
                            <input type="text" id="nombre_accion" name="nombre_accion" required>
                            <div class="button-container">
                                <button type="submit">Agregar</button>
                                <button type="button" class="cancel" onclick="closeModal()">Cancelar</button>
                            </div>
                            <div id="errorMessage" class="error-message"></div>
                        </form>
                    </div>
                </div>
            <?php endif; ?>
        </main>
    </div>

    <script src="../../js/scripts.js"></script>
    <script>
        function openModal() {
            const modal = document.getElementById('actionModal');
            const inputAccion = document.getElementById('nombre_accion');
            modal.style.display = 'flex';
            document.getElementById('errorMessage').style.display = 'none';
            document.getElementById('actionForm').reset();
            // Establecer foco en el input con un pequeño retraso
            if (inputAccion) {
                setTimeout(() => inputAccion.focus(), 100);
            }
        }

        function closeModal() {
            document.getElementById('actionModal').style.display = 'none';
            document.getElementById('actionForm').reset();
            document.getElementById('errorMessage').style.display = 'none';
        }

        function cambiarEstado(idAccion, estadoActual) {
            const nuevoEstado = estadoActual === '1' ? '0' : '1';
            const formData = new FormData();
            formData.append('cambiar_estado_accion', '1');
            formData.append('id_accion', idAccion);
            formData.append('estado_accion', estadoActual);

            fetch(window.location.href, {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (response.ok) {
                    location.reload();
                } else {
                    console.error('Error al actualizar el estado');
                }
            })
            .catch(error => console.error('Error en la solicitud:', error));
        }

        // Manejar el envío del formulario con AJAX
        document.getElementById('actionForm')?.addEventListener('submit', function(event) {
            event.preventDefault();
            const formData = new FormData(this);
            const errorMessage = document.getElementById('errorMessage');

            fetch(window.location.href, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    closeModal();
                    location.reload();
                } else {
                    errorMessage.textContent = data.error || 'Error al insertar la acción.';
                    errorMessage.style.display = 'block';
                }
            })
            .catch(error => {
                errorMessage.textContent = 'Error en la solicitud: ' + error.message;
                errorMessage.style.display = 'block';
            });
        });

        // Cerrar modal al hacer clic fuera
        document.getElementById('actionModal')?.addEventListener('click', function(event) {
            if (event.target === this) {
                closeModal();
            }
        });
    </script>
</body>
</html>