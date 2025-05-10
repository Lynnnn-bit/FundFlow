function toggleForm(startupId) {
    const form = document.getElementById('form_' + startupId);
    const button = document.querySelector(`button[onclick="toggleForm('${startupId}')"]`);
    if (form.style.display === 'none' || form.style.display === '') {
        form.style.display = 'block';
        button.disabled = true;
    } else {
        form.style.display = 'none';
        button.disabled = false;
    }
}

function validateForm(startupId) {
    let date = document.getElementById('date_evenement_' + startupId).value.trim();
    let type = document.getElementById('type_' + startupId).value.trim();
    let horaire = document.getElementById('horaire_' + startupId).value.trim();
    let nbPlace = document.getElementById('nb_place_' + startupId).value.trim();
    let afficheInput = document.getElementById('affiche_' + startupId);
    let nom = document.getElementById('nom_' + startupId).value.trim();

    // Vérifications de base
    if (!date) {
        alert("Veuillez entrer une date d'évènement.");
        return false;
    }

    if (!type) {
        alert("Veuillez sélectionner un type d'évènement.");
        return false;
    }

    if (!horaire) {
        alert("Veuillez entrer un horaire.");
        return false;
    }

    if (!nbPlace || isNaN(nbPlace) || parseInt(nbPlace) <= 0) {
        alert("Veuillez entrer un nombre de places valide (entier positif).");
        return false;
    }

    if (!afficheInput || afficheInput.files.length === 0) {
        alert("Veuillez sélectionner une image pour l'affiche.");
        return false;
    }

    if (!nom || nom.length < 3) {
        alert("Veuillez entrer un nom d'évènement (au moins 3 caractères).");
        return false;
    }

    return true;
}
