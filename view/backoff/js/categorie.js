document.addEventListener('DOMContentLoaded', function () {
    const form = document.querySelector('form');
    if (!form) return;

    // Input fields
    const titreInput = document.querySelector('input[name="titre"]');
    const descriptionInput = document.querySelector('textarea[name="description"]');
    const montantInput = document.querySelector('input[name="montant_cible"]');
    const dureeInput = document.querySelector('input[name="duree"]');
    const categorieSelect = document.querySelector('select[name="id_categorie"]');

    // Create error message elements
    const createErrorElement = (inputElement) => {
        const errorElement = document.createElement('small');
        errorElement.className = 'error-message';
        errorElement.style.color = 'red';
        errorElement.style.display = 'block';
        errorElement.style.marginTop = '5px';
        inputElement.parentNode.appendChild(errorElement);
        return errorElement;
    };

    // Error elements
    const titreError = createErrorElement(titreInput);
    const descriptionError = createErrorElement(descriptionInput);
    const montantError = createErrorElement(montantInput);
    const dureeError = createErrorElement(dureeInput);
    const categorieError = createErrorElement(categorieSelect);

    // Validation functions
    const validateTitre = () => {
        const value = titreInput.value.trim();
        let isValid = true;

        if (!value) {
            titreError.textContent = 'Le titre est obligatoire.';
            isValid = false;
        } else if (value.length > 100) {
            titreError.textContent = 'Le titre ne doit pas dépasser 100 caractères.';
            isValid = false;
        } else {
            titreError.textContent = '';
        }

        titreInput.classList.toggle('is-invalid', !isValid);
        return isValid;
    };

    const validateDescription = () => {
        const value = descriptionInput.value.trim();
        let isValid = true;

        if (!value) {
            descriptionError.textContent = 'La description est obligatoire.';
            isValid = false;
        } else if (value.length > 2000) {
            descriptionError.textContent = 'La description ne doit pas dépasser 2000 caractères.';
            isValid = false;
        } else {
            descriptionError.textContent = '';
        }

        descriptionInput.classList.toggle('is-invalid', !isValid);
        return isValid;
    };

    const validateMontant = () => {
        const value = parseFloat(montantInput.value);
        let isValid = true;

        if (isNaN(value) || montantInput.value.trim() === "") {
            montantError.textContent = 'Veuillez entrer un montant valide.';
            isValid = false;
        } else if (value < 10000 || value > 10000000) {
            montantError.textContent = 'Le montant doit être entre 10 000 € et 10 000 000 €.';
            isValid = false;
        } else {
            montantError.textContent = '';
        }

        montantInput.classList.toggle('is-invalid', !isValid);
        return isValid;
    };

    const validateDuree = () => {
        const value = parseInt(dureeInput.value, 10);
        let isValid = true;

        if (isNaN(value) || dureeInput.value.trim() === "") {
            dureeError.textContent = 'Veuillez entrer une durée valide.';
            isValid = false;
        } else if (value < 6 || value > 60) {
            dureeError.textContent = 'La durée doit être entre 6 et 60 mois.';
            isValid = false;
        } else {
            dureeError.textContent = '';
        }

        dureeInput.classList.toggle('is-invalid', !isValid);
        return isValid;
    };

    const validateCategorie = () => {
        const value = categorieSelect.value;
        let isValid = true;

        if (!value) {
            categorieError.textContent = 'Veuillez sélectionner une catégorie.';
            isValid = false;
        } else {
            categorieError.textContent = '';
        }

        categorieSelect.classList.toggle('is-invalid', !isValid);
        return isValid;
    };

    // Real-time validation
    titreInput.addEventListener('input', validateTitre);
    descriptionInput.addEventListener('input', validateDescription);
    montantInput.addEventListener('input', validateMontant);
    dureeInput.addEventListener('input', validateDuree);
    categorieSelect.addEventListener('change', validateCategorie);

    // Form submission validation
    form.addEventListener('submit', function (event) {
        const isTitreValid = validateTitre();
        const isDescriptionValid = validateDescription();
        const isMontantValid = validateMontant();
        const isDureeValid = validateDuree();
        const isCategorieValid = validateCategorie();

        if (!isTitreValid || !isDescriptionValid || !isMontantValid || !isDureeValid || !isCategorieValid) {
            event.preventDefault();
            
            // Scroll to the first error
            const firstError = form.querySelector('.is-invalid');
            if (firstError) {
                firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                firstError.focus();
            }
        }
    });

    // Initialize validation
    validateTitre();
    validateDescription();
    validateMontant();
    validateDuree();
    validateCategorie();
});