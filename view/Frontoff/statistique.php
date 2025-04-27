<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../controlle/ProjectController.php';

$controller = new ProjectController();

// Fetch all projects
$projects = $controller->getAllProjects();

// Calculate statistics
$totalProjects = count($projects);
$totalFunding = array_sum(array_column($projects, 'montant_cible'));
$averageFunding = $totalProjects > 0 ? $totalFunding / $totalProjects : 0;
$maxFunding = $totalProjects > 0 ? max(array_column($projects, 'montant_cible')) : 0;
$minFunding = $totalProjects > 0 ? min(array_column($projects, 'montant_cible')) : 0;
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Statistiques des Projets</title>
    <link rel="stylesheet" href="css/stylecatego.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
<header class="navbar">
    <div class="logo-container">
        <span class="brand-name">FundFlow</span>
    </div>
    <nav>
        <a href="mesprojet.php"><i class="fas fa-list"></i> Mes Projets</a>
        <a href="#"><i class="fas fa-info-circle"></i> À propos</a>
        <a href="#"><i class="fas fa-envelope"></i> Contact</a>
        <a href="login.php"><i class="fas fa-sign-in-alt"></i> Connexion</a>
    </nav>
</header>

<main>
    <section class="hero-section">
        <h1><i class="fas fa-chart-bar"></i> Statistiques des Projets</h1>
        <p>Analysez les données de vos projets</p>
    </section>

    <!-- Statistics Section -->
    <div class="stats-container" style="display: flex; justify-content: center; gap: 2rem; margin: 2rem 0;">
        <div class="stat-card">
            <div class="stat-value"><?= $totalProjects ?></div>
            <div class="stat-label">Projets totaux</div>
        </div>
        <div class="stat-card">
            <div class="stat-value">€<?= number_format($totalFunding, 2) ?></div>
            <div class="stat-label">Montant total</div>
        </div>
        <div class="stat-card">
            <div class="stat-value">€<?= number_format($averageFunding, 2) ?></div>
            <div class="stat-label">Montant moyen</div>
        </div>
        <div class="stat-card">
            <div class="stat-value">€<?= number_format($maxFunding, 2) ?></div>
            <div class="stat-label">Montant max</div>
        </div>
        <div class="stat-card">
            <div class="stat-value">€<?= number_format($minFunding, 2) ?></div>
            <div class="stat-label">Montant min</div>
        </div>
    </div>
</main>
</body>
</html>
