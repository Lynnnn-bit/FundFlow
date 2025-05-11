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
        <p><strong>Catégorie:</strong> <?= htmlspecialchars($project['nom_categorie'] ?? 'N/A') ?></p>
    </section>
</main>
</body>
</html>
