<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../control/financecontroller.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$financeController = new FinanceController();
$demandes = $financeController->getAllFinanceRequests();

// Calculate statistics
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
    <link rel="stylesheet" href="../Frontoff/css/stylebackof.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .admin-background {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -2;
            background: linear-gradient(135deg, #f5f7ff 0%, #e8ecff 100%);
            overflow: hidden;
        }
        
        .admin-background::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('../Frontoff/assets/Logo_FundFlow.png') center/30% no-repeat;
            opacity: 0.03;
            pointer-events: none;
        }
        
        .admin-background::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle at 20% 30%, rgba(67, 97, 238, 0.05) 0%, transparent 50%),
                        radial-gradient(circle at 80% 70%, rgba(16, 185, 129, 0.05) 0%, transparent 50%);
        }
    </style>
</head>
<body>
    <button class="sidebar-toggle">
        <i class="fas fa-bars"></i>
    </button>
    
    <div class="admin-background"></div>
    
    <div class="admin-container">
        <!-- Sidebar Navigation -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <img src="../Frontoff/assets/Logo_FundFlow.png" alt="FundFlow Logo" class="sidebar-logo">
                <button class="sidebar-close">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <nav class="sidebar-menu">
                <ul>
                    <li><a href="backoffice.php"><i class="fas fa-tachometer-alt"></i> Tableau de bord</a></li>
                    <li><a href="utilisateurmet.php"><i class="fas fa-users"></i> Utilisateurs</a></li>
                    <li><a href="categories.php"><i class="fas fa-project-diagram"></i> Catégories</a></li>
                    <li>
                        <a href="#" class="toggle-submenu"><i class="fas fa-hand-holding-usd"></i> Financements</a>
                        <ul class="submenu active">
                            <li class="active"><a href="statistics.php"><i class="fas fa-chart-pie"></i> Statistiques</a></li>
                            <li><a href="demands.php"><i class="fas fa-file-invoice-dollar"></i> Demandes</a></li>
                        </ul>
                    </li>
                    <li><a href="feedback.php"><i class="fas fa-comments"></i> Feedbacks</a></li>
                    <li><a href="contrats.php"><i class="fas fa-handshake"></i> Contrats</a></li>
                    <li><a href="#"><i class="fas fa-rocket"></i> Startups</a></li>
                </ul>
            </nav>
            <div class="sidebar-footer">
                <p>FundFlow Admin v1.0</p>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <header class="top-nav">
                
            </header>

            <div class="main-container">
                <div class="page-header">
                    <h1><i class="fas fa-chart-pie"></i> Statistiques des financements</h1>
                </div>

                <section class="card">
                    <div class="card-header">
                        <h3><i class="fas fa-chart-line"></i> Aperçu global</h3>
                    </div>
                    <div class="card-body">
                        <div class="stats-container">
                            <div class="stat-card">
                                <div class="stat-icon">
                                    <i class="fas fa-file-invoice-dollar"></i>
                                </div>
                                <div class="stat-value"><?= $totalDemands ?></div>
                                <div class="stat-label">Total des Demandes</div>
                            </div>
                            
                            <div class="stat-card">
                                <div class="stat-icon">
                                    <i class="fas fa-euro-sign"></i>
                                </div>
                                <div class="stat-value">€<?= number_format($totalAmountRequested, 2) ?></div>
                                <div class="stat-label">Montant Demandé</div>
                            </div>
                            
                            <div class="stat-card">
                                <div class="stat-icon">
                                    <i class="fas fa-check-circle"></i>
                                </div>
                                <div class="stat-value"><?= $totalAcceptedDemands ?></div>
                                <div class="stat-label">Demandes Acceptées</div>
                            </div>
                            
                            <div class="stat-card">
                                <div class="stat-icon">
                                    <i class="fas fa-check-double"></i>
                                </div>
                                <div class="stat-value">€<?= number_format($totalAcceptedAmount, 2) ?></div>
                                <div class="stat-label">Montant Accepté</div>
                            </div>
                            
                            <div class="stat-card">
                                <div class="stat-icon">
                                    <i class="fas fa-times-circle"></i>
                                </div>
                                <div class="stat-value"><?= $totalRejectedDemands ?></div>
                                <div class="stat-label">Demandes Rejetées</div>
                            </div>
                            
                            <div class="stat-card">
                                <div class="stat-icon">
                                    <i class="fas fa-clock"></i>
                                </div>
                                <div class="stat-value"><?= $totalPendingDemands ?></div>
                                <div class="stat-label">En Attente</div>
                            </div>
                        </div>
                        
                        <div class="chart-container mt-5">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="chart-card">
                                        <canvas id="statusChart"></canvas>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="chart-card">
                                        <canvas id="amountChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </main>
    </div>

    <script>
        // Toggle sidebar with persistent state
        const sidebar = document.querySelector('.sidebar');
        const sidebarToggle = document.querySelector('.sidebar-toggle');
        const sidebarClose = document.querySelector('.sidebar-close');
        const mainContent = document.querySelector('.main-content');

        // Check localStorage for saved state
        const sidebarState = localStorage.getItem('sidebarState');

        // Initialize sidebar state
        if (sidebarState === 'collapsed') {
            sidebar.classList.add('collapsed');
            mainContent.classList.add('expanded');
        }

        // Toggle function
        function toggleSidebar() {
            sidebar.classList.toggle('collapsed');
            mainContent.classList.toggle('expanded');
            
            // Save state to localStorage
            const isCollapsed = sidebar.classList.contains('collapsed');
            localStorage.setItem('sidebarState', isCollapsed ? 'collapsed' : 'expanded');
        }

        // Event listeners
        sidebarToggle.addEventListener('click', toggleSidebar);
        sidebarClose.addEventListener('click', toggleSidebar);

        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', (e) => {
            if (window.innerWidth <= 991 && 
                !sidebar.contains(e.target) && 
                !sidebarToggle.contains(e.target) &&
                !sidebar.classList.contains('collapsed')) {
                toggleSidebar();
            }
        });

        // Toggle submenus
        document.querySelectorAll('.toggle-submenu').forEach(item => {
            item.addEventListener('click', function(e) {
                e.preventDefault();
                const submenu = this.nextElementSibling;
                submenu.classList.toggle('active');
            });
        });

        // Status Chart
        const statusCtx = document.getElementById('statusChart').getContext('2d');
        new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: ['Acceptées', 'Rejetées', 'En Attente'],
                datasets: [{
                    data: [<?= $totalAcceptedDemands ?>, <?= $totalRejectedDemands ?>, <?= $totalPendingDemands ?>],
                    backgroundColor: [
                        'rgba(16, 185, 129, 0.8)',
                        'rgba(231, 76, 60, 0.8)',
                        'rgba(243, 156, 18, 0.8)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'bottom' },
                    title: { display: true, text: 'Répartition des statuts' }
                },
                cutout: '70%'
            }
        });

        // Amount Chart
        const amountCtx = document.getElementById('amountChart').getContext('2d');
        new Chart(amountCtx, {
            type: 'bar',
            data: {
                labels: ['Montant Demandé', 'Montant Accepté'],
                datasets: [{
                    label: 'Montant (€)',
                    data: [<?= $totalAmountRequested ?>, <?= $totalAcceptedAmount ?>],
                    backgroundColor: [
                        'rgba(67, 97, 238, 0.7)',
                        'rgba(16, 185, 129, 0.7)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: false },
                    title: { display: true, text: 'Comparaison des montants' }
                },
                scales: { y: { beginAtZero: true } }
            }
        });
        </script>
</body>
</html>