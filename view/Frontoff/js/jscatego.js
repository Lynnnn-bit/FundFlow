document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    if (!form) return;

    // Add error message container
    const errorContainer = document.createElement('div');
    errorContainer.id = 'idCategorieError';
    errorContainer.style.display = 'none';
    errorContainer.style.color = 'red';
    errorContainer.style.marginBottom = '10px';
    form.querySelector('.form-group').prepend(errorContainer);

    const idInput = document.querySelector('input[name="id_categorie"]');
    if (!idInput) return;

    // Check if in edit mode
    const isEditInput = document.querySelector('input[name="is_edit"]');

    // Validation function
    const validateId = () => {
        if (isEditInput && isEditInput.value === 'true') return true;

        const idValue = idInput.value.trim();
        const idNumber = parseInt(idValue, 10);

        let isValid = true;
        
        if (!idValue) {
            errorContainer.textContent = 'L\'ID de catégorie est obligatoire.';
            isValid = false;
        } else if (!/^\d+$/.test(idValue)) {
            errorContainer.textContent = 'L\'ID de catégorie doit contenir uniquement des chiffres.';
            isValid = false;
        } else if (idValue.length !== 8) {
            errorContainer.textContent = 'L\'ID de catégorie doit contenir exactement 8 chiffres.';
            isValid = false;
        } else if (idNumber <= 0) {
            errorContainer.textContent = 'L\'ID de catégorie doit être un nombre positif.';
            isValid = false;
        } else {
            errorContainer.textContent = '';
            isValid = true;
        }

        errorContainer.style.display = isValid ? 'none' : 'block';
        return isValid;
    };

    // Real-time validation
    idInput.addEventListener('input', function() {
        if (!isEditInput || isEditInput.value !== 'true') {
            validateId();
        }
    });

    // Form submission validation
    form.addEventListener('submit', function(event) {
        if (!isEditInput || isEditInput.value !== 'true') {
            if (!validateId()) {
                event.preventDefault();
                idInput.focus();
            }
        }
    });

    // Initial validation check
    if (!isEditInput || isEditInput.value !== 'true') {
        validateId();
    }
});