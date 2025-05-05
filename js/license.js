// license.js - Funciones de JavaScript para el sistema de licencias

// Función para mostrar el modal de licencia expirada (se puede llamar desde cualquier página)
function showLicenseModal() {
    // Crear elementos del modal
    const modal = document.createElement('div');
    modal.className = 'license-modal';
    modal.id = 'licenseModal';
    
    const modalContent = document.createElement('div');
    modalContent.className = 'license-modal-content';
    
    // Cabecera del modal
    const header = document.createElement('div');
    header.className = 'license-modal-header';
    header.innerHTML = '<h2><i class="fas fa-key"></i> Licencia Expirada</h2>';
    
    // Cuerpo del modal
    const body = document.createElement('div');
    body.className = 'license-modal-body';
    body.innerHTML = `
        <p>Tu licencia de XQ0R3 ha expirado. Por favor, actualiza tu licencia para continuar usando el sistema.</p>
        <div class="license-modal-icon">
            <i class="fas fa-exclamation-triangle"></i>
        </div>
    `;
    
    // Pie del modal
    const footer = document.createElement('div');
    footer.className = 'license-modal-footer';
    footer.innerHTML = `
        <a href="license_activation.php" class="btn btn-primary">Activar Licencia</a>
    `;
    
    // Ensamblar el modal
    modalContent.appendChild(header);
    modalContent.appendChild(body);
    modalContent.appendChild(footer);
    modal.appendChild(modalContent);
    
    // Añadir estilos al modal
    const styles = document.createElement('style');
    styles.textContent = `
        .license-modal {
            position: fixed;
            z-index: 9999;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.7);
            display: flex;
            align-items: center;
            justify-content: center;
            backdrop-filter: blur(5px);
        }
        
        .license-modal-content {
            background-color: var(--bg-card, #1e1e2e);
            border: 1px solid var(--border-color, #44475a);
            border-radius: 8px;
            box-shadow: 0 0 30px rgba(0, 255, 0, 0.2);
            width: 90%;
            max-width: 500px;
            animation: modalFadeIn 0.5s ease;
            overflow: hidden;
        }
        
        .license-modal-header {
            padding: 15px;
            border-bottom: 1px solid var(--border-color, #44475a);
            text-align: center;
        }
        
        .license-modal-header h2 {
            margin: 0;
            color: var(--primary-color, #50fa7b);
            font-size: 1.5rem;
            text-shadow: 0 0 5px var(--glow-color, rgba(80, 250, 123, 0.5));
        }
        
        .license-modal-body {
            padding: 20px;
            text-align: center;
        }
        
        .license-modal-icon {
            font-size: 4rem;
            color: #ff5555;
            margin: 20px 0;
            animation: pulse 2s infinite;
        }
        
        .license-modal-footer {
            padding: 15px;
            border-top: 1px solid var(--border-color, #44475a);
            text-align: center;
        }
        
        @keyframes modalFadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }
    `;
    
    // Añadir el modal y los estilos al documento
    document.head.appendChild(styles);
    document.body.appendChild(modal);
    
    // Impedir el scroll del fondo
    document.body.style.overflow = 'hidden';
    
    // Redirigir a la página de activación después de 3 segundos
    setTimeout(() => {
        window.location.href = 'license_activation.php';
    }, 3000);
}

// Verificar si necesitamos mostrar el modal de licencia
document.addEventListener('DOMContentLoaded', function() {
    // Si existe un elemento con clase 'license-badge' que contiene 'Expirada'
    const licenseBadge = document.querySelector('.license-badge');
    if (licenseBadge && licenseBadge.textContent.includes('Expirada')) {
        // Mostrar el modal de licencia expirada automáticamente
        showLicenseModal();
    }
    
    // Mostrar los días restantes con un contador
    const daysElement = document.getElementById('licenseRemainingDays');
    if (daysElement) {
        const days = parseInt(daysElement.getAttribute('data-days'));
        if (days <= 5 && days > 0) {
            daysElement.classList.add('license-expiring-soon');
            daysElement.innerHTML = `<i class="fas fa-exclamation-circle"></i> ${days} días restantes`;
        }
    }
});