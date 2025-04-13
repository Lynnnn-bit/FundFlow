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

$responseController = new ResponseController();
$financeController = new FinanceController();

// Get demande_id from URL
$demande_id = $_GET['demande_id'] ?? null;
if (!$demande_id) {
    header("Location: demands_list.php");
    exit();
}

$demande = $financeController->getFinanceRequestById($demande_id);
if (!$demande) {
    $_SESSION['error'] = "Demande de financement introuvable";
    header("Location: demands_list.php");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
    try {
        $decision = $_POST['decision'];
        $message = $_POST['message'];
        $montant_accorde = $_POST['montant_accorde'];
        $date_reponse = $_POST['date_reponse'];
        
        // Create new Response object
        $response = new Response(
            $demande_id,
            $decision,
            $message,
            $montant_accorde,
            $date_reponse
        );
        
        if ($responseController->createResponse($response)) {
            $_SESSION['success'] = "Réponse enregistrée avec succès!";
            header("Location: demands_list.php?demande_id=$demande_id");
            exit();
        }
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
    <title>FundFlow - Nouvelle Réponse</title>
    <link rel="stylesheet" href="css/stylenewresp.css">
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
        <a href="responses.php?demande_id=<?= $demande_id ?>" class="btn btn-secondary mb-3">
            <i class="fas fa-arrow-left"></i> Retour aux réponses
        </a>

        <div class="header-section">
            <h1><i class="fas fa-plus-circle"></i> Nouvelle Réponse pour Demande #<?= $demande_id ?></h1>
        </div>

        <?php if (isset($success_message)): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success_message) ?></div>
        <?php endif; ?>
        
        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error_message) ?></div>
        <?php endif; ?>

        <div class="response-form">
            <form method="POST">
                <input type="hidden" name="id_demande" value="<?= $demande_id ?>">
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label class="form-label">Décision *</label>
                            <select class="form-select" name="decision" required>
                                <option value="accepte">Accepter</option>
                                <option value="refuse">Refuser</option>
                            </select>
                        </div>
                        
                        <!--<div class="form-group mb-3">
                            <label class="form-label">Montant accordé (€)</label>
                            <input type="number" class="form-control" name="montant_accorde" 
                                   min="0" step="100" value="<?= $demande['montant_demandee'] ?>">
                        </div>-->

                        <div class="form-group mb-3">
    <label class="form-label">Montant accordé (€)</label>
    <input type="number" class="form-control" name="montant_accorde" 
           min="0" step="100" value="<?= $demande['montant_demandee'] ?>">
    <div class="error-message text-danger mt-1" id="montantError" style="display: none; ; color: red;"></div>
</div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label class="form-label">Date de réponse *</label>
                            <input type="date" class="form-control" name="date_reponse" 
                                   value="<?= date('Y-m-d') ?>" required>
                        </div>
                    </div>
                </div>
                
                <div class="form-group mb-3">
                    <label class="form-label">Message</label>
                    <textarea class="form-control" name="message" rows="3"></textarea>
                </div>
                
                <button type="submit" name="submit" class="btn btn-primary">
                    <i class="fas fa-paper-plane"></i> Envoyer la réponse
                </button>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
   
    <script src="js/jsnewrep.js"></script></body>
    <!--<script>
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
    </script>-->
</body>
</html>