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

$demandes = $financeController->getAllFinanceRequests();

// Filter demands based on search query
if (!empty($searchQuery)) {
    $demandes = array_filter($demandes, function ($demand) use ($searchQuery) {
        return stripos($demand['projet_titre'], $searchQuery) !== false;
    });
}

// Sort demands based on selected column and order
if (!empty($sortColumn) && in_array($sortColumn, ['id_demande', 'projet_titre', 'montant_demandee', 'duree', 'status'])) {
    usort($demandes, function ($a, $b) use ($sortColumn, $sortOrder) {
        if ($sortOrder === 'asc') {
            return $a[$sortColumn] <=> $b[$sortColumn];
        } else {
            return $b[$sortColumn] <=> $a[$sortColumn];
        }
    });
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FundFlow - Demandes</title>
    <link rel="stylesheet" href="../Frontoff/css/stylefinan.css">
    <link rel="stylesheet" href="../Frontoff/css/stylebackof.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .sidebar {
            position: fixed;
            top: 0;
            left: -250px;
            width: 250px;
            height: 100%;
            background-color: #1c1c1c;
            color: white;
            transition: all 0.3s ease-in-out;
            z-index: 1000;
            padding: 1rem;
        }

        .sidebar.active {
            left: 0;
        }

        .sidebar h2 {
            text-align: center;
            margin-bottom: 1.5rem;
        }

        .sidebar ul {
            list-style: none;
            padding: 0;
        }

        .sidebar ul li {
            margin-bottom: 1rem;
        }

        .sidebar ul li a {
            color: white;
            text-decoration: none;
            font-size: 1rem;
            display: flex;
            align-items: center;
        }

        .sidebar ul li a i {
            margin-right: 10px;
        }

        .sidebar ul li a:hover {
            color: #00d09c;
        }

        .sidebar ul li .submenu {
            margin-left: 20px;
            display: none;
        }

        .sidebar ul li .submenu.active {
            display: block;
        }

        .toggle-sidebar {
            position: fixed;
            top: 20px;
            left: 20px;
            background-color: #1c1c1c;
            color: white;
            border: none;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            z-index: 1100;
        }

        .toggle-sidebar i {
            font-size: 1.5rem;
        }
    </style>
</head>
<body>
    <button class="toggle-sidebar">
        <i class="fas fa-bars"></i>
    </button>

    <div class="sidebar">
        <h2>Menu</h2>
        <ul>
            <li><a href="backoffice.php"><i class="fas fa-home"></i> Dashboard</a></li>
            <li><a href="#"><i class="fas fa-users"></i> Utilisateurs</a></li>
            <li><a href="#"><i class="fas fa-project-diagram"></i> Projets</a></li>
            <li>
                <a href="#" class="toggle-submenu"><i class="fas fa-hand-holding-usd"></i> Financements</a>
                <ul class="submenu">
                    <li><a href="statistics.php"><i class="fas fa-chart-bar"></i> Statistiques</a></li>
                    <li><a href="demands.php"><i class="fas fa-list"></i> Demandes</a></li>
                </ul>
            </li>
            <li><a href="#"><i class="fas fa-comments"></i> Consultations</a></li>
            <li><a href="#"><i class="fas fa-handshake"></i> Partenariats</a></li>
            <li><a href="#"><i class="fas fa-rocket"></i> Startups</a></li>
        </ul>
    </div>

    <div class="main-container">
        <h1 class="mb-4"><i class="fas fa-list-ol"></i> Liste des Demandes</h1>

        <div class="text-end mb-3">
            <!-- Removed PDF export button -->
        </div>

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
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($demandes)): ?>
                            <?php foreach ($demandes as $demande): ?>
                                <tr>
                                    <td><?= $demande['id_demande'] ?></td>
                                    <td><?= htmlspecialchars($demande['projet_titre']) ?></td>
                                    <td><?= number_format($demande['montant_demandee'], 2) ?> €</td>
                                    <td><?= $demande['duree'] ?> mois</td>
                                    <td>
                                        <span class="badge <?= 
                                            $demande['status'] == 'accepte' ? 'bg-success' : 
                                            ($demande['status'] == 'rejete' ? 'bg-danger' : 'bg-warning')
                                        ?>">
                                            <?= ucfirst($demande['status']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?= $demande['nb_reponses'] ?>
                                        <a href="responses.php?demande_id=<?= $demande['id_demande'] ?>" class="btn btn-sm btn-info">
                                            <i class="fas fa-eye"></i> Voir
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center">Aucune demande trouvée</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const toggleSidebarButton = document.querySelector('.toggle-sidebar');
        const sidebar = document.querySelector('.sidebar');
        const toggleSubmenuLinks = document.querySelectorAll('.toggle-submenu');

        toggleSidebarButton.addEventListener('click', () => {
            sidebar.classList.toggle('active');
        });

        toggleSubmenuLinks.forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                const submenu = link.nextElementSibling;
                submenu.classList.toggle('active');
            });
        });
    </script>
</body>
</html>
