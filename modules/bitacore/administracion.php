<?php
require_once '../../config.php';

// Configuración inicial
error_reporting(E_ALL); // Quitar en producción
ini_set('display_errors', 1);

// Conexión a la base de datos
$db = getDbConnection();

// Obtener todas las entradas de bitácora
$bitacoras = [];
$estadoFiltro = isset($_GET['estado']) ? $_GET['estado'] : 'all';
try {
    $query = "SELECT Id_bitacora, titulo, contenido, fecha_registro, estado FROM tbitacora";
    if ($estadoFiltro === '1' || $estadoFiltro === '0') {
        $query .= " WHERE estado = :estado";
    }
    $query .= " ORDER BY fecha_registro DESC";
    $stmt = $db->prepare($query);
    if ($estadoFiltro === '1' || $estadoFiltro === '0') {
        $stmt->bindParam(':estado', $estadoFiltro);
    }
    $stmt->execute();
    $bitacoras = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "<div class='alert alert-danger'>Error al obtener bitácoras: " . htmlspecialchars($e->getMessage()) . "</div>";
}

// Procesar solicitudes AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
    header('Content-Type: application/json');
    try {
        $db->beginTransaction();

        if (isset($_POST['accion'])) {
            switch ($_POST['accion']) {
                case 'crear':
                    $titulo = trim(strip_tags($_POST['titulo']));
                    $titulo = htmlspecialchars($titulo, ENT_QUOTES, 'UTF-8');
                    $contenido = trim(strip_tags($_POST['contenido']));
                    $contenido = preg_replace('/[\x00-\x09\x0B\x0C\x0E-\x1F]/', '', $contenido);
                    $contenido = htmlspecialchars($contenido, ENT_QUOTES, 'UTF-8');
                    $estado = '1';

                    if ($titulo && $contenido) {
                        $stmt = $db->prepare("INSERT INTO tbitacora (titulo, contenido, estado) VALUES (:titulo, :contenido, :estado)");
                        $stmt->bindParam(':titulo', $titulo);
                        $stmt->bindParam(':contenido', $contenido);
                        $stmt->bindParam(':estado', $estado);
                        $stmt->execute();
                        $db->commit();
                        echo json_encode(['success' => true, 'id' => $db->lastInsertId(), 'titulo' => $titulo, 'contenido' => $contenido, 'estado' => $estado, 'fecha_registro' => date('Y-m-d H:i:s')]);
                    } else {
                        echo json_encode(['success' => false, 'error' => 'Título y contenido son obligatorios.']);
                    }
                    break;

                case 'editar':
                    $id = filter_var($_POST['id'], FILTER_VALIDATE_INT);
                    $titulo = trim(strip_tags($_POST['titulo']));
                    $titulo = htmlspecialchars($titulo, ENT_QUOTES, 'UTF-8');
                    $contenido = trim(strip_tags($_POST['contenido']));
                    $contenido = preg_replace('/[\x00-\x09\x0B\x0C\x0E-\x1F]/', '', $contenido);
                    $contenido = htmlspecialchars($contenido, ENT_QUOTES, 'UTF-8');
                    $estado = $_POST['estado'] === '1' ? '1' : '0';

                    if ($id && $titulo && $contenido) {
                        $stmt = $db->prepare("UPDATE tbitacora SET titulo = :titulo, contenido = :contenido, estado = :estado WHERE Id_bitacora = :id");
                        $stmt->bindParam(':titulo', $titulo);
                        $stmt->bindParam(':contenido', $contenido);
                        $stmt->bindParam(':estado', $estado);
                        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                        $stmt->execute();
                        $db->commit();
                        echo json_encode(['success' => true, 'id' => $id, 'titulo' => $titulo, 'contenido' => $contenido, 'estado' => $estado]);
                    } else {
                        echo json_encode(['success' => false, 'error' => 'Datos inválidos o incompletos.']);
                    }
                    break;

                case 'eliminar':
                    $id = filter_var($_POST['id'], FILTER_VALIDATE_INT);
                    if ($id) {
                        $stmt = $db->prepare("DELETE FROM tbitacora WHERE Id_bitacora = :id");
                        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                        $stmt->execute();
                        $db->commit();
                        echo json_encode(['success' => true]);
                    } else {
                        echo json_encode(['success' => false, 'error' => 'ID inválido.']);
                    }
                    break;

                case 'cambiar_estado':
                    $id = filter_var($_POST['id'], FILTER_VALIDATE_INT);
                    $estado = $_POST['estado'] === '1' ? '0' : '1';
                    if ($id) {
                        $stmt = $db->prepare("UPDATE tbitacora SET estado = :estado WHERE Id_bitacora = :id");
                        $stmt->bindParam(':estado', $estado);
                        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                        $stmt->execute();
                        $db->commit();
                        echo json_encode(['success' => true, 'nuevo_estado' => $estado]);
                    } else {
                        echo json_encode(['success' => false, 'error' => 'ID inválido.']);
                    }
                    break;

                default:
                    echo json_encode(['success' => false, 'error' => 'Acción no válida.']);
            }
        } else {
            echo json_encode(['success' => false, 'error' => 'Acción no especificada.']);
        }
    } catch (PDOException $e) {
        if ($db->inTransaction()) {
            $db->rollBack();
        }
        echo json_encode(['success' => false, 'error' => 'Error de base de datos: ' . htmlspecialchars($e->getMessage())]);
    }
    exit;
}

// Cerrar conexión
$db = null;
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administración de Bitacore - XQ0R3</title>
    <link rel="stylesheet" href="../../css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link href="https://fonts.googleapis.com/css2?family=Cinzel+Decorative&family=Cinzel:wght@400;700&family=Uncial+Antiqua&family=Share+Tech+Mono&display=swap" rel="stylesheet">
    <style>
        /* Estilos específicos para Bitacore */
        .bitacore-container {
            background: url('../../images/parchment.png') repeat, linear-gradient(45deg, #1a1a1a, #2a2a2a);
            border: 3px solid #d4af37;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 0 20px rgba(212, 175, 55, 0.5), inset 0 0 10px rgba(0, 255, 255, 0.2);
            position: relative;
            overflow: hidden;
        }
        .bitacore-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle, rgba(0, 255, 255, 0.1), transparent);
            opacity: 0.3;
            pointer-events: none;
        }
        .papyrus-title {
            font-family: 'Uncial Antiqua', 'Cinzel', serif;
            color: #d4af37;
            text-shadow: 0 0 8px rgba(212, 175, 55, 0.7), 0 0 4px rgba(255, 0, 255, 0.5);
            text-align: center;
            font-size: 28px;
            position: relative;
            margin-bottom: 30px;
            letter-spacing: 2px;
            text-transform: uppercase;
        }
        .papyrus-title::after {
            content: 'Living Memory';
            font-family: 'Cinzel Decorative', serif;
            font-size: 12px;
            color: #00ffff;
            position: absolute;
            bottom: -15px;
            left: 50%;
            transform: translateX(-50%);
            text-shadow: 0 0 5px #ff00ff;
        }
        .papyrus-decoration {
            font-family: 'Cinzel Decorative', 'Cinzel', serif;
            color: #b8860b;
            text-align: center;
            text-shadow: 0 0 12px rgba(184, 134, 11, 0.8), 0 0 6px rgba(139, 69, 19, 0.5);
            font-size: 18px;
            margin: 15px auto;
            padding: 8px;
            background: linear-gradient(90deg, transparent, rgba(184, 134, 11, 0.4), transparent);
            border: 1px dashed rgba(184, 134, 11, 0.5);
            width: 60%;
            letter-spacing: 4px;
            box-shadow: inset 0 0 10px rgba(0, 0, 0, 0.3);
        }
        .table-container {
            background: rgba(0, 0, 0, 0.8);
            border: 2px solid #00ffff;
            border-radius: 8px;
            padding: 15px;
            box-shadow: 0 0 15px rgba(0, 255, 255, 0.5);
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            font-family: 'Share Tech Mono', monospace;
            color: var(--text-color);
        }
        .table th, .table td {
            padding: 10px;
            border-bottom: 1px solid #d4af37;
            text-align: left;
        }
        .table th {
            background: linear-gradient(45deg, #1a1a1a, #2a2a2a);
            color: #00ffff;
            text-shadow: 0 0 5px #ff00ff;
        }
        .table tr:hover {
            background: rgba(212, 175, 55, 0.1);
            cursor: pointer;
        }
        .estado-activo {
            color: #00ff00;
            text-shadow: 0 0 5px #00ff00;
        }
        .estado-inactivo {
            color: #ff0000;
            text-shadow: 0 0 5px #ff0000;
        }
        .action-btn {
            padding: 5px 10px;
            margin: 0 5px;
            background: linear-gradient(45deg, rgba(0, 255, 255, 0.2), rgba(255, 0, 255, 0.2));
            color: var(--text-color);
            border: 1px solid #d4af37;
            border-radius: 3px;
            font-family: 'Share Tech Mono', monospace;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        .action-btn:hover {
            background: linear-gradient(45deg, rgba(0, 255, 255, 0.4), rgba(255, 0, 255, 0.4));
            box-shadow: 0 0 10px rgba(212, 175, 55, 0.7);
            transform: translateY(-2px);
            animation: glitch 0.3s linear;
        }
        .action-btn.delete {
            background: linear-gradient(45deg, rgba(255, 0, 0, 0.2), rgba(255, 50, 50, 0.2));
            border-color: #ff0000;
        }
        .action-btn.delete:hover {
            background: linear-gradient(45deg, rgba(255, 50, 50, 0.4), rgba(255, 100, 100, 0.4));
            box-shadow: 0 0 10px rgba(255, 0, 0, 0.7);
        }
        .add-button {
            display: inline-flex;
            align-items: center;
            padding: 10px 20px;
            background: linear-gradient(45deg, rgba(212, 175, 55, 0.2), rgba(0, 255, 255, 0.2));
            color: var(--text-color);
            border: 2px solid #d4af37;
            border-radius: 5px;
            font-family: 'Share Tech Mono', monospace;
            font-size: 14px;
            margin-bottom: 20px;
            transition: all 0.3s ease;
            box-shadow: 0 0 10px rgba(212, 175, 55, 0.5);
            cursor: pointer;
        }
        .add-button:hover {
            background: linear-gradient(45deg, rgba(212, 175, 55, 0.4), rgba(0, 255, 255, 0.4));
            box-shadow: 0 0 15px rgba(0, 255, 255, 0.7);
            transform: translateY(-2px);
            animation: glitch 0.3s linear;
        }
        .add-button i {
            margin-right: 5px;
        }
        .filter-container {
            margin-bottom: 20px;
            display: flex;
            align-items: center;
        }
        .filter-container label {
            font-family: 'Share Tech Mono', monospace;
            color: #00ffff;
            margin-right: 10px;
        }
        .filter-container select {
            padding: 5px;
            background: rgba(0, 0, 0, 0.5);
            border: 1px solid #d4af37;
            color: var(--text-color);
            font-family: 'Share Tech Mono', monospace;
            border-radius: 3px;
        }
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
            border: 3px solid #d4af37;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(212, 175, 55, 0.5), inset 0 0 10px rgba(0, 255, 255, 0.2);
            width: 90%;
            max-width: 500px;
            font-family: 'Share Tech Mono', monospace;
            color: var(--text-color);
        }
        .modal-content h2 {
            font-family: 'Cinzel', serif;
            color: #d4af37;
            text-shadow: 0 0 10px #00ffff;
            margin-bottom: 20px;
        }
        .modal-content label {
            color: #00ffff;
            margin-bottom: 5px;
            display: block;
        }
        .modal-content input[type="text"],
        .modal-content textarea,
        .modal-content select {
            width: 100%;
            padding: 8px;
            margin-bottom: 15px;
            background: rgba(0, 0, 0, 0.5);
            border: 1px solid #d4af37;
            color: var(--text-color);
            font-family: 'Share Tech Mono', monospace;
            border-radius: 3px;
            transition: all 0.3s ease;
        }
        .modal-content textarea {
            height: 150px;
            resize: vertical;
        }
        .modal-content input:focus,
        .modal-content textarea:focus,
        .modal-content select:focus {
            border-color: #00ffff;
            box-shadow: 0 0 10px rgba(0, 255, 255, 0.5);
            outline: none;
        }
        .modal-content .button-container {
            display: flex;
            justify-content: space-between;
        }
        .modal-content button {
            padding: 10px 20px;
            background: linear-gradient(45deg, rgba(212, 175, 55, 0.2), rgba(0, 255, 255, 0.2));
            color: var(--text-color);
            border: 1px solid #d4af37;
            border-radius: 5px;
            font-family: 'Share Tech Mono', monospace;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .modal-content button:hover {
            background: linear-gradient(45deg, rgba(212, 175, 55, 0.4), rgba(0, 255, 255, 0.4));
            box-shadow: 0 0 10px rgba(0, 255, 255, 0.7);
            transform: translateY(-2px);
        }
        .modal-content button.cancel,
        .modal-content button.delete {
            background: linear-gradient(45deg, rgba(255, 0, 0, 0.2), rgba(255, 50, 50, 0.2));
            border-color: #ff0000;
        }
        .modal-content button.cancel:hover,
        .modal-content button.delete:hover {
            background: linear-gradient(45deg, rgba(255, 50, 50, 0.4), rgba(255, 100, 100, 0.4));
            box-shadow: 0 0 10px rgba(255, 0, 0, 0.7);
        }
        .modal-content .error-message {
            color: #ff0000;
            font-size: 12px;
            margin-top: 10px;
            display: none;
            text-shadow: 0 0 5px #ff0000;
        }
        .alert {
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 5px;
            font-family: 'Share Tech Mono', monospace;
            text-align: center;
        }
        .alert-danger {
            background: rgba(255, 0, 0, 0.2);
            border: 1px solid #ff0000;
            color: #ff0000;
            text-shadow: 0 0 5px #ff0000;
        }
        .alert-warning {
            background: rgba(212, 175, 55, 0.2);
            border: 1px solid #d4af37;
            color: #d4af37;
            text-shadow: 0 0 5px #d4af37;
        }

    </style>
</head>
<body>
    <div class="container">
        <nav class="sidebar">
            <div class="logo" data-text="XQ0R3">XQ0R3</div>
            <ul class="menu">
                <li><a href="../../index.php"><i class="fas fa-home"></i> Dashboard</a></li>
                <li><a href="../cingles/index.php"><i class="fas fa-language"></i> Cingles</a></li>
                <li><a href="../xlist/index.php"><i class="fas fa-list"></i> XList</a></li>
                <li class="active"><a href="index.php"><i class="fas fa-book"></i> Bitacore</a></li>
                <li><a href="../motivaus/index.php"><i class="fas fa-bolt"></i> Motivaus</a></li>
                
            </ul>
        </nav>

        <main class="content">
            <header>
                <div class="header-content">
                    <h1><i class="fas fa-book"></i> Bitacora</h1>
                    <div class="date-time" id="dateTime">Cargando...</div>
                </div>
            </header>

            <div class="bitacore-container">
                <h2 class="papyrus-title">Crónicas del Alma</h2>
                <div class="papyrus-decoration"><═════➳═════></div>
                <?php if (isset($error)): ?>
                    <?php echo $error; ?>
                <?php endif; ?>

                <div class="filter-container">
                    <label for="estadoFiltro">Filtrar por estado:</label>
                    <select id="estadoFiltro" onchange="filtrarBitacoras()">
                        <option value="all" <?php echo $estadoFiltro === 'all' ? 'selected' : ''; ?>>Todas</option>
                        <option value="1" <?php echo $estadoFiltro === '1' ? 'selected' : ''; ?>>Activas</option>
                        <option value="0" <?php echo $estadoFiltro === '0' ? 'selected' : ''; ?>>Inactivas</option>
                    </select>
                </div>

                <button class="add-button" onclick="openModal('crear')"><i class="fas fa-plus"></i> Nueva Registro</button>

                <div class="table-container">
                    <?php if (empty($bitacoras)): ?>
                        <div class="alert alert-warning">No hay entradas de bitácora registradas.</div>
                    <?php else: ?>
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Título</th>
                                    <th>Fecha</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="bitacorasTable">
                                <?php foreach ($bitacoras as $bitacora): ?>
                                    <tr data-id="<?php echo $bitacora['Id_bitacora']; ?>">
                                        <td><?php echo htmlspecialchars($bitacora['titulo']); ?></td>
                                        <td><?php echo date('d/m/Y H:i', strtotime($bitacora['fecha_registro'])); ?></td>
                                        <td class="<?php echo $bitacora['estado'] == '1' ? 'estado-activo' : 'estado-inactivo'; ?>" 
                                            >
                                            <?php echo $bitacora['estado'] == '1' ? 'Activa' : 'Inactiva'; ?>
                                        </td>
                                        <td class="actions">
                                            <a href="#" class="action-btn edit" 
                                               data-id="<?php echo $bitacora['Id_bitacora']; ?>" 
                                               data-titulo="<?php echo htmlspecialchars($bitacora['titulo'], ENT_QUOTES, 'UTF-8'); ?>" 
                                               data-contenido="<?php echo htmlspecialchars($bitacora['contenido'], ENT_QUOTES, 'UTF-8'); ?>" 
                                               data-estado="<?php echo $bitacora['estado']; ?>">
                                               <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="#" class="action-btn delete" 
                                               data-id="<?php echo $bitacora['Id_bitacora']; ?>" 
                                               data-titulo="<?php echo htmlspecialchars($bitacora['titulo'], ENT_QUOTES, 'UTF-8'); ?>">
                                               <i class="fas fa-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Modal para crear/editar entrada -->
            <div id="bitacoraModal" class="modal">
                <div class="modal-content">
                    <h2 id="modalTitle">Nueva Registro</h2>
                    <div class="papyrus-decoration"><═════➳═════></div>
                    <form id="bitacoraForm">
                        <input type="hidden" id="bitacoraId" name="id">
                        <input type="hidden" id="bitacoraAccion" name="accion" value="crear">
                        <label for="titulo">Título:</label>
                        <input type="text" id="titulo" name="titulo" required>
                        <label for="contenido">Contenido:</label>
                        <textarea id="contenido" name="contenido" required></textarea>
                        <label for="estado">Estado:</label>
                        <select id="estado" name="estado">
                            <option value="1">Activa</option>
                            <option value="0">Inactiva</option>
                        </select>
                        <div class="button-container">
                            <button type="submit">Guardar</button>
                            <button type="button" class="cancel" onclick="closeModal()">Cancelar</button>
                        </div>
                        <div id="errorMessage" class="error-message"></div>
                    </form>
                </div>
            </div>
            <a href="index.php" class="back-link"><i class="fas fa-arrow-left"></i> Volver</a>

            <!-- Modal para confirmar eliminación -->
            <div id="deleteModal" class="modal">
                <div class="modal-content">
                    <h2>Confirmar Eliminación</h2>
                    <div class="papyrus-decoration"><═════➳═════></div>
                    <p>¿Estás seguro de eliminar la entrada "<span id="deleteTitle"></span>"?</p>
                    <div class="button-container">
                        <button class="delete" onclick="eliminarBitacora()">Eliminar</button>
                        <button class="cancel" onclick="closeDeleteModal()">Cancelar</button>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="../../js/scripts.js"></script>
    <script>
        // Variables globales
        let deleteId = null;

        // Función para escapar completamente una cadena para uso en HTML
        function escapeHtml(str) {
            const div = document.createElement('div');
            div.textContent = str;
            return div.innerHTML;
        }

        // Función para abrir el modal (crear/editar)
        function openModal(accion, id = null, titulo = '', contenido = '', estado = '1') {
            const modal = document.getElementById('bitacoraModal');
            const form = document.getElementById('bitacoraForm');
            const modalTitle = document.getElementById('modalTitle');
            const bitacoraId = document.getElementById('bitacoraId');
            const bitacoraAccion = document.getElementById('bitacoraAccion');
            const inputTitulo = document.getElementById('titulo');
            const inputContenido = document.getElementById('contenido');
            const selectEstado = document.getElementById('estado');
            const errorMessage = document.getElementById('errorMessage');

            modal.style.display = 'flex';
            modalTitle.textContent = accion === 'crear' ? 'Nueva Entrada' : 'Editar Entrada';
            bitacoraAccion.value = accion;
            bitacoraId.value = id || '';
            
            // Asignar valores directamente (sin decodeURIComponent, ya que usamos htmlspecialchars)
            inputTitulo.value = titulo;
            inputContenido.value = contenido;
            selectEstado.value = estado;
            
            errorMessage.style.display = 'none';
            
            // Poner foco en el título
            setTimeout(() => inputTitulo.focus(), 100);
        }

        // Función para cerrar el modal
        function closeModal() {
            document.getElementById('bitacoraModal').style.display = 'none';
            document.getElementById('bitacoraForm').reset();
            document.getElementById('errorMessage').style.display = 'none';
        }

        // Función para abrir el modal de confirmación de eliminación
        function openDeleteModal(id, titulo) {
            deleteId = id;
            document.getElementById('deleteTitle').textContent = titulo; // Sin decodeURIComponent
            document.getElementById('deleteModal').style.display = 'flex';
        }

        // Función para cerrar el modal de confirmación de eliminación
        function closeDeleteModal() {
            deleteId = null;
            document.getElementById('deleteModal').style.display = 'none';
        }

        // Función para filtrar bitácoras por estado
        function filtrarBitacoras() {
            const estado = document.getElementById('estadoFiltro').value;
            window.location.href = `index.php?estado=${estado}`;
        }

        // Función para cambiar el estado de una bitácora
        function cambiarEstado(id, estadoActual) {
            const nuevoEstado = estadoActual === '1' ? '0' : '1';
            const formData = new FormData();
            formData.append('accion', 'cambiar_estado');
            formData.append('id', id);
            formData.append('estado', estadoActual);

            fetch(window.location.href, {
                method: 'POST',
                body: formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const row = document.querySelector(`tr[data-id="${id}"]`);
                    const estadoCell = row.querySelector('td:nth-child(3)');
                    estadoCell.textContent = data.nuevo_estado === '1' ? 'Activa' : 'Inactiva';
                    estadoCell.className = data.nuevo_estado === '1' ? 'estado-activo' : 'estado-inactivo';
                    estadoCell.setAttribute(' onclick', `cambiarEstado(${id}, '${data.nuevo_estado}')`);
                } else {
                    alert(data.error || 'Error al cambiar el estado.');
                }
            })
            .catch(error => alert('Error en la solicitud: ' + error.message));
        }

        // Función para eliminar una bitácora
        function eliminarBitacora() {
            if (!deleteId) return;

            const formData = new FormData();
            formData.append('accion', 'eliminar');
            formData.append('id', deleteId);

            fetch(window.location.href, {
                method: 'POST',
                body: formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.querySelector(`tr[data-id="${deleteId}"]`).remove();
                    closeDeleteModal();
                    if (!document.querySelector('#bitacorasTable tr')) {
                        document.getElementById('bitacorasTable').innerHTML = '<tr><td colspan="4" class="alert alert-warning">No hay entradas de bitácora registradas.</td></tr>';
                    }
                } else {
                    alert(data.error || 'Error al eliminar la entrada.');
                }
            })
            .catch(error => alert('Error en la solicitud: ' + error.message));
        }

        // Event listener para el envío del formulario
        document.getElementById('bitacoraForm')?.addEventListener('submit', function(event) {
            event.preventDefault();
            const form = this;
            const inputContenido = document.getElementById('contenido');
            const errorMessage = document.getElementById('errorMessage');

            let contenido = inputContenido.value;
            contenido = contenido.replace(/[\x00-\x09\x0B\x0C\x0E-\x1F]/g, '');
            inputContenido.value = contenido;

            const formData = new FormData(form);

            fetch(window.location.href, {
                method: 'POST',
                body: formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    closeModal();
                    if (formData.get('accion') === 'crear') {
                        const tbody = document.getElementById('bitacorasTable');
                        if (tbody.querySelector('.alert')) {
                            tbody.innerHTML = '';
                        }
                        const row = document.createElement('tr');
                        row.setAttribute('data-id', data.id);
                        
                        row.innerHTML = `
                            <td>${escapeHtml(data.titulo)}</td>
                            <td>${new Date(data.fecha_registro).toLocaleString('es-ES', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' })}</td>
                            <td class="estado-activo">Activa</td>
                            <td class="actions">
                                <a href="#" class="action-btn edit" data-id="${data.id}" data-titulo="${escapeHtml(data.titulo)}" data-contenido="${escapeHtml(data.contenido)}" data-estado="1"><i class="fas fa-edit"></i></a>
                                <a href="#" class="action-btn delete" data-id="${data.id}" data-titulo="${escapeHtml(data.titulo)}"><i class="fas fa-trash"></i></a>
                            </td>
                        `;
                        tbody.insertBefore(row, tbody.firstChild);
                        
                        addButtonEventListeners(row);
                    } else {
                        const row = document.querySelector(`tr[data-id="${formData.get('id')}"]`);
                        row.querySelector('td:nth-child(1)').textContent = formData.get('titulo');
                        const estadoCell = row.querySelector('td:nth-child(3)');
                        estadoCell.textContent = formData.get('estado') === '1' ? 'Activa' : 'Inactiva';
                        estadoCell.className = formData.get('estado') === '1' ? 'estado-activo' : 'estado-inactivo';
                        
                        const editButton = row.querySelector('.action-btn.edit');
                        editButton.dataset.titulo = escapeHtml(formData.get('titulo'));
                        editButton.dataset.contenido = escapeHtml(formData.get('contenido'));
                        editButton.dataset.estado = formData.get('estado');
                        
                        const deleteButton = row.querySelector('.action-btn.delete');
                        deleteButton.dataset.titulo = escapeHtml(formData.get('titulo'));
                    }
                } else {
                    errorMessage.textContent = data.error || 'Error al guardar la entrada.';
                    errorMessage.style.display = 'block';
                }
            })
            .catch(error => {
                errorMessage.textContent = 'Error en la solicitud: ' + error.message;
                errorMessage.style.display = 'block';
            });
        });

        // Función para agregar event listeners a los botones de acción
        function addButtonEventListeners(container) {
            container.querySelectorAll('.action-btn.edit').forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    const id = this.dataset.id;
                    const titulo = this.dataset.titulo; // Sin decodeURIComponent
                    const contenido = this.dataset.contenido; // Sin decodeURIComponent
                    const estado = this.dataset.estado;
                    openModal('editar', id, titulo, contenido, estado);
                });
            });

            container.querySelectorAll('.action-btn.delete').forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    const id = this.dataset.id;
                    const titulo = this.dataset.titulo; // Sin decodeURIComponent
                    openDeleteModal(id, titulo);
                });
            });
        }

        // Event listeners para cerrar modales al hacer clic fuera
        document.getElementById('bitacoraModal')?.addEventListener('click', function(event) {
            if (event.target === this) {
                closeModal();
            }
        });

        document.getElementById('deleteModal')?.addEventListener('click', function(event) {
            if (event.target === this) {
                closeDeleteModal();
            }
        });

        // Inicializar event listeners para los botones existentes
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('#bitacorasTable tr').forEach(row => {
                addButtonEventListeners(row);
            });
        });
    </script>
</body>
</html>