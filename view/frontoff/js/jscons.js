document.getElementById('consultationForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const list = document.getElementById('consultationList');
    const item = document.createElement('li');
    item.textContent = "Consultation ajoutée (exemple)";
    list.appendChild(item);
});