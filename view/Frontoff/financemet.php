<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../controlle/financecontroller.php';
require_once __DIR__ . '/../../lib/fpdf.php'; // Include FPDF library

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$controller = new FinanceController();

// Handle delete action
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['delete_id'])) {
    try {
        $deleteId = $_GET['delete_id'];
        if ($controller->deleteFinanceRequest($deleteId)) {
            $_SESSION['success'] = "Demande supprimée avec succès!";
            header("Location: financemet.php");
            exit();
        }
    } catch (Exception $e) {
        $error_message = $e->getMessage();
    }
}

// Handle edit form display
$editMode = false;
$financeToEdit = null;
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['edit_id'])) {
    $editMode = true;
    $editId = $_GET['edit_id'];
    $financeToEdit = $controller->getFinanceRequestById($editId);
}

// Fetch all projects and existing demands
$projects = $controller->getProjects(1);
$existingDemands = $controller->getAllFinanceRequests();

// Capture the sorting order for ID and project count
$idSortOrder = $_GET['id_sort'] ?? null;
$projectCountSortOrder = $_GET['project_count_sort'] ?? null;

// Sort the financing requests based on the sorting order
if ($idSortOrder === 'asc') {
    usort($existingDemands, fn($a, $b) => $a['id_demande'] <=> $b['id_demande']);
} elseif ($idSortOrder === 'desc') {
    usort($existingDemands, fn($a, $b) => $b['id_demande'] <=> $a['id_demande']);
} elseif ($projectCountSortOrder === 'asc') {
    usort($existingDemands, fn($a, $b) => $a['nb_reponses'] <=> $b['nb_reponses']);
} elseif ($projectCountSortOrder === 'desc') {
    usort($existingDemands, fn($a, $b) => $b['nb_reponses'] <=> $a['nb_reponses']);
}

// Generate a new unique ID
$newId = 1;
if (!empty($existingDemands)) {
    $maxId = max(array_column($existingDemands, 'id_demande'));
    $newId = $maxId + 1;
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
    try {
        $id_demande = $_POST['id_demande'];
        $id_project = $_POST['id_project'];
        $montant_demandee = $_POST['montant'];
        $duree = $_POST['duree'];
        $status = $editMode ? $_POST['status'] : 'en_attente'; // Get status from form if in edit mode
        
        $user_id = $controller->getProjectOwner($id_project);
        
        $finance = new Finance($id_project, $user_id, $duree, $montant_demandee, $status, $id_demande);
        $project = $controller->getProjectById($id_project);
        if (!$project) {
            throw new Exception("Le projet sélectionné n'existe pas");
        }
        if (isset($_POST['is_edit']) && $_POST['is_edit'] === 'true') {
            if ($controller->updateFinanceRequest($finance)) {
                $_SESSION['success'] = "Demande mise à jour avec succès! (ID: $id_demande)";
            }
        } else {
            if ($controller->createFinanceRequest($finance)) {
                $_SESSION['success'] = "Demande enregistrée avec succès! (ID: $id_demande)";
            }
        }
        
        header("Location: financemet.php");
        exit();
    } catch (Exception $e) {
        $error_message = $e->getMessage();
    }
}

// Handle PDF export for all financing requests
if (isset($_GET['export_pdf'])) {
    $pdf = new FPDF();
    $pdf->AddPage();
    $pdf->SetFont('Arial', 'B', 16);

    // Title
    $pdf->Cell(0, 10, 'Liste des Demandes de Financement', 0, 1, 'C');
    $pdf->Ln(10);

    // Table Header
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(30, 10, 'ID Demande', 1);
    $pdf->Cell(50, 10, 'Projet', 1);
    $pdf->Cell(30, 10, 'Montant', 1);
    $pdf->Cell(20, 10, 'Durée', 1);
    $pdf->Cell(30, 10, 'Statut', 1);
    $pdf->Cell(30, 10, 'Réponses', 1);
    $pdf->Ln();

    // Table Data
    $pdf->SetFont('Arial', '', 12);
    foreach ($existingDemands as $demand) {
        $project = $controller->getProjectById($demand['id_project']);
        $projectTitle = $project ? $project['titre'] : 'Projet inconnu';

        $pdf->Cell(30, 10, $demand['id_demande'], 1);
        $pdf->Cell(50, 10, substr($projectTitle, 0, 20), 1);
        $pdf->Cell(30, 10, number_format($demand['montant_demandee'], 2) . ' €', 1);
        $pdf->Cell(20, 10, $demand['duree'] . ' mois', 1);
        $pdf->Cell(30, 10, ucfirst($demand['status']), 1);
        $pdf->Cell(30, 10, $demand['nb_reponses'], 1);
        $pdf->Ln();
    }

    // Output the PDF
    $pdf->Output('D', 'Demandes_Financement.pdf');
    exit;
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
    <title>FundFlow - Gestion des Financements</title>
    <link rel="stylesheet" href="css/stylefinan.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

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
            <h1><i class="fas fa-hand-holding-usd"></i> Demandes de Financement</h1>
            <div class="stats-container">
                <div class="stat-card">
                    <div class="stat-value"><?= count($existingDemands) ?></div>
                    <div class="stat-label">Demandes totales</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value"><?= count(array_filter($existingDemands, fn($d) => $d['status'] === 'en_attente')) ?></div>
                    <div class="stat-label">En attente</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value">€<?= number_format(array_sum(array_column($existingDemands, 'montant_demandee')) / 1000000, 1) ?>M</div>
                    <div class="stat-label">Total demandé</div>
                </div>
            </div>
        </div>

        <!-- Search Bar and Export Button -->
        <div class="search-container" style="text-align: center; margin-bottom: 1.5rem;">
            <form method="GET" class="search-box" style="display: inline-block;">
                <input type="text" name="search" placeholder="Rechercher une demande..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
                <button type="submit"><i class="fas fa-search"></i> Rechercher</button>
            </form>
            <a href="?export_pdf=true" class="btn btn-primary" style="margin-left: 10px;">
                <i class="fas fa-file-pdf"></i> Exporter en PDF
            </a>
        </div>

        <div class="finance-form-container">
            <h2 class="text-center mb-4"><?= $editMode ? 'Modifier' : 'Nouvelle' ?> Demande de Financement</h2>
            
            <?php if (isset($success_message)): ?>
                <div class="alert alert-success"><?= htmlspecialchars($success_message) ?></div>
            <?php endif; ?>
            
            <?php if (isset($error_message)): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error_message) ?></div>
            <?php endif; ?>

            <form method="POST">
                <input type="hidden" name="id_demande" value="<?= $editMode ? $financeToEdit['id_demande'] : $newId ?>">
                <input type="hidden" name="is_edit" value="<?= $editMode ? 'true' : 'false' ?>">
                
                <div class="form-group">
                    <label class="form-label"><i class="fas fa-project-diagram"></i> Projet *</label>
                    <select class="form-select" name="id_project" required>
                        <option value="">Sélectionnez un projet</option>
                        <?php foreach ($projects as $project): ?>
                            <option value="<?= $project['id_projet'] ?>" 
                                <?= ($editMode && $financeToEdit['id_project'] == $project['id_projet']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($project['titre']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label"><i class="fas fa-euro-sign"></i> Montant (€) *</label>
                    <input type="number" class="form-control" name="montant" min="10000" max="10000000" 
                           value="<?= $editMode ? $financeToEdit['montant_demandee'] : '500000' ?>" required>
                </div>

                <div class="form-group">
                    <label class="form-label"><i class="fas fa-calendar-alt"></i> Durée (mois) *</label>
                    <input type="number" class="form-control" name="duree" min="6" max="60" 
                           value="<?= $editMode ? $financeToEdit['duree'] : '24' ?>" required>
                </div>

                <div class="form-actions">
                    <button type="submit" name="submit" class="btn btn-primary">
                        <i class="fas fa-<?= $editMode ? 'save' : 'paper-plane' ?>"></i>
                        <?= $editMode ? 'Mettre à jour' : 'Soumettre' ?>
                    </button>
                    <?php if ($editMode): ?>
                        <a href="financemet.php" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Annuler
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <div class="table-container">
            <h3 class="text-center mb-3"><i class="fas fa-list-ol"></i> Historique des Demandes</h3>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead class="table-dark">
                        <tr>
                            <th>ID Demande</th>
                            <th>Projet</th>
                            <th>Montant</th>
                            <th>Durée</th>
                            <th>Statut</th>
                            <th>Réponses</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($existingDemands as $demand): ?>
                            <?php 
                            $project = $controller->getProjectById($demand['id_project']);
                            $projectTitle = $project ? $project['titre'] : 'Projet inconnu';
                            ?>
                            <tr>
                                <td><?= $demand['id_demande'] ?></td>
                                <td><?= htmlspecialchars($projectTitle) ?></td>
                                <td><?= number_format($demand['montant_demandee'], 2) ?> €</td>
                                <td><?= $demand['duree'] ?> mois</td>
                                <td>
                                    <span class="badge <?= 
                                        $demand['status'] == 'accepte' ? 'bg-success' : 
                                        ($demand['status'] == 'rejete' ? 'bg-danger' : 'bg-warning')
                                    ?>">
                                        <?= ucfirst($demand['status']) ?>
                                    </span>
                                </td>
                                <td><?= $demand['nb_reponses'] ?></td>
                                <td class="action-buttons">
                                    <a href="responses.php?demande_id=<?= $demand['id_demande'] ?>" 
                                       class="btn btn-sm btn-info"
                                       title="Voir réponses">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="financemet.php?edit_id=<?= $demand['id_demande'] ?>" 
                                       class="btn btn-sm btn-warning"
                                       title="Modifier">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="financemet.php?delete_id=<?= $demand['id_demande'] ?>" 
                                       class="btn btn-sm btn-danger" 
                                       onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette demande?')"
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
    <script src="js/jsfinan.js"></script></body>
</html>