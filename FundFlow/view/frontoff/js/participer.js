document.addEventListener("DOMContentLoaded", function() {
    const form = document.getElementById("participationForm");

    form.addEventListener("submit", function(event) {
        let valid = true;

        // Réinitialisation des messages d'erreur
        const nomError = document.getElementById("nomError");
        const prenomError = document.getElementById("prenomError");
        const nomInput = document.getElementById("nom");
        const prenomInput = document.getElementById("prenom");

        nomError.innerText = "";
        prenomError.innerText = "";

        // Vérification du nom
        const nom = nomInput.value.trim();
        if (nom === "") {
            nomError.innerText = "Le nom est requis.";
            valid = false;
        } else if (!/^[A-Za-zÀ-ÿ\-'\s]+$/.test(nom)) {
            nomError.innerText = "Le nom contient des caractères non valides.";
            valid = false;
        }

        // Vérification du prénom
        const prenom = prenomInput.value.trim();
        if (prenom === "") {
            prenomError.innerText = "Le prénom est requis.";
            valid = false;
        } else if (!/^[A-Za-zÀ-ÿ\-'\s]+$/.test(prenom)) {
            prenomError.innerText = "Le prénom contient des caractères non valides.";
            valid = false;
        }

        // Empêche la soumission si invalide
        if (!valid) {
            event.preventDefault();
        }
    });
});
