// scripts_licencia.js - Lógica para el popup de activación de licencia

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
                        <input type="text" name="licencia_clave" placeholder="Clave de licencia (ej. xqore30)" required>
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
                const respuesta = await fetch('activar_licencia.php', {
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