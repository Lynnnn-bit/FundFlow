document.addEventListener('DOMContentLoaded', () => {
  // ------------------- Input Validation Functions -------------------
  // Utility: Set error message
  function setErrorMsg(input, message) {
    let error = input.parentElement.querySelector('.error');
    if (!error) {
      error = document.createElement('small');
      error.className = 'error';
      input.parentElement.appendChild(error);
    }
    error.innerText = message;
  }
  
  // Utility: Clear error message
  function clearErrorMsg(input) {
    const error = input.parentElement.querySelector('.error');
    if (error) {
      error.innerText = '';
    }
  }
  
  // ------------------- Partners Form Validation -------------------
  const partnerForm = document.getElementById('partnerForm');
  
  partnerForm.addEventListener('submit', (e) => {
    e.preventDefault();
    let valid = true;
    
    const nom = document.getElementById('nom');
    const email = document.getElementById('email');
    const telephone = document.getElementById('telephone');
    const contrat = document.getElementById('contrat');
    
    // Validate name (min 3 characters)
    if (nom.value.trim().length < 3) {
      setErrorMsg(nom, 'Entrez un nom (au moins 3 caractères).');
      valid = false;
    } else {
      clearErrorMsg(nom);
    }
    
    // Validate email with basic regex
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email.value.trim())) {
      setErrorMsg(email, 'Entrez un email valide.');
      valid = false;
    } else {
      clearErrorMsg(email);
    }
    
    // Validate telephone: at least 8 digits
    const telRegex = /^[0-9]{8,}$/;
    if (!telRegex.test(telephone.value.trim())) {
      setErrorMsg(telephone, 'Entrez un téléphone (min 8 chiffres).');
      valid = false;
    } else {
      clearErrorMsg(telephone);
    }
    
    // Validate contract selection
    if (contrat.value === '') {
      setErrorMsg(contrat, 'Sélectionnez un contrat.');
      valid = false;
    } else {
      clearErrorMsg(contrat);
    }
    
    if (valid) {
      alert("Le formulaire Partenaires est validé.");
      partnerForm.reset();
    }
  });
  
  // ------------------- Contracts Form Validation -------------------
  const contractForm = document.getElementById('contractForm');
  
  contractForm.addEventListener('submit', (e) => {
    e.preventDefault();
    let valid = true;
    
    const nomContrat = document.getElementById('nom-contrat');
    const partenaireAssocie = document.getElementById('partenaire-associe');
    const dateDebut = document.getElementById('date-debut');
    const dateFin = document.getElementById('date-fin');
    const montantContrat = document.getElementById('montant-contrat');
    
    // Validate contract name
    if (nomContrat.value.trim().length < 3) {
      setErrorMsg(nomContrat, 'Entrez un nom pour le contrat (min 3 caractères).');
      valid = false;
    } else {
      clearErrorMsg(nomContrat);
    }
    
    // Validate associated partner is not empty
    if (partenaireAssocie.value.trim() === '') {
      setErrorMsg(partenaireAssocie, 'Entrez le nom du partenaire associé.');
      valid = false;
    } else {
      clearErrorMsg(partenaireAssocie);
    }
    
    // Validate dates
    if (!dateDebut.value) {
      setErrorMsg(dateDebut, 'Sélectionnez une date de début.');
      valid = false;
    } else {
      clearErrorMsg(dateDebut);
    }
    if (!dateFin.value) {
      setErrorMsg(dateFin, 'Sélectionnez une date de fin.');
      valid = false;
    } else if (new Date(dateFin.value) <= new Date(dateDebut.value)) {
      setErrorMsg(dateFin, 'La date de fin doit être après la date de début.');
      valid = false;
    } else {
      clearErrorMsg(dateFin);
    }
    
    // Validate amount (must be positive)
    if (!montantContrat.value || Number(montantContrat.value) <= 0) {
      setErrorMsg(montantContrat, 'Entrez un montant positif.');
      valid = false;
    } else {
      clearErrorMsg(montantContrat);
    }
    
    if (valid) {
      alert("Le formulaire Contrats est validé.");
      contractForm.reset();
    }
  });
  
  // ----------------- Optional: Clear error messages on input change -----------------
  document.querySelectorAll('input, select').forEach((input) => {
    input.addEventListener('input', () => clearErrorMsg(input));
  });
  
  // ----------------- Basic Module and Sub-View Toggling (for navigation) -----------------
  // Top-level module toggling
  const modulePartnersBtn = document.getElementById('module-partners');
  const moduleContractsBtn = document.getElementById('module-contracts');
  const partnersModule = document.getElementById('partners-module');
  const contractsModule = document.getElementById('contracts-module');
  
  function setActiveModule(moduleBtn) {
    modulePartnersBtn.classList.remove('active');
    moduleContractsBtn.classList.remove('active');
    partnersModule.classList.remove('active');
    contractsModule.classList.remove('active');
    if (moduleBtn === modulePartnersBtn) {
      modulePartnersBtn.classList.add('active');
      partnersModule.classList.add('active');
    } else {
      moduleContractsBtn.classList.add('active');
      contractsModule.classList.add('active');
    }
  }
  
  modulePartnersBtn.addEventListener('click', () => setActiveModule(modulePartnersBtn));
  moduleContractsBtn.addEventListener('click', () => setActiveModule(moduleContractsBtn));
  
  // Sub-view toggling for Partners
  const partnersListBtn = document.getElementById('partners-list-btn');
  const partnersCreateBtn = document.getElementById('partners-create-btn');
  const partnersAnalyticsBtn = document.getElementById('partners-analytics-btn');
  const partnersListView = document.getElementById('partners-list');
  const partnersCreateView = document.getElementById('partners-create');
  const partnersAnalyticsView = document.getElementById('partners-analytics');
  
  function setActivePartnersView(viewBtn) {
    partnersListBtn.classList.remove('active');
    partnersCreateBtn.classList.remove('active');
    partnersAnalyticsBtn.classList.remove('active');
    partnersListView.classList.remove('active');
    partnersCreateView.classList.remove('active');
    partnersAnalyticsView.classList.remove('active');
    if (viewBtn === partnersListBtn) {
      partnersListBtn.classList.add('active');
      partnersListView.classList.add('active');
    } else if (viewBtn === partnersCreateBtn) {
      partnersCreateBtn.classList.add('active');
      partnersCreateView.classList.add('active');
    } else {
      partnersAnalyticsBtn.classList.add('active');
      partnersAnalyticsView.classList.add('active');
    }
  }
  
  partnersListBtn.addEventListener('click', () => setActivePartnersView(partnersListBtn));
  partnersCreateBtn.addEventListener('click', () => setActivePartnersView(partnersCreateBtn));
  partnersAnalyticsBtn.addEventListener('click', () => setActivePartnersView(partnersAnalyticsBtn));
  
  // Sub-view toggling for Contracts
  const contractsListBtn = document.getElementById('contracts-list-btn');
  const contractsCreateBtn = document.getElementById('contracts-create-btn');
  const contractsAnalyticsBtn = document.getElementById('contracts-analytics-btn');
  const contractsListView = document.getElementById('contracts-list');
  const contractsCreateView = document.getElementById('contracts-create');
  const contractsAnalyticsView = document.getElementById('contracts-analytics');
  
  function setActiveContractsView(viewBtn) {
    contractsListBtn.classList.remove('active');
    contractsCreateBtn.classList.remove('active');
    contractsAnalyticsBtn.classList.remove('active');
    contractsListView.classList.remove('active');
    contractsCreateView.classList.remove('active');
    contractsAnalyticsView.classList.remove('active');
    if (viewBtn === contractsListBtn) {
      contractsListBtn.classList.add('active');
      contractsListView.classList.add('active');
    } else if (viewBtn === contractsCreateBtn) {
      contractsCreateBtn.classList.add('active');
      contractsCreateView.classList.add('active');
    } else {
      contractsAnalyticsBtn.classList.add('active');
      contractsAnalyticsView.classList.add('active');
    }
  }
  
  contractsListBtn.addEventListener('click', () => setActiveContractsView(contractsListBtn));
  contractsCreateBtn.addEventListener('click', () => setActiveContractsView(contractsCreateBtn));
  contractsAnalyticsBtn.addEventListener('click', () => setActiveContractsView(contractsAnalyticsBtn));
  
  // ----------------- End Input Validation & Toggling Code -----------------
});
