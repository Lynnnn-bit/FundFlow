<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../controlle/financecontroller.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$financeController = new FinanceController();
$demands = $financeController->getAllFinanceRequests();

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
    <title>FundFlow - Liste des Demandes</title>
    <link rel="stylesheet" href="css/styledemand.css">
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
        <div class="header-section">
            <h1><i class="fas fa-list-ol"></i> Liste des Demandes de Financement</h1>
            <p>Sélectionnez une demande pour y répondre</p>
        </div>

        <?php if (isset($success_message)): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success_message) ?></div>
        <?php endif; ?>
        
        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error_message) ?></div>
        <?php endif; ?>

        <div class="table-container">
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
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($demands as $demand): ?>
                            <tr>
                                <td><?= $demand['id_demande'] ?></td>
                                <td><?= htmlspecialchars($demand['projet_titre']) ?></td>
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
                                <td>
                                    <a href="new_response.php?demande_id=<?= $demand['id_demande'] ?>" 
                                    class="btn btn-sm btn-primary">
                                        <i class="fas fa-reply"></i> Répondre
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
</body>
</html>