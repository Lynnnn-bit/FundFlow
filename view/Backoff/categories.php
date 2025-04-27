<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../controlle/CategorieController.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$controller = new CategorieController();

// Fetch all categories
$categories = $controller->getAllCategories();

// Handle PDF export for all categories
if (isset($_GET['export_pdf'])) {
    require_once __DIR__ . '/../../libs/fpdf/fpdf.php'; // Ensure FPDF is included

    $pdf = new FPDF();
    $pdf->AddPage();
    $pdf->SetFont('Arial', 'B', 16);

    // Title
    $pdf->Cell(0, 10, 'Liste des Catégories', 0, 1, 'C');
    $pdf->Ln(10);

    // Table Header
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(30, 10, 'ID', 1);
    $pdf->Cell(50, 10, 'Nom', 1);
    $pdf->Cell(80, 10, 'Description', 1);
    $pdf->Cell(30, 10, 'Projets', 1);
    $pdf->Ln();

    // Table Data
    $pdf->SetFont('Arial', '', 12);
    foreach ($categories as $categorie) {
        $projectCount = $controller->countProjectsInCategory($categorie['id_categorie']);
        $pdf->Cell(30, 10, $categorie['id_categorie'], 1);
        $pdf->Cell(50, 10, substr($categorie['nom_categorie'], 0, 20), 1);
        $pdf->Cell(80, 10, substr($categorie['description'] ?? 'N/A', 0, 40), 1);
        $pdf->Cell(30, 10, $projectCount, 1);
        $pdf->Ln();
    }

    // Output the PDF
    $pdf->Output('D', 'Liste_Categories.pdf');
    exit;
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

// Capture the search term from the request
$searchTerm = $_GET['search'] ?? null;

// Filter categories based on the search term
if ($searchTerm) {
    $categories = array_filter($categories, function ($category) use ($searchTerm) {
        return stripos($category['nom_categorie'], $searchTerm) !== false ||
               stripos($category['description'], $searchTerm) !== false;
    });
}

// Capture the sorting order for ID and project count
$idSortOrder = $_GET['id_sort'] ?? null;
$projectCountSortOrder = $_GET['project_count_sort'] ?? null;

// Sort categories based on the sorting order
if ($idSortOrder === 'asc') {
    usort($categories, fn($a, $b) => $a['id_categorie'] <=> $b['id_categorie']);
} elseif ($idSortOrder === 'desc') {
    usort($categories, fn($a, $b) => $b['id_categorie'] <=> $a['id_categorie']);
} elseif ($projectCountSortOrder === 'asc') {
    usort($categories, fn($a, $b) => $controller->countProjectsInCategory($a['id_categorie']) <=> $controller->countProjectsInCategory($b['id_categorie']));
} elseif ($projectCountSortOrder === 'desc') {
    usort($categories, fn($a, $b) => $controller->countProjectsInCategory($b['id_categorie']) <=> $controller->countProjectsInCategory($a['id_categorie']));
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
        // Retrieve form inputs
        $nom_categorie = trim($_POST['nom_categorie'] ?? '');
        $description = trim($_POST['description'] ?? '');

        // Validate inputs
        if (empty($nom_categorie)) {
            throw new Exception("Le nom de la catégorie est obligatoire!");
        }

        // Create a new category object
        $categorie = new Categorie(
            $editMode ? $categorieToEdit['id_categorie'] : null, // Use the ID if editing
            $nom_categorie,
            $description
        );

        // Check if it's an edit or a new category
        if ($editMode) {
            if ($controller->updateCategorie($categorie)) {
                $_SESSION['success'] = "Catégorie mise à jour avec succès!";
            }
        } else {
            if ($controller->createCategorie($categorie)) {
                $_SESSION['success'] = "Catégorie créée avec succès!";
            }
        }

        // Redirect to the categories page
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
    <title>FundFlow - Gestion des Catégories</title>
    <link rel="stylesheet" href="../Frontoff/css/stylecatego.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <header class="navbar">
        <div class="logo-container">
            <span class="brand-name">FundFlow</span>
        </div>
        <nav>
            <a href="#"><i class="fas fa-info-circle"></i> À propos</a>
            <a href="#"><i class="fas fa-envelope"></i> Contact</a>
            <a href="#" class="logout"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
        </nav>
    </header>

    <div class="main-container">
        <div class="header-section">
            <h1><i class="fas fa-tags"></i> Gestion des Catégories</h1>
            <div class="stats-container">
                <div class="stat-card">
                    <div class="stat-value"><?= count($categories) ?></div>
                    <div class="stat-label">Catégories totales</div>
                </div>
            </div>
        </div>

        <!-- Search Bar and Sorting Buttons -->
        <div class="search-container" style="text-align: center; margin-bottom: 1.5rem;">
            <form method="GET" class="search-box" style="display: inline-block;">
                <input type="text" name="search" placeholder="Rechercher une catégorie..." value="<?= htmlspecialchars($searchTerm) ?>">
                <button type="submit"><i class="fas fa-search"></i> Rechercher</button>
            </form>
            <div class="sort-buttons" style="display: inline-block;">
                <a href="?id_sort=asc" class="btn btn-primary <?= $idSortOrder === 'asc' ? 'active' : '' ?>">
                    <i class="fas fa-sort-numeric-up"></i> Trier par ID croissant
                </a>
                <a href="?id_sort=desc" class="btn btn-primary <?= $idSortOrder === 'desc' ? 'active' : '' ?>">
                    <i class="fas fa-sort-numeric-down"></i> Trier par ID décroissant
                </a>
                <a href="?project_count_sort=asc" class="btn btn-primary <?= $projectCountSortOrder === 'asc' ? 'active' : '' ?>">
                    <i class="fas fa-sort-amount-up"></i> Trier par nombre de projets croissant
                </a>
                <a href="?project_count_sort=desc" class="btn btn-primary <?= $projectCountSortOrder === 'desc' ? 'active' : '' ?>">
                    <i class="fas fa-sort-amount-down"></i> Trier par nombre de projets décroissant
                </a>
            </div>
            <!-- PDF Export Button -->
            <div style="margin-top: 1rem;">
                <a href="?export_pdf=true" class="btn btn-primary">
                    <i class="fas fa-file-pdf"></i> Exporter en PDF
                </a>
            </div>
        </div>

        <div class="form-container">
            <h2 class="text-center mb-4"><?= $editMode ? 'Modifier' : 'Nouvelle' ?> Catégorie</h2>
            
            <?php if (isset($success_message)): ?>
                <div class="alert alert-success"><?= htmlspecialchars($success_message) ?></div>
            <?php endif; ?>
            
            <?php if (isset($error_message)): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error_message) ?></div>
            <?php endif; ?>

            <form method="POST">
                <input type="hidden" name="is_edit" value="<?= $editMode ? 'true' : 'false' ?>">
                
                <div class="form-group">
                    <label class="form-label"><i class="fas fa-tag"></i> Nom de la catégorie *</label>
                    <input type="text" class="form-control" name="nom_categorie" 
                           value="<?= $editMode ? htmlspecialchars($categorieToEdit['nom_categorie']) : '' ?>" required>
                </div>

                <div class="form-group">
                    <label class="form-label"><i class="fas fa-align-left"></i> Description</label>
                    <textarea class="form-control" name="description" rows="3"><?= 
                        $editMode ? htmlspecialchars($categorieToEdit['description']) : '' 
                    ?></textarea>
                </div>

                <div class="form-actions">
                    <button type="submit" name="submit" class="btn btn-primary">
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

        <div class="table-container">
            <h3 class="text-center mb-3"><i class="fas fa-list-ol"></i> Liste des Catégories</h3>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Nom</th>
                            <th>Description</th>
                            <th>Projets</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($categories as $categorie): ?>
                            <tr>
                                <td><?= $categorie['id_categorie'] ?></td>
                                <td><?= htmlspecialchars($categorie['nom_categorie']) ?></td>
                                <td><?= htmlspecialchars($categorie['description'] ?? 'N/A') ?></td>
                                <td>
                                    <span class="badge-count">
                                        <?= $controller->countProjectsInCategory($categorie['id_categorie']) ?>
                                    </span>
                                </td>
                                <td class="action-buttons">
                                    <a href="categories.php?edit_id=<?= $categorie['id_categorie'] ?>" 
                                       class="btn btn-sm btn-warning"
                                       title="Modifier">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="categories.php?delete_id=<?= $categorie['id_categorie'] ?>" 
                                       class="btn btn-sm btn-danger" 
                                       onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette catégorie?')"
                                       title="Supprimer">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../Frontoff/js/jscatego.js"></script>
</body>
</html>