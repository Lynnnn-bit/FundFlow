<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../controlle/financecontroller.php';
require_once __DIR__ . '/../../controlle/responsecontroller.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check admin authentication (add your own logic)
// if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
//     header("Location: login.php");
//     exit();
// }

$financeController = new FinanceController();
$responseController = new ResponseController();

// Handle delete actions
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['delete_demande'])) {
        try {
            if ($financeController->deleteFinanceRequest($_GET['delete_demande'])) {
                $_SESSION['success'] = "Demande supprimée avec succès!";
            }
        } catch (Exception $e) {
            $_SESSION['error'] = $e->getMessage();
        }
        header("Location: backoffice.php");
        exit();
    }
    
    if (isset($_GET['delete_reponse'])) {
        try {
            if ($responseController->deleteResponse($_GET['delete_reponse'])) {
                $_SESSION['success'] = "Réponse supprimée avec succès!";
            }
        } catch (Exception $e) {
            $_SESSION['error'] = $e->getMessage();
        }
        header("Location: backoffice.php");
        exit();
    }
}

// Get all data
$demandes = $financeController->getAllFinanceRequests();
$allResponses = [];
foreach ($demandes as $demande) {
    $responses = $responseController->getResponsesByDemande($demande['id_demande']);
    $allResponses[$demande['id_demande']] = $responses;
}

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
    <title>FundFlow - Backoffice</title>
    <link rel="stylesheet" href="../Frontoff/css/fundflow.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .demande-card, .response-card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            padding: 20px;
            margin-bottom: 20px;
        }
        .badge {
            padding: 0.35em 0.65em;
            font-size: 0.75em;
            font-weight: 700;
        }
        .action-buttons a, .action-buttons form {
            margin-right: 5px;
            display: inline-block;
        }
    </style>
</head>
<body>
    <header class="navbar">
        <div class="logo-container">
            <span class="brand-name">FundFlow Backoffice</span>
        </div>
        <nav>
            <a href="../Frontoff/financemet.php"><i class="fas fa-hand-holding-usd"></i> Demandes</a>
            <a href="#" class="logout"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
        </nav>
    </header>

    <div class="main-container">
        <?php if (isset($success_message)): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success_message) ?></div>
        <?php endif; ?>
        
        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error_message) ?></div>
        <?php endif; ?>

        <h1 class="mb-4"><i class="fas fa-tachometer-alt"></i> Tableau de bord administrateur</h1>

        <div class="demandes-section mb-5">
            <h2><i class="fas fa-hand-holding-usd"></i> Toutes les demandes de financement</h2>
            
            <?php foreach ($demandes as $demande): ?>
                <div class="demande-card mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h3>
                            Demande #<?= $demande['id_demande'] ?> 
                            <span class="badge bg-<?= 
                                $demande['status'] == 'accepte' ? 'success' : 
                                ($demande['status'] == 'rejete' ? 'danger' : 'warning')
                            ?>">
                                <?= ucfirst($demande['status']) ?>
                            </span>
                        </h3>
                        <div class="action-buttons">
                            <a href="edit_demande.php?id=<?= $demande['id_demande'] ?>" class="btn btn-sm btn-warning">
                                <i class="fas fa-edit"></i> Modifier
                            </a>
                            <a href="backoffice.php?delete_demande=<?= $demande['id_demande'] ?>" 
                            class="btn btn-sm btn-danger"
                            onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette demande?')">
                                <i class="fas fa-trash"></i> Supprimer
                            </a>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Projet:</strong> <?= htmlspecialchars($demande['projet_titre']) ?></p>
                            <p><strong>Porteur:</strong> <?= htmlspecialchars($demande['nom'] . ' ' . $demande['prenom']) ?></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Montant:</strong> <?= number_format($demande['montant_demandee'], 2) ?> €</p>
                            <p><strong>Durée:</strong> <?= $demande['duree'] ?> mois</p>
                        </div>
                    </div>

                    <div class="responses mt-4">
                        <h4><i class="fas fa-reply"></i> Réponses (<?= count($allResponses[$demande['id_demande']] ?? []) ?>)</h4>
                        
                        <?php if (!empty($allResponses[$demande['id_demande']])): ?>
                            <?php foreach ($allResponses[$demande['id_demande']] as $response): ?>
                                <div class="response-card mb-3">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <div>
                                            <span class="badge bg-<?= $response['decision'] == 'accepte' ? 'success' : 'danger' ?>">
                                                <?= ucfirst($response['decision']) ?>
                                            </span>
                                            <span class="badge bg-<?= 
                                                $response['status'] == 'accepte' ? 'success' : 
                                                ($response['status'] == 'rejete' ? 'danger' : 'warning')
                                            ?>">
                                                <?= ucfirst($response['status']) ?>
                                            </span>
                                            <span><?= date('d/m/Y', strtotime($response['date_reponse'])) ?></span>
                                        </div>
                                        <div class="action-buttons">
                                            <a href="edit_reponse.php?id=<?= $response['id_reponse'] ?>" 
                                            class="btn btn-sm btn-warning">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="backoffice.php?delete_reponse=<?= $response['id_reponse'] ?>" 
                                            class="btn btn-sm btn-danger"
                                            onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette réponse?')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </div>
                                    
                                    <p><strong>Montant accordé:</strong> <?= number_format($response['montant_accorde'], 2) ?> €</p>
                                    
                                    <?php if (!empty($response['message'])): ?>
                                        <p><strong>Message:</strong> <?= nl2br(htmlspecialchars($response['message'])) ?></p>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="alert alert-info">
                                Aucune réponse pour cette demande.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>