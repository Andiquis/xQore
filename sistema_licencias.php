<?php
// sistema_licencias.php - Sistema completo de gestión de licencias

// Inicializar la base de datos SQLite
try {
    $licencia_db_path = __DIR__ . '/database/xqore_licencias.db';
    $licencia_db = new PDO('sqlite:' . $licencia_db_path);
    $licencia_db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Crear tabla de licencias si no existe
    $licencia_db->exec("
        CREATE TABLE IF NOT EXISTS licencias (
            licencia_id INTEGER PRIMARY KEY AUTOINCREMENT,
            licencia_clave TEXT,
            licencia_fecha_activacion TEXT NOT NULL,
            licencia_fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");

    // Insertar licencia por defecto (plan free) si la tabla está vacía
    $licencia_stmt = $licencia_db->query("SELECT COUNT(*) FROM licencias");
    $licencia_count = $licencia_stmt->fetchColumn();
    if ($licencia_count == 0) {
        $licencia_stmt = $licencia_db->prepare("INSERT INTO licencias (licencia_clave, licencia_fecha_activacion) VALUES (?, ?)");
        $licencia_stmt->execute([null, date('Y-m-d')]);
    }
} catch (PDOException $e) {
    die("Error al inicializar la base de datos de licencias: " . $e->getMessage());
}

// Clase para manejar licencias
class ManejadorLicencia {
    private $licencia_archivo = 'licencias.json';
    private $licencia_datos = [];

    public function __construct() {
        if (file_exists($this->licencia_archivo)) {
            $licencia_contenido = file_get_contents($this->licencia_archivo);
            $licencia_json = json_decode($licencia_contenido, true);
            if (isset($licencia_json['licencias'])) {
                $this->licencia_datos = $licencia_json['licencias'];
            }
        }
    }

    public function validarLicenciaClave($licencia_clave) {
        foreach ($this->licencia_datos as $licencia) {
            if ($licencia['licencia_clave'] === $licencia_clave || ($licencia['licencia_clave'] === null && $licencia_clave === null)) {
                return $licencia;
            }
        }
        return null;
    }

    public function esLicenciaActiva($licencia_info, $licencia_fecha_activacion) {
        if ($licencia_info['licencia_duracion_dias'] === null) {
            return true;
        }
        $licencia_fecha_inicio = new DateTime($licencia_fecha_activacion);
        $licencia_fecha_actual = new DateTime();
        $licencia_intervalo = $licencia_fecha_inicio->diff($licencia_fecha_actual);
        $licencia_dias_transcurridos = $licencia_intervalo->days;
        return $licencia_dias_transcurridos <= $licencia_info['licencia_duracion_dias'];
    }

    public function mostrarPopupLicencia($licencia_info, $licencia_activa) {
        return $licencia_info['licencia_popup_activacion'] && !$licencia_activa;
    }
}

// Inicializar el manejador de licencias
$licencia_manejador = new ManejadorLicencia();

// Obtener la licencia actual
$licencia_stmt = $licencia_db->query("SELECT licencia_clave, licencia_fecha_activacion FROM licencias ORDER BY licencia_fecha_creacion DESC LIMIT 1");
$licencia_datos = $licencia_stmt->fetch(PDO::FETCH_ASSOC);
if (!$licencia_datos) {
    $licencia_stmt = $licencia_db->prepare("INSERT INTO licencias (licencia_clave, licencia_fecha_activacion) VALUES (?, ?)");
    $licencia_stmt->execute([null, date('Y-m-d')]);
    $licencia_datos = ['licencia_clave' => null, 'licencia_fecha_activacion' => date('Y-m-d')];
}
$licencia_clave_actual = $licencia_datos['licencia_clave'];
$licencia_fecha_activacion = $licencia_datos['licencia_fecha_activacion'];

// Función para procesar la licencia
function procesarLicencia($licencia_manejador, $licencia_db, $licencia_clave, $licencia_fecha_activacion) {
    $licencia_resultado = [
        'licencia_valida' => false,
        'licencia_mostrar_popup' => false,
        'licencia_plan' => null,
        'licencia_mensaje' => ''
    ];

    $licencia_info = $licencia_manejador->validarLicenciaClave($licencia_clave);
    if (!$licencia_info) {
        $licencia_resultado['licencia_mensaje'] = 'Clave de licencia no válida';
        return $licencia_resultado;
    }

    $licencia_resultado['licencia_valida'] = true;
    $licencia_resultado['licencia_plan'] = $licencia_info['licencia_plan'];
    $licencia_activa = $licencia_manejador->esLicenciaActiva($licencia_info, $licencia_fecha_activacion);

    if (!$licencia_activa) {
        $licencia_resultado['licencia_mensaje'] = 'La licencia ha expirado. Por favor, renueve su licencia.';
        $licencia_resultado['licencia_mostrar_popup'] = $licencia_manejador->mostrarPopupLicencia($licencia_info, $licencia_activa);
    } else {
        $licencia_resultado['licencia_mensaje'] = 'Licencia activa: ' . $licencia_info['licencia_plan'];
    }

    return $licencia_resultado;
}

// Manejar solicitud AJAX para activar nueva licencia
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['licencia_clave'])) {
    header('Content-Type: application/json');
    $licencia_nueva_clave = trim($_POST['licencia_clave']);
    
    $licencia_info = $licencia_manejador->validarLicenciaClave($licencia_nueva_clave);
    
    if ($licencia_info) {
        $licencia_stmt = $licencia_db->prepare("INSERT INTO licencias (licencia_clave, licencia_fecha_activacion) VALUES (?, ?)");
        $licencia_stmt->execute([$licencia_nueva_clave, date('Y-m-d')]);
        $licencia_respuesta = ['licencia_exito' => true, 'licencia_mensaje' => 'Licencia activada correctamente: ' . $licencia_info['licencia_plan']];
    } else {
        $licencia_respuesta = ['licencia_exito' => false, 'licencia_mensaje' => 'Clave de licencia no válida'];
    }
    
    echo json_encode($licencia_respuesta);
    exit;
}

// Procesar la licencia actual
$licencia_estado = procesarLicencia($licencia_manejador, $licencia_db, $licencia_clave_actual, $licencia_fecha_activacion);

// Generar popup si es necesario
if ($licencia_estado['licencia_mostrar_popup']): ?>
<style>
/* Estilos para el popup de activación de licencia */
.licencia-popup {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.7);
    z-index: 1000;
    justify-content: center;
    align-items: center;
}

.licencia-popup-contenido {
    background: #1a1a1a;
    padding: 20px;
    border-radius: 8px;
    width: 400px;
    max-width: 90%;
    color: #fff;
    box-shadow: 0 0 10px rgba(0, 255, 0, 0.5);
}

.licencia-popup-contenido h2 {
    margin-top: 0;
    color: #0f0;
    font-family: 'Courier New', monospace;
}

.licencia-popup-contenido form {
    display: flex;
    flex-direction: column;
}

.licencia-popup-contenido input[type="text"] {
    padding: 10px;
    margin-bottom: 15px;
    border: 1px solid #0f0;
    background: #000;
    color: #0f0;
    font-family: 'Courier New', monospace;
}

.licencia-popup-contenido button {
    padding: 10px;
    background: #0f0;
    color: #000;
    border: none;
    cursor: pointer;
    font-family: 'Courier New', monospace;
}

.licencia-popup-contenido button:hover {
    background: #0c0;
}

.licencia-popup-error {
    color: #f00;
    margin-bottom: 10px;
    display: none;
}

.licencia-popup-exito {
    color: #0f0;
    margin-bottom: 10px;
    display: none;
}
</style>

<script>
// Lógica para el popup de activación de licencia
function mostrarPopupLicencia() {
    let licencia_popup = document.querySelector('.licencia-popup');
    if (!licencia_popup) {
        const licencia_popup_html = `
            <div class="licencia-popup">
                <div class="licencia-popup-contenido">
                    <h2>Activar Licencia</h2>
                    <p>Tu licencia ha expirado. Ingresa una nueva clave de licencia:</p>
                    <div class="licencia-popup-error"></div>
                    <div class="licencia-popup-exito"></div>
                    <form id="licencia-formulario">
                        <input type="text" name="licencia_clave" placeholder="Si no tiene licencia solicite a 910345120" required>
                        <button type="submit">Activar</button>
                    </form>
                </div>
            </div>
        `;
        document.body.insertAdjacentHTML('beforeend', licencia_popup_html);
        licencia_popup = document.querySelector('.licencia-popup');

        const licencia_formulario = document.getElementById('licencia-formulario');
        licencia_formulario.addEventListener('submit', async (e) => {
            e.preventDefault();
            const licencia_clave = licencia_formulario.querySelector('input[name="licencia_clave"]').value;
            const licencia_error = licencia_popup.querySelector('.licencia-popup-error');
            const licencia_exito = licencia_popup.querySelector('.licencia-popup-exito');

            try {
                const respuesta = await fetch('sistema_licencias.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `licencia_clave=${encodeURIComponent(licencia_clave)}`
                });
                const resultado = await respuesta.json();

                if (resultado.licencia_exito) {
                    licencia_exito.textContent = resultado.licencia_mensaje;
                    licencia_exito.style.display = 'block';
                    licencia_error.style.display = 'none';
                    setTimeout(() => {
                        licencia_popup.style.display = 'none';
                        window.location.reload();
                    }, 2000);
                } else {
                    licencia_error.textContent = resultado.licencia_mensaje;
                    licencia_error.style.display = 'block';
                    licencia_exito.style.display = 'none';
                }
            } catch (err) {
                licencia_error.textContent = 'Error al procesar la solicitud';
                licencia_error.style.display = 'block';
                licencia_exito.style.display = 'none';
            }
        });
    }

    licencia_popup.style.display = 'flex';
}

document.addEventListener('DOMContentLoaded', () => {
    mostrarPopupLicencia();
});
</script>
<?php endif; ?>