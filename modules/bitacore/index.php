<?php
require_once '../../config.php';

// Configuración inicial
error_reporting(E_ALL); // Quitar en producción
ini_set('display_errors', 1);

// Conexión a la base de datos
$db = getDbConnection();

// Obtener la última entrada de bitácora por defecto
$bitacora = null;
$id = isset($_GET['id']) ? filter_var($_GET['id'], FILTER_VALIDATE_INT) : null;

// Cargar contenido
try {
    if ($id) {
        // Si se proporciona un ID, obtener esa entrada específica
        $stmt = $db->prepare("SELECT Id_bitacora, titulo, contenido, fecha_registro, estado FROM tbitacora WHERE Id_bitacora = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $bitacora = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$bitacora) {
            // Si no se encuentra la entrada solicitada, obtener la más reciente
            $stmt = $db->prepare("SELECT Id_bitacora, titulo, contenido, fecha_registro, estado FROM tbitacora WHERE estado = '1' ORDER BY fecha_registro DESC LIMIT 1");
            $stmt->execute();
            $bitacora = $stmt->fetch(PDO::FETCH_ASSOC);
        }
    } else {
        // Obtener la entrada más reciente
        $stmt = $db->prepare("SELECT Id_bitacora, titulo, contenido, fecha_registro, estado FROM tbitacora WHERE estado = '1' ORDER BY fecha_registro DESC LIMIT 1");
        $stmt->execute();
        $bitacora = $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    // Obtener todas las entradas para la navegación
    $stmt = $db->prepare("SELECT Id_bitacora, titulo, fecha_registro FROM tbitacora WHERE estado = '1' ORDER BY fecha_registro DESC");
    $stmt->execute();
    $entradas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    $error = "Error al obtener la bitácora: " . htmlspecialchars($e->getMessage());
}

// Cerrar conexión
$db = null;
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bitacora - XQ0R3</title>
    <link rel="stylesheet" href="../../css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;700&family=Share+Tech+Mono&family=IM+Fell+English&family=Cormorant+Garamond:wght@400;500&display=swap" rel="stylesheet">
    <style>
        /* Estilos específicos para la visualización del papiro */
        .papyrus-container {
            max-width: 800px;
            margin: 20px auto;
            position: relative;
            overflow: hidden;
        }
        
        .papyrus {
            background: url('https://i.pinimg.com/736x/6d/8e/9a/6d8e9a750b08665e11dc11e8e8bdec29.jpg'), #f2e8c9;
            background-blend-mode: multiply;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.7), inset 0 0 50px rgba(94, 75, 49, 0.7);
            border-radius: 5px;
            padding: 40px 60px;
            position: relative;
            color: #3a2c1d;
            font-family: 'IM Fell English', serif;
            line-height: 1.6;
            min-height: 600px;
            border: 1px solid #b89f65;
        }
        
        .papyrus::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(to right, 
                rgba(94, 75, 49, 0.2) 0%, 
                rgba(94, 75, 49, 0) 5%, 
                rgba(94, 75, 49, 0) 95%, 
                rgba(94, 75, 49, 0.2) 100%);
            pointer-events: none;
            z-index: 1;
        }
        
        .papyrus::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: repeating-linear-gradient(
                to bottom,
                transparent 0px,
                transparent 24px,
                rgba(94, 75, 49, 0.1) 24px,
                rgba(94, 75, 49, 0.1) 25px
            );
            pointer-events: none;
            z-index: 2;
        }
        
        /* Estilo para el título con un aire ancestral y mítico */
        .papyrus-title {
            font-family: 'Uncial Antiqua', 'Cinzel', serif;
            color: rgb(136, 100, 9);
            text-shadow: 0 0 8px rgba(184, 134, 11, 0.7), 0 0 4px rgba(139, 69, 19, 0.5);
            text-align: center;
            font-size: 28px;
            position: relative;
            margin-bottom: 30px;
            letter-spacing: 2px;
            text-transform: uppercase;
        }

        .papyrus-title::after {
            content: '<═╪═⚜══╪❖╪══⚜═╪═>';
            display: block;
            width: 80%;
            font-family: 'Junicode', 'Arial Unicode MS', sans-serif;
            font-size: 14px;
            color: #8b4513;
            text-shadow: 0 0 6px rgba(139, 69, 19, 0.6);
            text-align: center;
            margin: 10px auto;
            padding: 5px;
            background: linear-gradient(90deg, transparent, rgba(184, 134, 11, 0.4), transparent);
            border-top: 1px solid rgba(184, 134, 11, 0.5);
            border-bottom: 1px solid rgba(184, 134, 11, 0.5);
            box-shadow: inset 0 0 10px rgba(0, 0, 0, 0.3);
        }

        .papyrus-date {
            font-family: 'Cormorant Garamond', serif;
            text-align: right;
            font-style: italic;
            margin-bottom: 30px;
            color: #5a4a35;
        }
        
        .papyrus-content {
            text-align: justify;
            white-space: pre-line;
            font-size: 18px;
            position: relative;
            z-index: 5;
        }
        
        .papyrus-content p:first-letter {
            font-size: 30px;
            font-weight: bold;
            color: #8b0000;
            float: left;
            line-height: 0.8;
            margin-right: 6px;
            font-family: 'Cinzel', serif;
        }
        
        .navigation-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 20px 0;
        }
        
        .entry-dropdown {
            padding: 8px;
            background: rgba(0, 0, 0, 0.8);
            color: #d4af37;
            border: 1px solid #d4af37;
            font-family: 'Share Tech Mono', monospace;
            border-radius: 4px;
            min-width: 200px;
            cursor: pointer;
        }
        
        .action-button {
            padding: 8px 15px;
            background: linear-gradient(45deg, rgba(212, 175, 55, 0.2), rgba(0, 255, 255, 0.2));
            color: var(--text-color);
            border: 1px solid #d4af37;
            border-radius: 5px;
            font-family: 'Share Tech Mono', monospace;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            transition: all 0.3s ease;
            text-decoration: none;
            margin: 0 5px;
        }
        
        .action-button:hover {
            background: linear-gradient(45deg, rgba(212, 175, 55, 0.4), rgba(0, 255, 255, 0.4));
            box-shadow: 0 0 10px rgba(0, 255, 255, 0.7);
            transform: translateY(-2px);
        }
        
        .action-button i {
            margin-right: 5px;
        }
        
        /* Estilos para impresión */
        @media print {
            .container, body, html {
                background: white !important;
                padding: 0 !important;
                margin: 0 !important;
            }
            
            .sidebar, header, .navigation-container, .action-button, footer {
                display: none !important;
            }
            
            .content {
                margin: 0 !important;
                padding: 0 !important;
                width: 100% !important;
                max-width: 100% !important;
            }
            
            .papyrus-container {
                width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
                box-shadow: none !important;
            }
            
            .papyrus {
                background: white !important;
                box-shadow: none !important;
                border: none !important;
                padding: 10mm !important;
                color: black !important;
                font-size: 12pt !important;
            }
            
            .papyrus::before, .papyrus::after {
                display: none !important;
            }
            
            .papyrus-content {
                page-break-inside: auto;
            }
            
            .page-break {
                page-break-after: always;
            }
        }
        
        .no-entries {
            font-family: 'Cormorant Garamond', serif;
            text-align: center;
            font-style: italic;
            padding: 50px 0;
            color: #5a4a35;
            font-size: 20px;
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

            <div class="navigation-container">
                <select class="entry-dropdown" onchange="if(this.value) window.location.href=this.value;">
                    <option value="">Seleccionar entrada...</option>
                    <?php foreach ($entradas as $entrada): ?>
                        <option value="?id=<?php echo $entrada['Id_bitacora']; ?>" <?php echo isset($bitacora) && $bitacora['Id_bitacora'] == $entrada['Id_bitacora'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($entrada['titulo']) . ' - ' . date('d/m/Y', strtotime($entrada['fecha_registro'])); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <div>
                    <a href="administracion.php" class="action-button">
                        <i class="fas fa-cog"></i> Administrar
                    </a>
                    <button onclick="imprimirBitacora()" class="action-button">
                        <i class="fas fa-print"></i> Imprimir
                    </button>
                    <form method="POST" action="pass.php" style="display: inline;">
                        <button title="Restablecer contraseña" type="submit" name="reset_password" value="1" class="action-button">
                            <i class="fas fa-key"></i>
                        </button>
                    </form>
                </div>
            </div>

            <div class="papyrus-container">
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php elseif ($bitacora): ?>
                    <div class="papyrus" id="papyrus">
                        <h2 class="papyrus-title"><?php echo htmlspecialchars($bitacora['titulo']); ?></h2>
                        <div class="papyrus-date"><?php echo date('d \d\e F \d\e Y', strtotime($bitacora['fecha_registro'])); ?></div>
                        <div class="papyrus-content" id="papyrus-content">
                            <?php echo nl2br(htmlspecialchars($bitacora['contenido'])); ?>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="papyrus">
                        <div class="no-entries">No hay registros en la bitácora disponibles. Utiliza el botón "Administrar" para escribir una nueva entrada.</div>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <script src="../../js/scripts.js"></script>
    <script>
        function imprimirBitacora() {
            const contenido = document.getElementById('papyrus-content');
            if (contenido) {
                const contenidoOriginal = contenido.innerHTML;
                let contenidoParaImprimir = contenidoOriginal.replace(/<br>\s*<br>/g, '<div class="page-break"></div>');
                contenido.innerHTML = contenidoParaImprimir;
                window.print();
                setTimeout(() => {
                    contenido.innerHTML = contenidoOriginal;
                }, 1000);
            } else {
                window.print();
            }
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