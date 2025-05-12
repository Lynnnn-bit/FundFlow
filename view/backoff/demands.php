<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../control/financecontroller.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$financeController = new FinanceController();

// Handle search and sort functionality
$searchQuery = $_GET['search'] ?? '';
$sortColumn = $_GET['sort_column'] ?? 'id_demande';
$sortOrder = $_GET['sort_order'] ?? 'asc';

$demandes = $financeController->getAllFinanceRequests();

// Filter demands based on search query
if (!empty($searchQuery)) {
    $demandes = array_filter($demandes, function ($demand) use ($searchQuery) {
        return stripos($demand['projet_titre'], $searchQuery) !== false;
    });
}

// Sort demands
if (!empty($sortColumn) && in_array($sortColumn, ['id_demande', 'projet_titre', 'montant_demandee', 'duree', 'status'])) {
    usort($demandes, function ($a, $b) use ($sortColumn, $sortOrder) {
        if ($sortOrder === 'asc') {
            return $a[$sortColumn] <=> $b[$sortColumn];
        } else {
            return $b[$sortColumn] <=> $a[$sortColumn];
        }
    });
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FundFlow - Demandes</title>
    <link rel="stylesheet" href="../Frontoff/css/stylebackof.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
                            <li><a href="statistics.php"><i class="fas fa-chart-pie"></i> Statistiques</a></li>
                            <li class="active"><a href="demands.php"><i class="fas fa-file-invoice-dollar"></i> Demandes</a></li>
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
                <button class="menu-toggle">
                    <i class="fas fa-bars"></i>
                </button>
                
            </header>

            <div class="main-container">
                <div class="page-header">
                    <h1><i class="fas fa-file-invoice-dollar"></i> Gestion des demandes</h1>
                    <div class="header-actions">
                        <form method="GET" class="search-form">
                            <div class="input-group">
                                <input type="text" name="search" class="form-control" placeholder="Rechercher..." value="<?= htmlspecialchars($searchQuery) ?>">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <section class="card">
                    <div class="card-header">
                        <h3><i class="fas fa-list"></i> Liste des demandes</h3>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>
                                            <a href="?sort_column=id_demande&sort_order=<?= $sortColumn === 'id_demande' && $sortOrder === 'asc' ? 'desc' : 'asc' ?>&search=<?= urlencode($searchQuery) ?>">
                                                ID Demande
                                                <?php if ($sortColumn === 'id_demande'): ?>
                                                    <i class="fas fa-sort-<?= $sortOrder === 'asc' ? 'up' : 'down' ?>"></i>
                                                <?php endif; ?>
                                            </a>
                                        </th>
                                        <th>
                                            <a href="?sort_column=projet_titre&sort_order=<?= $sortColumn === 'projet_titre' && $sortOrder === 'asc' ? 'desc' : 'asc' ?>&search=<?= urlencode($searchQuery) ?>">
                                                Projet
                                                <?php if ($sortColumn === 'projet_titre'): ?>
                                                    <i class="fas fa-sort-<?= $sortOrder === 'asc' ? 'up' : 'down' ?>"></i>
                                                <?php endif; ?>
                                            </a>
                                        </th>
                                        <th>
                                            <a href="?sort_column=montant_demandee&sort_order=<?= $sortColumn === 'montant_demandee' && $sortOrder === 'asc' ? 'desc' : 'asc' ?>&search=<?= urlencode($searchQuery) ?>">
                                                Montant
                                                <?php if ($sortColumn === 'montant_demandee'): ?>
                                                    <i class="fas fa-sort-<?= $sortOrder === 'asc' ? 'up' : 'down' ?>"></i>
                                                <?php endif; ?>
                                            </a>
                                        </th>
                                        <th>
                                            <a href="?sort_column=duree&sort_order=<?= $sortColumn === 'duree' && $sortOrder === 'asc' ? 'desc' : 'asc' ?>&search=<?= urlencode($searchQuery) ?>">
                                                Durée
                                                <?php if ($sortColumn === 'duree'): ?>
                                                    <i class="fas fa-sort-<?= $sortOrder === 'asc' ? 'up' : 'down' ?>"></i>
                                                <?php endif; ?>
                                            </a>
                                        </th>
                                        <th>
                                            <a href="?sort_column=status&sort_order=<?= $sortColumn === 'status' && $sortOrder === 'asc' ? 'desc' : 'asc' ?>&search=<?= urlencode($searchQuery) ?>">
                                                Statut
                                                <?php if ($sortColumn === 'status'): ?>
                                                    <i class="fas fa-sort-<?= $sortOrder === 'asc' ? 'up' : 'down' ?>"></i>
                                                <?php endif; ?>
                                            </a>
                                        </th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($demandes)): ?>
                                        <?php foreach ($demandes as $demande): ?>
                                            <tr>
                                                <td><?= $demande['id_demande'] ?></td>
                                                <td><?= htmlspecialchars($demande['projet_titre']) ?></td>
                                                <td><?= number_format($demande['montant_demandee'], 2) ?> €</td>
                                                <td><?= $demande['duree'] ?> mois</td>
                                                <td>
                                                    <span class="badge <?= 
                                                        $demande['status'] == 'accepte' ? 'badge-success' : 
                                                        ($demande['status'] == 'rejete' ? 'badge-danger' : 'badge-warning')
                                                    ?>">
                                                        <?= ucfirst($demande['status']) ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="action-buttons">
                                                        <a href="responses.php?demande_id=<?= $demande['id_demande'] ?>" class="btn btn-sm btn-info" title="Voir les réponses">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="6" class="text-center">Aucune demande trouvée</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
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

        // Sortable table headers
        document.querySelectorAll('th a').forEach(header => {
            header.addEventListener('click', function(e) {
                e.preventDefault();
                // Sorting logic would be handled by PHP in this implementation
                window.location.href = this.href;
            });
        });
        </script>
</body>
</html>