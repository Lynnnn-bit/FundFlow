<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../controlle/PartenaireController.php';

session_start();

$partenaireController = new PartenaireController();

// Handle admin actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['approve'])) {
        $partenaireController->updateContractProposalStatus($_POST['id'], 'approuvé');
        $_SESSION['success'] = "Proposition de contrat approuvée avec succès!";
    } elseif (isset($_POST['reject'])) {
        $partenaireController->updateContractProposalStatus($_POST['id'], 'rejeté');
        $_SESSION['success'] = "Proposition de contrat rejetée avec succès!";
    }
    header("Location: admin_contracts.php");
    exit();
}

// Fetch all contract proposals
$proposals = $partenaireController->getAllContractProposals();

// Display messages
$success_message = $_SESSION['success'] ?? null;
unset($_SESSION['success']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Propositions de Contrat</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1>Propositions de Contrat</h1>

        <?php if ($success_message): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success_message) ?></div>
        <?php endif; ?>

        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Partenaire</th>
                    <th>Date Début</th>
                    <th>Date Fin</th>
                    <th>Termes</th>
                    <th>Statut</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($proposals as $proposal): ?>
                    <tr>
                        <td><?= htmlspecialchars($proposal['partenaire_nom']) ?></td>
                        <td><?= htmlspecialchars($proposal['date_deb']) ?></td>
                        <td><?= htmlspecialchars($proposal['date_fin']) ?></td>
                        <td><?= htmlspecialchars($proposal['terms']) ?></td>
                        <td><?= htmlspecialchars($proposal['status']) ?></td>
                        <td>
                            <form method="POST" class="d-inline">
                                <input type="hidden" name="id" value="<?= $proposal['id'] ?>">
                                <button type="submit" name="approve" class="btn btn-success btn-sm">Approuver</button>
                                <button type="submit" name="reject" class="btn btn-danger btn-sm">Rejeter</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>