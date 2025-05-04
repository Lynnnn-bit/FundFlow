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

// Calculate project distribution by category
$categoryDistribution = [];
foreach ($projects as $project) {
    $category = $project['categorie'] ?? 'Non catégorisé';
    if (!isset($categoryDistribution[$category])) {
        $categoryDistribution[$category] = 0;
    }
    $categoryDistribution[$category]++;
}

// Convert data for JavaScript
$chartData = json_encode($categoryDistribution);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Statistiques des Projets</title>
    <link rel="stylesheet" href="css/stylecatego.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .chart-container {
            width: 80%;
            margin: 2rem auto;
            text-align: center;
        }
        canvas {
            max-width: 600px;
            margin: 0 auto;
        }
    </style>
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

    <!-- Chart Section -->
    <div class="chart-container">
        <h1>Répartition des Projets par Catégorie</h1>
        <canvas id="categoryChart"></canvas>
    </div>
</main>

<script>
    const chartData = <?= $chartData ?>;
    const ctx = document.getElementById('categoryChart').getContext('2d');
    const labels = Object.keys(chartData);
    const data = Object.values(chartData);

    new Chart(ctx, {
        type: 'pie',
        data: {
            labels: labels,
            datasets: [{
                label: 'Projets par Catégorie',
                data: data,
                backgroundColor: [
                    '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF', '#FF9F40'
                ],
                hoverOffset: 4
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'top',
                },
                tooltip: {
                    callbacks: {
                        label: function(tooltipItem) {
                            const total = data.reduce((sum, value) => sum + value, 0);
                            const percentage = ((tooltipItem.raw / total) * 100).toFixed(2);
                            return `${tooltipItem.label}: ${tooltipItem.raw} (${percentage}%)`;
                        }
                    }
                }
            }
        }
    });
</script>
</body>
</html>
