document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('.form-wrapper form');
    
    if (form) {
        // Add input event listeners for real-time validation
        const fieldsToValidate = form.querySelectorAll('input[name], textarea[name]');
        fieldsToValidate.forEach(field => {
            field.addEventListener('input', () => validateField(field));
        });

        form.addEventListener('submit', function(e) {
            let isValid = true;
            
            // Validate all fields
            const fields = [
                {name: 'nom_startup', required: true, minLength: 3},
                {name: 'secteur', required: true},
                {name: 'adresse_site', required: true, type: 'url'},
                {name: 'description', required: true, minLength: 10},
                {name: 'email', required: true, type: 'email'},
                {name: 'logo', type: 'file', accept: 'image/*'},
                {name: 'video_presentation', type: 'file', accept: 'video/*'}
            ];

            fields.forEach(fieldConfig => {
                const input = form.querySelector(`[name="${fieldConfig.name}"]`);
                const validation = validateField(input, fieldConfig);
                if (!validation.isValid) isValid = false;
            });

            if (!isValid) {
                e.preventDefault();
            }
        });
    }

    function validateField(input, config = {}) {
        const value = input.type === 'file' ? input.files[0] : input.value.trim();
        let isValid = true;
        let message = '';

        // Clear previous error
        const errorElement = input.nextElementSibling;
        if (errorElement && errorElement.classList.contains('error-message')) {
            errorElement.remove();
        }

        // Required validation
        if (config.required && !value && input.type !== 'file') {
            isValid = false;
            message = 'Ce champ est requis';
        }

        // Minimum length
        if (config.minLength && value.length < config.minLength) {
            isValid = false;
            message = `Minimum ${config.minLength} caractères requis`;
        }

        // Email validation
        if (config.type === 'email' && value) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(value)) {
                isValid = false;
                message = 'Format email invalide';
            }
        }

        // URL validation
        if (config.type === 'url' && value) {
            const urlRegex = /^(https?:\/\/)/;
            if (!urlRegex.test(value)) {
                isValid = false;
                message = 'URL doit commencer par http:// ou https://';
            }
        }

        // File validation
        if (config.type === 'file' && value) {
            const fileType = value.type;
            const acceptedTypes = config.accept.split(',');
            
            if (!acceptedTypes.some(type => {
                const cleanType = type.replace('*', '');
                return fileType.startsWith(cleanType);
            })) {
                isValid = false;
                message = `Type de fichier non supporté (${config.accept})`;
            }
        }

        if (!isValid) {
            showError(input, message);
        } else {
            input.style.border = '';
        }

        return { isValid, message };
    }

    function showError(input, message) {
        const errorDiv = document.createElement('div');
        errorDiv.className = 'error-message';
        errorDiv.textContent = message;
        errorDiv.style.color = '#ff4444';
        errorDiv.style.fontSize = '0.85em';
        errorDiv.style.marginTop = '5px';
        input.insertAdjacentElement('afterend', errorDiv);
        input.style.border = '1px solid #ff4444';
    }
});