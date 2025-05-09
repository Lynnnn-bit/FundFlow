document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('consultationForm');

    form.addEventListener('submit', function (e) {
        e.preventDefault();

        // Clear previous error messages
        clearErrorMessages();

        console.log("Validating form...");

        // Validate fields
        const isValidConsultant = validateConsultant();
        const isValidClient = validateClient();
        const isValidDates = validateDates();
        const isValidTarif();

        // If any validation fails, show a warning and focus on the first invalid field
        if (!isValidConsultant || !isValidClient || !isValidDates || !isValidTarif) {
            alert("Veuillez corriger les erreurs dans le formulaire avant de soumettre.");
            const firstError = document.querySelector('.form-group .error-message:not(:empty)');
            if (firstError) {
                const inputWithError = firstError.closest('.form-group').querySelector('select, input');
                inputWithError.focus();
            }
            console.log("Validation failed.");
            return;
        }

        console.log("Validation passed. Submitting form...");
        // Submit the form if all validations pass
        form.submit();
    });

    function validateConsultant() {
        const consultantSelect = document.querySelector('select[name="id_utilisateur1"]');
        if (!consultantSelect || !consultantSelect.value.trim()) {
            showError(consultantSelect, "Veuillez sélectionner un consultant");
            return false;
        }
        return true;
    }

    function validateClient() {
        const clientSelect = document.querySelector('select[name="id_utilisateur2"]');
        if (!clientSelect || !clientSelect.value.trim()) {
            showError(clientSelect, "Veuillez sélectionner un client");
            return false;
        }

        // Ensure consultant and client are not the same
        const consultantSelect = document.querySelector('select[name="id_utilisateur1"]');
        if (consultantSelect && consultantSelect.value === clientSelect.value) {
            showError(clientSelect, "Le consultant et le client ne peuvent pas être la même personne");
            return false;
        }

        return true;
    }

    function validateDates() {
        const dateInput = document.querySelector('input[name="date_consultation"]');
        const heureDebInput = document.querySelector('input[name="heure_deb"]');
        const heureFinInput = document.querySelector('input[name="heure_fin"]');

        if (!dateInput || !dateInput.value.trim()) {
            showError(dateInput, "Veuillez sélectionner une date");
            return false;
        }

        if (!heureDebInput || !heureDebInput.value.trim()) {
            showError(heureDebInput, "Veuillez saisir une heure de début");
            return false;
        }

        if (!heureFinInput || !heureFinInput.value.trim()) {
            showError(heureFinInput, "Veuillez saisir une heure de fin");
            return false;
        }

        // Convert to Date objects for comparison
        const debDateTime = new Date(`${dateInput.value}T${heureDebInput.value}`);
        const finDateTime = new Date(`${dateInput.value}T${heureFinInput.value}`);

        if (finDateTime <= debDateTime) {
            showError(heureFinInput, "L'heure de fin doit être après l'heure de début");
            return false;
        }

        return true;
    }

    function validateTarif() {
        const tarifInput = document.querySelector('input[name="tarif"]');
        const tarifValue = parseFloat(tarifInput.value);

        if (!tarifInput || isNaN(tarifValue)) {
            showError(tarifInput, "Veuillez saisir un tarif valide");
            return false;
        }

        if (tarifValue < 1 || tarifValue > 2000) {
            showError(tarifInput, "Le tarif doit être compris entre 1 et 2000 €");
            return false;
        }

        return true;
    }

    function showError(inputElement, message) {
        if (!inputElement) {
            console.error("Input element not found for error:", message);
            return;
        }
        const errorElement = inputElement.closest('.form-group')?.querySelector('.error-message');
        if (errorElement) {
            errorElement.textContent = message;
            errorElement.style.color = 'red';
        } else {
            console.error("Error element not found for input:", inputElement);
        }
        inputElement.style.borderColor = 'red';
    }

    function clearErrorMessages() {
        document.querySelectorAll('.error-message').forEach(el => el.textContent = '');
        document.querySelectorAll('select, input').forEach(el => el.style.borderColor = '');
    }
});