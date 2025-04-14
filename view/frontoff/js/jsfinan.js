document.addEventListener('DOMContentLoaded', function() {
    // Form elements
    const form = document.getElementById('financementForm');
    const idProject = document.getElementById('id_project');
    const montantInput = document.getElementById('montant');
    const dureeInput = document.getElementById('duree');
    const statusSelect = document.getElementById('status');
    
    // Cancel button
    document.querySelector('.btn-cancel').addEventListener('click', function() {
        if (confirm('Voulez-vous vraiment annuler cette demande ?')) {
            form.reset();
            resetValidation();
        }
    });
    
    // Form validation
    form.addEventListener('submit', function(event) {
        event.preventDefault();
        let isValid = true;
        
        // Reset previous validation
        resetValidation();
        
        // Validate project ID
        if (!idProject.value || isNaN(idProject.value) || idProject.value <= 0) {
            document.getElementById('id_project_error').textContent = 'Veuillez entrer un ID de projet valide';
            idProject.classList.add('is-invalid');
            isValid = false;
        } else {
            idProject.classList.add('is-valid');
        }
        
        // Validate montant
        const montant = parseFloat(montantInput.value);
        if (isNaN(montant) || montant < 10000 || montant > 10000000) {
            document.getElementById('montant_error').textContent = 'Le montant doit être entre 10 000 € et 10 000 000 €';
            montantInput.classList.add('is-invalid');
            isValid = false;
        } else {
            montantInput.classList.add('is-valid');
        }
        
        // Validate durée
        const duree = parseInt(dureeInput.value);
        if (isNaN(duree) || duree < 6 || duree > 60) {
            document.getElementById('duree_error').textContent = 'La durée doit être entre 6 et 60 mois';
            dureeInput.classList.add('is-invalid');
            isValid = false;
        } else {
            dureeInput.classList.add('is-valid');
        }
        
        // Validate status
        if (!statusSelect.value) {
            document.getElementById('status_error').textContent = 'Veuillez sélectionner un statut';
            statusSelect.classList.add('is-invalid');
            isValid = false;
        } else {
            statusSelect.classList.add('is-valid');
        }
        
        // If form is valid, submit it
        if (isValid) {
            alert('Formulaire validé avec succès !');
            // form.submit(); // Uncomment to enable actual form submission
        }
    });
    
    // Reset validation states
    function resetValidation() {
        const inputs = [idProject, montantInput, dureeInput, statusSelect];
        inputs.forEach(input => {
            input.classList.remove('is-invalid', 'is-valid');
        });
        
        const errorMessages = document.querySelectorAll('[id$="_error"]');
        errorMessages.forEach(el => el.textContent = '');
    }
});