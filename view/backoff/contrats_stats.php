<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../control/ContratController.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$contratController = new ContratController();
$contrats = $contratController->getAllContracts();

// Calculate statistics
$statusCounts = [
    'en attente' => 0,
    'actif' => 0,
    'expiré' => 0,
    'rejeté' => 0,
];

foreach ($contrats as $contrat) {
    if (isset($statusCounts[$contrat['status']])) {
        $statusCounts[$contrat['status']]++;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FundFlow - Statistiques Contrats</title>
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
        
        .chart-container {
            background: var(--white);
            border-radius: 12px;
            padding: 1.8rem;
            box-shadow: var(--shadow-sm);
            margin-bottom: 2rem;
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
                        <ul class="submenu">
                            <li><a href="statistics.php"><i class="fas fa-chart-pie"></i> Statistiques</a></li>
                            <li><a href="demands.php"><i class="fas fa-file-invoice-dollar"></i> Demandes</a></li>
                        </ul>
                    </li>
                    <li><a href="#"><i class="fas fa-comments"></i> Consultations</a></li>
                    <li class="active"><a href="contrats.php"><i class="fas fa-handshake"></i> Partenariats</a></li>
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
                <button class="menu-toggle">
                    <i class="fas fa-bars"></i>
                </button>
            </header>

            <div class="main-container">
                <div class="page-header">
                    <h1><i class="fas fa-chart-pie"></i> Statistiques des Contrats</h1>
                    <div class="header-actions">
                        <a href="contrats.php" class="btn btn-primary">
                            <i class="fas fa-arrow-left"></i> Retour
                        </a>
                    </div>
                </div>

                <section class="card">
                    <div class="card-header">
                        <h3><i class="fas fa-chart-bar"></i> Répartition des contrats par statut</h3>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="contractsChart"></canvas>
                        </div>
                    </div>
                </section>
            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
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

        // Initialize chart
        document.addEventListener('DOMContentLoaded', function () {
            const ctx = document.getElementById('contractsChart').getContext('2d');
            new Chart(ctx, {
                type: 'pie',
                data: {
                    labels: ['En attente', 'Actif', 'Expiré', 'Rejeté'],
                    datasets: [{
                        label: 'Statistiques des Contrats',
                        data: [
                            <?= $statusCounts['en attente'] ?>,
                            <?= $statusCounts['actif'] ?>,
                            <?= $statusCounts['expiré'] ?>,
                            <?= $statusCounts['rejeté'] ?>
                        ],
                        backgroundColor: [
                            '#f39c12', // En attente
                            '#00d09c',  // Actif
                            '#e74c3c',   // Expiré
                            '#7f8c8d'    // Rejeté
                        ],
                        borderColor: '#fff',
                        borderWidth: 2
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
                                label: function(context) {
                                    const label = context.label || '';
                                    const value = context.raw || 0;
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const percentage = Math.round((value / total) * 100);
                                    return `${label}: ${value} (${percentage}%)`;
                                }
                            }
                        }
                    }
                }
            });
        });
    </script>
</body>
</html>