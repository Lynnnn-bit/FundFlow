<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../controlle/financecontroller.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$controller = new FinanceController();

// Handle delete action
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['delete_id'])) {
    try {
        $deleteId = $_GET['delete_id'];
        if ($controller->deleteFinanceRequest($deleteId)) {
            $_SESSION['success'] = "Demande supprimée avec succès!";
            header("Location: financemet.php");
            exit();
        }
    } catch (Exception $e) {
        $error_message = $e->getMessage();
    }
}

// Handle edit form display
$editMode = false;
$financeToEdit = null;
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['edit_id'])) {
    $editMode = true;
    $editId = $_GET['edit_id'];
    $financeToEdit = $controller->getFinanceRequestById($editId);
}

// Fetch all projects and existing demands
$projects = $controller->getProjects(1);
$existingDemands = $controller->getAllFinanceRequests();

// Generate a new unique ID
$newId = 1;
if (!empty($existingDemands)) {
    $maxId = max(array_column($existingDemands, 'id_demande'));
    $newId = $maxId + 1;
}

// Handle search and sort functionality
$searchQuery = $_GET['search'] ?? '';
$sortColumn = $_GET['sort_column'] ?? 'id_demande';
$sortOrder = $_GET['sort_order'] ?? 'asc';

if (!empty($searchQuery)) {
    $existingDemands = array_filter($existingDemands, function ($demand) use ($searchQuery) {
        return stripos($demand['projet_titre'], $searchQuery) !== false;
    });
}

if (!empty($sortColumn) && in_array($sortColumn, ['id_demande', 'projet_titre', 'montant_demandee', 'duree', 'status'])) {
    usort($existingDemands, function ($a, $b) use ($sortColumn, $sortOrder) {
        if ($sortOrder === 'asc') {
            return $a[$sortColumn] <=> $b[$sortColumn];
        } else {
            return $b[$sortColumn] <=> $a[$sortColumn];
        }
    });
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
    try {
        $id_demande = $_POST['id_demande'];
        $id_project = $_POST['id_project'];
        $montant_demandee = $_POST['montant'];
        $duree = $_POST['duree'];
        $status = $editMode ? $_POST['status'] : 'en_attente'; // Get status from form if in edit mode

        $project = $controller->getProjectById($id_project);
        if (!$project) {
            $projectError = "Veuillez sélectionner un projet valide.";
        } else {
            $user_id = $controller->getProjectOwner($id_project);
            $finance = new Finance($id_project, $user_id, $duree, $montant_demandee, $status, $id_demande);

            if (isset($_POST['is_edit']) && $_POST['is_edit'] === 'true') {
                if ($controller->updateFinanceRequest($finance)) {
                    $_SESSION['success'] = "Demande mise à jour avec succès! (ID: $id_demande)";
                }
            } else {
                if ($controller->createFinanceRequest($finance)) {
                    $_SESSION['success'] = "Demande enregistrée avec succès! (ID: $id_demande)";
                }
            }

            header("Location: financemet.php");
            exit();
        }
    } catch (Exception $e) {
        $error_message = $e->getMessage();
    }
}

// Display success message from session
if (isset($_SESSION['success'])) {
    $success_message = $_SESSION['success'];
    unset($_SESSION['success']);
}

// Calculate statistics
$totalDemands = count($existingDemands);
$totalAmountRequested = array_sum(array_column($existingDemands, 'montant_demandee'));
$totalAcceptedDemands = count(array_filter($existingDemands, fn($d) => $d['status'] === 'accepte'));
$totalRejectedDemands = count(array_filter($existingDemands, fn($d) => $d['status'] === 'rejete'));
$totalPendingDemands = count(array_filter($existingDemands, fn($d) => $d['status'] === 'en_attente'));
$totalAcceptedAmount = array_sum(array_map(function ($d) {
    return $d['status'] === 'accepte' ? $d['montant_demandee'] : 0;
}, $existingDemands));
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FundFlow - Gestion des Financements</title>
    <link rel="stylesheet" href="css/stylefinan.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
        <div class="nav-buttons">
            <button class="btn btn-primary" id="btn-new-request">Nouvelle Demande</button>
            <button class="btn btn-secondary" id="btn-history">Historique des Demandes</button>
            <button class="btn btn-info" id="btn-stats">Statistiques</button>
            <button class="btn btn-success" id="btn-chatbot">Chatbot</button>
        </div>

        <!-- Nouvelle Demande de Financement Section -->
        <section id="new-request-section" class="section active">
            <div class="finance-form-container">
                <h2 class="text-center mb-4"><?= $editMode ? 'Modifier' : 'Nouvelle' ?> Demande de Financement</h2>
                
                <?php if (isset($success_message)): ?>
                    <div class="alert alert-success"><?= htmlspecialchars($success_message) ?></div>
                <?php endif; ?>
                
                <?php if (isset($error_message)): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($error_message) ?></div>
                <?php endif; ?>

                <form method="POST">
                    <input type="hidden" name="id_demande" value="<?= $editMode ? $financeToEdit['id_demande'] : $newId ?>">
                    <input type="hidden" name="is_edit" value="<?= $editMode ? 'true' : 'false' ?>">
                    
                    <div class="form-group">
                        <label class="form-label"><i class="fas fa-project-diagram"></i> Projet *</label>
                        <select class="form-select" name="id_project" >
                            <option value="">Sélectionnez un projet</option>
                            <?php foreach ($projects as $project): ?>
                                <option value="<?= $project['id_projet'] ?>" 
                                    <?= ($editMode && $financeToEdit['id_project'] == $project['id_projet']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($project['titre']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (isset($projectError)): ?>
                            <div class="error-message text-danger mt-1"><?= htmlspecialchars($projectError) ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label class="form-label"><i class="fas fa-euro-sign"></i> Montant (€) *</label>
                        <input type="number" class="form-control" name="montant" min="10000" max="10000000" 
                               value="<?= $editMode ? $financeToEdit['montant_demandee'] : '500000' ?>" >
                    </div>

                    <div class="form-group">
                        <label class="form-label"><i class="fas fa-calendar-alt"></i> Durée (mois) *</label>
                        <input type="number" class="form-control" name="duree" min="6" max="60" 
                               value="<?= $editMode ? $financeToEdit['duree'] : '24' ?>" >
                    </div>

                    <div class="form-actions">
                        <button type="submit" name="submit" class="btn btn-primary">
                            <i class="fas fa-<?= $editMode ? 'save' : 'paper-plane' ?>"></i>
                            <?= $editMode ? 'Mettre à jour' : 'Soumettre' ?>
                        </button>
                        <?php if ($editMode): ?>
                            <a href="financemet.php" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Annuler
                            </a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </section>

        <!-- Historique des Demandes Section -->
        <section id="history-section" class="section">
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
                <div class="text-end mb-3">
                    <a href="generate_pdf.php" class="btn btn-primary">
                        <i class="fas fa-file-pdf"></i> Télécharger PDF
                    </a>
                </div>
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
                            <?php if (!empty($existingDemands)): ?>
                                <?php foreach ($existingDemands as $demand): ?>
                                    <?php 
                                    $project = $controller->getProjectById($demand['id_project']);
                                    $projectTitle = $project ? $project['titre'] : 'Projet inconnu';
                                    ?>
                                    <tr>
                                        <td><?= $demand['id_demande'] ?></td>
                                        <td><?= htmlspecialchars($projectTitle) ?></td>
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
                                            <a href="responses.php?demande_id=<?= $demand['id_demande'] ?>" 
                                                class="btn btn-sm btn-info" title="Voir réponses">
                                                    <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="financemet.php?edit_id=<?= $demand['id_demande'] ?>" 
                                               class="btn btn-sm btn-warning"
                                               title="Modifier">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="financemet.php?delete_id=<?= $demand['id_demande'] ?>" 
                                               class="btn btn-sm btn-danger" 
                                               onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette demande?')"
                                               title="Supprimer">
                                                <i class="fas fa-trash"></i>
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
        </section>

        <!-- Statistiques Section -->
        <section id="stats-section" class="stats-section">
            <h2 class="text-center mb-4"><i class="fas fa-chart-bar"></i> Statistiques</h2>
            <div class="stats-container">
                <div class="stats-card">
                    <h3>Total des Demandes</h3>
                    <div class="value"><?= $totalDemands ?></div>
                </div>
                <div class="stats-card">
                    <h3>Montant Total Demandé</h3>
                    <div class="value">€<?= number_format($totalAmountRequested, 2) ?></div>
                </div>
                <div class="stats-card">
                    <h3>Demandes Acceptées</h3>
                    <div class="value"><?= $totalAcceptedDemands ?></div>
                </div>
                <div class="stats-card">
                    <h3>Montant Accepté</h3>
                    <div class="value">€<?= number_format($totalAcceptedAmount, 2) ?></div>
                </div>
                <div class="stats-card">
                    <h3>Demandes Rejetées</h3>
                    <div class="value"><?= $totalRejectedDemands ?></div>
                </div>
                <div class="stats-card">
                    <h3>Demandes en Attente</h3>
                    <div class="value"><?= $totalPendingDemands ?></div>
                </div>
            </div>
            <div class="chart-container">
                <h3>Statut des Demandes</h3>
                <canvas id="statusChart"></canvas>
            </div>
            <div class="chart-container">
                <h3>Montant Total Demandé vs Accepté</h3>
                <canvas id="amountChart"></canvas>
            </div>
        </section>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/jsfinan.js"></script>
    <script>
        document.getElementById('btn-new-request').addEventListener('click', function () {
            document.getElementById('new-request-section').classList.add('active');
            document.getElementById('history-section').classList.remove('active');
            document.getElementById('stats-section').classList.remove('active');
        });

        document.getElementById('btn-history').addEventListener('click', function () {
            document.getElementById('history-section').classList.add('active');
            document.getElementById('new-request-section').classList.remove('active');
            document.getElementById('stats-section').classList.remove('active');
        });

        document.getElementById('btn-stats').addEventListener('click', function () {
            document.getElementById('stats-section').classList.add('active');
            document.getElementById('new-request-section').classList.remove('active');
            document.getElementById('history-section').classList.remove('active');
        });

        document.getElementById('btn-chatbot').addEventListener('click', function () {
            window.location.href = 'chatbot.php';
        });

        // Chart.js: Statut des Demandes
        const statusChartCtx = document.getElementById('statusChart').getContext('2d');
        new Chart(statusChartCtx, {
            type: 'doughnut',
            data: {
                labels: ['Acceptées', 'Rejetées', 'En Attente'],
                datasets: [{
                    data: [<?= $totalAcceptedDemands ?>, <?= $totalRejectedDemands ?>, <?= $totalPendingDemands ?>],
                    backgroundColor: ['#2ecc71', '#e74c3c', '#f39c12'],
                    borderColor: ['#27ae60', '#c0392b', '#d35400'],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            color: 'white'
                        }
                    }
                }
            }
        });

        // Chart.js: Montant Total Demandé vs Accepté
        const amountChartCtx = document.getElementById('amountChart').getContext('2d');
        new Chart(amountChartCtx, {
            type: 'bar',
            data: {
                labels: ['Montant Total Demandé', 'Montant Total Accepté'],
                datasets: [{
                    label: 'Montant (€)',
                    data: [<?= $totalAmountRequested ?>, <?= $totalAcceptedAmount ?>],
                    backgroundColor: ['#3498db', '#2ecc71'],
                    borderColor: ['#2980b9', '#27ae60'],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    x: {
                        ticks: {
                            color: 'white'
                        }
                    },
                    y: {
                        ticks: {
                            color: 'white'
                        },
                        beginAtZero: true
                    }
                }
            }
        });
    </script>
</body>
</html>