<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../controlle/CategorieController.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$controller = new CategorieController();

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

// Fetch all categories
$categories = $controller->getAllCategories();

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
    <title>FundFlow - Gestion des Catégories</title>
    <link rel="stylesheet" href="../Frontoff/css/stylecatego.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!--<style>
        .badge-count {
            background-color: #6c757d;
            color: white;
            border-radius: 10px;
            padding: 2px 6px;
            font-size: 12px;
        }
        .form-container {
            max-width: 800px;
            margin: 0 auto;
        }
        .table-container {
            margin-top: 30px;
        }
    </style>-->
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

        <div class="form-container">
            <h2 class="text-center mb-4"><?= $editMode ? 'Modifier' : 'Nouvelle' ?> Catégorie</h2>
            
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
                        <label class="form-label"><i class="fas fa-id-card"></i> ID Catégorie *</label>
                        <input type="number" class="form-control" name="id_categorie" 
                               min="1" required>
                               
                        <!--<small class="form-text text-muted">Entrez un nombre unique positif</small>-->
                    </div>
                    
                <?php endif; ?>
                
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