<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../control/responsecontroller.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$responseController = new ResponseController();
$demande_id = $_GET['demande_id'] ?? null;

if (!$demande_id) {
    header("Location: demands.php");
    exit();
}

$responses = $responseController->getResponsesByDemande($demande_id);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FundFlow - Réponses</title>
    <link rel="stylesheet" href="../Frontoff/css/stylebackof.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .admin-background {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -2;
            background: linear-gradient(135deg, #f5f7ff 0%, #e8ecff 100%);
            overflow: hidden;
        }
        
        .admin-background::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('../Frontoff/assets/Logo_FundFlow.png') center/30% no-repeat;
            opacity: 0.03;
            pointer-events: none;
        }
        
        .admin-background::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle at 20% 30%, rgba(67, 97, 238, 0.05) 0%, transparent 50%),
                        radial-gradient(circle at 80% 70%, rgba(16, 185, 129, 0.05) 0%, transparent 50%);
        }
        
        .response-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }
        
        .back-button {
            margin-bottom: 1.5rem;
        }
    </style>
</head>
<body>
    <div class="admin-background"></div>
    
    <div class="response-container">
        <a href="demands.php" class="btn btn-secondary back-button">
            <i class="fas fa-arrow-left"></i> Retour aux demandes
        </a>

        <div class="card">
            <div class="card-header">
                <h2><i class="fas fa-reply"></i> Réponses pour la Demande #<?= $demande_id ?></h2>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID Réponse</th>
                                <th>Décision</th>
                                <th>Montant Accordé</th>
                                <th>Date</th>
                                <th>Message</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($responses)): ?>
                                <?php foreach ($responses as $response): ?>
                                    <tr>
                                        <td><?= $response['id_reponse'] ?></td>
                                        <td>
                                            <span class="badge <?= $response['decision'] == 'accepte' ? 'badge-success' : 'badge-danger' ?>">
                                                <?= ucfirst($response['decision']) ?>
                                            </span>
                                        </td>
                                        <td><?= number_format($response['montant_accorde'], 2) ?> €</td>
                                        <td><?= date('d/m/Y', strtotime($response['date_reponse'])) ?></td>
                                        <td><?= htmlspecialchars($response['message']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center">Aucune réponse trouvée</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Simple back button functionality
        document.querySelector('.back-button').addEventListener('click', function(e) {
            e.preventDefault();
            window.location.href = this.href;
        });

        // No sidebar toggle needed for this page
    </script>
</body>
</html>