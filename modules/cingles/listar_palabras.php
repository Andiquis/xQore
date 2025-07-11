<?php
require_once '../../config.php';

$db = getDbConnection();
$stmt = $db->query("SELECT * FROM tword_ingles ORDER BY Palabraen ASC");
$palabras = $stmt->fetchAll(PDO::FETCH_ASSOC);

$mensaje = "";
if (isset($_GET['borrado']) && $_GET['borrado'] === 'true') {
    $mensaje = "<span style='color: green;'>Palabra borrada correctamente.</span>";
} elseif (isset($_GET['borrado']) && $_GET['borrado'] === 'false') {
    $mensaje = "<span style='color: red;'>Error al borrar la palabra.</span>";
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Listar Palabras</title>
    <link rel="stylesheet" href="../../css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" crossorigin="anonymous">
    <style>
        /* Estilos para la tabla de palabras (mantener los existentes) */
        .list-container { /* ... */ }
        .word-table { /* ... */ }
        .word-table th, .word-table td { /* ... */ }
        .word-table th { /* ... */ }
        .back-link { /* ... */ }
        .back-link:hover { /* ... */ }
        .delete-button {
            background-color: #d9534f;
            color: white;
            border: none;
            padding: 8px 12px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            margin-left: 5px;
        }
        .delete-button:hover {
            background-color: #c9302c;
        }

        /* Estilos para la ventana modal de confirmación */
        .modal {
            display: none; /* Hidden by default */
            position: fixed; /* Stay in place */
            z-index: 1; /* Sit on top */
            left: 0;
            top: 0;
            width: 100%; /* Full width */
            height: 100%; /* Full height */
            overflow: auto; /* Enable scroll if needed */
            background-color: rgba(0,0,0,0.4); /* Black w/ opacity */
        }

        .modal-content {
            background-color: #fefefe;
            margin: 15% auto; /* 15% from the top and centered */
            padding: 20px;
            border: 1px solid #888;
            width: 80%; /* Could be more or less, depending on screen size */
            border-radius: 5px;
            text-align: center;
        }

        .modal-buttons button {
            padding: 10px 20px;
            margin: 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }

        .modal-button-confirm {
            background-color: #d9534f;
            color: white;
        }

        .modal-button-cancel {
            background-color: #5cb85c;
            color: white;
        }

        .close-button {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }

        .close-button:hover,
        .close-button:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }
    </style>
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
                    <h1><i class="fas fa-list-ul"></i> Listado de Palabras</h1>
                    <div class="date-time" id="dateTime">Cargando...</div>
                </div>
            </header>

            <div class="list-container">
                <?php if (!empty($mensaje)): ?>
                    <p><?php echo $mensaje; ?></p>
                <?php endif; ?>
                <?php if (empty($palabras)): ?>
                    <p>No hay palabras registradas aún.</p>
                <?php else: ?>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Palabra en Inglés</th>
                                <th>Palabra en Español</th>
                                <th>Contador</th>
                                <th>Estado Count</th>
                                <th>Fecha Intento</th>
                                <th>Fecha de Registro</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($palabras as $palabra): ?>
                                <tr>
                                    <td><?php echo $palabra['Id_palabra']; ?></td>
                                    <td><?php echo htmlspecialchars($palabra['Palabraen']); ?></td>
                                    <td><?php echo htmlspecialchars($palabra['Palabraes']); ?></td>
                                    <td><?php echo $palabra['Contador']; ?></td>
                                    <td><?php echo $palabra['Estado_count']; ?></td>
                                    <td><?php echo $palabra['Fecha_intento']; ?></td>
                                    <td><?php echo $palabra['Fecha_registro']; ?></td>
                                    <td>
                                        <button class="delete-button" onclick="mostrarModalBorrar(<?php echo $palabra['Id_palabra']; ?>, '<?php echo htmlspecialchars($palabra['Palabraen']); ?>')"><i class="fas fa-trash-alt"></i> Borrar</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
                <a href="index.php" class="back-link"><i class="fas fa-arrow-left"></i> Volver a Cingles</a>
            </div>

            <div id="borrarModal" class="modal">
                <div class="modal-content">
                    <span class="close-button" onclick="cerrarModalBorrar()">&times;</span>
                    <p>¿Estás seguro de que deseas borrar la palabra "<strong id="palabraABorrar"></strong>"?</p>
                    <div class="modal-buttons">
                        <button class="modal-button-confirm" onclick="borrarPalabra()">Borrar</button>
                        <button class="modal-button-cancel" onclick="cerrarModalBorrar()">Cancelar</button>
                    </div>
                </div>
            </div>

            <form id="formBorrar" method="POST" action="eliminar_palabra.php" style="display: none;">
                <input type="hidden" name="id_palabra_borrar" id="idPalabraBorrar">
            </form>
        </main>
    </div>

    <script src="../../js/scripts.js"></script>
    <script>
        var modalBorrar = document.getElementById("borrarModal");
        var palabraABorrarSpan = document.getElementById("palabraABorrar");
        var idPalabraBorrarInput = document.getElementById("idPalabraBorrar");
        var formBorrar = document.getElementById("formBorrar");

        function mostrarModalBorrar(id, palabra) {
            idPalabraBorrarInput.value = id;
            palabraABorrarSpan.textContent = palabra;
            modalBorrar.style.display = "block";
        }

        function cerrarModalBorrar() {
            modalBorrar.style.display = "none";
        }

        function borrarPalabra() {
            formBorrar.submit();
        }

        // Cerrar la modal si se hace clic fuera de ella
        window.onclick = function(event) {
            if (event.target == modalBorrar) {
                cerrarModalBorrar();
            }
        }
    </script>
</body>
</html>