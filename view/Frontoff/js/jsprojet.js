document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form[method="POST"]');
    const titreInput = document.querySelector('input[name="titre"]');
    const descriptionInput = document.querySelector('textarea[name="description"]');
    const montantInput = document.querySelector('input[name="montant_cible"]');
    const dureeInput = document.querySelector('input[name="duree"]');

    // Create error message elements
    function createErrorElement(input) {
        const error = document.createElement('div');
        error.className = 'error-message';
        error.style.color = 'red';
        error.style.marginTop = '5px';
        input.parentNode.insertBefore(error, input.nextSibling);
        return error;
    }

    const titreError = createErrorElement(titreInput);
    const descriptionError = createErrorElement(descriptionInput);
    const montantError = createErrorElement(montantInput);
    const dureeError = createErrorElement(dureeInput);

    function validateTitre() {
        const value = titreInput.value.trim();
<<<<<<< HEAD
        const isValid = value.length > 0 && value.length <= 100;
        
        if (value.length === 0) {
            titreError.textContent = 'Le titre est obligatoire';
        } else if (value.length > 100) {
            titreError.textContent = 'Le titre ne doit pas dépasser 100 caractères';
=======
        const isValid = value.length > 0 && value.length <= 20;
        
        if (value.length === 0) {
            titreError.textContent = 'Le titre est obligatoire';
        } else if (value.length > 20) {
            titreError.textContent = 'Le titre ne doit pas dépasser 20 caractères';
>>>>>>> d0210638b57fd33a4db9e98e7a8c3f062c8beab9
        } else {
            titreError.textContent = '';
        }
        return isValid;
    }

    function validateDescription() {
        const value = descriptionInput.value.trim();
<<<<<<< HEAD
        const isValid = value.length > 0 && value.length <= 2000;
        
        if (value.length === 0) {
            descriptionError.textContent = 'La description est obligatoire';
        } else if (value.length > 2000) {
            descriptionError.textContent = 'La description ne doit pas dépasser 2000 caractères';
=======
        const isValid = value.length > 0;
        
        if (value.length === 0) {
            descriptionError.textContent = 'La description est obligatoire';
>>>>>>> d0210638b57fd33a4db9e98e7a8c3f062c8beab9
        } else {
            descriptionError.textContent = '';
        }
        return isValid;
    }

    function validateMontant() {
        const value = parseFloat(montantInput.value);
        const isValid = !isNaN(value) && value >= 10000 && value <= 1000000;
        
        if (!isValid) {
            montantError.textContent = 'Le montant doit être compris entre 10 000 € et 1 000 000 €';
        } else {
            montantError.textContent = '';
        }
        return isValid;
    }

    function validateDuree() {
        const value = parseInt(dureeInput.value);
        const isValid = !isNaN(value) && value >= 6 && value <= 60;
        
        if (!isValid) {
            dureeError.textContent = 'La durée doit être comprise entre 6 et 60 mois';
        } else {
            dureeError.textContent = '';
        }
        return isValid;
    }

    // Real-time validation
    titreInput.addEventListener('input', validateTitre);
    descriptionInput.addEventListener('input', validateDescription);
    montantInput.addEventListener('input', validateMontant);
    dureeInput.addEventListener('input', validateDuree);

    // Form submission handling
    form.addEventListener('submit', function(e) {
        const isTitreValid = validateTitre();
        const isDescriptionValid = validateDescription();
        const isMontantValid = validateMontant();
        const isDureeValid = validateDuree();

        if (!isTitreValid || !isDescriptionValid || !isMontantValid || !isDureeValid) {
            e.preventDefault();
            
            // Scroll to first error
            const firstInvalid = [
                !isTitreValid && titreInput,
                !isDescriptionValid && descriptionInput,
                !isMontantValid && montantInput,
                !isDureeValid && dureeInput
            ].find(el => el);
            
            if (firstInvalid) {
                firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
                firstInvalid.focus();
            }
        }
    });

    // Initial validation for edit mode
    if (titreInput.value) validateTitre();
    if (descriptionInput.value) validateDescription();
    if (montantInput.value) validateMontant();
    if (dureeInput.value) validateDuree();
});