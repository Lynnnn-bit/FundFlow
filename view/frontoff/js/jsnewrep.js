document.addEventListener('DOMContentLoaded', function() {
    const decisionSelect = document.querySelector('select[name="decision"]');
    const amountField = document.querySelector('input[name="montant_accorde"]');
    const montantError = document.getElementById('montantError');
    const form = document.querySelector('form');

    // Enable/disable amount field based on decision
    if (decisionSelect && amountField) {
        decisionSelect.addEventListener('change', function() {
            const isAccepted = this.value === 'accepte';
            amountField.disabled = !isAccepted;
            montantError.style.display = 'none'; // Hide error on decision change
            
            if (!isAccepted) {
                amountField.value = '0';
            }
        });
        
        // Initialize on page load
        decisionSelect.dispatchEvent(new Event('change'));
    }

    // Form submission validation
    form.addEventListener('submit', function(e) {
        const decision = decisionSelect.value;
        const montant = parseFloat(amountField.value);

        if (decision === 'accepte') {
            if (montant < 10000 || montant > 1000000 || isNaN(montant)) {
                e.preventDefault();
                montantError.textContent = 'Le montant doit être entre 10 000 € et 1 000 000 €.';
                montantError.style.display = 'block';
                montantError.style.color = 'red'; // Add red color
            }
        }
    });

    // Real-time validation feedback
    amountField.addEventListener('input', function() {
        if (decisionSelect.value === 'accepte') {
            const montant = parseFloat(this.value);
            
            if (montant >= 10000 && montant <= 1000000) {
                montantError.style.display = 'none';
            } else {
                montantError.textContent = 'Le montant doit être entre 10 000 € et 1 000 000 €.';
                montantError.style.display = 'block';
            }
        }
    });
});