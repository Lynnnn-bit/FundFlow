<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../controlle/ProjectController.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$controller = new ProjectController();

// Handle delete action
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['delete_id'])) {
    try {
        $deleteId = $_GET['delete_id'];
        if ($controller->deleteProject($deleteId)) {
            $_SESSION['success'] = "Projet supprimé avec succès!";
            header("Location: projets.php");
            exit();
        }
    } catch (Exception $e) {
        $error_message = $e->getMessage();
    }
}

// Handle edit form display
$editMode = false;
$projectToEdit = null;
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['edit_id'])) {
    $editMode = true;
    $editId = $_GET['edit_id'];
    $projectToEdit = $controller->getProjectById($editId);
}

// Fetch all projects and categories
$projects = $controller->getAllProjects();
$categories = $controller->getCategories();

// Process form submission
// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
    try {
        $id_projet = $_POST['id_projet'] ?? null;
        $titre = trim($_POST['titre']);
        $description = trim($_POST['description']);
        $montant_cible = $_POST['montant_cible'];
        $duree = $_POST['duree'];
        $id_categorie = $_POST['id_categorie'] ?: null;
        $status = $_POST['status'] ?? 'en_attente';
        $user_id = 1; // Set the user ID to 1
        
        // Validate title
        if (empty($titre)) {
            throw new Exception("Le titre est obligatoire");
        }
        if (strlen($titre) > 100) {
            throw new Exception("Le titre ne doit pas dépasser 100 caractères");
        }
        
        // Validate description
        if (empty($description)) {
            throw new Exception("La description est obligatoire");
        }
        if (strlen($description) > 2000) {
            throw new Exception("La description ne doit pas dépasser 2000 caractères");
        }
        
        $project = new Project(
            $user_id, // Pass the user ID here
            $titre, 
            $description, 
            $montant_cible, 
            $duree, 
            $id_categorie, 
            $status, 
            $id_projet
        );                
        if (isset($_POST['is_edit']) && $_POST['is_edit'] === 'true') {
            if ($controller->updateProject($project)) {
                $_SESSION['success'] = "Projet mis à jour avec succès! (ID: $id_projet)";
            }
        } else {
            if ($controller->createProject($project)) {
                $_SESSION['success'] = "Projet créé avec succès!";
            }
        }
        
        header("Location: projets.php");
        exit();
    } catch (Exception $e) {
        $error_message = $e->getMessage();
    }
}

// Display success message from session
if (isset($_SESSION['success'])) {
    $success_message = $_SESSION['success'];
    unset($_SESSION['success']);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FundFlow - Gestion des Projets</title>
    <link rel="stylesheet" href="css/styleprojet.css">
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
            <h1><i class="fas fa-project-diagram"></i> Gestion des Projets</h1>
            <div class="stats-container">
                <div class="stat-card">
                    <div class="stat-value"><?= count($projects) ?></div>
                    <div class="stat-label">Projets total</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value"><?= count(array_filter($projects, fn($p) => $p['status'] === 'en_attente')) ?></div>
                    <div class="stat-label">En attente</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value">€<?= number_format(array_sum(array_column($projects, 'montant_cible')) / 1000000, 1) ?>M</div>
                    <div class="stat-label">Total demandé</div>
                </div>
            </div>
        </div>

        <div class="project-form-container">
            <h2 class="text-center mb-4"><?= $editMode ? 'Modifier' : 'Nouveau' ?> Projet</h2>
            
            <?php if (isset($success_message)): ?>
                <div class="alert alert-success"><?= htmlspecialchars($success_message) ?></div>
            <?php endif; ?>
            
            <?php if (isset($error_message)): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error_message) ?></div>
            <?php endif; ?>

            <form method="POST">
                <input type="hidden" name="id_projet" value="<?= $editMode ? $projectToEdit['id_projet'] : '' ?>">
                <input type="hidden" name="is_edit" value="<?= $editMode ? 'true' : 'false' ?>">
                
                <div class="form-group">
                    <label class="form-label"><i class="fas fa-heading"></i> Titre *</label>
                    <input type="text" class="form-control" name="titre" 
                           value="<?= $editMode ? htmlspecialchars($projectToEdit['titre']) : '' ?>" required>
                </div>

                <div class="form-group">
                    <label class="form-label"><i class="fas fa-align-left"></i> Description *</label>
                    <textarea class="form-control" name="description" rows="4" required><?= 
                        $editMode ? htmlspecialchars($projectToEdit['description']) : '' 
                    ?></textarea>
                </div>

                <div class="form-group">
                    <label class="form-label"><i class="fas fa-euro-sign"></i> Montant Cible (€) *</label>
                    <input type="number" class="form-control" name="montant_cible" min="10000" max="10000000" 
                           value="<?= $editMode ? $projectToEdit['montant_cible'] : '500000' ?>" required>
                </div>

                <div class="form-group">
                    <label class="form-label"><i class="fas fa-calendar-alt"></i> Durée (mois) *</label>
                    <input type="number" class="form-control" name="duree" min="6" max="60" 
                           value="<?= $editMode ? $projectToEdit['duree'] : '24' ?>" required>
                </div>

                <div class="form-group">
                    <label class="form-label"><i class="fas fa-tag"></i> Catégorie</label>
                    <select class="form-select" name="id_categorie">
                        <option value="">Sélectionnez une catégorie</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?= $category['id_categorie'] ?>" 
                                <?= ($editMode && $projectToEdit['id_categorie'] == $category['id_categorie']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($category['nom_categorie']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <?php if ($editMode): ?>
                    <div class="form-group">
                        <label class="form-label"><i class="fas fa-info-circle"></i> Statut *</label>
                        <select class="form-select" name="status" required>
                            <option value="en_attente" <?= $projectToEdit['status'] == 'en_attente' ? 'selected' : '' ?>>En attente</option>
                            <option value="actif" <?= $projectToEdit['status'] == 'actif' ? 'selected' : '' ?>>Actif</option>
                            <option value="inactif" <?= $projectToEdit['status'] == 'inactif' ? 'selected' : '' ?>>Inactif</option>
                            <option value="termine" <?= $projectToEdit['status'] == 'termine' ? 'selected' : '' ?>>Terminé</option>
                            <option value="rejete" <?= $projectToEdit['status'] == 'rejete' ? 'selected' : '' ?>>Rejeté</option>
                        </select>
                    </div>
                <?php endif; ?>

                <div class="form-actions">
                    <button type="submit" name="submit" class="btn btn-primary">
                        <i class="fas fa-<?= $editMode ? 'save' : 'plus' ?>"></i>
                        <?= $editMode ? 'Mettre à jour' : 'Créer' ?>
                    </button>
                    <?php if ($editMode): ?>
                        <a href="projets.php" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Annuler
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <div class="table-container">
            <h3 class="text-center mb-3"><i class="fas fa-list-ol"></i> Liste des Projets</h3>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Titre</th>
                            <th>Description</th>
                            <th>Montant</th>
                            <th>Durée</th>
                            <th>Catégorie</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($projects as $project): ?>
                            <tr>
                                <td><?= $project['id_projet'] ?></td>
                                <td><?= htmlspecialchars($project['titre']) ?></td>
                                <td><?= htmlspecialchars(substr($project['description'], 0, 50)) ?>...</td>
                                <td><?= number_format($project['montant_cible'], 2) ?> €</td>
                                <td><?= $project['duree'] ?> mois</td>
                                <td><?= htmlspecialchars($project['nom_categorie'] ?? 'Non spécifiée') ?></td>
                                <td>
                                    <span class="badge <?= 
                                        $project['status'] == 'actif' ? 'bg-success' : 
                                        ($project['status'] == 'termine' ? 'bg-info' : 
                                        ($project['status'] == 'rejete' ? 'bg-danger' : 
                                        ($project['status'] == 'inactif' ? 'bg-secondary' : 'bg-warning')))
                                    ?>">
                                        <?= ucfirst($project['status']) ?>
                                    </span>
                                </td>
                                <td class="action-buttons">
                                    <a href="projets.php?edit_id=<?= $project['id_projet'] ?>" 
                                       class="btn btn-sm btn-warning"
                                       title="Modifier">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="projets.php?delete_id=<?= $project['id_projet'] ?>" 
                                       class="btn btn-sm btn-danger" 
                                       onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce projet?')"
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
    <script src="js/jsprojet.js"></script>
</body>
</html>