<?php
require_once '../../config.php';
$contador = 1;

// Configuración inicial
error_reporting(E_ALL); // Quitar en producción
ini_set('display_errors', 1);

// Conexión a la base de datos
$db = getDbConnection();

// Obtener todas las listas
$listas = [];
$error = '';
try {
    $stmt = $db->query("SELECT * FROM tlistas ORDER BY nombre_lista ASC");
    $listas = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "<div class='alert alert-danger'>Error al obtener listas: " . htmlspecialchars($e->getMessage()) . "</div>";
}

// Procesar mensajes de eliminación
$mensaje = '';
if (isset($_GET['borrado'])) {
    $mensaje = $_GET['borrado'] === 'true'
        ? "<div class='alert alert-success'>Lista borrada correctamente.</div>"
        : "<div class='alert alert-danger'>Error al borrar la lista.</div>";
}

// Cerrar conexión
$db = null;
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Módulo XList</title>
    <link rel="stylesheet" href="../../css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" crossorigin="anonymous">
    <style>
        /* Estilos para filas clicables y botón pequeño */
        .table-row-button {
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .table-row-button:hover {
            background-color: rgba(0, 255, 0, 0.2);
        }
        .small-btn {
    display: inline-flex;
    align-items: center;
    padding: 6px 14px;
    background: linear-gradient(45deg, rgba(0, 255, 0, 0.2), rgba(0, 255, 255, 0.2));
    color: var(--text-color);
    text-decoration: none;
    font-size: 13px;
    font-family: 'Share Tech Mono', monospace; /* Asegúrate de que esta fuente esté en styles.css */
    border: 2px solid var(--primary-color); /* Borde neón */
    border-radius: 3px;
    margin-bottom: 20px;
    transition: all 0.3s ease;
    box-shadow: 0 0 8px rgba(0, 255, 0, 0.5); /* Brillo neón */
    position: relative;
    overflow: hidden;
}

.small-btn:hover {
    background: linear-gradient(45deg, rgba(0, 255, 255, 0.3), rgba(255, 0, 255, 0.3));
    border-color: var(--secondary-color);
    box-shadow: 0 0 12px rgba(0, 255, 255, 0.7);
    transform: translateY(-2px); /* Ligero levantamiento */
    animation: glitch 0.3s linear; /* Efecto glitch */
}

.small-btn i {
    margin-right: 4px;
    font-size: 12px;
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
                    <h1><i class="fas fa-list"></i> Administrar Listas</h1>
                    <div class="date-time" id="dateTime">Cargando...</div>
                </div>
            </header>

            <div class="cingles-container">
                <a href="insertar_lista.php" class="small-btn"><i class="fas fa-plus"></i> Nueva Lista</a>

                <div class="table-container">
                    <h2 class="form-title">Listas Registradas</h2>
                    <?php if ($mensaje): ?>
                        <?php echo $mensaje; ?>
                    <?php endif; ?>
                    <?php if ($error): ?>
                        <?php echo $error; ?>
                    <?php endif; ?>
                    <?php if (empty($listas)): ?>
                        <div class="alert alert-warning">No hay listas registradas aún.</div>
                    <?php else: ?>
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>N°</th>
                                    <th>Nombre</th>
                                    <th>Descripción</th>
                                    <!--th>Estado</th-->
                                    <th>...</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($listas as $lista): ?>
                                    <tr class="table-row-button" onclick="window.location.href='acciones.php?id_lista=<?php echo $lista['Id_lista']; ?>'">
                                        <td><?php echo $contador; ?></td>
                                        <td><?php echo htmlspecialchars($lista['nombre_lista']); ?></td>
                                        <td><?php echo htmlspecialchars($lista['descripcion_lista'] ?: 'Sin descripción'); ?></td>
                                        
                                        <td class="actions">
                                            <a class="small-btn"><i class="fas fa-plus"></i>Agregar Accion</a>
                                            <button class="small-btn" onclick="mostrarModalBorrar(<?php echo $lista['Id_lista']; ?>, '<?php echo htmlspecialchars($lista['nombre_lista']); ?>'); event.stopPropagation();"><i class="fas fa-trash-alt"></i></button>
                                        </td>
                                    </tr>
                                    <?php $contador++; ?>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>

            <div id="borrarModal" class="modal">
                <div class="modal-content">
                    <span class="close-button" onclick="cerrarModalBorrar()">×</span>
                    <p>¿Estás seguro de que deseas borrar la lista "<strong id="listaABorrar" class="resalt-text"></strong>"?</p>
                    <div class="modal-buttons">
                        <button class="modal-button-confirm" onclick="borrarLista()">Borrar</button>
                        <button class="modal-button-cancel" onclick="cerrarModalBorrar()">Cancelar</button>
                    </div>
                </div>
            </div>

            <form id="formBorrar" method="POST" action="eliminar_lista.php" style="display: none;">
                <input type="hidden" name="id_lista_borrar" id="idListaBorrar">
            </form>
            <a href="index.php" class="back-link"><i class="fas fa-arrow-left"></i> Volver</a>
        </main>
    </div>

    <script src="../../js/scripts.js"></script>
    <script>
        const modalBorrar = document.getElementById("borrarModal");
        const listaABorrarSpan = document.getElementById("listaABorrar");
        const idListaBorrarInput = document.getElementById("idListaBorrar");
        const formBorrar = document.getElementById("formBorrar");

        function mostrarModalBorrar(id, nombre) {
            idListaBorrarInput.value = id;
            listaABorrarSpan.textContent = nombre;
            modalBorrar.style.display = "block";
        }

        function cerrarModalBorrar() {
            modalBorrar.style.display = "none";
        }

        function borrarLista() {
            formBorrar.submit();
        }

        window.onclick = function(event) {
            if (event.target === modalBorrar) {
                cerrarModalBorrar();
            }
        };
    </script>
</body>
</html>