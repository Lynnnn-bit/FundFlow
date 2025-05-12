<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../controlle/financecontroller.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check admin authentication
// if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
//     header("Location: login.php");
//     exit();
// }

$financeController = new FinanceController();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
    try {
        $id_demande = $_POST['id_demande'];
        $id_project = $_POST['id_project'];
        $montant = $_POST['montant'];
        $duree = $_POST['duree'];
        $status = $_POST['status'];
        
        $user_id = $financeController->getProjectOwner($id_project);
        
        $finance = new Finance($id_project, $user_id, $duree, $montant, $status, $id_demande);
        
        if ($financeController->updateFinanceRequest($finance)) {
            $_SESSION['success'] = "Demande mise à jour avec succès!";
            header("Location: backoffice.php");
            exit();
        }
    } catch (Exception $e) {
        $error_message = $e->getMessage();
    }
}

// Get demande to edit
$demande_id = $_GET['id'] ?? null;
if (!$demande_id) {
    header("Location: backoffice.php");
    exit();
}

$demande = $financeController->getFinanceRequestById($demande_id);
if (!$demande) {
    $_SESSION['error'] = "Demande introuvable";
    header("Location: backoffice.php");
    exit();
}

$projects = $financeController->getProjects($demande['id_utilisateur']);

// Display messages
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
    <title>FundFlow - Modifier Demande</title>
    <link rel="stylesheet" href="../Frontoff/css/fundflow.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <header class="navbar">
        <div class="logo-container">
            <span class="brand-name">FundFlow Backoffice</span>
        </div>
        <nav>
            <a href="backoffice.php"><i class="fas fa-arrow-left"></i> Retour</a>
            <a href="#" class="logout"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
        </nav>
    </header>

    <div class="main-container">
        <h1 class="mb-4"><i class="fas fa-edit"></i> Modifier Demande #<?= $demande['id_demande'] ?></h1>

        <?php if (isset($success_message)): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success_message) ?></div>
        <?php endif; ?>
        
        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error_message) ?></div>
        <?php endif; ?>

        <div class="card">
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="id_demande" value="<?= $demande['id_demande'] ?>">
                    
                    <div class="form-group mb-3">
                        <label class="form-label">Projet</label>
                        <select class="form-select" name="id_project" required>
                            <?php foreach ($projects as $project): ?>
                                <option value="<?= $project['id_projet'] ?>" 
                                    <?= $project['id_projet'] == $demande['id_project'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($project['titre']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group mb-3">
                        <label class="form-label">Montant (€)</label>
                        <input type="number" class="form-control" name="montant" 
                               min="10000" max="10000000" value="<?= $demande['montant_demandee'] ?>" required>
                    </div>

                    <div class="form-group mb-3">
                        <label class="form-label">Durée (mois)</label>
                        <input type="number" class="form-control" name="duree" 
                               min="6" max="60" value="<?= $demande['duree'] ?>" required>
                    </div>

                    <div class="form-group mb-3">
                        <label class="form-label">Statut</label>
                        <select class="form-select" name="status" required>
                            <option value="en_attente" <?= $demande['status'] == 'en_attente' ? 'selected' : '' ?>>En attente</option>
                            <option value="accepte" <?= $demande['status'] == 'accepte' ? 'selected' : '' ?>>Accepté</option>
                            <option value="rejete" <?= $demande['status'] == 'rejete' ? 'selected' : '' ?>>Rejeté</option>
                        </select>
                    </div>

                    <button type="submit" name="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Enregistrer
                    </button>
                    <a href="backoffice.php" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Annuler
                    </a>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../Frontoff/js/jseditdemande.js"></script>
</body>
</html>