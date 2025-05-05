// scripts.js - Scripts generales de la aplicación

// Actualizar fecha y hora en tiempo real
function updateDateTime() {
    const now = new Date();
    const dateOptions = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
    const timeOptions = { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: false };
    
    const dateStr = now.toLocaleDateString('es-ES', dateOptions);
    const timeStr = now.toLocaleTimeString('es-ES', timeOptions);
    
    document.getElementById('dateTime').textContent = `${dateStr} | ${timeStr}`;
}

// Efecto de typewriter para mensajes motivacionales
function typeWriterEffect() {
    const messageElement = document.getElementById('motivationalMessage');
    if (!messageElement) return;
    
    const text = messageElement.textContent || messageElement.innerText;
    messageElement.textContent = '';
    let i = 0;
    const speed = 50; // Velocidad en milisegundos
    
    function typeChar() {
        if (i < text.length) {
            messageElement.textContent += text.charAt(i);
            i++;
            setTimeout(typeChar, speed);
        }
    }
    
    // Iniciar la animación de escritura
    typeChar();
    
    // Añadir el parpadeo del cursor
    const cursor = document.querySelector('.cursor');
    if (cursor) {
        setInterval(() => {
            cursor.style.opacity = cursor.style.opacity === '0' ? '1' : '0';
        }, 500);
    }
}

// Crear efecto Matrix en el fondo
function createMatrixBackground() {
    const canvas = document.createElement('canvas');
    canvas.classList.add('matrix-bg');
    document.body.appendChild(canvas);
    
    const ctx = canvas.getContext('2d');
    canvas.width = window.innerWidth;
    canvas.height = window.innerHeight;
    
    const matrix = '01';
    const drops = [];
    const fontSize = 14;
    const columns = canvas.width / fontSize;
    
    for (let i = 0; i < columns; i++) {
        drops[i] = 1;
    }
    
    function draw() {
        ctx.fillStyle = 'rgba(0, 0, 0, 0.05)';
        ctx.fillRect(0, 0, canvas.width, canvas.height);
        
        ctx.fillStyle = '#0f0';
        ctx.font = fontSize + 'px Share Tech Mono';
        
        for (let i = 0; i < drops.length; i++) {
            const text = matrix.charAt(Math.floor(Math.random() * matrix.length));
            ctx.fillText(text, i * fontSize, drops[i] * fontSize);
            
            // Resetear cuando las gotas llegan al final o aleatoriamente
            if (drops[i] * fontSize > canvas.height && Math.random() > 0.975) {
                drops[i] = 0;
            }
            
            drops[i]++;
        }
    }
    
    setInterval(draw, 35);
    
    // Ajustar el tamaño del canvas cuando cambia el tamaño de la ventana
    window.addEventListener('resize', () => {
        canvas.width = window.innerWidth;
        canvas.height = window.innerHeight;
    });
}

// Función para efectos de hover en los elementos interactivos
function applyHoverEffects() {
    const interactiveElements = document.querySelectorAll('.module-card, .stat-card, .btn');
    
    interactiveElements.forEach(element => {
        element.addEventListener('mouseover', () => {
            const glowEffect = document.createElement('div');
            glowEffect.classList.add('glow-effect');
            element.appendChild(glowEffect);
            
            setTimeout(() => {
                if (glowEffect && glowEffect.parentNode === element) {
                    element.removeChild(glowEffect);
                }
            }, 1000);
        });
    });
}

// Crear notificaciones estilo hacker
function createNotification(message, type = 'info', duration = 3000) {
    const notification = document.createElement('div');
    notification.classList.add('notification', `notification-${type}`);
    
    const icon = document.createElement('i');
    
    switch(type) {
        case 'success':
            icon.className = 'fas fa-check-circle';
            break;
        case 'warning':
            icon.className = 'fas fa-exclamation-triangle';
            break;
        case 'error':
            icon.className = 'fas fa-times-circle';
            break;
        default:
            icon.className = 'fas fa-info-circle';
    }
    
    const textSpan = document.createElement('span');
    textSpan.textContent = message;
    
    notification.appendChild(icon);
    notification.appendChild(textSpan);
    
    const notificationsContainer = document.querySelector('.notifications-container');
    
    if (!notificationsContainer) {
        const container = document.createElement('div');
        container.classList.add('notifications-container');
        document.body.appendChild(container);
        container.appendChild(notification);
    } else {
        notificationsContainer.appendChild(notification);
    }
    
    setTimeout(() => {
        notification.classList.add('show');
    }, 10);
    
    setTimeout(() => {
        notification.classList.remove('show');
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 300);
    }, duration);
}

// Validación de formularios
function validateForm(formId, rules) {
    const form = document.getElementById(formId);
    if (!form) return;
    
    form.addEventListener('submit', (e) => {
        let isValid = true;
        const errors = {};
        
        // Eliminar mensajes de error anteriores
        const errorElements = form.querySelectorAll('.error-message');
        errorElements.forEach(el => el.parentNode.removeChild(el));
        
        // Restablecer estilos de campos
        form.querySelectorAll('.form-control').forEach(field => {
            field.classList.remove('error');
        });
        
        // Validar cada campo según las reglas
        for (const fieldName in rules) {
            const field = form.querySelector(`[name="${fieldName}"]`);
            const fieldRules = rules[fieldName];
            
            if (!field) continue;
            
            // Validar reglas
            if (fieldRules.required && !field.value.trim()) {
                isValid = false;
                errors[fieldName] = 'Este campo es obligatorio';
            } else if (fieldRules.minLength && field.value.length < fieldRules.minLength) {
                isValid = false;
                errors[fieldName] = `Debe tener al menos ${fieldRules.minLength} caracteres`;
            } else if (fieldRules.pattern && !fieldRules.pattern.test(field.value)) {
                isValid = false;
                errors[fieldName] = fieldRules.message || 'Formato inválido';
            }
        }
        
        // Mostrar errores si los hay
        if (!isValid) {
            e.preventDefault();
            
            for (const fieldName in errors) {
                const field = form.querySelector(`[name="${fieldName}"]`);
                const errorMessage = errors[fieldName];
                
                field.classList.add('error');
                
                const errorElement = document.createElement('div');
                errorElement.classList.add('error-message');
                errorElement.textContent = errorMessage;
                
                field.parentNode.appendChild(errorElement);
            }
            
            createNotification('Por favor, corrija los errores en el formulario', 'error');
        }
    });
}

// Confirmación para eliminación
function confirmDelete(selector) {
    const deleteButtons = document.querySelectorAll(selector);
    
    deleteButtons.forEach(button => {
        button.addEventListener('click', (e) => {
            e.preventDefault();
            
            const confirmModal = document.createElement('div');
            confirmModal.classList.add('modal');
            confirmModal.innerHTML = `
                <div class="modal-content">
                    <div class="modal-header">
                        <h3>Confirmar eliminación</h3>
                        <span class="close-modal">&times;</span>
                    </div>
                    <div class="modal-body">
                        <p>¿Está seguro de que desea eliminar este elemento? Esta acción no se puede deshacer.</p>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-secondary cancel-delete">Cancelar</button>
                        <button class="btn btn-danger confirm-delete">Eliminar</button>
                    </div>
                </div>
            `;
            
            document.body.appendChild(confirmModal);
            setTimeout(() => confirmModal.classList.add('show'), 10);
            
            // Evento para cerrar modal
            const closeModal = () => {
                confirmModal.classList.remove('show');
                setTimeout(() => {
                    if (confirmModal.parentNode) {
                        confirmModal.parentNode.removeChild(confirmModal);
                    }
                }, 300);
            };
            
            // Cerrar al hacer clic en la X
            confirmModal.querySelector('.close-modal').addEventListener('click', closeModal);
            
            // Cerrar al hacer clic en Cancelar
            confirmModal.querySelector('.cancel-delete').addEventListener('click', closeModal);
            
            // Confirmar eliminación
            confirmModal.querySelector('.confirm-delete').addEventListener('click', () => {
                window.location.href = button.getAttribute('href');
            });
        });
    });
}

// Función para peticiones AJAX
function ajaxRequest(url, method = 'GET', data = null, onSuccess = null, onError = null) {
    const xhr = new XMLHttpRequest();
    xhr.open(method, url, true);
    xhr.setRequestHeader('Content-Type', 'application/json');
    xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
    
    xhr.onload = function() {
        if (xhr.status >= 200 && xhr.status < 300) {
            if (onSuccess) {
                try {
                    const response = JSON.parse(xhr.responseText);
                    onSuccess(response);
                } catch (e) {
                    onSuccess(xhr.responseText);
                }
            }
        } else {
            if (onError) {
                onError(xhr.statusText);
            }
        }
    };
    
    xhr.onerror = function() {
        if (onError) {
            onError('Error de red');
        }
    };
    
    if (data) {
        xhr.send(JSON.stringify(data));
    } else {
        xhr.send();
    }
}
// Inicializar todas las funciones cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', () => {
    // Actualizar fecha y hora cada segundo
    updateDateTime();
    setInterval(updateDateTime, 1000);
    
    // Iniciar efecto typewriter para mensaje motivacional
    typeWriterEffect();
    
    // Crear efecto Matrix en el fondo
    createMatrixBackground();
    
    // Aplicar efectos de hover
    applyHoverEffects();
    
    // Configurar validación de formularios si existen
    if (document.querySelector('form')) {
        const formRules = {
            // Ejemplo de reglas para el formulario de palabras en inglés
            palabraen: { required: true, minLength: 1 },
            palabraes: { required: true, minLength: 1 }
        };
        
        validateForm('formPalabra', formRules);
    }
    
    // Configurar confirmación de eliminación
    confirmDelete('.btn-delete, .action-btn.delete');
    
    // Inicializar el efecto Matrix para la navbar
    initMatrixNavBackground();
});

// Función para inicializar el fondo Matrix en la navbar
function initMatrixNavBackground() {
    const canvas = document.getElementById('matrixNavCanvas');
    if (!canvas) return; // Verificar que el canvas existe
    
    const ctx = canvas.getContext('2d');
    
    // Ajustar el tamaño del canvas al de la navbar
    function resizeCanvas() {
        const sidebar = document.querySelector('.sidebar');
        if (!sidebar) return;
        
        canvas.width = sidebar.offsetWidth;
        canvas.height = sidebar.offsetHeight;
    }
    
    resizeCanvas();
    window.addEventListener('resize', resizeCanvas);
    
    // Caracteres para el efecto Matrix
    const characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789$+-*/=%"\'#&_(),.;:?!\\|{}<>[]^~';
    const fontSize = 14;
    const columns = Math.floor(canvas.width / fontSize);
    
    // Array para guardar la posición Y de cada columna
    const drops = [];
    for (let i = 0; i < columns; i++) {
        drops[i] = Math.random() * canvas.height;
    }
    
    // Función de dibujo del efecto Matrix
    function drawMatrix() {
        // Fondo semi-transparente para crear el efecto de desvanecimiento
        ctx.fillStyle = 'rgba(0, 0, 0, 0.05)';
        ctx.fillRect(0, 0, canvas.width, canvas.height);
        
        // Texto en verde estilo Matrix
        ctx.fillStyle = '#0f0';
        ctx.font = `${fontSize}px monospace`;
        
        // Dibujar los caracteres
        for (let i = 0; i < drops.length; i++) {
            // Obtener un caracter aleatorio
            const char = characters[Math.floor(Math.random() * characters.length)];
            
            // Dibujar el caracter
            ctx.fillText(char, i * fontSize, drops[i] * fontSize);
            
            // Si la gota llega al final, o aleatoriamente, volver arriba
            if (drops[i] * fontSize > canvas.height && Math.random() > 0.975) {
                drops[i] = 0;
            }
            
            // Mover la gota hacia abajo
            drops[i]++;
        }
    }
    
    // Animar el efecto Matrix
    setInterval(drawMatrix, 80);
}