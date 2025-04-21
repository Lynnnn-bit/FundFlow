// Password strength indicator
const passwordInput = document.getElementById('mdp');
const strengthText = document.getElementById('strengthText');
const passwordStrength = document.getElementById('passwordStrength');

if (passwordInput && strengthText) {
    passwordInput.addEventListener('input', function() {
        const password = this.value;
        let strength = 0;
        let text = 'Faible';
        let className = 'strength-weak';
        
        // Check length
        if (password.length >= 8) strength++;
        if (password.length >= 12) strength++;
        
        // Check for mixed case
        if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength++;
        
        // Check for numbers
        if (/\d/.test(password)) strength++;
        
        // Check for special chars
        if (/[^a-zA-Z0-9]/.test(password)) strength++;
        
        // Determine strength level
        if (strength > 3) {
            text = 'Fort';
            className = 'strength-strong';
        } else if (strength > 1) {
            text = 'Moyen';
            className = 'strength-medium';
        }
        
        // Update display
        strengthText.textContent = text;
        strengthText.className = className;
    });
}

// Form validation
const form = document.getElementById('registrationForm');
if (form) {
    form.addEventListener('submit', function(e) {
        // Reset previous errors
        document.querySelectorAll('.error-message').forEach(el => el.remove());
        document.querySelectorAll('.error-border').forEach(el => el.classList.remove('error-border'));
        
        let isValid = true;
        
        // Validate nom (not empty, only letters and spaces)
        const nomInput = document.getElementById('nom');
        if (!nomInput.value.trim()) {
            showError(nomInput, "Le nom est obligatoire");
            isValid = false;
        } else if (!/^[a-zA-ZÀ-ÿ\s\-']+$/.test(nomInput.value)) {
            showError(nomInput, "Le nom ne doit contenir que des lettres");
            isValid = false;
        }
        
        // Validate prenom (not empty, only letters and spaces)
        const prenomInput = document.getElementById('prenom');
        if (!prenomInput.value.trim()) {
            showError(prenomInput, "Le prénom est obligatoire");
            isValid = false;
        } else if (!/^[a-zA-ZÀ-ÿ\s\-']+$/.test(prenomInput.value)) {
            showError(prenomInput, "Le prénom ne doit contenir que des lettres");
            isValid = false;
        }
        
        // Validate email (proper format)
        const emailInput = document.getElementById('email');
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailInput.value.trim()) {
            showError(emailInput, "L'email est obligatoire");
            isValid = false;
        } else if (!emailRegex.test(emailInput.value)) {
            showError(emailInput, "Format d'email invalide (ex: exemple@domaine.com)");
            isValid = false;
        }
        
        // Validate role (must be selected)
        const roleInput = document.getElementById('role');
        if (!roleInput.value) {
            showError(roleInput, "Veuillez sélectionner un rôle");
            isValid = false;
        }
        
        // Validate password
        const password = document.getElementById('mdp').value;
        if (!password) {
            showError(document.getElementById('mdp'), "Le mot de passe est obligatoire");
            isValid = false;
        } else if (password.length < 8) {
            showError(document.getElementById('mdp'), "Le mot de passe doit contenir au moins 8 caractères");
            isValid = false;
        }
        
        // Validate password confirmation
        const confirmPassword = document.getElementById('confirmation_mdp').value;
        if (password !== confirmPassword) {
            showError(document.getElementById('confirmation_mdp'), "Les mots de passe ne correspondent pas");
            isValid = false;
        }
        
        // Validate adresse (not empty, minimum length)
        const adresseInput = document.getElementById('adresse');
        if (!adresseInput.value.trim()) {
            showError(adresseInput, "L'adresse est obligatoire");
            isValid = false;
        } else if (adresseInput.value.trim().length < 10) {
            showError(adresseInput, "L'adresse doit contenir au moins 10 caractères");
            isValid = false;
        }
        
        // Dans la section de validation du formulaire (submit)
    const telInput = document.getElementById('tel');
    if (telInput.value.trim()) { // Si le champ n'est pas vide
        if (!/^\d{8}$/.test(telInput.value)) {
            showError(telInput, "Le téléphone doit contenir exactement 8 chiffres");
            isValid = false;
    }
}
   

        if (!isValid) {
            e.preventDefault();
            // Scroll to first error
            const firstError = document.querySelector('.error-border');
            if (firstError) {
                firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
            return false;
        }
        
        return true;
    });
}

// Helper function to display error messages
function showError(input, message) {
    input.classList.add('error-border');
    
    const errorElement = document.createElement('div');
    errorElement.className = 'error-message';
    errorElement.style.color = 'red';
    errorElement.style.fontSize = '0.8rem';
    errorElement.style.marginTop = '5px';
    errorElement.textContent = message;
    
    input.parentNode.appendChild(errorElement);
}

// Add real-time validation for telephone field
/*const telInput = document.getElementById('tel');
if (telInput) {
    telInput.addEventListener('input', function() {
        // Remove non-digit characters
        this.value = this.value.replace(/\D/g, '');
        // Limit to 8 characters
        if (this.value.length > 8) {
            this.value = this.value.slice(0, 8);
        }
    });
}*/