<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../control/CategorieController.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$controller = new CategorieController();

$searchTerm = $_GET['search'] ?? null;

if ($searchTerm) {
    $categories = $controller->searchCategories($searchTerm);
} else {
    $categories = $controller->getAllCategories();
}

// Fetch statistics for the pie chart
$categoryStats = $controller->getCategoryUsageStats();

// Handle delete action
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['delete_id'])) {
    try {
        $deleteId = $_GET['delete_id'];
        if ($controller->deleteCategorie($deleteId)) {
            $_SESSION['success'] = "Catégorie supprimée avec succès!";
        }
    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
    }
    header("Location: categories.php");
    exit();
}

// Handle edit form display
$editMode = false;
$categorieToEdit = null;
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['edit_id'])) {
    $editMode = true;
    $editId = $_GET['edit_id'];
    $categorieToEdit = $controller->getCategorieById($editId);
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
    try {
        $id_categorie = (int)$_POST['id_categorie'];
        $nom_categorie = trim($_POST['nom_categorie']);
        $description = trim($_POST['description']);
        
        // Validate inputs
        if ($id_categorie <= 0) {
            throw new Exception("L'ID de catégorie doit être un nombre positif!");
        }
        if (empty($nom_categorie)) {
            throw new Exception("Le nom de la catégorie est obligatoire!");
        }
        
        $categorie = new Categorie($id_categorie, $nom_categorie, $description);
        
        if (isset($_POST['is_edit']) && $_POST['is_edit'] === 'true') {
            if ($controller->updateCategorie($categorie)) {
                $_SESSION['success'] = "Catégorie mise à jour avec succès!";
            }
        } else {
            if ($controller->createCategorie($categorie)) {
                $_SESSION['success'] = "Catégorie créée avec succès! (ID: $id_categorie)";
            }
        }
        
        header("Location: categories.php");
        exit();
    } catch (Exception $e) {
        $error_message = $e->getMessage();
    }
}

// Display messages from session
if (isset($_SESSION['success'])) {
    $success_message = $_SESSION['success'];
    unset($_SESSION['success']);
}
if (isset($_SESSION['error'])) {
    $error_message = $_SESSION['error'];
    unset($_SESSION['error']);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FundFlow - Catégories</title>
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
        
        .translate-btn {
            margin-left: 1rem;
        }
        
        .action-buttons {
            display: flex;
            gap: 0.5rem;
        }
        
        .form-actions {
            margin-top: 1.5rem;
            display: flex;
            gap: 0.5rem;
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
                    <li class="active"><a href="categories.php"><i class="fas fa-project-diagram"></i> Catégories</a></li>
                    <li>
                        <a href="#" class="toggle-submenu"><i class="fas fa-hand-holding-usd"></i> Financements</a>
                        <ul class="submenu">
                            <li><a href="statistics.php"><i class="fas fa-chart-pie"></i> Statistiques</a></li>
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
                <button class="menu-toggle">
                    <i class="fas fa-bars"></i>
                </button>
            </header>

            <div class="main-container">
                <div class="page-header">
                    <h1><i class="fas fa-tags"></i> <span id="page-title">Gestion des Catégories</span></h1>
                    <div class="header-actions">
                        <form method="GET" class="search-form">
                            <div class="input-group">
                                <input type="text" name="search" class="form-control" placeholder="Rechercher..." id="search-placeholder" value="<?= htmlspecialchars($searchTerm ?? '') ?>">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search"></i>
                                </button>
                                <?php if ($searchTerm): ?>
                                    <a href="categories.php" class="btn btn-danger">
                                        <i class="fas fa-times"></i>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </form>
                        <button id="translate-btn" class="btn btn-secondary translate-btn">
                            <i class="fas fa-language"></i> <span id="translate-text">Traduire</span>
                        </button>
                    </div>
                </div>

                <?php if (isset($success_message)): ?>
                    <div class="alert alert-success"><?= $success_message ?></div>
                <?php endif; ?>

                <?php if (isset($error_message)): ?>
                    <div class="alert alert-danger"><?= $error_message ?></div>
                <?php endif; ?>

                <section class="card">
                    <div class="card-header">
                        <h3><i class="fas fa-<?= $editMode ? 'edit' : 'plus' ?>"></i> <span id="form-title"><?= $editMode ? 'Modifier' : 'Nouvelle' ?> Catégorie</span></h3>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <?php if ($editMode): ?>
                                <input type="hidden" name="id_categorie" value="<?= $categorieToEdit['id_categorie'] ?>">
                                <input type="hidden" name="is_edit" value="true">
                            <?php else: ?>
                                <div class="form-group">
                                    <label class="form-label" id="id-label"><i class="fas fa-id-card"></i> ID Catégorie *</label>
                                    <input type="number" class="form-control" name="id_categorie" min="1" required>
                                </div>
                            <?php endif; ?>
                            
                            <div class="form-group">
                                <label class="form-label" id="name-label"><i class="fas fa-tag"></i> Nom de la catégorie *</label>
                                <input type="text" class="form-control" name="nom_categorie" 
                                       value="<?= $editMode ? htmlspecialchars($categorieToEdit['nom_categorie']) : '' ?>" required>
                            </div>

                            <div class="form-group">
                                <label class="form-label" id="description-label"><i class="fas fa-align-left"></i> Description</label>
                                <textarea class="form-control" name="description" rows="3"><?= 
                                    $editMode ? htmlspecialchars($categorieToEdit['description']) : '' 
                                ?></textarea>
                            </div>

                            <div class="form-actions">
                                <button type="submit" name="submit" class="btn btn-primary" id="submit-btn">
                                    <i class="fas fa-<?= $editMode ? 'save' : 'plus' ?>"></i>
                                    <span id="submit-text"><?= $editMode ? 'Mettre à jour' : 'Créer' ?></span>
                                </button>
                                <?php if ($editMode): ?>
                                    <a href="categories.php" class="btn btn-secondary">
                                        <i class="fas fa-times"></i> <span id="cancel-text">Annuler</span>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </form>
                    </div>
                </section>

                <section class="card">
                    <div class="card-header">
                        <h3><i class="fas fa-list"></i> <span id="table-title">Liste des Catégories</span></h3>
                        <div class="card-header-actions">
                            <button class="btn btn-info" data-bs-toggle="modal" data-bs-target="#statsModal">
                                <i class="fas fa-chart-pie"></i> <span id="stats-text">Statistiques</span>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th id="id-header">ID</th>
                                        <th id="name-header">Nom</th>
                                        <th id="desc-header">Description</th>
                                        <th id="actions-header">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($categories)): ?>
                                        <?php foreach ($categories as $category): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($category['id_categorie']) ?></td>
                                                <td><?= htmlspecialchars($category['nom_categorie']) ?></td>
                                                <td><?= htmlspecialchars($category['description']) ?></td>
                                                <td>
                                                    <div class="action-buttons">
                                                        <a href="categories.php?edit_id=<?= $category['id_categorie'] ?>" 
                                                           class="btn btn-warning btn-sm" 
                                                           title="Modifier" id="edit-btn">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <a href="categories.php?delete_id=<?= $category['id_categorie'] ?>"
                                                           class="btn btn-danger btn-sm"
                                                           onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette catégorie?')"
                                                           title="Supprimer" id="delete-btn">
                                                            <i class="fas fa-trash"></i>
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="4" class="text-center">Aucune catégorie trouvée</td>
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

    <!-- Modal for Statistics -->
    <div class="modal fade" id="statsModal" tabindex="-1" aria-labelledby="statsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="statsModalLabel">Statistiques des Catégories</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <h6 class="text-center mb-4">Répartition des projets par catégorie</h6>
                    <canvas id="categoryStatsChart"></canvas>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" id="close-modal-btn">Fermer</button>
                </div>
            </div>
        </div>
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
            const ctx = document.getElementById('categoryStatsChart').getContext('2d');
            const data = {
                labels: <?= json_encode(array_column($categoryStats, 'nom_categorie')) ?>,
                datasets: [{
                    label: 'Nombre de projets',
                    data: <?= json_encode(array_column($categoryStats, 'project_count')) ?>,
                    backgroundColor: [
                        '#4361ee', '#3a0ca3', '#f72585', '#4cc9f0', '#4895ef', '#b5179e'
                    ],
                    hoverOffset: 4
                }]
            };
            new Chart(ctx, {
                type: 'pie',
                data: data,
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'top',
                        },
                        tooltip: {
                            callbacks: {
                                label: function (tooltipItem) {
                                    const label = tooltipItem.label || '';
                                    const value = tooltipItem.raw || 0;
                                    const total = tooltipItem.dataset.data.reduce((a, b) => a + b, 0);
                                    const percentage = Math.round((value / total) * 100);
                                    return `${label}: ${value} (${percentage}%)`;
                                }
                            }
                        }
                    }
                }
            });
        });

        // Translation functionality
        const translations = {
            fr: {
                "Gestion des Catégories": "Category Management",
                "Rechercher...": "Search...",
                "Traduire": "Translate",
                "Modifier Catégorie": "Edit Category",
                "Nouvelle Catégorie": "New Category",
                "ID Catégorie *": "Category ID *",
                "Nom de la catégorie *": "Category Name *",
                "Description": "Description",
                "Mettre à jour": "Update",
                "Créer": "Create",
                "Annuler": "Cancel",
                "Liste des Catégories": "Categories List",
                "Statistiques": "Statistics",
                "ID": "ID",
                "Nom": "Name",
                "Description": "Description",
                "Actions": "Actions",
                "Modifier": "Edit",
                "Supprimer": "Delete",
                "Statistiques des Catégories": "Categories Statistics",
                "Répartition des projets par catégorie": "Projects distribution by category",
                "Fermer": "Close",
                "Aucune catégorie trouvée": "No categories found"
            },
            en: {}
        };

        // Reverse translations for English to French
        for (const [key, value] of Object.entries(translations.fr)) {
            translations.en[value] = key;
        }

        let currentLanguage = 'fr';

        document.getElementById('translate-btn').addEventListener('click', function() {
            const elementsToTranslate = document.querySelectorAll('[id], [placeholder], [title]');
            const translateText = (text) => translations[currentLanguage][text.trim()] || text;

            elementsToTranslate.forEach(element => {
                if (element.placeholder) {
                    element.placeholder = translateText(element.placeholder);
                } else if (element.title) {
                    element.title = translateText(element.title);
                } else if (element.id && translations[currentLanguage][element.textContent.trim()]) {
                    element.textContent = translateText(element.textContent.trim());
                }
            });

            currentLanguage = currentLanguage === 'fr' ? 'en' : 'fr';
            document.getElementById('translate-text').textContent = currentLanguage === 'fr' ? "Traduire" : "Translate";
        });
    </script>
</body>
</html>