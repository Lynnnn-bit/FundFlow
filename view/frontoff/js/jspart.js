let partners = [];
let contracts = [];

document.getElementById('partnerForm').addEventListener('submit', function (e) {
  e.preventDefault();
  const name = document.getElementById('partnerName').value;
  const email = document.getElementById('partnerEmail').value;
  const phone = document.getElementById('partnerPhone').value;
  const type = document.getElementById('partnerType').value;

  const partner = { id: Date.now(), name, email, phone, type };
  partners.push(partner);
  updatePartnerList();
  updatePartnerSelect();
  this.reset();
});

document.getElementById('contractForm').addEventListener('submit', function (e) {
  e.preventDefault();
  const partnerId = parseInt(document.getElementById('partnerSelect').value);
  const startDate = document.getElementById('startDate').value;
  const endDate = document.getElementById('endDate').value;
  const details = document.getElementById('contractDetails').value;

  const contract = { id: Date.now(), partnerId, startDate, endDate, details };
  contracts.push(contract);
  updateContractList();
  this.reset();
});

function updatePartnerList() {
  const list = document.getElementById('partnersList');
  list.innerHTML = '';
  partners.forEach(partner => {
    const li = document.createElement('li');
    li.innerHTML = `
      <strong>${partner.name}</strong> (${partner.type})<br>
      ${partner.email}, ${partner.phone}
      <button onclick="editPartner(${partner.id})">Modifier</button>
      <button onclick="deletePartner(${partner.id})">Supprimer</button>
    `;
    list.appendChild(li);
  });
}

function updateContractList() {
  const list = document.getElementById('contractsList');
  list.innerHTML = '';
  contracts.forEach(contract => {
    const partner = partners.find(p => p.id === contract.partnerId);
    const li = document.createElement('li');
    li.innerHTML = `
      <strong>Contrat avec ${partner ? partner.name : 'Partenaire inconnu'}</strong><br>
      Du ${contract.startDate} au ${contract.endDate}<br>
      ${contract.details}
      <button onclick="deleteContract(${contract.id})">Supprimer</button>
    `;
    list.appendChild(li);
  });
}

function updatePartnerSelect() {
  const select = document.getElementById('partnerSelect');
  select.innerHTML = '<option value="">-- Choisissez un partenaire --</option>';
  partners.forEach(partner => {
    const option = document.createElement('option');
    option.value = partner.id;
    option.textContent = partner.name;
    select.appendChild(option);
  });
}

function deletePartner(id) {
  partners = partners.filter(p => p.id !== id);
  contracts = contracts.filter(c => c.partnerId !== id); // Supprimer les contrats associés
  updatePartnerList();
  updateContractList();
  updatePartnerSelect();
}

function deleteContract(id) {
  contracts = contracts.filter(c => c.id !== id);
  updateContractList();
}

function editPartner(id) {
  const partner = partners.find(p => p.id === id);
  if (partner) {
    document.getElementById('partnerName').value = partner.name;
    document.getElementById('partnerEmail').value = partner.email;
    document.getElementById('partnerPhone').value = partner.phone;
    document.getElementById('partnerType').value = partner.type;

    deletePartner(id); // Supprimer l'ancien avant de réajouter
  }
}