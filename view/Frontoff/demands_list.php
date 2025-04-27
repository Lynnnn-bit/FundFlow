<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../controlle/financecontroller.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$financeController = new FinanceController();

// Handle search and sort functionality
$searchQuery = $_GET['search'] ?? '';
$sortColumn = $_GET['sort_column'] ?? 'id_demande';
$sortOrder = $_GET['sort_order'] ?? 'asc';

$demands = $financeController->getAllFinanceRequests();

// Filter demands based on search query
if (!empty($searchQuery)) {
    $demands = array_filter($demands, function ($demand) use ($searchQuery) {
        return stripos($demand['projet_titre'], $searchQuery) !== false;
    });
}

// Sort demands based on selected column and order
if (!empty($sortColumn) && in_array($sortColumn, ['id_demande', 'projet_titre', 'montant_demandee', 'duree', 'status'])) {
    usort($demands, function ($a, $b) use ($sortColumn, $sortOrder) {
        if ($sortOrder === 'asc') {
            return $a[$sortColumn] <=> $b[$sortColumn];
        } else {
            return $b[$sortColumn] <=> $a[$sortColumn];
        }
    });
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

        <!-- Search and Sort Form -->
        <form method="GET" class="mb-4 d-flex align-items-center">
            <div class="input-group me-3">
                <input type="text" name="search" class="form-control" placeholder="Rechercher par nom de projet" value="<?= htmlspecialchars($searchQuery) ?>">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i> Rechercher
                </button>
            </div>
            <div class="input-group">
                <select name="sort_column" class="form-select">
                    <option value="id_demande" <?= $sortColumn === 'id_demande' ? 'selected' : '' ?>>ID Demande</option>
                    <option value="projet_titre" <?= $sortColumn === 'projet_titre' ? 'selected' : '' ?>>Projet</option>
                    <option value="montant_demandee" <?= $sortColumn === 'montant_demandee' ? 'selected' : '' ?>>Montant</option>
                    <option value="duree" <?= $sortColumn === 'duree' ? 'selected' : '' ?>>Durée</option>
                    <option value="status" <?= $sortColumn === 'status' ? 'selected' : '' ?>>Statut</option>
                </select>
                <select name="sort_order" class="form-select">
                    <option value="asc" <?= $sortOrder === 'asc' ? 'selected' : '' ?>>Ascendant</option>
                    <option value="desc" <?= $sortOrder === 'desc' ? 'selected' : '' ?>>Descendant</option>
                </select>
                <button type="submit" class="btn btn-secondary">
                    <i class="fas fa-sort"></i> Trier
                </button>
            </div>
        </form>

        <div class="table-container">
            <h3 class="text-center mb-3"><i class="fas fa-list-ol"></i> Historique des Demandes</h3>
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
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($demands)): ?>
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
                                    <td class="action-buttons">
                                        <a href="new_response.php?demande_id=<?= $demand['id_demande'] ?>" 
                                           class="btn btn-sm btn-response" title="Répondre">
                                            <i class="fas fa-reply"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center">Aucune demande trouvée</td>
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