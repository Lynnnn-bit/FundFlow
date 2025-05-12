<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../control/financecontroller.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: connexion.php");
    exit();
}

$userId = $_SESSION['user_id']; // Get the logged-in user's ID
$controller = new FinanceController();
$existingDemands = $controller->getFinanceRequestsByUser($userId); // Fetch only the user's demands

$totalDemands = count($existingDemands);
$totalAmountRequested = array_sum(array_column($existingDemands, 'montant_demandee'));
$totalAcceptedDemands = count(array_filter($existingDemands, fn($d) => $d['status'] === 'accepte'));
$totalRejectedDemands = count(array_filter($existingDemands, fn($d) => $d['status'] === 'rejete'));
$totalPendingDemands = count(array_filter($existingDemands, fn($d) => $d['status'] === 'en_attente'));
$totalAcceptedAmount = array_sum(array_map(function ($d) {
    return $d['status'] === 'accepte' ? $d['montant_demandee'] : 0;
}, $existingDemands));

// Calculate percentages
$acceptanceRate = $totalDemands > 0 ? round(($totalAcceptedDemands / $totalDemands) * 100) : 0;
$rejectionRate = $totalDemands > 0 ? round(($totalRejectedDemands / $totalDemands) * 100) : 0;
$averageAmount = $totalDemands > 0 ? round($totalAmountRequested / $totalDemands) : 0;

// Simulate monthly data for the chart
$monthlyDemands = [12, 19, 15, 22, 18, 25, 20, 23, 19, 22, 25, 30];
$monthlyAccepted = [8, 12, 10, 15, 12, 18, 14, 16, 13, 15, 18, 22];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FundFlow - Statistiques</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&family=Playfair+Display:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        /* Statistics Specific Styles */
        .stats-container {
            margin-top: 2rem;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border-radius: 16px;
            padding: 1.5rem;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            background: rgba(255, 255, 255, 0.15);
        }

        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
        }

        .stat-icon.bg-blue {
            background: rgba(67, 97, 238, 0.2);
            color: #4361ee;
        }

        .stat-icon.bg-green {
            background: rgba(16, 185, 129, 0.2);
            color: #10b981;
        }

        .stat-icon.bg-orange {
            background: rgba(249, 115, 22, 0.2);
            color: #f97316;
        }

        .stat-icon.bg-red {
            background: rgba(239, 68, 68, 0.2);
            color: #ef4444;
        }

        .stat-content h3 {
            font-size: 0.9rem;
            color: rgba(255,255,255,0.8);
            margin-bottom: 0.5rem;
        }

        .stat-value {
            font-size: 1.5rem;
            font-weight: 600;
            color: white;
        }

        .stat-comparison {
            font-size: 0.8rem;
            color: rgba(255,255,255,0.7);
        }

        .action-buttons {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
            flex-wrap: wrap;
        }

        .chart-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .chart-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border-radius: 16px;
            padding: 1.5rem;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .chart-card h3 {
            font-size: 1.25rem;
            margin-bottom: 1.5rem;
            color: white;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .chart-wrapper {
            position: relative;
            height: 250px;
            width: 100%;
        }

        @media (max-width: 768px) {
            .chart-container {
                grid-template-columns: 1fr;
            }
        }
        /* Button Container Styles */
        .action-buttons {
            display: flex;
            gap: 15px;
            margin: 25px 0;
            flex-wrap: wrap;
            justify-content: center;
        }

        /* Base Button Styles */
        .action-btn {
            position: relative;
            padding: 12px 25px;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            min-width: 180px;
            overflow: hidden;
            color: white;
        }

        /* Button Hover Effects */
        .action-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
        }

        .action-btn:active {
            transform: translateY(1px);
        }

        /* Button Before Pseudo-element (for animation) */
        .action-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: 0.5s;
        }

        .action-btn:hover::before {
            left: 100%;
        }

        /* Individual Button Colors */
        .btn-new {
            background: linear-gradient(135deg, #4e54c8, #8f94fb);
            border-left: 4px solid #8f94fb;
        }

        .btn-history {
            background: linear-gradient(135deg, #11998e, #38ef7d);
            border-left: 4px solid #38ef7d;
        }

        .btn-stats {
            background: linear-gradient(135deg, #f46b45, #eea849);
            border-left: 4px solid #eea849;
        }

        .btn-chatbot {
            background: linear-gradient(135deg, #8E2DE2, #4A00E0);
            border-left: 4px solid #4A00E0;
        }

        /* Button Icons */
        .action-btn i {
            margin-right: 8px;
            font-size: 18px;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .action-buttons {
                flex-direction: column;
                align-items: center;
            }
            
            .action-btn {
                width: 100%;
                max-width: 250px;
            }
        }

        /* Enhanced Styling for "Mon compte" Dropdown */
        .profile-menu-container {
            position: relative;
        }

        .profile-menu-btn {
            background: linear-gradient(135deg, #3a56d4, #10b981);
            color: white;
            border: none;
            border-radius: 50px;
            padding: 0.75rem 1.5rem;
            font-size: 0.95rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .profile-menu-btn:hover {
            background: linear-gradient(135deg, #10b981, #3a56d4);
            transform: translateY(-2px);
            box-shadow: 0 8px 12px rgba(0, 0, 0, 0.2);
        }

        .profile-menu {
            display: none;
            position: absolute;
            top: 100%;
            right: 0;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 12px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
            list-style: none;
            padding: 0.5rem 0;
            margin: 0;
            z-index: 10;
            animation: fadeIn 0.3s ease;
        }

        .profile-menu li {
            padding: 0.5rem 1rem;
            transition: background 0.3s ease;
        }

        .profile-menu li a {
            text-decoration: none;
            color: #333;
            font-weight: 500;
            display: block;
            transition: color 0.3s ease, background 0.3s ease;
        }

        .profile-menu li:hover {
            background: rgba(16, 185, 129, 0.1);
        }

        .profile-menu li a:hover {
            color: #10b981;
        }

        .profile-menu li a.logout {
            color: #dc2626;
            font-weight: 600;
        }

        .profile-menu li a.logout:hover {
            color: white;
            background: #dc2626;
        }

        .profile-menu-container:hover .profile-menu {
            display: block;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>
<body>
<div class="background-effect"></div>
<div class="particles-container" id="particles-js"></div>

<div class="dashboard-container">
<header class="navbar">
        <div class="logo-container">
            <a href="acceuil2.php">
                <img src="assets/Logo_FundFlow.png" alt="FundFlow Logo" class="brand-logo">
            </a>
        </div>
        
        <div class="nav-links">
            <a href="acceuil2.php" class="nav-link"><i class="fas fa-home"></i> Accueil</a>
            <a href="apropos.html" class="nav-link"><i class="fas fa-info-circle"></i> À propos</a>
            <a href="contact.html" class="nav-link"><i class="fas fa-envelope"></i> Contact</a>
            <a href="events.php" class="nav-link"><i class="fas fa-calendar-alt"></i> Événements</a>
            <a href="partenaire.php" class="nav-link"><i class="fas fa-handshake"></i> Partenariats</a>
            
            <div class="profile-menu-container">
                <button class="profile-menu-btn">Mon compte ▼</button>
                <ul class="profile-menu">
                    <li><a href="profiles.php">Profil</a></li>
                    <?php if ($_SESSION['user']['role'] === 'investisseur'): ?>
                        <li><a href="mesreponses.php">Mes réponses</a></li>
                        <li><a href="demands_list.php">Liste des demandes</a></li>
                    <?php endif; ?>
                    <?php if ($_SESSION['user']['role'] === 'entrepreneur'): ?>
                        <li><a href="mesprojet.php">Mes projets</a></li>
                        <li><a href="historique.php">mes demandes</a></li>
                    <?php endif; ?>
                    <li><a href="allconsult.php">Consultation</a></li>
                    <li><a href="connexion.php?logout=1" class="logout">Déconnexion</a></li>
                </ul>
            </div>
        </div>
    </header>

    <div class="page-header animate__animated animate__fadeInDown">
        <h1><i class="fas fa-chart-pie header-icon"></i> Tableau de Bord Statistique</h1>
        <p>Analyse et visualisation des données de financement</p>
    </div>

    <div class="stats-container">
    <div class="action-buttons">
        <a href="financemet.php" class="action-btn btn-new animate__animated animate__fadeIn">
            <i class="fas fa-plus-circle"></i> Nouvelle Demande
        </a>
        <a href="historique.php" class="action-btn btn-history animate__animated animate__fadeIn animate__delay-1s">
            <i class="fas fa-history"></i> Historique
        </a>
        <a href="statistiquesf.php" class="action-btn btn-stats animate__animated animate__fadeIn animate__delay-2s">
            <i class="fas fa-chart-pie"></i> Statistiques
        </a>
        <a href="chatbot.php" class="action-btn btn-chatbot animate__animated animate__fadeIn animate__delay-3s">
            <i class="fas fa-robot"></i> Chatbot
        </a>
    </div>

        <!-- Stats Cards -->
        <div class="stats-grid">
            <div class="stat-card animate__animated animate__fadeIn">
                <div class="stat-icon bg-blue">
                    <i class="fas fa-file-invoice-dollar"></i>
                </div>
                <div class="stat-content">
                    <h3>Total des Demandes</h3>
                    <div class="stat-value"><?= $totalDemands ?></div>
                    <div class="stat-comparison">+12% vs mois dernier</div>
                </div>
            </div>
            <div class="stat-card animate__animated animate__fadeIn animate__delay-1s">
                <div class="stat-icon bg-green">
                    <i class="fas fa-euro-sign"></i>
                </div>
                <div class="stat-content">
                    <h3>Montant Total</h3>
                    <div class="stat-value"><?= number_format($totalAmountRequested, 0, ',', ' ') ?> €</div>
                    <div class="stat-comparison">Moyenne: <?= number_format($averageAmount, 0, ',', ' ') ?> €</div>
                </div>
            </div>
            <div class="stat-card animate__animated animate__fadeIn animate__delay-2s">
                <div class="stat-icon bg-green">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-content">
                    <h3>Taux d'Acceptation</h3>
                    <div class="stat-value"><?= $acceptanceRate ?>%</div>
                    <div class="stat-comparison"><?= $totalAcceptedDemands ?> demandes acceptées</div>
                </div>
            </div>
            <div class="stat-card animate__animated animate__fadeIn animate__delay-3s">
                <div class="stat-icon bg-red">
                    <i class="fas fa-times-circle"></i>
                </div>
                <div class="stat-content">
                    <h3>Taux de Rejet</h3>
                    <div class="stat-value"><?= $rejectionRate ?>%</div>
                    <div class="stat-comparison"><?= $totalRejectedDemands ?> demandes rejetées</div>
                </div>
            </div>
        </div>

        <!-- Charts -->
        <div class="chart-container animate__animated animate__fadeInUp">
            <div class="chart-card">
                <h3><i class="fas fa-chart-pie"></i> Répartition des Statuts</h3>
                <div class="chart-wrapper">
                    <canvas id="statusChart"></canvas>
                </div>
            </div>

            <div class="chart-card">
                <h3><i class="fas fa-chart-bar"></i> Montants Demandés vs Acceptés</h3>
                <div class="chart-wrapper">
                    <canvas id="amountChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<footer class="footer">
  <div class="footer-container">
    <div class="footer-col logo-col">
      <img src="assets/Logo_FundFlow.png" alt="Company Logo" class="footer-logo">
      <p class="footer-description">Plateforme de financement collaboratif</p>
    </div>
    <div class="footer-col links-col">
      <h4>Liens Rapides</h4>
      <ul>
        <li><a href="financemet.php">Accueil</a></li>
        <li><a href="#">À propos</a></li>
        <li><a href="#">Services</a></li>
        <li><a href="#">Blog</a></li>
        <li><a href="#">Contact</a></li>
      </ul>
    </div>
    <div class="footer-col contact-col">
      <h4>Contactez-nous</h4>
      <p>123 Rue de Finance, Paris 75001</p>
      <p>Email: <a href="mailto:contact@fundflow.com">contact@fundflow.com</a></p>
      <p>Tél: +33 1 23 45 67 89</p>
    </div>
    <div class="footer-col social-col">
      <h4>Suivez-nous</h4>
      <div class="social-icons">
        <a href="#" aria-label="Facebook"><i class="fab fa-facebook"></i></a>
        <a href="#" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
        <a href="#" aria-label="LinkedIn"><i class="fab fa-linkedin"></i></a>
        <a href="#" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
      </div>
    </div>
  </div>
  <div class="footer-legal">
    <a href="#">Politique de confidentialité</a> |
    <a href="#">Conditions d'utilisation</a> |
    <span>&copy; 2025 FundFlow. Tous droits réservés.</span>
  </div>
</footer>

<script src="https://cdn.jsdelivr.net/particles.js/2.0.0/particles.min.js"></script>
<script>
// Initialize particles.js
particlesJS("particles-js", {
    "particles": {
        "number": {
            "value": 60,
            "density": {
                "enable": true,
                "value_area": 800
            }
        },
        "color": {
            "value": "#4cc9f0"
        },
        "shape": {
            "type": "circle",
            "stroke": {
                "width": 0,
                "color": "#000000"
            }
        },
        "opacity": {
            "value": 0.5,
            "random": true,
            "anim": {
                "enable": true,
                "speed": 1,
                "opacity_min": 0.1,
                "sync": false
            }
        },
        "size": {
            "value": 3,
            "random": true,
            "anim": {
                "enable": true,
                "speed": 2,
                "size_min": 0.1,
                "sync": false
            }
        },
        "line_linked": {
            "enable": true,
            "distance": 150,
            "color": "#4cc9f0",
            "opacity": 0.4,
            "width": 1
        },
        "move": {
            "enable": true,
            "speed": 1,
            "direction": "none",
            "random": true,
            "straight": false,
            "out_mode": "out",
            "bounce": false,
            "attract": {
                "enable": true,
                "rotateX": 600,
                "rotateY": 1200
            }
        }
    },
    "interactivity": {
        "detect_on": "canvas",
        "events": {
            "onhover": {
                "enable": true,
                "mode": "grab"
            },
            "onclick": {
                "enable": true,
                "mode": "push"
            },
            "resize": true
        },
        "modes": {
            "grab": {
                "distance": 140,
                "line_linked": {
                    "opacity": 1
                }
            },
            "push": {
                "particles_nb": 4
            }
        }
    },
    "retina_detect": true
});

function handleMenu(select) {
    const value = select.value;
    if (value === 'logout') {
        window.location.href = 'connexion.php?logout=1';
    } else if (value) {
        window.location.href = value + '.php';
    }
    select.value = ''; // Réinitialiser la sélection
}

// Status Chart
const statusChartCtx = document.getElementById('statusChart').getContext('2d');
new Chart(statusChartCtx, {
    type: 'doughnut',
    data: {
        labels: ['Acceptées', 'Rejetées', 'En Attente'],
        datasets: [{
            data: [<?= $totalAcceptedDemands ?>, <?= $totalRejectedDemands ?>, <?= $totalPendingDemands ?>],
            backgroundColor: [
                'rgba(16, 185, 129, 0.8)',
                'rgba(239, 68, 68, 0.8)',
                'rgba(251, 191, 36, 0.8)'
            ],
            borderColor: [
                'rgba(16, 185, 129, 1)',
                'rgba(239, 68, 68, 1)',
                'rgba(251, 191, 36, 1)'
            ],
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom',
                labels: {
                    color: 'white',
                    font: {
                        size: 12
                    }
                }
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        const label = context.label || '';
                        const value = context.raw || 0;
                        const total = context.dataset.data.reduce((a, b) => a + b, 0);
                        const percentage = Math.round((value / total) * 100);
                        return `${label}: ${value} (${percentage}%)`;
                    }
                }
            }
        },
        cutout: '70%'
    }
});

// Amount Chart
const amountChartCtx = document.getElementById('amountChart').getContext('2d');
new Chart(amountChartCtx, {
    type: 'bar',
    data: {
        labels: ['Montant Total Demandé', 'Montant Total Accepté'],
        datasets: [{
            label: 'Montant (€)',
            data: [<?= $totalAmountRequested ?>, <?= $totalAcceptedAmount ?>],
            backgroundColor: [
                'rgba(67, 97, 238, 0.8)',
                'rgba(16, 185, 129, 0.8)'
            ],
            borderColor: [
                'rgba(67, 97, 238, 1)',
                'rgba(16, 185, 129, 1)'
            ],
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        return context.raw.toLocaleString() + ' €';
                    }
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return value.toLocaleString() + ' €';
                    },
                    color: 'white'
                },
                grid: {
                    color: 'rgba(255,255,255,0.1)'
                }
            },
            x: {
                ticks: {
                    color: 'white'
                },
                grid: {
                    color: 'rgba(255,255,255,0.1)'
                }
            }
        }
    }
});

// Monthly Chart
const monthlyChartCtx = document.getElementById('monthlyChart').getContext('2d');
new Chart(monthlyChartCtx, {
    type: 'line',
    data: {
        labels: ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Jun', 'Jul', 'Aoû', 'Sep', 'Oct', 'Nov', 'Déc'],
        datasets: [{
            label: 'Demandes',
            data: <?= json_encode($monthlyDemands) ?>,
            borderColor: 'rgba(67, 97, 238, 1)',
            backgroundColor: 'rgba(67, 97, 238, 0.1)',
            tension: 0.3,
            fill: true
        }, {
            label: 'Acceptées',
            data: <?= json_encode($monthlyAccepted) ?>,
            borderColor: 'rgba(16, 185, 129, 1)',
            backgroundColor: 'rgba(16, 185, 129, 0.1)',
            tension: 0.3,
            fill: true
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            tooltip: {
                mode: 'index',
                intersect: false
            },
            legend: {
                labels: {
                    color: 'white'
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    color: 'white'
                },
                grid: {
                    color: 'rgba(255,255,255,0.1)'
                }
            },
            x: {
                ticks: {
                    color: 'white'
                },
                grid: {
                    color: 'rgba(255,255,255,0.1)'
                }
            }
        }
    }
});
</script>
</body>
</html>