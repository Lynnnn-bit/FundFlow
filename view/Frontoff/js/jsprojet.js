// jsprojet.js
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form[method="POST"]');
    const montantInput = document.querySelector('input[name="montant_cible"]');
    const dureeInput = document.querySelector('input[name="duree"]');

    // Create error message elements
    const montantError = document.createElement('div');
    montantError.className = 'error-message';
    montantError.style.color = 'red';
    montantError.style.marginTop = '5px';
    montantInput.parentNode.insertBefore(montantError, montantInput.nextSibling);

    const dureeError = document.createElement('div');
    dureeError.className = 'error-message';
    dureeError.style.color = 'red';
    dureeError.style.marginTop = '5px';
    dureeInput.parentNode.insertBefore(dureeError, dureeInput.nextSibling);

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
    montantInput.addEventListener('input', validateMontant);
    dureeInput.addEventListener('input', validateDuree);

    // Form submission handling
    form.addEventListener('submit', function(e) {
        const isMontantValid = validateMontant();
        const isDureeValid = validateDuree();

        if (!isMontantValid || !isDureeValid) {
            e.preventDefault();
            
            // Scroll to first error
            if (!isMontantValid) {
                montantInput.scrollIntoView({ behavior: 'smooth', block: 'center' });
                montantInput.focus();
            } else {
                dureeInput.scrollIntoView({ behavior: 'smooth', block: 'center' });
                dureeInput.focus();
            }
        }
    });

    // Initial validation for edit mode
    if (montantInput.value) validateMontant();
    if (dureeInput.value) validateDuree();
});