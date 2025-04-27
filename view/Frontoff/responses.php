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

// Handle accept and delete actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['accept_response'])) {
        try {
            if ($responseController->acceptResponse($_POST['response_id'])) {
                // Check if the sum of accepted responses meets the requested amount
                $acceptedResponses = $responseController->getAcceptedResponsesByDemande($_POST['demande_id']);
                $totalAcceptedAmount = array_sum(array_column($acceptedResponses, 'montant_accorde'));

                $demande = $financeController->getFinanceRequestById($_POST['demande_id']);
                if ($totalAcceptedAmount >= $demande['montant_demandee']) {
                    $financeController->updateDemandeStatus($_POST['demande_id'], 'accepte');
                }

                $_SESSION['success'] = "Réponse acceptée avec succès!";
            }
        } catch (Exception $e) {
            $_SESSION['error'] = $e->getMessage();
        }
        header("Location: responses.php?demande_id=" . $_POST['demande_id']);
        exit();
    } elseif (isset($_POST['delete_response'])) {
        try {
            $responseController->rejectResponse($_POST['response_id']);
            $_SESSION['success'] = "Réponse rejetée et supprimée avec succès!";
        } catch (Exception $e) {
            $_SESSION['error'] = $e->getMessage();
        }
        header("Location: responses.php?demande_id=" . $_POST['demande_id']);
        exit();
    }
}

// Get current demande and responses
$demande_id = $_GET['demande_id'] ?? null;
if (!$demande_id) {
    header("Location: financemet.php");
    exit();
}

$demande = $financeController->getFinanceRequestById($demande_id);
if (!$demande) {
    $_SESSION['error'] = "Demande de financement introuvable";
    header("Location: financemet.php");
    exit();
}

$responses = $responseController->getResponsesByDemande($demande_id);

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
    <title>FundFlow - Gestion des Réponses</title>
    <link rel="stylesheet" href="css/styleresp.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
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
        <a href="financemet.php" class="btn btn-secondary mb-3">
            <i class="fas fa-arrow-left"></i> Retour aux demandes
        </a>

        <div class="header-section">
            <h1><i class="fas fa-reply"></i> Réponses à la Demande #<?= $demande['id_demande'] ?></h1>
        </div>

        <?php if (isset($success_message)): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success_message) ?></div>
        <?php endif; ?>
        
        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error_message) ?></div>
        <?php endif; ?>

        <div class="demande-info response-card">
            <div class="response-header">
                <h2>Détails de la demande</h2>
                <span class="badge bg-<?= 
                    $demande['status'] == 'accepte' ? 'success' : 
                    ($demande['status'] == 'rejete' ? 'danger' : 'warning')
                ?>">
                    <?= ucfirst($demande['status']) ?>
                </span>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <p><strong>Projet:</strong> <?= htmlspecialchars($demande['projet_titre']) ?></p>
                    <p><strong>Porteur:</strong> <?= htmlspecialchars($demande['nom'] . ' ' . $demande['prenom']) ?></p>
                </div>
                <div class="col-md-6">
                    <p><strong>Montant demandé:</strong> <?= number_format($demande['montant_demandee'], 2) ?> €</p>
                    <p><strong>Durée:</strong> <?= $demande['duree'] ?> mois</p>
                </div>
            </div>
        </div>

        <div class="responses-list">
            <h2><i class="fas fa-list"></i> Réponses existantes (<?= count($responses) ?>)</h2>
            
            <?php if (count($responses) > 0): ?>
                <?php foreach ($responses as $response): ?>
                    <div class="response-card mb-3">
                        <div class="response-header">
                            <div>
                                <h3>
                                    Réponse du <?= date('d/m/Y', strtotime($response['date_reponse'])) ?>
                                    <span class="badge bg-<?= $response['decision'] == 'accepte' ? 'success' : 'danger' ?>">
                                        <?= ucfirst($response['decision']) ?>
                                    </span>
                                </h3>
                                <p class="mb-0">
                                    <strong>Montant accordé:</strong> 
                                    <?= number_format($response['montant_accorde'], 2) ?> €
                                </p>
                            </div>
                            <span class="badge bg-<?= 
                                $response['status'] == 'accepte' ? 'success' : 
                                ($response['status'] == 'rejete' ? 'danger' : 'warning')
                            ?>">
                                <?= ucfirst($response['status']) ?>
                            </span>
                        </div>
                        
                        <?php if (!empty($response['message'])): ?>
                            <div class="mb-3">
                                <strong>Message:</strong>
                                <p><?= nl2br(htmlspecialchars($response['message'])) ?></p>
                            </div>
                        <?php endif; ?>
                        
                        <div class="action-buttons">
                            <?php if ($response['status'] == 'en_attente'): ?>
                                <form method="POST">
                                    <input type="hidden" name="response_id" value="<?= $response['id_reponse'] ?>">
                                    <input type="hidden" name="demande_id" value="<?= $demande_id ?>">
                                    <button type="submit" name="accept_response" class="btn btn-sm btn-success">
                                        <i class="fas fa-check"></i> Accepter
                                    </button>
                                </form>
                            <?php endif; ?>
                            <form method="POST">
                                <input type="hidden" name="response_id" value="<?= $response['id_reponse'] ?>">
                                <input type="hidden" name="demande_id" value="<?= $demande_id ?>">
                                <button type="submit" name="delete_response" class="btn btn-sm btn-danger">
                                    <i class="fas fa-trash"></i> Supprimer
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="alert alert-info">
                    Aucune réponse pour cette demande.
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>