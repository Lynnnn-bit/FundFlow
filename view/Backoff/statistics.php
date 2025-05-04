<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../controlle/financecontroller.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$financeController = new FinanceController();

// Calculate statistics
$demandes = $financeController->getAllFinanceRequests();
$totalDemands = count($demandes);
$totalAmountRequested = array_sum(array_column($demandes, 'montant_demandee'));
$totalAcceptedDemands = count(array_filter($demandes, fn($d) => $d['status'] === 'accepte'));
$totalRejectedDemands = count(array_filter($demandes, fn($d) => $d['status'] === 'rejete'));
$totalPendingDemands = count(array_filter($demandes, fn($d) => $d['status'] === 'en_attente'));
$totalAcceptedAmount = array_sum(array_map(function ($d) {
    return $d['status'] === 'accepte' ? $d['montant_demandee'] : 0;
}, $demandes));
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FundFlow - Statistiques</title>
    <link rel="stylesheet" href="../Frontoff/css/stylefinan.css">
    <link rel="stylesheet" href="../Frontoff/css/stylebackof.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .sidebar {
            position: fixed;
            top: 0;
            left: -250px;
            width: 250px;
            height: 100%;
            background-color: #1c1c1c;
            color: white;
            transition: all 0.3s ease-in-out;
            z-index: 1000;
            padding: 1rem;
        }

        .sidebar.active {
            left: 0;
        }

        .sidebar h2 {
            text-align: center;
            margin-bottom: 1.5rem;
        }

        .sidebar ul {
            list-style: none;
            padding: 0;
        }

        .sidebar ul li {
            margin-bottom: 1rem;
        }

        .sidebar ul li a {
            color: white;
            text-decoration: none;
            font-size: 1rem;
            display: flex;
            align-items: center;
        }

        .sidebar ul li a i {
            margin-right: 10px;
        }

        .sidebar ul li a:hover {
            color: #00d09c;
        }

        .sidebar ul li .submenu {
            margin-left: 20px;
            display: none;
        }

        .sidebar ul li .submenu.active {
            display: block;
        }

        .toggle-sidebar {
            position: fixed;
            top: 20px;
            left: 20px;
            background-color: #1c1c1c;
            color: white;
            border: none;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            z-index: 1100;
        }

        .toggle-sidebar i {
            font-size: 1.5rem;
        }
    </style>
</head>
<body>
    <button class="toggle-sidebar">
        <i class="fas fa-bars"></i>
    </button>

    <div class="sidebar">
        <h2>Menu</h2>
        <ul>
            <li><a href="backoffice.php"><i class="fas fa-home"></i> Dashboard</a></li>
            <li><a href="#"><i class="fas fa-users"></i> Utilisateurs</a></li>
            <li><a href="#"><i class="fas fa-project-diagram"></i> Projets</a></li>
            <li>
                <a href="#" class="toggle-submenu"><i class="fas fa-hand-holding-usd"></i> Financements</a>
                <ul class="submenu">
                    <li><a href="statistics.php"><i class="fas fa-chart-bar"></i> Statistiques</a></li>
                    <li><a href="demands.php"><i class="fas fa-list"></i> Demandes</a></li>
                </ul>
            </li>
            <li><a href="#"><i class="fas fa-comments"></i> Consultations</a></li>
            <li><a href="#"><i class="fas fa-handshake"></i> Partenariats</a></li>
            <li><a href="#"><i class="fas fa-rocket"></i> Startups</a></li>
        </ul>
    </div>

    <div class="main-container">
        <h1 class="mb-4"><i class="fas fa-chart-bar"></i> Statistiques</h1>

        <div class="text-end mb-3">
            <!-- Removed PDF export button -->
        </div>

        <div class="stats-container">
            <div class="stat-card">
                <div class="stat-value"><?= $totalDemands ?></div>
                <div class="stat-label">Total des Demandes</div>
            </div>
            <div class="stat-card">
                <div class="stat-value">€<?= number_format($totalAmountRequested, 2) ?></div>
                <div class="stat-label">Montant Total Demandé</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?= $totalAcceptedDemands ?></div>
                <div class="stat-label">Demandes Acceptées</div>
            </div>
            <div class="stat-card">
                <div class="stat-value">€<?= number_format($totalAcceptedAmount, 2) ?></div>
                <div class="stat-label">Montant Accepté</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?= $totalRejectedDemands ?></div>
                <div class="stat-label">Demandes Rejetées</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?= $totalPendingDemands ?></div>
                <div class="stat-label">Demandes en Attente</div>
            </div>
        </div>

        <div class="chart-container mt-5">
            <h3 class="text-center mb-4">Répartition des Statuts des Demandes</h3>
            <canvas id="statusChart"></canvas>
        </div>

        <div class="chart-container mt-5">
            <h3 class="text-center mb-4">Montant Total Demandé vs Accepté</h3>
            <canvas id="amountChart"></canvas>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const toggleSidebarButton = document.querySelector('.toggle-sidebar');
        const sidebar = document.querySelector('.sidebar');
        const toggleSubmenuLinks = document.querySelectorAll('.toggle-submenu');

        toggleSidebarButton.addEventListener('click', () => {
            sidebar.classList.toggle('active');
        });

        toggleSubmenuLinks.forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                const submenu = link.nextElementSibling;
                submenu.classList.toggle('active');
            });
        });

        // Chart.js: Répartition des Statuts des Demandes
        const statusChartCtx = document.getElementById('statusChart').getContext('2d');
        new Chart(statusChartCtx, {
            type: 'doughnut',
            data: {
                labels: ['Acceptées', 'Rejetées', 'En Attente'],
                datasets: [{
                    data: [<?= $totalAcceptedDemands ?>, <?= $totalRejectedDemands ?>, <?= $totalPendingDemands ?>],
                    backgroundColor: ['#2ecc71', '#e74c3c', '#f39c12'],
                    borderColor: ['#27ae60', '#c0392b', '#d35400'],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            color: 'white'
                        }
                    }
                }
            }
        });

        // Chart.js: Montant Total Demandé vs Accepté
        const amountChartCtx = document.getElementById('amountChart').getContext('2d');
        new Chart(amountChartCtx, {
            type: 'bar',
            data: {
                labels: ['Montant Total Demandé', 'Montant Total Accepté'],
                datasets: [{
                    label: 'Montant (€)',
                    data: [<?= $totalAmountRequested ?>, <?= $totalAcceptedAmount ?>],
                    backgroundColor: ['#3498db', '#2ecc71'],
                    borderColor: ['#2980b9', '#27ae60'],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    x: {
                        ticks: {
                            color: 'white'
                        }
                    },
                    y: {
                        ticks: {
                            color: 'white'
                        },
                        beginAtZero: true
                    }
                }
            }
        });
    </script>
</body>
</html>
