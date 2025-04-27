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

// Calculate the remaining amount required for the demande
$totalAcceptedResponses = array_sum(array_map(function ($response) {
    return $response['montant_accorde'];
}, $responseController->getAcceptedResponsesByDemande($demande_id)));
$remainingAmount = $demande['montant_demandee'] - $totalAcceptedResponses;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
    try {
        $montant_accorde = $_POST['montant_accorde'];
        $message = $_POST['message'];
        $date_reponse = $_POST['date_reponse'];

        if ($montant_accorde > $remainingAmount) {
            throw new Exception("Le montant accordé dépasse le montant restant requis.");
        }

        // Create new Response object
        $response = new Response(
            $demande_id,
            'accepte', // Default decision is "accepte"
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
        <a href="demands_list.php?demande_id=<?= $demande_id ?>" class="btn btn-secondary mb-3">
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
                
                <div class="form-group mb-3">
                    <label class="form-label">Montant accordé (€) *</label>
                    <input type="number" class="form-control" name="montant_accorde" 
                           min="0" max="<?= $remainingAmount ?>" step="100" required>
                    <div class="error-message text-danger mt-1" id="montantError" style="display: none;"></div>
                </div>

                <div class="form-group mb-3">
                    <label class="form-label">Date de réponse *</label>
                    <input type="date" class="form-control" name="date_reponse" 
                           value="<?= date('Y-m-d') ?>" required>
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

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const amountField = document.querySelector('input[name="montant_accorde"]');
            const montantError = document.getElementById('montantError');
            const maxAmount = <?= $remainingAmount ?>;

            // Real-time validation feedback
            amountField.addEventListener('input', function() {
                const montant = parseFloat(this.value);

                if (montant > maxAmount) {
                    montantError.textContent = `Le montant ne peut pas dépasser ${maxAmount.toLocaleString()} €.`;
                    montantError.style.display = 'block';
                } else {
                    montantError.style.display = 'none';
                }
            });
        });
    </script>
</body>
</html>