document.getElementById('startupForm').addEventListener('submit', function(e) {
    e.preventDefault();
    let isValid = true;
    const errors = {};

    const validationRules = {
        'nom_startup': { message: 'Le nom est requis' },
        'secteur': { message: 'Le secteur est requis' },
        'adresse_site': { message: "L'adresse du site est requise" },
        'description': { message: 'La description est requise' },
        'email': {
            message: 'Email invalide',
            validator: (value) => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)
        },
        'logo': { message: 'Le logo est requis' },
        'video_presentation': { message: 'La vidéo est requise' }
    };

    document.querySelectorAll('.error').forEach(el => {
        el.style.display = 'none';
        el.textContent = '';
    });

    for (const [field, rule] of Object.entries(validationRules)) {
        const input = this.elements[field];
        let value = input.type === 'file' ? input.files.length > 0 : input.value.trim();

        if (!value) {
            errors[field] = rule.message;
            isValid = false;
        } else if (field === 'email' && !rule.validator(input.value.trim())) {
            errors[field] = rule.message;
            isValid = false;
        }
    }

    for (const [field, message] of Object.entries(errors)) {
        const errorDiv = document.querySelector(`[data-error="${field}"]`);
        if (errorDiv) {
            errorDiv.textContent = message;
            errorDiv.style.display = 'block';
        }
    }

    if (isValid) this.submit();
});
