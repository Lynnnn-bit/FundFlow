<?php
include_once 'C:\xampp\htdocs\user\FundFlow\control\startupC.php';
include_once 'C:\xampp\htdocs\user\FundFlow\control\EvennementC.php';

$startupC = new startupC();
$evenementC = new EvennementC();
$startups = $startupC->getAllStartups();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Front Office - Startups</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="css/stylesfront.css">
</head>
<body>

<div class="navbar">
    <img src="assets/logo.png" alt="Logo">
    <div class="nav-links">
        <a href="#" class="nav-link">Accueil</a>
        <a href="events.php" class="nav-link">Events</a>
        <a href="startup.php" class="nav-link">Startups</a>
        <a href="#" class="nav-link">Contact</a>
    </div>
</div>

<div class="container">
    <h1 class="title">Liste des Startups</h1>

    <?php foreach ($startups as $s): ?>
        <?php
            // Récupération des chemins relatifs pour les fichiers logo et vidéo
            $logoFile = basename($s['logo']);
            $videoFile = basename($s['video_presentation']);
            $logoPath = '../backoff/uploads/' . $logoFile;
            $videoPath = '../backoff/uploads/' . $videoFile;
        ?>

        <div class="startup-card">
            <p><strong>ID:</strong> <?= $s['id_startup'] ?></p>
            <p><strong>Nom:</strong> <?= $s['nom_startup'] ?></p>
            <p><strong>Secteur:</strong> <?= $s['secteur'] ?></p>
            <p><strong>Adresse site:</strong> <a href="<?= $s['adresse_site'] ?>" target="_blank"><?= $s['adresse_site'] ?></a></p>
            <p><strong>Description:</strong> <?= $s['description'] ?></p>
            <p><strong>Email:</strong> <?= $s['email'] ?></p>

            <!-- Affichage du logo -->
            <p><strong>Logo:</strong><br>
                <img src="<?= $logoPath ?>" alt="Logo startup" style="max-width: 150px;">
            </p>
            
            <!-- Affichage de la vidéo -->
            <p><strong>Vidéo de Présentation:</strong><br>
                <video controls style="max-width: 300px;">
                    <source src="<?= $videoPath ?>" type="video/mp4">
                    Votre navigateur ne supporte pas la lecture de vidéos.
                </video>
            </p>

            <!-- Bouton pour afficher le formulaire d'ajout d'événement -->
            <button class="btn-ajouter" onclick="toggleForm('<?= $s['id_startup'] ?>')">Ajouter Évènement</button>

            <!-- Formulaire d'ajout d'événement -->
            <div class="evenement-form" id="form_<?= $s['id_startup'] ?>" style="display: none;">
                <h3>Ajouter un Évènement</h3>
                <form method="POST" enctype="multipart/form-data" action="addevennement.php" class="event-form">
                    <input type="hidden" name="id_startup" value="<?= $s['id_startup'] ?>">

                    <label>Date de l'évènement</label>
                    <input type="date" name="date_evenement">
                    <div class="error-message">Veuillez choisir une date valide.</div>

                    <label>Type</label>
                    <select name="type">
                        <option value="">-- Choisir un type --</option>
                        <option value="Présentiel">Présentiel</option>
                        <option value="En ligne">En ligne</option>
                    </select>
                    <div class="error-message">Veuillez choisir un type.</div>

                    <label>Horaire</label>
                    <input type="time" name="horaire">
                    <div class="error-message">Veuillez entrer un horaire.</div>

                    <label>Nombre de places</label>
                    <input type="number" name="nb_place">
                    <div class="error-message">Veuillez entrer un nombre de places.</div>

                    <label>Affiche (image)</label>
                    <input type="file" name="affiche" accept="image/*">
                    <div class="error-message">Veuillez ajouter une affiche.</div>

                    <label>Nom de l'évènement</label>
                    <input type="text" name="nom">
                    <div class="error-message">Veuillez entrer un nom d'évènement.</div>

                    <button type="submit" class="btn-ajouter">Enregistrer</button>
                </form>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<script>
// Afficher / cacher formulaire événement
function toggleForm(startupId) {
    const form = document.getElementById('form_' + startupId);
    const button = document.querySelector(`button[onclick="toggleForm('${startupId}')"]`);
    form.style.display = form.style.display === 'none' ? 'block' : 'none';
    button.disabled = form.style.display === 'block';
}

// Validation pour ajouter un événement
document.querySelectorAll('.event-form').forEach(function(form) {
    form.addEventListener('submit', function(e) {
        let valid = true;
        const fields = {
            date_evenement: form.querySelector('input[name="date_evenement"]'),
            type: form.querySelector('select[name="type"]'),
            horaire: form.querySelector('input[name="horaire"]'),
            nb_place: form.querySelector('input[name="nb_place"]'),
            affiche: form.querySelector('input[name="affiche"]'),
            nom: form.querySelector('input[name="nom"]')
        };
        const errorMessages = form.querySelectorAll('.error-message');

        errorMessages.forEach(msg => msg.style.display = 'none');

        function showError(field, index) {
            if (!field.value || (field.type === "file" && field.files.length === 0)) {
                errorMessages[index].style.display = 'block';
                valid = false;
            }
        }

        showError(fields.date_evenement, 0);
        showError(fields.type, 1);
        showError(fields.horaire, 2);
        showError(fields.nb_place, 3);
        showError(fields.affiche, 4);
        showError(fields.nom, 5);

        const selectedDate = new Date(fields.date_evenement.value);
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        if (fields.date_evenement.value && selectedDate < today) {
            errorMessages[0].style.display = 'block';
            valid = false;
        }

        if (!valid) {
            e.preventDefault();
        }
    });
});
</script>

</body>
</html>
