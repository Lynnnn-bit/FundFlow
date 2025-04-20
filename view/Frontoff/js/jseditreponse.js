// Frontoff/js/jseditreponse.js
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    const decisionSelect = document.querySelector('select[name="decision"]');
    const montantInput = document.querySelector('input[name="montant_accorde"]');
    const dateInput = document.querySelector('input[name="date_reponse"]');
    const messageInput = document.querySelector('textarea[name="message"]');
    const statusSelect = document.querySelector('select[name="status"]');

    // Validation functions
    const validators = {
        montant: function() {
            const decision = decisionSelect.value;
            const value = parseInt(montantInput.value);
            
            if (decision === 'accepte') {
                if (isNaN(value) || value < 1000 || value > 1000000) {
                    showError(montantInput, 'Le montant accordé doit être entre 1 000€ et 1 000 000€');
                    return false;
                }
            } else {
                if (value !== 0) {
                    showError(montantInput, 'Le montant doit être 0€ pour un refus');
                    return false;
                }
            }
            clearError(montantInput);
            return true;
        },

        date: function() {
            const selectedDate = new Date(dateInput.value);
            const today = new Date();
            today.setHours(0, 0, 0, 0);

            if (!dateInput.value) {
                showError(dateInput, 'La date de réponse est obligatoire');
                return false;
            }
            
            if (selectedDate > today) {
                showError(dateInput, 'La date ne peut pas être dans le futur');
                return false;
            }
            
            clearError(dateInput);
            return true;
        },

        message: function() {
            if (messageInput.value.trim().length < 20) {
                showError(messageInput, 'Le message doit contenir au moins 20 caractères');
                return false;
            }
            clearError(messageInput);
            return true;
        },

        status: function() {
            if (!statusSelect.value) {
                showError(statusSelect, 'Veuillez sélectionner un statut');
                return false;
            }
            clearError(statusSelect);
            return true;
        }
    };

    // Helper functions
    function showError(input, message) {
        clearError(input);
        const errorDiv = document.createElement('div');
        errorDiv.className = 'text-danger mt-1 small';
        errorDiv.textContent = message;
        input.parentNode.appendChild(errorDiv);
        input.classList.add('is-invalid');
    }

    function clearError(input) {
        const errorDiv = input.parentNode.querySelector('.text-danger');
        if (errorDiv) errorDiv.remove();
        input.classList.remove('is-invalid');
    }

    // Decision change handler
    function handleDecisionChange() {
        const isAccepte = decisionSelect.value === 'accepte';
        montantInput.disabled = !isAccepte;
        
        if (!isAccepte) {
            montantInput.value = '0';
        }
        validators.montant();
    }

    // Event listeners
    decisionSelect.addEventListener('change', handleDecisionChange);
    montantInput.addEventListener('input', validators.montant);
    dateInput.addEventListener('change', validators.date);
    messageInput.addEventListener('input', validators.message);
    statusSelect.addEventListener('change', validators.status);

    // Form submission handler
    form.addEventListener('submit', function(e) {
        let isValid = true;
        
        // Run all validations
        Object.values(validators).forEach(validator => {
            if (!validator()) isValid = false;
        });

        if (!isValid) {
            e.preventDefault();
            // Focus on first invalid field
            const firstInvalid = form.querySelector('.is-invalid');
            if (firstInvalid) firstInvalid.focus();
        }
    });

    // Initial setup
    handleDecisionChange();
});