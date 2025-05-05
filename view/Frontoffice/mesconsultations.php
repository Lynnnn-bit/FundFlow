<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../Controller/consultationcontroller.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$controller = new ConsultationController();

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['delete_id'])) {
    try {
        $deleteId = $_GET['delete_id'];
        if ($controller->deleteConsultation($deleteId)) {
            $_SESSION['success'] = "Consultation et ses feedbacks associés supprimés avec succès!";
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = "Erreur lors de la suppression: " . $e->getMessage();
    }
    header("Location: mesconsultations.php");
    exit;
}

// Get all consultations
$allConsultations = $controller->getAllConsultations();

// Sorting logic
$sort_by_id_desc = isset($_GET['sort']) && $_GET['sort'] === 'id_desc';
if ($sort_by_id_desc) {
    usort($allConsultations, function ($a, $b) {
        return $b['id_consultation'] - $a['id_consultation'];
    });
}

$sort_by_id_asc = isset($_GET['sort']) && $_GET['sort'] === 'id_asc';
if ($sort_by_id_asc) {
    usort($allConsultations, function ($a, $b) {
        return $a['id_consultation'] - $b['id_consultation'];
    });
}

$sort_feedback_by_rate_desc = isset($_GET['sort_feedback']) && $_GET['sort_feedback'] === 'rate_desc';
if ($sort_feedback_by_rate_desc) {
    usort($allConsultations, function ($a, $b) use ($controller) {
        $rateA = $controller->getFeedbackRate($a['id_consultation']);
        $rateB = $controller->getFeedbackRate($b['id_consultation']);
        return $rateB - $rateA;
    });
}

// Search logic
if (isset($_GET['search_id']) && !empty($_GET['search_id'])) {
    $searchId = intval($_GET['search_id']);
    $filteredConsultations = array_filter($allConsultations, function ($consultation) use ($searchId) {
        return $consultation['id_consultation'] == $searchId;
    });

    if (empty($filteredConsultations)) {
        $error_message = "Veuillez indiquer un ID existant.";
    } else {
        $allConsultations = $filteredConsultations;
    }
}

// Pagination logic
$consultationsPerPage = 5;
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($currentPage < 1) {
    $currentPage = 1;
}

$totalConsultations = count($allConsultations);
$totalPages = ceil($totalConsultations / $consultationsPerPage);

// Ensure current page doesn't exceed total pages
if ($currentPage > $totalPages && $totalPages > 0) {
    $currentPage = $totalPages;
}

// Get consultations for current page
$offset = ($currentPage - 1) * $consultationsPerPage;
$consultations = array_slice($allConsultations, $offset, $consultationsPerPage);

// Stats calculation
$upcoming = 0;
$completed = 0;
$totalRevenue = 0;

foreach ($allConsultations as $consultation) {
    if (isset($consultation['date_consultation']) && !empty($consultation['date_consultation']) && $consultation['date_consultation'] !== '0000-00-00') {
        $heureDeb = isset($consultation['heure_deb']) && !empty($consultation['heure_deb'])
            ? strtotime($consultation['date_consultation'] . ' ' . $consultation['heure_deb'])
            : strtotime($consultation['date_consultation']);
    } else {
        $heureDeb = false;
    }

    if ($heureDeb && $heureDeb > time()) {
        $upcoming++;
    } elseif ($heureDeb) {
        $completed++;
    }

    $totalRevenue += isset($consultation['tarif']) ? floatval($consultation['tarif']) : 0;
}

$avgPrice = $totalConsultations > 0 ? number_format($totalRevenue / $totalConsultations, 2) : 0;
$totalRevenueFormatted = number_format($totalRevenue, 2);

$consultantCounts = [];
foreach ($allConsultations as $consultation) {
    $consultantId = $consultation['id_utilisateur1'];
    if (!isset($consultantCounts[$consultantId])) {
        $consultantCounts[$consultantId] = 0;
    }
    $consultantCounts[$consultantId]++;
}

$mostChosenConsultantId = !empty($consultantCounts) ? array_keys($consultantCounts, max($consultantCounts))[0] : null;
$mostChosenConsultant = $mostChosenConsultantId ? $controller->getUserById($mostChosenConsultantId) : null;

$success_message = isset($_SESSION['success']) ? $_SESSION['success'] : null;
$error_message = isset($_SESSION['error']) ? $_SESSION['error'] : null;
unset($_SESSION['success'], $_SESSION['error']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes Consultations</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/consultation.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels"></script>
    <style>
    /* Ajoutez ces styles dans la section <style> existante */
    .header-section h1 {
        margin-top: 20px;
        color: white; /* Texte en blanc */
    }
    
    .table-container {
        background-color: #2c3e50; /* Même couleur que le fond du body */
        border-radius: 8px;
        padding: 15px;
        margin-top: 20px;
    }
    
    .table {
        color: white; /* Texte du tableau en blanc */
        background-color: transparent; /* Fond transparent pour le tableau */
    }
    
    .table-dark {
        --bs-table-bg: #34495e; /* Couleur légèrement plus claire pour l'en-tête */
        --bs-table-striped-bg: #3d566e;
        --bs-table-striped-color: #fff;
        --bs-table-active-bg: #3d566e;
        --bs-table-active-color: #fff;
        --bs-table-hover-bg: #3d566e;
        --bs-table-hover-color: #fff;
        color: white;
        border-color: #455b73;
    }
    
    .table-striped > tbody > tr:nth-of-type(odd) {
        --bs-table-accent-bg: rgba(255, 255, 255, 0.05);
    }
    
    .page-link {
        background-color: #2c3e50;
        color: white;
        border-color: #34495e;
    }
    
    .page-item.active .page-link {
        background-color: #17a2b8;
        border-color: #17a2b8;
        color: white;
    }
    .search-container {
    margin-top: 40px; /* Décalage vers le bas */
}
.fixed-pagination {
    position: fixed;
    bottom: 0;
    left: 0;
    width: 100%;
    /* background-color: #fff; */ /* <-- supprimé */
    z-index: 1000;
    padding: 10px 0;
    box-shadow: 0 -2px 5px rgba(0, 0, 0, 0.1); /* toujours utile pour la lisibilité */
}


</style>
</head>
<body>
    <header class="navbar">
        <div class="logo-container">
            <span class="brand-name">FundFlow</span>
        </div>
        <nav>
            <a href="consultation.php"><i class="fas fa-calendar-plus"></i> Nouvelle Consultation</a>
            <a href="mesconsultations.php" class="active"><i class="fas fa-list"></i> Mes Consultations</a>
            <a href="#"><i class="fas fa-info-circle"></i> À propos</a>
            <a href="#"><i class="fas fa-envelope"></i> Contact</a>
            <a href="#" class="logout"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
        </nav>
    </header>

    <div class="main-container">
        <div class="header-section">
            <h1><i class="fas fa-calendar-check"></i> Mes Consultations</h1>
            <div class="action-buttons">
                <a href="mesconsultations.php?sort=id_desc" class="btn btn-primary btn-sm"><i class="fas fa-sort-amount-down"></i> Trier par ID décroissant</a>
                <a href="mesconsultations.php?sort=id_asc" class="btn btn-primary btn-sm"><i class="fas fa-sort-amount-up"></i> Trier par ID croissant</a>
                <button id="showStatsBtn" class="btn btn-info btn-sm"><i class="fas fa-chart-pie"></i> Afficher les statistiques</button>
            </div>

            <?php if (!empty($success_message)): ?>
                <div class="alert alert-success"><?= htmlspecialchars($success_message) ?></div>
            <?php endif; ?>
            <?php if (!empty($error_message)): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error_message) ?></div>
            <?php endif; ?>
        </div>

        <div id="consultationStats" style="display: none;">
    <h3 style="color: white;"><i class="fas fa-chart-pie"></i> Tableau de Bord des Consultations</h3>
    <div class="stats-cards-container" style="display: flex; justify-content: space-around; margin-top: 20px;">
        <div class="stats-card" style="background-color: #17a2b8; color: white; padding: 20px; border-radius: 8px; text-align: center; width: 200px;">
            <h4>Total Consultations</h4>
            <p style="font-size: 24px; font-weight: bold;"><?= $totalConsultations ?></p>
        </div>
        <div class="stats-card" style="background-color: #28a745; color: white; padding: 20px; border-radius: 8px; text-align: center; width: 200px;">
            <h4>Consultations Terminées</h4>
            <p style="font-size: 24px; font-weight: bold;"><?= $completed ?></p>
        </div>
        <div class="stats-card" style="background-color: #ffc107; color: black; padding: 20px; border-radius: 8px; text-align: center; width: 200px;">
            <h4>Consultations à Venir</h4>
            <p style="font-size: 24px; font-weight: bold;"><?= $upcoming ?></p>
        </div>
        <?php if ($mostChosenConsultant): ?>
        <div class="stats-card" style="background-color: #6f42c1; color: white; padding: 20px; border-radius: 8px; text-align: center; width: 200px;">
            <h4>Consultant le Plus Choisi</h4>
            <p style="font-size: 18px; font-weight: bold;"><?= htmlspecialchars($mostChosenConsultant['prenom'] . ' ' . $mostChosenConsultant['nom']) ?></p>
        </div>
        <?php endif; ?>
    </div>
</


        <div class="search-container" style="margin-top: 20px; text-align: right;">
            <form method="GET" action="mesconsultations.php">
                <div class="input-group" style="justify-content: flex-end; max-width: 300px;">
                    <input type="text" name="search_id" class="form-control" placeholder="Rechercher par ID" value="<?= isset($_GET['search_id']) ? htmlspecialchars($_GET['search_id']) : '' ?>">
                    <button type="submit" class="btn btn-primary">Rechercher</button>
                </div>
            </form>
        </div>

        <div class="table-container">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Consultant</th>
                            <th>Client</th>
                            <th>Date</th>
                            <th>Heure début</th>
                            <th>Heure fin</th>
                            <th>Tarif</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($consultations as $consultation): ?>
                            <?php
                            $consultant = $controller->getUserById($consultation['id_utilisateur1']);
                            $client = $controller->getUserById($consultation['id_utilisateur2']);
                            
                            $consultation_date = 'Non spécifiée';
                            if (isset($consultation['date_consultation']) && !empty($consultation['date_consultation']) && $consultation['date_consultation'] !== '0000-00-00') {
                                try {
                                    $date_obj = new DateTime($consultation['date_consultation']);
                                    $consultation_date = $date_obj->format('d/m/Y');
                                } catch (Exception $e) {
                                    $consultation_date = 'Date invalide';
                                }
                            }
                            
                            $heure_deb = isset($consultation['date_consultation'], $consultation['heure_deb']) 
                                ? strtotime($consultation['date_consultation'] . ' ' . $consultation['heure_deb']) 
                                : 0;
                            $status_class = ($heure_deb > time()) ? 'bg-warning' : 'bg-success';
                            $status_text = ($heure_deb > time()) ? 'À venir' : 'Terminée';
                            ?>
                            <tr>
                                <td><?= htmlspecialchars($consultation['id_consultation']) ?></td>
                                <td><?= htmlspecialchars($consultant['prenom'] . ' ' . $consultant['nom']) ?></td>
                                <td><?= htmlspecialchars($client['prenom'] . ' ' . $client['nom']) ?></td>
                                <td><?= $consultation_date ?></td>
                                <td><?= htmlspecialchars(isset($consultation['heure_deb']) ? substr($consultation['heure_deb'], 0, 5) : 'Non spécifiée') ?></td>
                                <td><?= htmlspecialchars(isset($consultation['heure_fin']) ? substr($consultation['heure_fin'], 0, 5) : 'Non spécifiée') ?></td>
                                <td><?= number_format(isset($consultation['tarif']) ? $consultation['tarif'] : 0, 2) ?> €</td>
                                <td>
                                    <span class="badge <?= $status_class ?>"><?= $status_text ?></span>
                                </td>
                                <td class="action-buttons">
                                    <a href="editconsultation.php?id=<?= htmlspecialchars($consultation['id_consultation']) ?>"
                                       class="btn btn-sm"
                                       style="background-color: #006400; color: white;"
                                       title="Modifier">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="mesconsultations.php?delete_id=<?= htmlspecialchars($consultation['id_consultation']) ?>"
                                       class="btn btn-danger btn-sm"
                                       onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette consultation?')"
                                       title="Supprimer">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                    <a href="../Backoffice/addfeedback.php?id_consultation=<?= urlencode($consultation['id_consultation']) ?>"
                                       class="btn btn-warning btn-sm"
                                       title="Ajouter un Feedback">
                                        <i class="fas fa-comment-alt"></i> Ajouter un Feedback
                                    </a>
                                    <a href="visio.php?room=consultation_<?= urlencode($consultation['id_consultation']) ?>"
   class="btn btn-primary btn-sm"
   title="Rejoindre la visio">
    <i class="fas fa-video"></i> Visioconférence
</a>

                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
    <div class="fixed-pagination d-flex justify-content-center">
        <nav aria-label="Page navigation">
            <ul class="pagination">
                <?php if ($currentPage > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?= $currentPage - 1 ?><?= isset($_GET['sort']) ? '&sort=' . htmlspecialchars($_GET['sort']) : '' ?><?= isset($_GET['search_id']) ? '&search_id=' . htmlspecialchars($_GET['search_id']) : '' ?>" aria-label="Previous">
                            <span aria-hidden="true">&laquo;</span>
                        </a>
                    </li>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <li class="page-item <?= $i == $currentPage ? 'active' : '' ?>">
                        <a class="page-link" href="?page=<?= $i ?><?= isset($_GET['sort']) ? '&sort=' . htmlspecialchars($_GET['sort']) : '' ?><?= isset($_GET['search_id']) ? '&search_id=' . htmlspecialchars($_GET['search_id']) : '' ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>

                <?php if ($currentPage < $totalPages): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?= $currentPage + 1 ?><?= isset($_GET['sort']) ? '&sort=' . htmlspecialchars($_GET['sort']) : '' ?><?= isset($_GET['search_id']) ? '&search_id=' . htmlspecialchars($_GET['search_id']) : '' ?>" aria-label="Next">
                            <span aria-hidden="true">&raquo;</span>
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>
<?php endif; ?>

    <div class="export-container" style="text-align: center; margin-top: 20px;">
        <a href="../../Controller/exportconsultations.php" class="btn btn-success btn-sm" target="_blank">
            <i class="fas fa-file-pdf"></i> Exporter en PDF
        </a>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
document.addEventListener('DOMContentLoaded', () => {
    const showStatsBtn = document.getElementById('showStatsBtn');
    const statsDiv = document.getElementById('consultationStats');

    showStatsBtn.addEventListener('click', (e) => {
        e.preventDefault();
        const isHidden = statsDiv.style.display === 'none';
        statsDiv.style.display = isHidden ? 'block' : 'none';
        showStatsBtn.innerHTML = isHidden
            ? '<i class="fas fa-chart-pie"></i> Masquer les statistiques'
            : '<i class="fas fa-chart-pie"></i> Afficher les statistiques';
    });

    // Revenu total et tarif moyen
    const revenueTotal = <?= $totalRevenue ?>;
    const avgTarif = <?= $avgPrice ?>;

    // Données supplémentaires si disponibles
    const nbConsultations = <?= isset($consultationCount) ? $consultationCount : 0 ?>;

    // Graphe 1 : Répartition du revenu et tarif moyen
    const revenueCtx = document.getElementById('revenueChart').getContext('2d');
    new Chart(revenueCtx, {
        type: 'doughnut',
        data: {
            labels: ['Revenu Total (€)', 'Tarif Moyen (€)'],
            datasets: [{
                data: [revenueTotal, avgTarif],
                backgroundColor: ['#28a745', '#17a2b8'],
                hoverBackgroundColor: ['#218838', '#138496'],
                borderWidth: 1,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'bottom' },
                tooltip: {
                    callbacks: {
                        label: (ctx) => `${ctx.label}: ${ctx.raw.toFixed(2)} €`
                    }
                }
            }
        }
    });

    // Graphe 2 : Nombre total de consultations (si utile)
    const countCtx = document.getElementById('countChart').getContext('2d');
    new Chart(countCtx, {
        type: 'bar',
        data: {
            labels: ['Nombre de consultations'],
            datasets: [{
                label: 'Consultations',
                data: [nbConsultations],
                backgroundColor: '#ffc107',
                borderColor: '#e0a800',
                borderWidth: 1
            }]
        },
        options: {
            indexAxis: 'y',
            scales: {
                x: {
                    beginAtZero: true
                }
            },
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: (ctx) => `${ctx.raw} consultations`
                    }
                }
            }
        }
    });
});
</script>

</body>
</html>