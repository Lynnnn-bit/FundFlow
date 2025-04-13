<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../models/response.php';
require_once __DIR__ . '/../../controlle/responsecontroller.php';
require_once __DIR__ . '/../../controlle/financecontroller.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check admin authentication
// if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
//     header("Location: login.php");
//     exit();
// }

$responseController = new ResponseController();
$financeController = new FinanceController();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
    try {
        $id_reponse = $_POST['id_reponse'];
        $id_demande = $_POST['id_demande'];
        $decision = $_POST['decision'];
        $message = $_POST['message'];
        $montant_accorde = $_POST['montant_accorde'];
        $date_reponse = $_POST['date_reponse'];
        $status = $_POST['status'];
        
        // Create response object
        $response = new Response(
            $id_demande,
            $decision,
            $message,
            $montant_accorde,
            $date_reponse,
            $status,
            $id_reponse
        );
        
        if ($responseController->updateResponseStatus($id_reponse, $status)) {
            // Update other response fields if needed
            $db = Config::getConnexion(); // Assuming you have this method in Config
            $stmt = $db->prepare("
                UPDATE reponse 
                SET decision = ?, message = ?, montant_accorde = ?, date_reponse = ?
                WHERE id_reponse = ?
            ");
            $stmt->execute([
                $decision,
                $message,
                $montant_accorde,
                $date_reponse,
                $id_reponse
            ]);
            
            $_SESSION['success'] = "Réponse mise à jour avec succès!";
            header("Location: backoffice.php");
            exit();
        }
    } catch (Exception $e) {
        $error_message = $e->getMessage();
    }
}

// Get response to edit
$reponse_id = $_GET['id'] ?? null;
if (!$reponse_id) {
    header("Location: backoffice.php");
    exit();
}

$reponse = $responseController->getResponseById($reponse_id);
if (!$reponse) {
    $_SESSION['error'] = "Réponse introuvable";
    header("Location: backoffice.php");
    exit();
}

$demande = $financeController->getFinanceRequestById($reponse['id_demande']);

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
    <title>FundFlow - Modifier Réponse</title>
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
        <h1 class="mb-4"><i class="fas fa-edit"></i> Modifier Réponse #<?= $reponse['id_reponse'] ?></h1>

        <?php if (isset($success_message)): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success_message) ?></div>
        <?php endif; ?>
        
        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error_message) ?></div>
        <?php endif; ?>

        <div class="card mb-4">
            <div class="card-header">
                <h2>Détails de la demande</h2>
            </div>
            <div class="card-body">
                <p><strong>Projet:</strong> <?= htmlspecialchars($demande['projet_titre']) ?></p>
                <p><strong>Montant demandé:</strong> <?= number_format($demande['montant_demandee'], 2) ?> €</p>
                <p><strong>Statut:</strong> 
                    <span class="badge bg-<?= 
                        $demande['status'] == 'accepte' ? 'success' : 
                        ($demande['status'] == 'rejete' ? 'danger' : 'warning')
                    ?>">
                        <?= ucfirst($demande['status']) ?>
                    </span>
                </p>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="id_reponse" value="<?= $reponse['id_reponse'] ?>">
                    <input type="hidden" name="id_demande" value="<?= $reponse['id_demande'] ?>">
                    
                    <div class="form-group mb-3">
                        <label class="form-label">Décision</label>
                        <select class="form-select" name="decision" required>
                            <option value="accepte" <?= $reponse['decision'] == 'accepte' ? 'selected' : '' ?>>Accepter</option>
                            <option value="refuse" <?= $reponse['decision'] == 'refuse' ? 'selected' : '' ?>>Refuser</option>
                        </select>
                    </div>

                    <div class="form-group mb-3">
                        <label class="form-label">Montant accordé (€)</label>
                        <input type="number" class="form-control" name="montant_accorde" 
                               min="0" step="100" value="<?= $reponse['montant_accorde'] ?>" required>
                    </div>

                    <div class="form-group mb-3">
                        <label class="form-label">Date de réponse</label>
                        <input type="date" class="form-control" name="date_reponse" 
                               value="<?= $reponse['date_reponse'] ?>" required>
                    </div>

                    <div class="form-group mb-3">
                        <label class="form-label">Message</label>
                        <textarea class="form-control" name="message" rows="3"><?= htmlspecialchars($reponse['message']) ?></textarea>
                    </div>

                    <div class="form-group mb-3">
                        <label class="form-label">Statut</label>
                        <select class="form-select" name="status" required>
                            <option value="en_attente" <?= $reponse['status'] == 'en_attente' ? 'selected' : '' ?>>En attente</option>
                            <option value="accepte" <?= $reponse['status'] == 'accepte' ? 'selected' : '' ?>>Accepté</option>
                            <option value="rejete" <?= $reponse['status'] == 'rejete' ? 'selected' : '' ?>>Rejeté</option>
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
    <script>
        // Enable/disable amount field based on decision
        document.addEventListener('DOMContentLoaded', function() {
            const decisionSelect = document.querySelector('select[name="decision"]');
            const amountField = document.querySelector('input[name="montant_accorde"]');
            
            if (decisionSelect && amountField) {
                decisionSelect.addEventListener('change', function() {
                    amountField.disabled = this.value !== 'accepte';
                    if (this.value !== 'accepte') {
                        amountField.value = '0';
                    }
                });
                
                // Initialize on page load
                decisionSelect.dispatchEvent(new Event('change'));
            }
        });
    </script>
    <script src="../Frontoff/js/jseditreponse.js"></script>
</body>
</html>