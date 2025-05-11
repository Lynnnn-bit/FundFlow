document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    if (!form) {
        console.error("Formulaire non trouvé");
        return;
    }

    // Sélection des champs avec vérification
    const getField = (name) => {
        const field = form.querySelector(`[name="${name}"]`);
        if (!field) console.error(`Champ ${name} non trouvé`);
        return field;
    };

    const titre = getField('titre');
    const description = getField('description');
    const montantCible = getField('montant_cible');
    const duree = getField('duree');
    const idCategorie = getField('id_categorie');

    if (!titre || !description || !montantCible || !duree || !idCategorie) return;

    // Fonctions utilitaires améliorées
    const showError = (field, message) => {
        // Crée un conteneur parent si nécessaire
        const fieldContainer = field.closest('.form-group') || field.parentNode;
        
        // Crée ou récupère l'élément d'erreur
        let errorElement = fieldContainer.querySelector('.error-message');
        if (!errorElement) {
            errorElement = document.createElement('div');
            errorElement.className = 'error-message';
            fieldContainer.appendChild(errorElement);
        }
        
        errorElement.textContent = message;
        errorElement.style.display = 'block';
        field.classList.add('is-invalid');
        
        // Ajoute une classe au conteneur parent
        fieldContainer.classList.add('has-error');
    };

    const hideError = (field) => {
        const fieldContainer = field.closest('.form-group') || field.parentNode;
        const errorElement = fieldContainer.querySelector('.error-message');
        
        if (errorElement) {
            errorElement.style.display = 'none';
        }
        
        field.classList.remove('is-invalid');
        fieldContainer.classList.remove('has-error');
    };

    // Validation en temps réel améliorée
    const setupValidation = (field, validationFn) => {
        field.addEventListener('input', () => {
            if (validationFn(field.value)) {
                hideError(field);
            }
        });
    };

    // Configurations de validation pour chaque champ
    setupValidation(titre, value => value.trim().length > 0 && value.trim().length <= 100);
    setupValidation(description, value => value.trim().length > 0 && value.trim().length <= 2000);
    setupValidation(montantCible, value => {
        const num = parseFloat(value);
        return !isNaN(num) && num >= 10000 && num <= 10000000;
    });
    setupValidation(duree, value => {
        const num = parseInt(value);
        return !isNaN(num) && num >= 6 && num <= 60;
    });
    idCategorie.addEventListener('change', () => {
        if (idCategorie.value) hideError(idCategorie);
    });

    // Validation à la soumission
    form.addEventListener('submit', function(event) {
        let isValid = true;

        // Validation Titre
        if (!titre.value.trim()) {
            showError(titre, "Le titre est obligatoire");
            isValid = false;
        } else if (titre.value.trim().length > 100) {
            showError(titre, "Le titre ne doit pas dépasser 100 caractères");
            isValid = false;
        }

        // Validation Description
        if (!description.value.trim()) {
            showError(description, "La description est obligatoire");
            isValid = false;
        } else if (description.value.trim().length > 2000) {
            showError(description, "La description ne doit pas dépasser 2000 caractères");
            isValid = false;
        }

        // Validation Montant
        const montant = parseFloat(montantCible.value);
        if (isNaN(montant)) {
            showError(montantCible, "Veuillez entrer un montant valide");
            isValid = false;
        } else if (montant < 10000 || montant > 10000000) {
            showError(montantCible, "Le montant doit être entre 10 000 € et 10 000 000 €");
            isValid = false;
        }

        // Validation Durée
        const dureeValue = parseInt(duree.value);
        if (isNaN(dureeValue)) {
            showError(duree, "Veuillez entrer une durée valide");
            isValid = false;
        } else if (dureeValue < 6 || dureeValue > 60) {
            showError(duree, "La durée doit être entre 6 et 60 mois");
            isValid = false;
        }

        // Validation Catégorie
        if (!idCategorie.value) {
            showError(idCategorie, "Veuillez sélectionner une catégorie");
            isValid = false;
        }

        if (!isValid) {
            event.preventDefault();
            // Scroll vers le premier champ invalide
            const firstInvalid = form.querySelector('.is-invalid');
            if (firstInvalid) {
                firstInvalid.scrollIntoView({ 
                    behavior: 'smooth', 
                    block: 'center' 
                });
                firstInvalid.focus();
            }
        }
    });
});