<?php
// index.php - Página principal
// Configurar encabezados CORS para permitir acceso desde cualquier IP
header("Access-Control-Allow-Origin: *"); // Permite solicitudes desde cualquier origen
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Manejar solicitudes OPTIONS (preflight)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}


require_once 'config.php'; // Incluir la configuración de la base de datos
require_once 'sistema_licencias.php'; // Incluir el sistema de licencias

// Obtener frase motivacional aleatoria
function getMensajeMotivacional() {
    $db = getDbConnection();
    $stmt = $db->query("SELECT Frase FROM tmotivaus WHERE estado = '1' ORDER BY RANDOM() LIMIT 1");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result) {
        return $result['Frase'];
    } else {
        return "El camino hacia el conocimiento comienza con tu esfuerzo diario.";
    }
}

$mensajeMotivacional = getMensajeMotivacional();

// Obtener estadísticas para el dashboard
$db = getDbConnection();
$totalPalabras = $db->query("SELECT COUNT(*) FROM tword_ingles")->fetchColumn();
$totalListas = $db->query("SELECT COUNT(*) FROM tlistas WHERE Estado_lista = '1'")->fetchColumn();
$totalBitacoras = $db->query("SELECT COUNT(*) FROM tbitacora WHERE estado = '1'")->fetchColumn();
$palabrasAprendidas = $db->query("SELECT COUNT(*) FROM tword_ingles WHERE Contador >= 20")->fetchColumn();

// Calcular porcentaje de aprendizaje
$porcentajeAprendizaje = ($totalPalabras > 0) ? round(($palabrasAprendidas / $totalPalabras) * 100) : 0;

// Obtener las últimas palabras agregadas
$ultimasPalabras = [];
try {
    $stmt = $db->query("SELECT Palabra_ingles, Palabra_espanol FROM tword_ingles ORDER BY id DESC LIMIT 5");
    $ultimasPalabras = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Manejar error si es necesario
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>XQ0R3 - Sistema Integral</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" crossorigin="anonymous">
    <meta http-equiv="Content-Security-Policy" content="
      default-src 'self';
      script-src 'self';
      connect-src 'self';
      img-src 'self' data:;
      style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdnjs.cloudflare.com;
      font-src 'self' https://fonts.gstatic.com https://cdnjs.cloudflare.com;
    ">
    <style>
        /* Estilos para el fondo Matrix en la navbar */

    </style>
</head>
<body>
    <!-- Efectos decorativos globales -->
    <div class="scan-line"></div>
    <div class="matrix-bg"></div>
    
    <div class="container">
        <nav class="sidebar">
            <div class="matrix-nav-bg">
                <canvas id="matrixNavCanvas"></canvas>
            </div>
            <div class="logo" data-text="XQ0R3">XQ0R3</div>
            <ul class="menu">
                <li class="active"><a href="index.php"><i class="fas fa-home"></i> Dashboard</a></li>
                <li><a href="modules/cingles/index.php"><i class="fas fa-language"></i> Cingles</a></li>
                <li><a href="modules/xlist/index.php"><i class="fas fa-list"></i> XList</a></li>
                <li><a href="modules/bitacore/pass.php"><i class="fas fa-book"></i> Bitacore</a></li>
                <li><a href="modules/motivaus/index.php"><i class="fas fa-bolt"></i> Motivaus</a></li>
                
            </ul>
        </nav>
        
        <main class="content">
            <header>
                <div class="header-content">
                    <h1 data-text="XQore System"><i class="fas fa-terminal"></i> XQore System</h1>
                    <div class="date-time" id="dateTime">Cargando...</div>
                </div>
            </header>
            
            <div class="motivational-message">
                <div class="terminal">
                    <div class="terminal-header">
                        <div class="terminal-buttons">
                            <span class="terminal-button red"></span>
                            <span class="terminal-button yellow"></span>
                            <span class="terminal-button green"></span>
                        </div>
                        <div class="terminal-title">xqore@andisystems:~$</div>
                    </div>
                    <div class="terminal-body">
                        <div class="typewriter" id="typewriter">
                            <span class="prompt">root@xqore:~$ </span>
                            <span class="message_terminal" id="motivationalMessage"><?php echo $mensajeMotivacional; ?></span>
                            <span class="cursor">_</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="stats-container">
                <div class="stat-card">
                    <div class="stat-icon"><i class="fas fa-language"></i></div>
                    <div class="stat-content">
                        <h3>Palabras</h3>
                        <p class="stat-number"><?php echo $totalPalabras; ?></p>
                        <p class="stat-desc">Total registradas</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
                    <div class="stat-content">
                        <h3>Aprendidas</h3>
                        <p class="stat-number"><?php echo $palabrasAprendidas; ?> (<?php echo $porcentajeAprendizaje; ?>%)</p>
                        <p class="stat-desc">Nivel maestro</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon"><i class="fas fa-list"></i></div>
                    <div class="stat-content">
                        <h3>Listas</h3>
                        <p class="stat-number"><?php echo $totalListas; ?></p>
                        <p class="stat-desc">Activas</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon"><i class="fas fa-book"></i></div>
                    <div class="stat-content">
                        <h3>Bitácoras</h3>
                        <p class="stat-number"><?php echo $totalBitacoras; ?></p>
                        <p class="stat-desc">Entradas</p>
                    </div>
                </div>
            </div>
            
            <!-- Sección de últimas palabras añadidas -->
            <?php if (!empty($ultimasPalabras)): ?>
            <div class="table-container">
                <h2 class="form-title">Últimas palabras añadidas</h2>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Inglés</th>
                            <th>Español</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($ultimasPalabras as $palabra): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($palabra['Palabra_ingles']); ?></td>
                            <td><?php echo htmlspecialchars($palabra['Palabra_espanol']); ?></td>
                            <td class="actions">
                                <a href="modules/cingles/word.php?word=<?php echo urlencode($palabra['Palabra_ingles']); ?>" class="action-btn edit">Ver</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
            
            <div class="modules-grid">
                <a href="modules/cingles/index.php" class="module-card">
                    <div class="module-icon"><i class="fas fa-language"></i></div>
                    <h3>Cingles</h3>
                    <p>Aprende inglés diariamente con un sistema basado en repetición espaciada</p>
                </a>
                
                <a href="modules/xlist/index.php" class="module-card">
                    <div class="module-icon"><i class="fas fa-list"></i></div>
                    <h3>XList</h3>
                    <p>Gestiona tus listas de acciones y tareas pendientes</p>
                </a>
                
                <a href="modules/bitacore/pass.php" class="module-card">
                    <div class="module-icon"><i class="fas fa-book"></i></div>
                    <h3>Bitacore</h3>
                    <p>Registra tus pensamientos y experiencias en formato diario</p>
                </a>
                
                <a href="modules/motivaus/index.php" class="module-card">
                    <div class="module-icon"><i class="fas fa-bolt"></i></div>
                    <h3>Motivaus</h3>
                    <p>Gestiona tus frases motivacionales para impulsar tu productividad</p>
                </a>
            </div>
            
            <!-- Alerta con notificación del sistema -->
            <!--div class="alert alert-success">
                Sistema XQ0R3 en línea. Todos los sistemas operando correctamente.
            </div-->
        </main>
    </div>
    <footer style="text-align:center; font-size: 0.75rem; color: #00ff00; padding: 10px 0; border-top: 1px solid #0f0;">
        © 2025 XQ0R3 Systems - Todos los derechos reservados.<br>
        <a href="https://chat.whatsapp.com/BaPnXJ4xAaJIpRkqaZ86gB?mode=ac_t" target="_blank" 
        style="display: inline-block; margin-top: 5px; background-color: #00ff00; color: #000; padding: 4px 10px; border-radius: 4px; text-decoration: none; font-weight: bold;">
            <i class="fab fa-whatsapp"></i> Soporte
        </a>
    </footer>


    <!-- Usar script externo en lugar de script en línea -->
    <script src="js/scripts.js"></script>

    
</body>
</html>