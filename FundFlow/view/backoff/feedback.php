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
    <title>Gestion des Feedbacks</title>
    <link rel="stylesheet" href="cssback/feedback.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="cssback/navbar.css">
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
            background-color: #fefefe;
            margin: 15% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 50%;
            border-radius: 8px;
            text-align: center;
        }
        
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }
        
        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }
        
        .stat-box {
            display: inline-block;
            margin: 10px;
            padding: 20px;
            background-color: #f9f9f9;
            border: 1px solid #ddd;
            border-radius: 8px;
            text-align: center;
            width: 150px;
        }
        
        .stat-box h3 {
            margin-bottom: 10px;
            font-size: 16px;
        }
        
        .stat-box p {
            font-size: 20px;
            font-weight: bold;
            color: #007bff;
        }

        .navbar {
            background-color: #0d3b66;
            color: white;
            padding: 10px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .navbar-menu a {
            color: white;
            text-decoration: none;
            font-size: 16px;
            display: flex;
            align-items: center;
        }
        
        .navbar-menu a i {
            margin-right: 5px;
        }
        
        .pagination {
            display: flex;
            justify-content: center;
            margin-top: 20px;
        }
        
        .pagination a {
            color: #007bff;
            padding: 8px 16px;
            text-decoration: none;
            border: 1px solid #ddd;
            margin: 0 4px;
            border-radius: 4px;
        }
        
        .pagination a.active {
            background-color: #007bff;
            color: white;
            border: 1px solid #007bff;
        }
        
        .pagination a:hover:not(.active) {
            background-color: #ddd;
        }
        
        .pagination a.disabled {
            pointer-events: none;
            color: #aaa;
            border-color: #ddd;
        }
        
    </style>
</head>
<body>
    <header class="navbar">
        <div class="logo-container">
            <span class="brand-name">FundFlow</span>
        </div>
        <nav>
        <a href="feedback.php" class="active"><i class="fas fa-comment-alt"></i> Feedbacks</a>
        <a href="../frontoff/apropos.html"><i class="fas fa-info-circle"></i> À propos</a>
        <a href="../frontoff/contact.html"><i class="fas fa-envelope"></i> Contact</a>
        <a href="../frontoff/accueil.html" class="logout"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
    </nav>
    </header>

    <div class="main-container">
        <h1><i class="fas fa-comment-alt"></i> Gestion des Feedbacks</h1>
        
        <?php if (isset($success_message) && !empty($success_message)): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success_message); ?></div>
        <?php endif; ?>
        
        <?php if (isset($error_message) && !empty($error_message)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>

        <div class="actions" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <div>
                <a href="addfeedback.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Ajouter un Feedback
                </a>
                <a href="feedback.php?sort=rate_desc" class="btn btn-primary">
                    <i class="fas fa-star"></i> Trier par Note (décroissant)
                </a>
                <a href="feedback.php?sort=rate_asc" class="btn btn-primary">
                    <i class="fas fa-star"></i> Trier par Note (croissant)
                </a>
            </div>
            <div class="search-container">
                <form method="GET" action="feedback.php" style="display: flex; align-items: center;">
                    <input type="text" name="search_consultant_id" placeholder="Rechercher par ID de Consultation" 
                           value="<?php echo isset($search_consultant_id) ? htmlspecialchars($search_consultant_id) : ''; ?>" 
                           class="form-control" style="width: 250px; margin-right: 10px;">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> Rechercher
                    </button>
                </form>
            </div>
        </div>

        <div style="text-align: right; margin-bottom: 20px;">
            <button id="statsButton" class="btn btn-primary">
                <i class="fas fa-chart-pie"></i> Afficher les Statistiques
            </button>
        </div>

        <div id="statsModal" class="modal">
            <div class="modal-content">
                <span class="close">&times;</span>
                <h2>Statistiques des Feedbacks</h2>
                <canvas id="ratingDistributionChart"></canvas>
                <canvas id="consultantPercentageChart" style="margin-top: 20px;"></canvas>
            </div>
        </div>

        <div class="table-container">
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
                            <td class="actions">
                                <?php if (isset($feedback['id_feedback'])): ?>
                                    <a href="editfeedback.php?id=<?php echo $feedback['id_feedback']; ?>" class="btn btn-warning">
                                        <i class="fas fa-edit"></i> Modifier
                                    </a>
                                    <a href="deletefeedback.php?id=<?php echo $feedback['id_feedback']; ?>" 
                                       class="btn btn-danger"
                                       onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce feedback?')">
                                        <i class="fas fa-trash"></i> Supprimer
                                    </a>
                                <?php endif; ?>
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
                    <a href="?page=1<?php echo isset($search_consultant_id) ? '&search_consultant_id=' . urlencode($search_consultant_id) : ''; ?><?php echo isset($_GET['sort']) ? '&sort=' . htmlspecialchars($_GET['sort']) : ''; ?>">&laquo; Première</a>
                    <a href="?page=<?php echo $current_page - 1; ?><?php echo isset($search_consultant_id) ? '&search_consultant_id=' . urlencode($search_consultant_id) : ''; ?><?php echo isset($_GET['sort']) ? '&sort=' . htmlspecialchars($_GET['sort']) : ''; ?>">&lsaquo; Précédente</a>
                <?php else: ?>
                    <span class="disabled">&laquo; Première</span>
                    <span class="disabled">&lsaquo; Précédente</span>
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
                    <a href="?page=<?php echo $current_page + 1; ?><?php echo isset($search_consultant_id) ? '&search_consultant_id=' . urlencode($search_consultant_id) : ''; ?><?php echo isset($_GET['sort']) ? '&sort=' . htmlspecialchars($_GET['sort']) : ''; ?>">Suivante &rsaquo;</a>
                    <a href="?page=<?php echo $total_pages; ?><?php echo isset($search_consultant_id) ? '&search_consultant_id=' . urlencode($search_consultant_id) : ''; ?><?php echo isset($_GET['sort']) ? '&sort=' . htmlspecialchars($_GET['sort']) : ''; ?>">Dernière &raquo;</a>
                <?php else: ?>
                    <span class="disabled">Suivante &rsaquo;</span>
                    <span class="disabled">Dernière &raquo;</span>
                <?php endif; ?>
            <?php endif; ?>
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
                        backgroundColor: ['#ff4d4d', '#ff9933', '#ffff66', '#99cc00', '#33cc33']
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });

            // Consultant percentage chart
            new Chart(consultantCtx, {
                type: 'pie',
                data: {
                    labels: ['Consultants Uniques', 'Autres Feedbacks'],
                    datasets: [{
                        data: [<?php echo $unique_consultants_percentage; ?>, <?php echo 100 - $unique_consultants_percentage; ?>],
                        backgroundColor: ['#007bff', '#6c757d']
                    }]
                }
            });
        }

        // Form validation
        document.addEventListener('DOMContentLoaded', function() {
            var searchForm = document.querySelector('form[action="feedback.php"]');
            var searchInput = document.querySelector('input[name="search_consultant_id"]');
            
            if (searchForm && searchInput) {
                var errorDiv = document.createElement('div');
                errorDiv.style.color = 'red';
                errorDiv.style.marginTop = '5px';
                errorDiv.style.display = 'none';
                searchInput.parentNode.appendChild(errorDiv);
                
                searchForm.addEventListener('submit', function(e) {
                    if (searchInput.value.trim() === '') {
                        e.preventDefault();
                        searchInput.style.border = '1px solid red';
                        errorDiv.textContent = 'Veuillez entrer un ID de consultation';
                        errorDiv.style.display = 'block';
                    }
                });
                
                searchInput.addEventListener('input', function() {
                    if (searchInput.value.trim() !== '') {
                        searchInput.style.border = '';
                        errorDiv.style.display = 'none';
                    }
                });
            }
        });
    </script>
</body>
</html>