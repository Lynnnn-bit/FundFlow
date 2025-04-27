<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../controlle/responsecontroller.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$responseController = new ResponseController();

// Get demande_id from URL
$demande_id = $_GET['demande_id'] ?? null;
if (!$demande_id) {
    header("Location: backoffice.php");
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
    <link rel="stylesheet" href="../Frontoff/css/stylefinan.css">
    <link rel="stylesheet" href="../Frontoff/css/stylebackof.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="main-container">
        <a href="backoffice.php" class="btn btn-secondary mb-3">
            <i class="fas fa-arrow-left"></i> Retour
        </a>

        <h1 class="mb-4"><i class="fas fa-reply"></i> Réponses pour la Demande #<?= $demande_id ?></h1>

        <div class="table-container">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead class="table-dark">
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
                                        <span class="badge <?= $response['decision'] == 'accepte' ? 'bg-success' : 'bg-danger' ?>">
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
