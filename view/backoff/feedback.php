<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../control/feedbackcontroller.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$controller = new FeedbackController();
$allFeedback = $controller->getAllFeedback();

// Sorting functionality
$sort_feedback_by_rate_desc = isset($_GET['sort']) && $_GET['sort'] === 'rate_desc';
if ($sort_feedback_by_rate_desc) {
    usort($allFeedback, function ($a, $b) {
        return $b['note'] - $a['note'];
    });
}

$sort_feedback_by_rate_asc = isset($_GET['sort']) && $_GET['sort'] === 'rate_asc';
if ($sort_feedback_by_rate_asc) {
    usort($allFeedback, function ($a, $b) {
        return $a['note'] - $b['note'];
    });
}

// Search functionality
$search_id = isset($_GET['search_id']) ? trim($_GET['search_id']) : null;
if ($search_id !== null && $search_id !== '') {
    $allFeedback = array_filter($allFeedback, function ($feedback) use ($search_id) {
        return isset($feedback['id_feedback']) && strpos((string)$feedback['id_feedback'], $search_id) !== false;
    });
}

$search_consultant_id = isset($_GET['search_consultant_id']) ? trim($_GET['search_consultant_id']) : null;
if ($search_consultant_id !== null && $search_consultant_id !== '') {
    $allFeedback = array_filter($allFeedback, function ($feedback) use ($search_consultant_id) {
        return isset($feedback['id_consultation']) && strpos((string)$feedback['id_consultation'], $search_consultant_id) !== false;
    });
}

// Pagination
$items_per_page = 5;
$total_items = count($allFeedback);
$total_pages = ceil($total_items / $items_per_page);

$current_page = isset($_GET['page']) ? max(1, min($total_pages, (int)$_GET['page'])) : 1;
$offset = ($current_page - 1) * $items_per_page;

$paginatedFeedback = array_slice($allFeedback, $offset, $items_per_page);

// Message handling
$success_message = isset($_SESSION['success']) ? $_SESSION['success'] : null;
$error_message = isset($_SESSION['error']) ? $_SESSION['error'] : null;

if (isset($_SESSION['success'])) {
    unset($_SESSION['success']);
}
if (isset($_SESSION['error'])) {
    unset($_SESSION['error']);
}

// Statistics calculations with proper validation
$total_feedback = count($allFeedback);
$total_rating = 0;
$average_rating = 0;
$rating_distribution = array_fill(1, 5, 0); // Initialize with zeros for all ratings
$unique_consultants = 0;
$unique_consultants_percentage = 0;

if (!empty($allFeedback)) {
    // Calculate total rating and rating distribution
    foreach ($allFeedback as $feedback) {
        if (isset($feedback['note']) && is_numeric($feedback['note'])) {
            $note = (int)$feedback['note'];
            $total_rating += $note;
            if ($note >= 1 && $note <= 5) {
                $rating_distribution[$note]++;
            }
        }
    }
    
    $average_rating = $total_feedback > 0 ? $total_rating / $total_feedback : 0;
    
    // Calculate unique consultants
    $consultant_ids = array();
    foreach ($allFeedback as $feedback) {
        if (isset($feedback['id_consultation'])) {
            $consultant_ids[] = $feedback['id_consultation'];
        }
    }
    $unique_consultants = count(array_unique($consultant_ids));
    $unique_consultants_percentage = $total_feedback > 0 ? ($unique_consultants / $total_feedback) * 100 : 0;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FundFlow - Gestion des Feedbacks</title>
    <link rel="stylesheet" href="../Frontoff/css/stylebackof.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .yellow-star {
            color: gold;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.4);
        }
        
        .modal-content {
            background-color: var(--white);
            margin: 15% auto;
            padding: 2rem;
            border-radius: 12px;
            width: 60%;
            max-width: 800px;
            box-shadow: var(--shadow-lg);
        }
        
        .close {
            color: var(--gray);
            float: right;
            font-size: 28px;
            font-weight: bold;
            transition: var(--transition);
        }
        
        .close:hover,
        .close:focus {
            color: var(--dark);
            text-decoration: none;
            cursor: pointer;
        }
        
        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background: var(--white);
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: var(--shadow-sm);
            transition: var(--transition);
            border-left: 4px solid var(--primary);
        }
        
        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            color: var(--dark);
            background: linear-gradient(to right, var(--primary), var(--secondary));
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .stat-label {
            font-size: 0.95rem;
            color: var(--gray);
        }
        
        .search-container {
            display: flex;
            gap: 1rem;
            align-items: center;
        }
        
        .search-container .form-control {
            min-width: 250px;
        }
        
        .action-buttons {
            display: flex;
            gap: 0.5rem;
        }
        
        .badge {
            display: inline-flex;
            align-items: center;
            padding: 0.4em 0.8em;
            font-size: 0.75em;
            font-weight: 700;
            line-height: 1;
            text-align: center;
            white-space: nowrap;
            vertical-align: baseline;
            border-radius: 50rem;
            gap: 0.4rem;
        }
        
        .badge-primary {
            background-color: var(--primary);
            color: white;
        }
        
        .badge-success {
            background-color: var(--secondary);
            color: white;
        }
        
        .badge-warning {
            background-color: #f39c12;
            color: white;
        }
        
        .badge-info {
            background-color: #3498db;
            color: white;
        }
        
        .badge-danger {
            background-color: #e74c3c;
            color: white;
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
                    <li class="active"><a href="feedback.php"><i class="fas fa-comments"></i> Feedbacks</a></li>
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
                    <h1><i class="fas fa-comment-alt"></i> Gestion des Feedbacks</h1>
                    <div class="header-actions">
                        <div class="search-container">
                            <form method="GET" action="feedback.php" class="search-form">
                                <input type="text" name="search_consultant_id" placeholder="Rechercher par ID Consultation" 
                                       value="<?php echo isset($search_consultant_id) ? htmlspecialchars($search_consultant_id) : ''; ?>" 
                                       class="form-control">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search"></i>
                                </button>
                                <?php if (isset($search_consultant_id)): ?>
                                    <a href="feedback.php" class="btn btn-danger">
                                        <i class="fas fa-times"></i>
                                    </a>
                                <?php endif; ?>
                            </form>
                        </div>
                    </div>
                </div>

                <?php if (isset($success_message) && !empty($success_message)): ?>
                    <div class="alert alert-success"><?php echo htmlspecialchars($success_message); ?></div>
                <?php endif; ?>
                
                <?php if (isset($error_message) && !empty($error_message)): ?>
                    <div class="alert alert-danger"><?php echo htmlspecialchars($error_message); ?></div>
                <?php endif; ?>

                <div class="stats-container">
                    <div class="stat-card">
                        <div class="stat-value"><?= $total_feedback ?></div>
                        <div class="stat-label">Feedbacks total</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value"><?= number_format($average_rating, 1) ?></div>
                        <div class="stat-label">Note moyenne</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value"><?= $unique_consultants ?></div>
                        <div class="stat-label">Consultants uniques</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value">5</div>
                        <div class="stat-label">Notes possibles</div>
                    </div>
                </div>

                <section class="card">
                    <div class="card-header">
                        <h3><i class="fas fa-list"></i> Liste des Feedbacks</h3>
                        <div class="card-header-actions">
                            <a href="addfeedback.php" class="btn btn-primary btn-sm">
                                <i class="fas fa-plus"></i> Ajouter
                            </a>
                            <a href="feedback.php?sort=rate_desc" class="btn btn-secondary btn-sm">
                                <i class="fas fa-sort-amount-down"></i> Note
                            </a>
                            <a href="feedback.php?sort=rate_asc" class="btn btn-secondary btn-sm">
                                <i class="fas fa-sort-amount-up"></i> Note
                            </a>
                            <button id="statsButton" class="btn btn-info btn-sm">
                                <i class="fas fa-chart-pie"></i> Stats
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Consultation ID</th>
                                        <th>Note</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($paginatedFeedback as $feedback): ?>
                                        <tr>
                                            <td><?php echo isset($feedback['id_feedback']) ? htmlspecialchars($feedback['id_feedback']) : ''; ?></td>
                                            <td><?php echo isset($feedback['id_consultation']) ? htmlspecialchars($feedback['id_consultation']) : ''; ?></td>
                                            <td>
                                                <div class="stars">
                                                    <?php 
                                                    $note = isset($feedback['note']) ? (int)$feedback['note'] : 0;
                                                    for ($i = 1; $i <= 5; $i++): ?>
                                                        <i class="fas fa-star <?php echo $i <= $note ? 'yellow-star' : ''; ?>"></i>
                                                    <?php endfor; ?>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="action-buttons">
                                                    <?php if (isset($feedback['id_feedback'])): ?>
                                                        <a href="editfeedback.php?id=<?php echo $feedback['id_feedback']; ?>" class="btn btn-warning btn-sm">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <a href="deletefeedback.php?id=<?php echo $feedback['id_feedback']; ?>" 
                                                           class="btn btn-danger btn-sm"
                                                           onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce feedback?')">
                                                            <i class="fas fa-trash"></i>
                                                        </a>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Pagination -->
                        <div class="pagination">
                            <?php if ($total_pages > 1): ?>
                                <?php if ($current_page > 1): ?>
                                    <a href="?page=1<?php echo isset($search_consultant_id) ? '&search_consultant_id=' . urlencode($search_consultant_id) : ''; ?><?php echo isset($_GET['sort']) ? '&sort=' . htmlspecialchars($_GET['sort']) : ''; ?>">&laquo;</a>
                                    <a href="?page=<?php echo $current_page - 1; ?><?php echo isset($search_consultant_id) ? '&search_consultant_id=' . urlencode($search_consultant_id) : ''; ?><?php echo isset($_GET['sort']) ? '&sort=' . htmlspecialchars($_GET['sort']) : ''; ?>">&lsaquo;</a>
                                <?php else: ?>
                                    <span class="disabled">&laquo;</span>
                                    <span class="disabled">&lsaquo;</span>
                                <?php endif; ?>

                                <?php 
                                // Show page numbers
                                $start_page = max(1, $current_page - 2);
                                $end_page = min($total_pages, $current_page + 2);
                                
                                if ($start_page > 1) {
                                    echo '<a href="?page=1' . (isset($search_consultant_id) ? '&search_consultant_id=' . urlencode($search_consultant_id) : '') . (isset($_GET['sort']) ? '&sort=' . htmlspecialchars($_GET['sort']) : '') . '">1</a>';
                                    if ($start_page > 2) {
                                        echo '<span>...</span>';
                                    }
                                }
                                
                                for ($i = $start_page; $i <= $end_page; $i++) {
                                    if ($i == $current_page) {
                                        echo '<a href="?page=' . $i . (isset($search_consultant_id) ? '&search_consultant_id=' . urlencode($search_consultant_id) : '') . (isset($_GET['sort']) ? '&sort=' . htmlspecialchars($_GET['sort']) : '') . '" class="active">' . $i . '</a>';
                                    } else {
                                        echo '<a href="?page=' . $i . (isset($search_consultant_id) ? '&search_consultant_id=' . urlencode($search_consultant_id) : '') . (isset($_GET['sort']) ? '&sort=' . htmlspecialchars($_GET['sort']) : '') . '">' . $i . '</a>';
                                    }
                                }
                                
                                if ($end_page < $total_pages) {
                                    if ($end_page < $total_pages - 1) {
                                        echo '<span>...</span>';
                                    }
                                    echo '<a href="?page=' . $total_pages . (isset($search_consultant_id) ? '&search_consultant_id=' . urlencode($search_consultant_id) : '') . (isset($_GET['sort']) ? '&sort=' . htmlspecialchars($_GET['sort']) : '') . '">' . $total_pages . '</a>';
                                }
                                ?>

                                <?php if ($current_page < $total_pages): ?>
                                    <a href="?page=<?php echo $current_page + 1; ?><?php echo isset($search_consultant_id) ? '&search_consultant_id=' . urlencode($search_consultant_id) : ''; ?><?php echo isset($_GET['sort']) ? '&sort=' . htmlspecialchars($_GET['sort']) : ''; ?>">&rsaquo;</a>
                                    <a href="?page=<?php echo $total_pages; ?><?php echo isset($search_consultant_id) ? '&search_consultant_id=' . urlencode($search_consultant_id) : ''; ?><?php echo isset($_GET['sort']) ? '&sort=' . htmlspecialchars($_GET['sort']) : ''; ?>">&raquo;</a>
                                <?php else: ?>
                                    <span class="disabled">&rsaquo;</span>
                                    <span class="disabled">&raquo;</span>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </section>
            </div>
        </main>
    </div>

    <!-- Stats Modal -->
    <div id="statsModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2><i class="fas fa-chart-pie"></i> Statistiques des Feedbacks</h2>
            <div style="display: flex; justify-content: space-around; margin-bottom: 2rem;">
                <div class="stat-card">
                    <div class="stat-value"><?= $total_feedback ?></div>
                    <div class="stat-label">Total Feedbacks</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value"><?= number_format($average_rating, 1) ?></div>
                    <div class="stat-label">Note Moyenne</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value"><?= $unique_consultants ?></div>
                    <div class="stat-label">Consultants Uniques</div>
                </div>
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
                <div>
                    <h3>Distribution des Notes</h3>
                    <canvas id="ratingDistributionChart"></canvas>
                </div>
                <div>
                    <h3>Consultants Uniques</h3>
                    <canvas id="consultantPercentageChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Modal handling
        var modal = document.getElementById("statsModal");
        var btn = document.getElementById("statsButton");
        var span = document.getElementsByClassName("close")[0];
        var chartInitialized = false;

        btn.onclick = function() {
            modal.style.display = "block";
            if (!chartInitialized) {
                initializeChart();
                chartInitialized = true;
            }
        }

        span.onclick = function() {
            modal.style.display = "none";
        }

        window.onclick = function(event) {
            if (event.target === modal) {
                modal.style.display = "none";
            }
        }

        // Chart initialization
        function initializeChart() {
            var ratingCtx = document.getElementById('ratingDistributionChart').getContext('2d');
            var consultantCtx = document.getElementById('consultantPercentageChart').getContext('2d');

            // Rating distribution chart
            new Chart(ratingCtx, {
                type: 'bar',
                data: {
                    labels: ['1 étoile', '2 étoiles', '3 étoiles', '4 étoiles', '5 étoiles'],
                    datasets: [{
                        label: 'Distribution des Notes',
                        data: [<?php echo implode(',', $rating_distribution); ?>],
                        backgroundColor: [
                            'rgba(255, 99, 132, 0.7)',
                            'rgba(255, 159, 64, 0.7)',
                            'rgba(255, 205, 86, 0.7)',
                            'rgba(75, 192, 192, 0.7)',
                            'rgba(54, 162, 235, 0.7)'
                        ],
                        borderColor: [
                            'rgba(255, 99, 132, 1)',
                            'rgba(255, 159, 64, 1)',
                            'rgba(255, 205, 86, 1)',
                            'rgba(75, 192, 192, 1)',
                            'rgba(54, 162, 235, 1)'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        }
                    }
                }
            });

            // Consultant percentage chart
            new Chart(consultantCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Consultants Uniques', 'Autres Feedbacks'],
                    datasets: [{
                        data: [<?php echo $unique_consultants_percentage; ?>, <?php echo 100 - $unique_consultants_percentage; ?>],
                        backgroundColor: [
                            'rgba(54, 162, 235, 0.7)',
                            'rgba(201, 203, 207, 0.7)'
                        ],
                        borderColor: [
                            'rgba(54, 162, 235, 1)',
                            'rgba(201, 203, 207, 1)'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });
        }
    </script>
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
    </script>
</body>
</html>