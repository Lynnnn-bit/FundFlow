document.addEventListener('DOMContentLoaded', () => {
    
    const form = document.getElementById('partnerForm');
    const errorContainer = document.createElement('div');
    errorContainer.id = 'error-container';
    form.prepend(errorContainer);

    // Input formatting
    const telephone = form.querySelector('[name="telephone"]');
    telephone.addEventListener('input', () => {
        telephone.value = telephone.value.replace(/\D/g, '').slice(0, 8);
    });

    const montant = form.querySelector('[name="montant"]');
    montant.addEventListener('input', () => {
        let value = montant.value.replace(/\D/g, '');
        value = value ? parseInt(value, 10) : '';
        montant.value = value === '' ? '' : value.toLocaleString('fr-FR');
    });

    // Form submission
    form.addEventListener('submit', (e) => {
        e.preventDefault();
        clearErrors();

        const errors = validateForm();
        
        if (Object.keys(errors).length > 0) {
            displayErrors(errors);
        } else {
            // Remove formatting before submission
            montant.value = montant.value.replace(/\s/g, '');
            form.submit();
        }
    });

    function validateForm() {
        const errors = {};
        const values = {
            nom: form.nom.value.trim(),
            email: form.email.value.trim(),
            telephone: form.telephone.value.replace(/\D/g, ''),
            montant: form.montant.value.replace(/\D/g, ''),
            description: form.description.value.trim()
        };

        // Name validation
        if (!values.nom) {
            errors.nom = "Nom de l'entreprise requis";
        } else if (values.nom.length < 2) {
            errors.nom = "Le nom doit contenir au moins 2 caractères";
        }

        // Email validation
        if (!values.email) {
            errors.email = "Email requis";
        } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(values.email)) {
            errors.email = "Format d'email invalide";
        }

        // Phone validation
        if (!values.telephone) {
            errors.telephone = "Téléphone requis";
        } else if (values.telephone.length !== 8) {
            errors.telephone = "Doit contenir 8 chiffres";
        }

        // Amount validation
        if (!values.montant) {
            errors.montant = "Montant requis";
        } else {
            const amount = parseInt(values.montant);
            if (amount < 1000) errors.montant = "Minimum 1 000 €";
            if (amount > 1000000) errors.montant = "Maximum 1 000 000 €";
        }

        // Description validation
        if (!values.description) {
            errors.description = "Description requise";
        } else if (values.description.length < 20) {
            errors.description = "20 caractères minimum requis";
        }

        return errors;
    }

    function displayErrors(errors) {
        let errorHTML = '<div class="alert alert-danger"><ul>';
        
        Object.entries(errors).forEach(([field, message]) => {
            errorHTML += `<li>${message}</li>`;
            const input = form[field];
            input.classList.add('is-invalid');
            
            const errorElement = document.createElement('div');
            errorElement.className = 'invalid-feedback';
            errorElement.textContent = message;
            input.parentNode.appendChild(errorElement);
        });
        
        errorContainer.innerHTML = errorHTML + '</ul></div>';
    }

    function clearErrors() {
        document.querySelectorAll('.invalid-feedback').forEach(el => el.remove());
        Array.from(form.elements).forEach(el => el.classList.remove('is-invalid'));
        errorContainer.innerHTML = '';
    }
});