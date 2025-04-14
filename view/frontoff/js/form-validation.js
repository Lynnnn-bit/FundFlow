document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('partnerForm');
    const fields = {
        nom: form.querySelector('[name="nom"]'),
        email: form.querySelector('[name="email"]'),
        telephone: form.querySelector('[name="telephone"]'),
        montant: form.querySelector('[name="montant"]'),
        description: form.querySelector('[name="description"]')
    };

    // Input formatting
    fields.telephone.addEventListener('input', formatPhone);
    fields.montant.addEventListener('input', formatAmount);

    form.addEventListener('submit', handleSubmit);

    function formatPhone() {
        this.value = this.value.replace(/\D/g, '').slice(0, 8);
    }

    function formatAmount() {
        let value = this.value.replace(/\D/g, '');
        value = value ? parseInt(value, 10) : 0;
        this.value = value.toLocaleString('fr-FR');
    }

    function handleSubmit(e) {
        e.preventDefault();
        clearErrors();

        const errors = validateForm();
        
        if (Object.keys(errors).length > 0) {
            showErrors(errors);
        } else {
            submitForm();
        }
    }

    function validateForm() {
        const errors = {};
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

        // Name validation
        if (!fields.nom.value.trim()) {
            errors.nom = "Nom de l'entreprise requis";
        }

        // Email validation
        if (!emailRegex.test(fields.email.value.trim())) {
            errors.email = "Email invalide";
        }

        // Phone validation
        const phone = fields.telephone.value.replace(/\D/g, '');
        if (phone && phone.length !== 8) {
            errors.telephone = "8 chiffres requis";
        }

        // Amount validation
        const amount = parseInt(fields.montant.value.replace(/\D/g, '') || 0);
        if (isNaN(amount)) {
            errors.montant = "Montant invalide";
        } else if (amount = 1000 || amount > 1000000) {
            errors.montant = "Doit être entre 1 000 € et 1 000 000 €";
        }

        return errors;
    }

    function showErrors(errors) {
        Object.entries(errors).forEach(([field, message]) => {
            const error = document.createElement('div');
            error.className = 'error-message';
            error.textContent = message;
            fields[field].parentNode.insertBefore(error, fields[field].nextSibling);
        });
    }

    function clearErrors() {
        document.querySelectorAll('.error-message').forEach(el => el.remove());
    }

    function submitForm() {
        const formData = new FormData();
        formData.append('nom', fields.nom.value.trim());
        formData.append('email', fields.email.value.trim());
        formData.append('telephone', fields.telephone.value.replace(/\D/g, ''));
        formData.append('montant', parseInt(fields.montant.value.replace(/\D/g, '')));
        formData.append('description', fields.description.value.trim());
        formData.append('csrf_token', form.querySelector('[name="csrf_token"]').value);
        formData.append('submit', '1');

        fetch(form.action, {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (response.redirected) window.location.href = response.url;
            else return response.text();
        })
        .catch(error => console.error('Error:', error));
    }
});