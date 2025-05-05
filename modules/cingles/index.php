<?php
require_once '../../config.php';

$db = getDbConnection();
$stmt = $db->query("SELECT * FROM tword_ingles ORDER BY Palabraen ASC");
$palabras = $stmt->fetchAll(PDO::FETCH_ASSOC);

$mensaje = "";
if (isset($_GET['borrado']) && $_GET['borrado'] === 'true') {
    $mensaje = "<div class='alert alert-success'>Palabra borrada correctamente.</div>";
} elseif (isset($_GET['borrado']) && $_GET['borrado'] === 'false') {
    $mensaje = "<div class='alert alert-danger'>Error al borrar la palabra.</div>";

}
$contador = 0;
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Módulo Cingles</title>
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
                    <h1><i class="fas fa-language"></i> Módulo de Ingles</h1>
                    <div class="date-time" id="dateTime">Cargando...</div>
                </div>
            </header>

            <div class="cingles-container">
                <div class="modules-grid">
                    <a href="insertar_palabra.php" class="module-card">
                        <div class="module-icon"><i class="fas fa-plus"></i></div>
                        <h3>Agregar Palabra</h3>
                        <p>Agregar una nueva palabra al módulo</p>
                    </a>
                    <a href="practicar.php" class="module-card">
                        <div class="module-icon"><i class="fas fa-play"></i></div>
                        <h3>Practicar</h3>
                        <p>Mejorar tu vocabulario</p>
                    </a>
                </div>
<br>
                <div class="table-container">
                    <h2 class="form-title">Mis Palabras</h2>
                    <?php if (!empty($mensaje)): ?>
                        <?php echo $mensaje; ?>
                    <?php endif; ?>
                    <?php if (empty($palabras)): ?>
                        <div class="alert alert-warning">No hay palabras registradas aún.</div>
                    <?php else: ?>
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>N°</th>
                                    <th>Palabra en Inglés</th>
                                    <th>Palabra en Español</th>
                                    <th>Progreso</th>
                                    <!--th>Estado</th-->
                                    <th>Fecha Intento</th>
                                    <th>Fecha de Registro</th>
                                    <th>...</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($palabras as $palabra): 
                                    $contador=$contador+1;
                                    ?>
                                    <tr>
                                        <td><?php echo $contador; //$palabra['Id_palabra']; ?></td>
                                        <td><?php echo htmlspecialchars($palabra['Palabraen']); ?></td>
                                        <td><?php echo htmlspecialchars($palabra['Palabraes']); ?></td>
                                        <td><?php echo $palabra['Contador']*5; ?>%</td>
                                        
                                        <td><?php echo $palabra['Fecha_intento']; ?></td>
                                        <td><?php echo $palabra['Fecha_registro']; ?></td>
                                        <td class="actions">
                                            <button class="action-btn delete" onclick="mostrarModalBorrar(<?php echo $palabra['Id_palabra']; ?>, '<?php echo htmlspecialchars($palabra['Palabraen']); ?>')"><i class="fas fa-trash-alt"></i></button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>

            <div id="borrarModal" class="modal">
                <div class="modal-content">
                    <span class="close-button" onclick="cerrarModalBorrar()">×</span>
                    <p>¿Estás seguro de que deseas borrar la palabra "<strong id="palabraABorrar" class="resalt-text"></strong>"?</p>
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

        window.onclick = function(event) {
            if (event.target == modalBorrar) {
                cerrarModalBorrar();
            }
        }
    </script>
</body>
</html>