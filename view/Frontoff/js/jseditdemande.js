// validation.js
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    const montantInput = document.querySelector('input[name="montant"]');
    const dureeInput = document.querySelector('input[name="duree"]');

    function showError(input, message) {
        const formGroup = input.closest('.form-group');
        let errorElement = formGroup.querySelector('.error-message');
        
        if (!errorElement) {
            errorElement = document.createElement('div');
            errorElement.className = 'error-message';
            formGroup.appendChild(errorElement);
        }

        errorElement.style.color = '#dc3545';
        errorElement.style.fontSize = '0.875em';
        errorElement.textContent = message;
    }

    function clearError(input) {
        const formGroup = input.closest('.form-group');
        const errorElement = formGroup.querySelector('.error-message');
        if (errorElement) {
            errorElement.remove();
        }
    }

    function validateMontant() {
        const value = parseFloat(montantInput.value);
        const min = 10000;
        const max = 1000000;
        
        if (isNaN(value) || value < min || value > max) {
            showError(montantInput, `Le montant doit être entre ${min.toLocaleString()} € et ${max.toLocaleString()} €`);
            return false;
        }
        
        clearError(montantInput);
        return true;
    }

    function validateDuree() {
        const value = parseInt(dureeInput.value);
        const min = 6;
        const max = 60;
        
        if (isNaN(value) || value < min || value > max) {
            showError(dureeInput, `La durée doit être entre ${min} et ${max} mois`);
            return false;
        }
        
        clearError(dureeInput);
        return true;
    }

    function validateForm() {
        let isValid = true;
        
        if (!validateMontant()) isValid = false;
        if (!validateDuree()) isValid = false;
        
        return isValid;
    }

    // Real-time validation while typing
    montantInput.addEventListener('input', function() {
        validateMontant();
    });

    dureeInput.addEventListener('input', function() {
        validateDuree();
    });

    // Form submission validation
    form.addEventListener('submit', function(e) {
        if (!validateForm()) {
            e.preventDefault();
        }
    });

    // Initial validation for edit mode pre-filled values
    if (montantInput.value || dureeInput.value) {
        validateMontant();
        validateDuree();
    }
});