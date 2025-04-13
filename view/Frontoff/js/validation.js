document.addEventListener('DOMContentLoaded', function() {
    // Initialize all form validations
    initFormValidations();
    
    // Enable/disable amount field based on decision
    initDecisionToggle();
    
    // Initialize tooltips
    initTooltips();
  });
  
  function initFormValidations() {
    // Validate all forms with class 'needs-validation'
    const forms = document.querySelectorAll('.needs-validation');
    
    Array.from(forms).forEach(form => {
      form.addEventListener('submit', function(event) {
        if (!form.checkValidity()) {
          event.preventDefault();
          event.stopPropagation();
        }
        
        // Add custom validations
        validateAmountFields(form);
        validateDateFields(form);
        
        form.classList.add('was-validated');
      }, false);
    });
  }
  
  function validateAmountFields(form) {
    const amountInputs = form.querySelectorAll('input[type="number"]');
    
    amountInputs.forEach(input => {
      input.addEventListener('input', function() {
        const min = parseFloat(input.min) || 0;
        const max = parseFloat(input.max) || Infinity;
        const value = parseFloat(input.value) || 0;
        
        if (value < min) {
          input.setCustomValidity(`Le montant doit être au moins ${min}`);
        } else if (value > max) {
          input.setCustomValidity(`Le montant ne peut pas dépasser ${max}`);
        } else {
          input.setCustomValidity('');
        }
        
        // Update feedback
        updateValidationFeedback(input);
      });
    });
  }
  
  function validateDateFields(form) {
    const dateInputs = form.querySelectorAll('input[type="date"]');
    const today = new Date().toISOString().split('T')[0];
    
    dateInputs.forEach(input => {
      input.addEventListener('change', function() {
        if (input.value < today) {
          input.setCustomValidity('La date ne peut pas être dans le passé');
        } else {
          input.setCustomValidity('');
        }
        
        // Update feedback
        updateValidationFeedback(input);
      });
    });
  }
  
  function updateValidationFeedback(input) {
    const feedback = input.nextElementSibling;
    
    if (input.validity.valid) {
      input.classList.remove('is-invalid');
      input.classList.add('is-valid');
      if (feedback) feedback.textContent = '';
    } else {
      input.classList.remove('is-valid');
      input.classList.add('is-invalid');
      if (feedback) feedback.textContent = input.validationMessage;
    }
  }
  
  function initDecisionToggle() {
    const decisionSelects = document.querySelectorAll('select[name="decision"]');
    
    decisionSelects.forEach(select => {
      select.addEventListener('change', function() {
        const amountField = this.closest('form').querySelector('input[name="montant_accorde"]');
        if (amountField) {
          amountField.disabled = this.value !== 'accepte';
          if (this.value !== 'accepte') {
            amountField.value = '0';
          }
          
          // Trigger validation
          amountField.dispatchEvent(new Event('input'));
        }
      });
      
      // Initialize on page load
      if (select.value) {
        select.dispatchEvent(new Event('change'));
      }
    });
  }
  
  function initTooltips() {
    // Initialize Bootstrap tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function(tooltipTriggerEl) {
      return new bootstrap.Tooltip(tooltipTriggerEl);
    });
  }
  
  // Helper function to format numbers
  function formatNumber(number) {
    return new Intl.NumberFormat('fr-FR', { 
      style: 'decimal', 
      minimumFractionDigits: 2,
      maximumFractionDigits: 2 
    }).format(number);
  }
  
  // Initialize number formatting
  document.querySelectorAll('.format-number').forEach(el => {
    if (el.value) {
      el.value = formatNumber(parseFloat(el.value));
    }
    
    el.addEventListener('blur', function() {
      if (this.value) {
        this.value = formatNumber(parseFloat(this.value));
      }
    });
    
    el.addEventListener('focus', function() {
      if (this.value) {
        this.value = parseFloat(this.value.replace(/\s/g, '').replace(',', '.'));
      }
    });
  });