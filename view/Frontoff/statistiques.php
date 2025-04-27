<!-- filepath: c:\xampp\htdocs\fund\view\Frontoff\statistiques.php -->
<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../controlle/ProjectController.php';

$controller = new ProjectController();

// Fetch statistics based on categories
$statistics = $controller->getStatisticsByCategory();

// Calculate the total number of projects for percentage calculation
$totalProjects = array_sum(array_column($statistics, 'total_projets'));
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Statistiques par Catégorie</title>
    <link rel="stylesheet" href="css/stylecatego.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
<header class="navbar">
    <div class="logo-container">
        <span class="brand-name">FundFlow</span>
    </div>
    <nav>
        <a href="mesprojet.php"><i class="fas fa-home"></i> Accueil</a>
        <a href="#"><i class="fas fa-info-circle"></i> À propos</a>
        <a href="#"><i class="fas fa-envelope"></i> Contact</a>
        <a href="login.php"><i class="fas fa-sign-in-alt"></i> Connexion</a>
    </nav>
</header>

<main>
    <section class="hero-section">
        <h1><i class="fas fa-chart-bar"></i> Statistiques par Catégorie</h1>
        <p>Visualisez les statistiques des projets selon leurs catégories.</p>
    </section>

    <div class="stats-container">
        <?php if (count($statistics) > 0): ?>
            <?php foreach ($statistics as $stat): ?>
                <?php 
                    // Calculate the percentage of total projects for this category
                    $percentage = $totalProjects > 0 ? ($stat['total_projets'] / $totalProjects) * 100 : 0;
                ?>
                <div class="stat-card">
                    <div class="circle" style="--percentage: <?= $percentage ?>%;">
                        <span><?= round($percentage, 1) ?>%</span>
                    </div>
                    <h3><?= htmlspecialchars($stat['nom_categorie']) ?></h3>
                    <p class="stat-label">Montant total : <?= number_format($stat['montant_total'], 2) ?> €</p>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="alert alert-info">Aucune statistique disponible.</div>
        <?php endif; ?>
    </div>
</main>
</body>
</html>