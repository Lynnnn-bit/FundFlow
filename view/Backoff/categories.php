<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../controlle/CategorieController.php';

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

// Fetch modification history
$modificationHistory = $controller->getAllModificationHistory();

// Filter modification history by action type
$actionFilter = $_GET['action_filter'] ?? null;
if ($actionFilter) {
    $modificationHistory = array_filter($modificationHistory, function ($entry) use ($actionFilter) {
        return stripos($entry['action'], $actionFilter) !== false;
    });
}

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

// Fetch historique des opportunités
function getHistoriqueOpportunites() {
    try {
        $sql = "SELECT id_opp, Titre_opp, Date_de_Publication FROM opportunite ORDER BY Date_de_Publication DESC";
        $db = Config::getConnexion();
        return $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Erreur lors de la récupération de l'historique des opportunités: " . $e->getMessage());
        return []; // Return an empty array if the table does not exist or another error occurs
    }
}

$historiqueOpportunites = getHistoriqueOpportunites();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FundFlow - Gestion des Catégories</title>
    <link rel="stylesheet" href="../Frontoff/css/stylecatego.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .table-container {
            margin: 2rem auto;
            max-width: 1200px;
        }
        .table-dark {
            background-color: #1e293b;
            color: #fff;
        }
        .table-dark th {
            background-color: #0f172a;
            color: #fff;
        }
        .table-dark td {
            background-color: #1e293b;
        }
        .badge {
            padding: 0.5rem 0.75rem;
            font-size: 0.9rem;
            border-radius: 0.5rem;
        }
        .badge.bg-warning {
            background-color: #f59e0b;
            color: #fff;
        }
        .badge.bg-success {
            background-color: #10b981;
            color: #fff;
        }
        .badge.bg-danger {
            background-color: #ef4444;
            color: #fff;
        }
        .badge.bg-info {
            background-color: #3b82f6;
            color: #fff;
        }
        .badge.bg-secondary {
            background-color: #6b7280;
            color: #fff;
        }
        .action-buttons a {
            margin-right: 0.5rem;
        }
    </style>
</head>
<body>
    <header class="navbar">
        <div class="logo-container">
            <span class="brand-name">FundFlow</span>
        </div>
        <nav>
            <a href="#" id="about-link"><i class="fas fa-info-circle"></i> À propos</a>
            <a href="#" id="contact-link"><i class="fas fa-envelope"></i> Contact</a>
            <a href="#" class="logout" id="logout-link"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
        </nav>
    </header>

    <div class="main-container">
        <div class="header-section">
            <h1 id="page-title"><i class="fas fa-tags"></i> Gestion des Catégories</h1>
            <div class="button-container" style="display: flex; gap: 1rem; justify-content: center; margin-bottom: 1.5rem;">
                <a href="export_categories.php" class="btn btn-primary" id="export-btn">
                    <i class="fas fa-file-pdf"></i> Exporter en PDF
                </a>
                <button class="btn btn-info" 
                        data-bs-toggle="modal" 
                        data-bs-target="#historyModal" 
                        onclick="loadAllHistory()" id="history-btn">
                    <i class="fas fa-history"></i> Voir l'historique des modifications
                </button>
                <button class="btn btn-info" 
                        data-bs-toggle="modal" 
                        data-bs-target="#statsModal" 
                        id="stats-btn">
                    <i class="fas fa-chart-pie"></i> Statistique
                </button>
                <button class="btn btn-secondary" id="translate-btn">
                    <i class="fas fa-language"></i> Traduire en Anglais
                </button>
            </div>
        </div>

        <div class="form-container">
            <h2 class="text-center mb-4" id="form-title"><?= $editMode ? 'Modifier' : 'Nouvelle' ?> Catégorie</h2>
            
            <?php if (isset($success_message)): ?>
                <div class="alert alert-success"><?= htmlspecialchars($success_message) ?></div>
            <?php endif; ?>
            
            <?php if (isset($error_message)): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error_message) ?></div>
            <?php endif; ?>

            <form method="POST">
                <?php if ($editMode): ?>
                    <input type="hidden" name="id_categorie" value="<?= $categorieToEdit['id_categorie'] ?>">
                    <input type="hidden" name="is_edit" value="true">
                <?php else: ?>
                    <div class="form-group">
                        <div id="idCategorieError" class="error-message" style="color: red; display: none;"></div>
                        <label class="form-label" id="id-label"><i class="fas fa-id-card"></i> ID Catégorie *</label>
                        <input type="number" class="form-control" name="id_categorie" 
                               min="1" required>
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
                        <?= $editMode ? 'Mettre à jour' : 'Créer' ?>
                    </button>
                    <?php if ($editMode): ?>
                        <a href="categories.php" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Annuler
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <div class="search-container" style="margin: 2rem 0; display: flex; justify-content: center; gap: 1rem;">
            <form method="GET" action="categories.php" class="d-flex">
                <input type="text" name="search" class="form-control w-50" placeholder="Rechercher par ID, nom ou description..." id="search-placeholder" value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
                <button type="submit" class="btn btn-primary ms-2" id="search-btn"><i class="fas fa-search"></i> Rechercher</button>
            </form>
            <button class="btn btn-info" data-bs-toggle="modal" data-bs-target="#statsModal">
                <i class="fas fa-chart-pie"></i> Statistique
            </button>
        </div>

        <div class="table-container">
            <h3 class="text-center mb-3" id="table-title"><i class="fas fa-list-ol"></i> Liste des Catégories</h3>
            <div class="table-responsive">
                <table class="table table-dark table-striped">
                    <thead>
                        <tr>
                            <th id="id-column">ID</th>
                            <th id="name-column">Nom</th>
                            <th id="description-column">Description</th>
                            <th id="actions-column">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($categories as $category): ?>
                            <tr>
                                <td><?= htmlspecialchars($category['id_categorie']) ?></td>
                                <td><?= htmlspecialchars($category['nom_categorie']) ?></td>
                                <td><?= htmlspecialchars($category['description']) ?></td>
                                <td class="action-buttons">
                                    <a href="categories.php?edit_id=<?= $category['id_categorie'] ?>" class="btn btn-sm btn-warning" title="Modifier" id="edit-btn">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="categories.php?delete_id=<?= $category['id_categorie'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette catégorie ?')" title="Supprimer" id="delete-btn">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Historique des Opportunités Section -->
        <div class="table-container">
            <h3 class="text-center mb-3"><i class="fas fa-history"></i> Historique des Opportunités</h3>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Titre</th>
                            <th>Date de Publication</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($historiqueOpportunites)): ?>
                            <?php foreach ($historiqueOpportunites as $opportunite): ?>
                                <tr>
                                    <td><?= htmlspecialchars($opportunite['id_opp']) ?></td>
                                    <td><?= htmlspecialchars($opportunite['Titre_opp']) ?></td>
                                    <td><?= htmlspecialchars($opportunite['Date_de_Publication']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="3" class="text-center">Aucune opportunité disponible.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
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
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../Frontoff/js/jscatego.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const translations = {
                fr: {
                    "À propos": "About",
                    "Contact": "Contact",
                    "Déconnexion": "Logout",
                    "Gestion des Catégories": "Category Management",
                    "Nouvelle Catégorie": "New Category",
                    "ID Catégorie *": "Category ID *",
                    "Nom de la catégorie *": "Category Name *",
                    "Description": "Description",
                    "Rechercher par ID, nom ou description...": "Search by ID, name, or description...",
                    "Liste des Catégories": "Category List",
                    "Rechercher": "Search",
                    "Actions": "Actions",
                    "Modifier": "Edit",
                    "Supprimer": "Delete",
                    "Créer": "Create",
                    "Mettre à jour": "Update"
                },
                en: {}
            };

            // Reverse translations for English to French
            for (const [key, value] of Object.entries(translations.fr)) {
                translations.en[value] = key;
            }

            let currentLanguage = 'fr';

            document.getElementById('translate-btn').addEventListener('click', function () {
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
                this.textContent = currentLanguage === 'fr' ? "Traduire en Anglais" : "Traduire en Français";
            });

            const ctx = document.getElementById('categoryStatsChart').getContext('2d');
            const data = {
                labels: <?= json_encode(array_column($categoryStats, 'nom_categorie')) ?>,
                datasets: [{
                    label: 'Nombre de projets',
                    data: <?= json_encode(array_column($categoryStats, 'project_count')) ?>,
                    backgroundColor: [
                        '#00c9a7', '#00ffc8', '#1e293b', '#f59e0b', '#10b981', '#3b82f6'
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
                                    return `${tooltipItem.label}: ${tooltipItem.raw} projets`;
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