document.addEventListener('DOMContentLoaded', function () {
    const form = document.querySelector('form');
    if (!form) return;

    // Input fields
    const idInput = document.querySelector('input[name="id_categorie"]');
    const nomCategorieInput = document.querySelector('input[name="nom_categorie"]');
    const descriptionInput = document.querySelector('textarea[name="description"]');
    if (!idInput || !nomCategorieInput || !descriptionInput) return;

    // Error message containers
    const idCategorieError = document.createElement('small');
    idCategorieError.className = 'error-message';
    idInput.parentElement.appendChild(idCategorieError);

    const nomCategorieError = document.createElement('small');
    nomCategorieError.className = 'error-message';
    nomCategorieInput.parentElement.appendChild(nomCategorieError);

    const descriptionError = document.createElement('small');
    descriptionError.className = 'error-message';
    descriptionInput.parentElement.appendChild(descriptionError);

    // Validation functions
    const validateIdCategorie = () => {
        const value = idInput.value.trim();
        let isValid = true;

        if (!value) {
            idCategorieError.textContent = 'L\'ID de catégorie est obligatoire.';
            isValid = false;
        } else if (!/^\d+$/.test(value)) {
            idCategorieError.textContent = 'L\'ID de catégorie doit contenir uniquement des chiffres.';
            isValid = false;
        } else if (value.length !== 8) {
            idCategorieError.textContent = 'L\'ID de catégorie doit contenir exactement 8 chiffres.';
            isValid = false;
        } else {
            idCategorieError.textContent = '';
        }

        idInput.classList.toggle('invalid', !isValid);
        return isValid;
    };

    const validateNomCategorie = () => {
        const value = nomCategorieInput.value.trim();
        let isValid = true;

        if (!value) {
            nomCategorieError.textContent = 'Le nom de la catégorie est obligatoire.';
            isValid = false;
        } else if (value.length < 3 || value.length > 50) {
            nomCategorieError.textContent = 'Le nom de la catégorie doit contenir entre 3 et 50 caractères.';
            isValid = false;
        } else {
            nomCategorieError.textContent = '';
        }

        nomCategorieInput.classList.toggle('invalid', !isValid);
        return isValid;
    };

    const validateDescription = () => {
        const value = descriptionInput.value.trim();
        const wordCount = value.split(/\s+/).filter(word => word.length > 0).length;
        let isValid = true;

        if (!value) {
            descriptionError.textContent = 'La description est obligatoire.';
            isValid = false;
        } else if (wordCount > 100) {
            descriptionError.textContent = 'La description ne doit pas dépasser 100 mots.';
            isValid = false;
        } else {
            descriptionError.textContent = '';
        }

        descriptionInput.classList.toggle('invalid', !isValid);
        return isValid;
    };

    // Real-time validation
    idInput.addEventListener('input', validateIdCategorie);
    nomCategorieInput.addEventListener('input', validateNomCategorie);
    descriptionInput.addEventListener('input', validateDescription);

    // Form submission validation
    form.addEventListener('submit', function (event) {
        const isIdValid = validateIdCategorie();
        const isNomCategorieValid = validateNomCategorie();
        const isDescriptionValid = validateDescription();

        if (!isIdValid || !isNomCategorieValid || !isDescriptionValid) {
            event.preventDefault();
        }
    });
});
