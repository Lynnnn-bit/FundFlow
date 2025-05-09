document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Dynamic note display
    const noteInput = document.querySelector('input[name="note"]');
    if (noteInput) {
        const noteDisplay = document.createElement('div');
        noteDisplay.className = 'note-display mt-2';
        noteDisplay.innerHTML = generateStars(noteInput.value);
        noteInput.parentNode.appendChild(noteDisplay);

        noteInput.addEventListener('input', function() {
            noteDisplay.innerHTML = generateStars(this.value);
        });
    }

    // Form validation
    const form = document.querySelector('form');
    if (form) {
        form.addEventListener('submit', function(e) {
            let isValid = true;
            
            // Validate consultation selection
            const consultationSelect = form.querySelector('select[name="id_consultation"]');
            if (!consultationSelect.value) {
                isValid = false;
                consultationSelect.classList.add('is-invalid');
            } else {
                consultationSelect.classList.remove('is-invalid');
            }

            // Validate note
            const noteInput = form.querySelector('input[name="note"]');
            if (!noteInput.value || noteInput.value < 1 || noteInput.value > 5) {
                isValid = false;
                noteInput.classList.add('is-invalid');
            } else {
                noteInput.classList.remove('is-invalid');
            }

            if (!isValid) {
                e.preventDefault();
                
                // Show error message
                if (!document.querySelector('.form-error')) {
                    const errorDiv = document.createElement('div');
                    errorDiv.className = 'alert alert-danger form-error mt-3';
                    errorDiv.textContent = 'Veuillez remplir tous les champs requis correctement.';
                    form.appendChild(errorDiv);
                }
            }
        });
    }

    // Function to generate star display
    function generateStars(rating) {
        const fullStars = '⭐'.repeat(Math.floor(rating));
        const emptyStars = '☆'.repeat(5 - Math.floor(rating));
        return `<span class="star-rating">${fullStars}${emptyStars} (${rating}/5)</span>`;
    }

    // Confirmation for delete actions
    const deleteButtons = document.querySelectorAll('.btn-danger');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            if (!confirm('Êtes-vous sûr de vouloir effectuer cette action ?')) {
                e.preventDefault();
            }
        });
    });

    // Animation for stats cards
    const statCards = document.querySelectorAll('.stat-card');
    statCards.forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        
        setTimeout(() => {
            card.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, index * 150);
    });
});

document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('feedbackForm');

    form.addEventListener('submit', function (e) {
        e.preventDefault();

        // Clear previous error messages
        clearErrorMessages();

        console.log("Validating form...");

        // Validate fields
        const isValidConsultation = validateConsultation();
        const isValidNote = validateNote();

        // If any validation fails, show a warning and prevent form submission
        if (!isValidConsultation || !isValidNote) {
            alert("Veuillez corriger les erreurs dans le formulaire avant de soumettre.");
            return;
        }

        console.log("Validation passed. Submitting form...");
        // Submit the form if all validations pass
        form.submit();
    });

    function validateConsultation() {
        const consultationSelect = document.getElementById('id_consultation');
        if (!consultationSelect || !consultationSelect.value.trim()) {
            showError(consultationSelect, "Veuillez sélectionner une consultation valide.");
            return false;
        }
        return true;
    }

    function validateNote() {
        const noteInput = document.getElementById('note');
        const noteValue = parseInt(noteInput.value, 10);

        if (!noteInput || isNaN(noteValue)) {
            showError(noteInput, "Veuillez saisir une note valide.");
            return false;
        }

        if (noteValue < 1 || noteValue > 5) {
            showError(noteInput, "La note doit être comprise entre 1 et 5.");
            return false;
        }

        return true;
    }

    function showError(inputElement, message) {
        if (!inputElement) {
            console.error("Input element not found for error:", message);
            return;
        }
        const errorElement = document.createElement('div');
        errorElement.className = 'error-message';
        errorElement.style.color = 'red';
        errorElement.textContent = message;
        inputElement.parentNode.appendChild(errorElement);
        inputElement.style.borderColor = 'red';
    }

    function clearErrorMessages() {
        document.querySelectorAll('.error-message').forEach(el => el.remove());
        document.querySelectorAll('select, input').forEach(el => el.style.borderColor = '');
    }
});