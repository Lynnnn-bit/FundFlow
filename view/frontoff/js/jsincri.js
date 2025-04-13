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
        const password = document.getElementById('mdp').value;
        const confirmPassword = document.getElementById('confirmation_mdp').value;
        
        if (password !== confirmPassword) {
            e.preventDefault();
            alert('Les mots de passe ne correspondent pas');
            return false;
        }
        
        return true;
    });
}