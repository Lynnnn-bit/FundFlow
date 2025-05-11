<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../control/ProjectController.php';

$controller = new ProjectController();
$projectId = $_GET['id'] ?? null;

if (!$projectId) {
    die("ID du projet manquant.");
}

$project = $controller->getProjectById($projectId);

if (!$project) {
    die("Projet introuvable.");
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Détails du Projet</title>
    <link rel="stylesheet" href="css/stylecatego.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .project-details {
            max-width: 800px;
            margin: 2rem auto;
            padding: 2rem;
            background: #1e293b;
            color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }
        .project-details h1 {
            color: #00ffc8;
            margin-bottom: 1rem;
        }
        .project-details p {
            margin-bottom: 1rem;
            line-height: 1.6;
        }
        .project-details .back-btn {
            display: inline-block;
            margin-top: 1rem;
            padding: 0.5rem 1rem;
            background: #00ffc8;
            color: #fff;
            border-radius: 4px;
            text-decoration: none;
            font-weight: bold;
            transition: background 0.3s ease;
        }
        .project-details .back-btn:hover {
            background: #00c9a7;
        }
    </style>
</head>
<body>
<header class="navbar">
    <div class="logo-container">
        <span class="brand-name">FundFlow</span>
    </div>
</header>

<main>
    <section class="project-details">
        <h1><?= htmlspecialchars($project['titre']) ?></h1>
        <p><strong>Description:</strong> <?= htmlspecialchars($project['description']) ?></p>
        <p><strong>Montant Cible:</strong> <?= number_format($project['montant_cible'], 2) ?> €</p>
        <p><strong>Durée:</strong> <?= $project['duree'] ?> mois</p>
        <p><strong>Statut:</strong> <?= htmlspecialchars($project['statut'] ?? 'N/A') ?></p>
        <p><strong>Catégorie:</strong> <?= htmlspecialchars($project['nom_categorie'] ?? 'N/A') ?></p>
        <a href="mesprojet.php" class="back-btn"><i class="fas fa-arrow-left"></i> Retour</a>
    </section>
</main>
</body>
</html>
