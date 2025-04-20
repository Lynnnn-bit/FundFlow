document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('partenaireForm');
    const nomInput = document.getElementById('nom');
    const emailInput = document.getElementById('email');
    const telephoneInput = document.getElementById('telephone');
    const montantInput = document.getElementById('montant');
    const descriptionInput = document.getElementById('description');

    // Validation functions
    function validateNom() {
        const nom = nomInput.value.trim();
        if (nom.length < 2 || nom.length > 50) {
            showError(nomInput, 'Le nom doit contenir entre 2 et 50 caractères');
            return false;
        }
        showSuccess(nomInput);
        return true;
    }

    function validateEmail() {
        const email = emailInput.value.trim();
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!re.test(email)) {
            showError(emailInput, 'Veuillez entrer une adresse email valide');
            return false;
        }
        showSuccess(emailInput);
        return true;
    }

    function validateTelephone() {
        const telephone = telephoneInput.value.trim();
        const re = /^[0-9]{10}$/;
        if (!re.test(telephone)) {
            showError(telephoneInput, 'Le téléphone doit contenir 10 chiffres');
            return false;
        }
        showSuccess(telephoneInput);
        return true;
    }

    function validateMontant() {
        const montant = parseFloat(montantInput.value);
        if (isNaN(montant) || montant <= 0) {
            showError(montantInput, 'Le montant doit être un nombre positif');
            return false;
        }
        showSuccess(montantInput);
        return true;
    }

    function validateDescription() {
        const description = descriptionInput.value.trim();
        if (description.length < 10 || description.length > 500) {
            showError(descriptionInput, 'La description doit contenir entre 10 et 500 caractères');
            return false;
        }
        showSuccess(descriptionInput);
        return true;
    }

    // Helper functions
    function showError(input, message) {
        const formGroup = input.closest('.form-group');
        const errorElement = formGroup.querySelector('.error-message');
        
        errorElement.textContent = message;
        input.classList.remove('is-valid');
        input.classList.add('is-invalid');
    }

    function showSuccess(input) {
        const formGroup = input.closest('.form-group');
        const errorElement = formGroup.querySelector('.error-message');
        
        errorElement.textContent = '';
        input.classList.remove('is-invalid');
        input.classList.add('is-valid');
    }

    // Event listeners for real-time validation
    nomInput.addEventListener('blur', validateNom);
    emailInput.addEventListener('blur', validateEmail);
    telephoneInput.addEventListener('blur', validateTelephone);
    montantInput.addEventListener('blur', validateMontant);
    descriptionInput.addEventListener('blur', validateDescription);

    // Form submission
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Validate all fields
        const isNomValid = validateNom();
        const isEmailValid = validateEmail();
        const isTelephoneValid = validateTelephone();
        const isMontantValid = validateMontant();
        const isDescriptionValid = validateDescription();

        if (isNomValid && isEmailValid && isTelephoneValid && 
            isMontantValid && isDescriptionValid) {
            // Form is valid, submit it
            form.submit();
        } else {
            // Scroll to first error
            const firstError = form.querySelector('.is-invalid');
            if (firstError) {
                firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }
    });

    // Cancel button
    const cancelBtn = document.querySelector('.btn-cancel');
    if (cancelBtn) {
        cancelBtn.addEventListener('click', function() {
            if (confirm('Voulez-vous vraiment annuler ?')) {
                form.reset();
                // Reset validation states
                const inputs = form.querySelectorAll('input, textarea');
                inputs.forEach(input => {
                    input.classList.remove('is-valid', 'is-invalid');
                    const formGroup = input.closest('.form-group');
                    if (formGroup) {
                        const errorElement = formGroup.querySelector('.error-message');
                        if (errorElement) {
                            errorElement.textContent = '';
                        }
                    }
                });
            }
        });
    }
});