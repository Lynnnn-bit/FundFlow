<?php
include_once '../../control/startupC.php';

$startupC = new startupC();
$startups = $startupC->getAllStartups();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Liste des Startups</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="css/styless.css">
</head>
<body>

<div class="navbar">
    <img src="assets/logo.png" alt="Logo">
    <div class="nav-links">
        <a href="frontoffice.php" class="nav-link">Accueil</a>
        <a href="events.php" class="nav-link">Events</a>
        <a href="startup.php" class="nav-link">Startups</a>
        <a href="#" class="nav-link">Contact</a>
    </div>
</div>

<div class="container">
    <h1 class="title">Liste des Startups</h1>

    <?php if (!empty($startups)): ?>
        <?php foreach ($startups as $s): ?>
            <?php
                $logoFile = basename($s['logo']);
                $videoFile = basename($s['video_presentation']);
                $logoPath = '../backoff/uploads/' . $logoFile;
                $videoPath = '../backoff/uploads/' . $videoFile;
            ?>
            <div class="startup-card">
                <p><strong>ID:</strong> <?= htmlspecialchars($s['id_startup']) ?></p>
                <p><strong>Nom:</strong> <?= htmlspecialchars($s['nom_startup']) ?></p>
                <p><strong>Secteur:</strong> <?= htmlspecialchars($s['secteur']) ?></p>
                <p><strong>Adresse site:</strong> <a href="<?= htmlspecialchars($s['adresse_site']) ?>" target="_blank"><?= htmlspecialchars($s['adresse_site']) ?></a></p>
                <p><strong>Description:</strong> <?= htmlspecialchars($s['description']) ?></p>
                <p><strong>Email:</strong> <?= htmlspecialchars($s['email']) ?></p>

                <p><strong>Logo:</strong><br>
                    <img src="<?= $logoPath ?>" alt="Logo startup" style="max-width: 150px;">
                </p>

                <p><strong>Vidéo de Présentation:</strong><br>
                    <video controls style="max-width: 300px;">
                        <source src="<?= $videoPath ?>" type="video/mp4">
                        Votre navigateur ne supporte pas la lecture vidéo.
                    </video>
                </p>

                <div class="button-group">
                    <a href="updatestartup.php?id=<?= $s['id_startup'] ?>" class="btn-modifier">
                        <i class="fas fa-edit"></i> Modifier
                    </a>
                    <a href="deletestartup.php?id=<?= $s['id_startup'] ?>" class="btn-supprimer" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette startup?')">
                        <i class="fas fa-trash"></i> Supprimer
                    </a>
                    <a href="generate_qr.php?id=<?= $s['id_startup'] ?>" class="btn-qr" target="_blank">
                        <i class="fas fa-qrcode"></i> QR Code
                    </a>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>Aucune startup trouvée.</p>
    <?php endif; ?>

    <div style="margin-top: 20px;">
        <a href="frontoffice.php" class="btn-ajouter" style="text-decoration: none; padding: 10px 20px; background-color: #3498db; color: white; border-radius: 5px;">Ajouter évènement</a>
    </div>
</div>

</body>
</html>
