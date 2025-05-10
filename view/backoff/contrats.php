<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../control/ContratController.php';
require_once __DIR__ . '/../../control/PartenaireController.php';

session_start();

$contratController = new ContratController();
$partenaireController = new PartenaireController();

// Handle search inputs for "Demandes de Partenariat"
$searchPartenaireId = $_GET['search_partenaire_id'] ?? null;
$sortOrder = $_GET['sort_order'] ?? 'created_at DESC'; // Default sort order

// Add sorting by montant
if (isset($_GET['sort_by']) && $_GET['sort_by'] === 'montant') {
    $sortOrder = 'montant ' . ($_GET['sort_direction'] === 'asc' ? 'ASC' : 'DESC');
}

if ($searchPartenaireId) {
    $unapprovedPartenaires = [$partenaireController->getPartenaire($searchPartenaireId)];
    $unapprovedPartenaires = array_filter($unapprovedPartenaires); // Remove null results
} else {
    $unapprovedPartenaires = $partenaireController->getUnapprovedPartenaires($sortOrder);
}

// Handle advanced search filters for "Demandes de Partenariat"
$searchPartenaireType = $_GET['search_partenaire_type'] ?? null;
$searchPartenaireId = $_GET['search_partenaire_id'] ?? null;
$searchPartenaireNom = $_GET['search_partenaire_nom'] ?? null;
$searchMontantMin = $_GET['search_montant_min'] ?? null;
$searchMontantMax = $_GET['search_montant_max'] ?? null;

if ($searchPartenaireType === 'id' && $searchPartenaireId) {
    $unapprovedPartenaires = [$partenaireController->getPartenaire($searchPartenaireId)];
    $unapprovedPartenaires = array_filter($unapprovedPartenaires); // Remove null results
} elseif ($searchPartenaireType === 'nom' && $searchPartenaireNom) {
    $unapprovedPartenaires = $partenaireController->filterPartenairesByName($searchPartenaireNom);
} elseif ($searchPartenaireType === 'montant_range' && ($searchMontantMin || $searchMontantMax)) {
    $unapprovedPartenaires = $partenaireController->filterPartenairesByMontantRange($searchMontantMin, $searchMontantMax);
} else {
    $unapprovedPartenaires = $partenaireController->getUnapprovedPartenaires($sortOrder);
}

// Update expired contracts
$contratController->updateExpiredContracts();

// Handle advanced search filters for "Contrats"
$searchType = $_GET['search_type'] ?? null;
$searchContratId = $_GET['search_contrat_id'] ?? null;
$searchDateStart = $_GET['search_date_start'] ?? null;
$searchDateEnd = $_GET['search_date_end'] ?? null;
$searchStatus = $_GET['search_status'] ?? null;

if ($searchType === 'id' && $searchContratId) {
    $contrats = [$contratController->getContract($searchContratId)];
    $contrats = array_filter($contrats); // Remove null results
} elseif ($searchType === 'status' && $searchStatus) {
    $contrats = $contratController->filterContractsByAdvancedSearch(null, null, $searchStatus);
} elseif ($searchType === 'date_range' && ($searchDateStart || $searchDateEnd)) {
    $contrats = $contratController->filterContractsByAdvancedSearch($searchDateStart, $searchDateEnd, null);
} else {
    $contrats = $contratController->getAllContracts();
}

// Get all approved partners for adding new contracts
$approvedPartenaires = $partenaireController->getAllApprovedPartenaires();

// Fetch contract statistics
$contractStats = $contratController->getContractStatistics();

$today = new DateTime();
$expiryThreshold = (clone $today)->modify('+30 days');
$expiringContracts = [];

foreach ($contrats as $contrat) {
    $dateFin = new DateTime($contrat['date_fin']);
    if ($dateFin <= $expiryThreshold && $dateFin >= $today) {
        $expiringContracts[] = $contrat;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Partenaires et Contrats</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Base Styles */
        body {
            margin: 0;
            font-family: 'Inter', sans-serif;
            background: linear-gradient(to right, #0f2027, #203a43, #2c5364);
            color: white;
            min-height: 100vh;
        }

        /* Navbar Styles */
        .navbar {
            background-color: rgba(15, 32, 39, 0.9);
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.3);
        }

        .logo-container {
            display: flex;
            align-items: center;
        }

        .brand-name {
            color: white;
            font-size: 1.5rem;
            font-weight: 600;
            margin-left: 10px;
            background: linear-gradient(to right, #00d09c, #1abc9c);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .navbar a {
            color: #cbd5e1;
            text-decoration: none;
            margin-left: 20px;
            font-size: 0.95rem;
            transition: color 0.3s;
        }

        .navbar a:hover {
            color: #00d09c;
        }

        .navbar a.logout:hover {
            color: #e74c3c;
        }

        .navbar i {
            margin-right: 8px;
        }

        /* Main Container */
        .main-container {
            padding: 2rem;
            max-width: 1400px;
            margin: 0 auto;
        }

        .header-section {
            margin-bottom: 2rem;
        }

        .header-section h1 {
            margin-bottom: 1.5rem;
            font-size: 1.8rem;
            display: flex;
            align-items: center;
            color: white;
        }

        .header-section h1 i {
            margin-right: 15px;
            color: #00d09c;
        }

        /* Card Styles */
        .card {
            background: rgba(30, 60, 82, 0.6);
            border-radius: 16px;
            padding: 2rem;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.3);
            margin-bottom: 2rem;
            backdrop-filter: blur(5px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        /* Table Styles */
        .table-container {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            color: white;
        }

        th, td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        th {
            background-color: rgba(15, 32, 39, 0.8);
            color: white;
            font-weight: 600;
            position: sticky;
            top: 0;
        }

        tr:hover {
            background-color: rgba(46, 79, 102, 0.4);
        }

        /* Badge Styles */
        .badge {
            display: inline-block;
            padding: 0.4rem 0.8rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: capitalize;
        }

        .badge-success {
            background: linear-gradient(to right, #00d09c, #2ecc71);
            color: white;
        }

        .badge-warning {
            background: linear-gradient(to right, #f39c12, #e67e22);
            color: white;
        }

        .badge-danger {
            background: linear-gradient(to right, #e74c3c, #c0392b);
            color: white;
        }

        .badge-secondary {
            background: linear-gradient(to right, #95a5a6, #7f8c8d);
            color: white;
        }

        /* Alert Messages */
        .alert {
            padding: 0.8rem 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
        }

        .alert-success {
            background-color: rgba(46, 204, 113, 0.2);
            color: #d4edda;
            border: 1px solid rgba(46, 204, 113, 0.3);
        }

        .alert-danger {
            background-color: rgba(231, 76, 60, 0.2);
            color: #f8d7da;
            border: 1px solid rgba(231, 76, 60, 0.3);
        }

        /* Button Styles */
        .btn {
            padding: 0.6rem 1.2rem;
            border: none;
            border-radius: 8px;
            font-size: 0.9rem;
            cursor: pointer;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
        }

        .btn i {
            margin-right: 8px;
        }

        .btn-primary {
            background: linear-gradient(to right, #00d09c, #1abc9c);
            color: white;
        }

        .btn-primary:hover {
            background: linear-gradient(to right, #1abc9c, #3498db);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 208, 156, 0.3);
        }

        .btn-success {
            background: linear-gradient(to right, #2ecc71, #27ae60);
            color: white;
        }

        .btn-success:hover {
            background: linear-gradient(to right, #27ae60, #219653);
        }

        .btn-danger {
            background: linear-gradient(to right, #e74c3c, #c0392b);
            color: white;
        }

        .btn-danger:hover {
            background: linear-gradient(to right, #c0392b, #a5281b);
        }

        .btn-info {
            background: linear-gradient(to right, #3498db, #2980b9);
            color: white;
        }

        .btn-info:hover {
            background: linear-gradient(to right, #2980b9, #1abc9c);
        }

        .btn-sm {
            padding: 0.4rem 0.8rem;
            font-size: 0.8rem;
        }

        .btn-group {
            display: flex;
            gap: 0.5rem;
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(5px);
        }

        .modal-content {
            background: rgba(30, 60, 82, 0.9);
            margin: 5% auto;
            padding: 2rem;
            border-radius: 16px;
            box-shadow: 0 0 30px rgba(0, 0, 0, 0.4);
            width: 60%;
            max-width: 700px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: white;
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
            transition: color 0.3s;
        }

        .close:hover {
            color: white;
        }

        /* Form Styles */
        .form-group {
            margin-bottom: 1.2rem;
        }

        .form-label {
            display: block;
            margin-bottom: 0.6rem;
            font-weight: 500;
            color: #cbd5e1;
            font-size: 0.9rem;
        }

        .form-control, .form-select {
            width: 100%;
            padding: 0.8rem 1rem;
            border: none;
            border-radius: 8px;
            font-size: 0.95rem;
            transition: all 0.3s;
            background-color: rgba(46, 79, 102, 0.8);
            color: white;
        }

        .form-control:focus, .form-select:focus {
            outline: none;
            box-shadow: 0 0 0 2px rgba(0, 208, 156, 0.3);
            background-color: rgba(46, 79, 102, 1);
        }

        textarea.form-control {
            min-height: 120px;
        }

        /* Responsive Adjustments */
        @media (max-width: 768px) {
            .main-container {
                padding: 1rem;
            }
            
            .modal-content {
                width: 90%;
                margin: 10% auto;
            }
            
            .btn-group {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
                margin-bottom: 0.5rem;
            }
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar">
        <div class="logo-container">
        <img src="assets/logo.png" alt="Logo">
            <i class="fas fa-file-contract fa-lg" style="color: #00d09c;"></i>
            
        </div>
        <nav>
            <a href="/"><i class="fas fa-home"></i> Accueil</a>
            <a href="/partenaires"><i class="fas fa-users"></i> Partenaires</a>
            <a href="/logout" class="logout"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
        </nav>
    </nav>
    

    <div class="main-container">
        <!-- Expiring Contracts Notification -->
        <div class="alert text-center" style="background: linear-gradient(to right,rgb(32, 52, 90),rgb(57, 82, 105)); color: white; border: none;">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <strong><?= count($expiringContracts) ?></strong> contrat(s) expirent dans les 30 prochains jours.
            <?php if (!empty($expiringContracts)): ?>
                <ul class="mt-2">
                    <?php foreach ($expiringContracts as $contrat): ?>
                        <li>
                            <strong>ID:</strong> <?= htmlspecialchars($contrat['id_contrat']) ?>, 
                            <strong>Nom Partenaire:</strong> <?= htmlspecialchars($contrat['partenaire_nom']) ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>

        <div class="header-section">
            <h1><i class="fas fa-handshake"></i> Gestion des Partenaires et Contrats</h1>
        </div>

        

        <!-- Download PDF Button -->
         
        <div class="mb-4">
            <a href="download_pdf.php" class="btn btn-primary">
                <i class="fas fa-file-pdf"></i> Télécharger le PDF
            </a>
        </div>
       

        

        <!-- Advanced Search Form for Demandes de Partenariat -->
        <div class="card mb-4">
            <h2><i class="fas fa-search me-2"></i>Recherche Avancée des Demandes de Partenariat</h2>
            <form method="GET" class="mb-4">
                <div class="row">
                    <div class="col-md-4">
                        <label for="search_partenaire_type" class="form-label">Type de Recherche</label>
                        <select class="form-select" id="search_partenaire_type" name="search_partenaire_type" onchange="togglePartenaireSearchFields()">
                            <option value="">Sélectionnez</option>
                            <option value="id" <?= ($searchPartenaireType === 'id') ? 'selected' : '' ?>>Par ID</option>
                            <option value="nom" <?= ($searchPartenaireType === 'nom') ? 'selected' : '' ?>>Par Nom</option>
                            <option value="montant_range" <?= ($searchPartenaireType === 'montant_range') ? 'selected' : '' ?>>Par Intervalle de Montant</option>
                        </select>
                    </div>
                </div>
                
                

                <div class="row mt-3" id="search_partenaire_by_id" style="display: none;">
                    <div class="col-md-6">
                        <label for="search_partenaire_id" class="form-label">ID Partenaire</label>
                        <input type="text" class="form-control" id="search_partenaire_id" name="search_partenaire_id" value="<?= htmlspecialchars($searchPartenaireId) ?>">
                    </div>
                </div>

                <div class="row mt-3" id="search_partenaire_by_nom" style="display: none;">
                    <div class="col-md-6">
                        <label for="search_partenaire_nom" class="form-label">Nom Partenaire</label>
                        <input type="text" class="form-control" id="search_partenaire_nom" name="search_partenaire_nom" value="<?= htmlspecialchars($searchPartenaireNom) ?>">
                    </div>
                </div>

                <div class="row mt-3" id="search_partenaire_by_montant_range" style="display: none;">
                    <div class="col-md-4">
                        <label for="search_montant_min" class="form-label">Montant Minimum</label>
                        <input type="number" class="form-control" id="search_montant_min" name="search_montant_min" value="<?= htmlspecialchars($searchMontantMin) ?>">
                    </div>
                    <div class="col-md-4">
                        <label for="search_montant_max" class="form-label">Montant Maximum</label>
                        <input type="number" class="form-control" id="search_montant_max" name="search_montant_max" value="<?= htmlspecialchars($searchMontantMax) ?>">
                    </div>
                </div>

                <div class="mt-3">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Rechercher</button>
                </div>
            </form>
        </div>

        <!-- Partner Requests Section -->
        <div class="card">
            <h2><i class="fas fa-user-clock me-2"></i>Demandes de Partenariat</h2>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nom</th>
                            <th>Email</th>
                            <th>Téléphone</th>
                            <th>
                                <a href="?sort_by=montant&sort_direction=asc" class="text-white text-decoration-none">
                                    Montant <i class="fas fa-sort-up"></i>
                                </a>
                                <a href="?sort_by=montant&sort_direction=desc" class="text-white text-decoration-none">
                                    <i class="fas fa-sort-down"></i>
                                </a>
                            </th>
                            <th>Description</th>
                            <th>Créé le</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($unapprovedPartenaires as $partner): ?>
                        <tr>
                            <td><?= htmlspecialchars($partner['id_partenaire']) ?></td>
                            <td><?= htmlspecialchars($partner['nom']) ?></td>
                            <td><?= htmlspecialchars($partner['email']) ?></td>
                            <td><?= htmlspecialchars($partner['telephone']) ?></td>
                            <td><?= htmlspecialchars($partner['montant']) ?> €</td>
                            <td><?= htmlspecialchars($partner['description']) ?></td>
                            <td><?= date('d/m/Y H:i', strtotime($partner['created_at'])) ?></td>
                            <td>
                                <div class="btn-group">
                                    <form method="POST" action="auto_approve.php">
                                        <input type="hidden" name="id_partenaire" value="<?= $partner['id_partenaire'] ?>">
                                        <button type="submit" class="btn btn-success btn-sm">
                                            <i class="fas fa-check"></i> Approuver
                                        </button>
                                    </form>
                                    <form method="POST" action="auto_approve.php">
                                        <input type="hidden" name="id_partenaire" value="<?= $partner['id_partenaire'] ?>">
                                        <button type="submit" name="reject_partner" class="btn btn-danger btn-sm" 
                                        onclick="return confirm('Voulez-vous vraiment rejeter cette demande?')">
                                            <i class="fas fa-times"></i> Rejeter
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($unapprovedPartenaires)): ?>
                        <tr>
                            <td colspan="8" style="text-align:center;">Aucune demande de partenariat en attente</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Advanced Search Form -->
        <div class="card mb-4 shadow-sm rounded-4">
            <div class="card-body">
                <h2 class="mb-4"><i class="fas fa-search me-2"></i>Recherche Avancée</h2>
                <form method="GET">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label for="search_type" class="form-label">Type de Recherche</label>
                            <select class="form-select" id="search_type" name="search_type" onchange="toggleSearchFields()">
                                <option value="">Sélectionnez</option>
                                <option value="id" <?= ($searchType === 'id') ? 'selected' : '' ?>>Par ID</option>
                                <option value="status" <?= ($searchType === 'status') ? 'selected' : '' ?>>Par Statut</option>
                                <option value="date_range" <?= ($searchType === 'date_range') ? 'selected' : '' ?>>Par Intervalle de Dates</option>
                            </select>
                        </div>
                    </div>

                    <div class="row mt-3" id="search_by_id" style="display: none;">
                        <div class="col-md-6">
                            <label for="search_contrat_id" class="form-label">ID Contrat</label>
                            <input type="text" class="form-control" id="search_contrat_id" name="search_contrat_id" value="<?= htmlspecialchars($searchContratId) ?>">
                        </div>
                    </div>

                    <div class="row mt-3" id="search_by_status" style="display: none;">
                        <div class="col-md-6">
                            <label for="search_status" class="form-label">Statut</label>
                            <select class="form-select" id="search_status" name="search_status">
                                <option value="">Tous</option>
                                <option value="actif" <?= ($searchStatus === 'actif') ? 'selected' : '' ?>>Actif</option>
                                <option value="en attente" <?= ($searchStatus === 'en attente') ? 'selected' : '' ?>>En attente</option>
                                <option value="expiré" <?= ($searchStatus === 'expiré') ? 'selected' : '' ?>>Expiré</option>
                            </select>
                        </div>
                    </div>

                    <div class="row mt-3" id="search_by_date_range" style="display: none;">
                        <div class="col-md-4">
                            <label for="search_date_start" class="form-label">Date Début</label>
                            <input type="date" class="form-control" id="search_date_start" name="search_date_start" value="<?= htmlspecialchars($searchDateStart) ?>">
                        </div>
                        <div class="col-md-4">
                            <label for="search_date_end" class="form-label">Date Fin</label>
                            <input type="date" class="form-control" id="search_date_end" name="search_date_end" value="<?= htmlspecialchars($searchDateEnd) ?>">
                        </div>
                    </div>

                    <div class="mt-3">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Rechercher</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card mb-4 shadow-sm rounded-4">
            <div class="card-body text-center">
                <h2><i class="fas fa-microphone me-2"></i>Recherche Vocale</h2>
                <p class="text-muted">Utilisez votre voix pour rechercher rapidement un contrat</p>
                <button id="voiceSearchBtn" class="btn btn-outline-primary btn-lg px-4 rounded-pill">
                    <i class="fas fa-microphone"></i> Commencer à parler
                </button>
                <div id="voiceStatus" class="mt-2 text-secondary small"></div>
            </div>
        </div>

        <!-- Résultats de la recherche vocale -->
        <div id="voiceSearchResults" class="card shadow-sm rounded-4 mb-4" style="display: none;">
            <div class="card-body">
                <h3 class="mb-3"><i class="fas fa-list me-2"></i>Résultats de la recherche vocale</h3>
                <div id="contractDetails"></div>
            </div>
        </div>

        <!-- Contracts Section -->
        <div class="card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                <h2><i class="fas fa-file-signature me-2"></i>Contrats</h2>
                <div>
                    <a href="contrats_stats.php" class="btn btn-primary me-2">
                        <i class="fas fa-chart-pie me-2"></i> Voir les Statistiques des Contrats
                    </a>
                    <button onclick="openModal('addModal')" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Nouveau Contrat
                    </button>
                </div>
            </div>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Partenaire</th>
                            <th>Date Début</th>
                            <th>Date Fin</th>
                            <th>Statut</th>
                            <th>Créé le</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($contrats as $contrat): ?>
                        <tr>
                            <td><?= htmlspecialchars($contrat['id_contrat']) ?></td>
                            <td><?= htmlspecialchars($contrat['partenaire_nom'] ?? 'N/A') ?></td>
                            <td><?= date('d/m/Y', strtotime($contrat['date_deb'])) ?></td>
                            <td><?= date('d/m/Y', strtotime($contrat['date_fin'])) ?></td>
                            <td>
                                <span class="badge badge-<?= 
                                    $contrat['status'] === 'actif' ? 'success' : 
                                    ($contrat['status'] === 'en attente' ? 'warning' : 'danger') 
                                ?>">
                                    <?= ucfirst($contrat['status']) ?>
                                </span>
                            </td>
                            <td><?= date('d/m/Y H:i', strtotime($contrat['created_at'])) ?></td>
                            <td>
                                <div class="btn-group">
                                    <button onclick="openEditModal(<?= $contrat['id_contrat'] ?>)" class="btn btn-info btn-sm">
                                        <i class="fas fa-edit"></i> Modifier
                                    </button>
                                    <form method="POST" style="display:inline;" action="supprimer.php">
                                        <input type="hidden" name="id_contrat" value="<?= $contrat['id_contrat'] ?>">
                                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Supprimer ce contrat?')">
                                            <i class="fas fa-trash"></i> Supprimer
                                        </button>
                                    </form>
                                    <button class="btn btn-secondary btn-sm" onclick="generateQrCode(<?= $contrat['id_contrat'] ?>)">
                                        <i class="fas fa-qrcode"></i> QR Code
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($contrats)): ?>
                        <tr>
                            <td colspan="7" style="text-align:center;">Aucun contrat trouvé</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Add Contract Modal -->
    <div id="addModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('addModal')">&times;</span>
            <h2><i class="fas fa-plus-circle me-2"></i>Nouveau Contrat</h2>
            <form method="POST" action="ajouter.php">
                <input type="hidden" name="add_contract" value="1">
                <div class="form-group">
                    <label class="form-label">Partenaire:</label>
                    <select name="id_partenaire" class="form-control" >
                        <?php foreach ($approvedPartenaires as $partenaire): ?>
                        <option value="<?= $partenaire['id_partenaire'] ?>"><?= htmlspecialchars($partenaire['nom']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Date Début:</label>
                    <input type="date" name="date_deb" class="form-control" >
                </div>
                <div class="form-group">
                    <label class="form-label">Date Fin:</label>
                    <input type="date" name="date_fin" class="form-control" >
                </div>
                <div class="form-group">
                    <label class="form-label">Statut:</label>
                    <select name="status" class="form-control" >
                        <option value="en attente">En attente</option>
                        <option value="actif">Actif</option>
                        <option value="expiré">Expiré</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Termes:</label>
                    <textarea name="terms" rows="4" class="form-control"></textarea>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Enregistrer
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Contract Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('editModal')">&times;</span>
            <h2><i class="fas fa-edit me-2"></i>Modifier Contrat</h2>
            <form method="POST" id="editForm">
                <input type="hidden" name="update_contract" value="1">
                <input type="hidden" name="id_contrat" id="edit_id_contrat">
                
                <div class="form-group">
                    <label class="form-label">Date Début:</label>
                    <input type="date" name="date_deb" id="edit_date_deb" class="form-control" 
                </div>
                
                <div class="form-group">
                    <label class="form-label">Date Fin:</label>
                    <input type="date" name="date_fin" id="edit_date_fin" class="form-control" 
                </div>
                
                <div class="form-group">
                    <label class="form-label">Statut:</label>
                    <select name="status" id="edit_status" class="form-control" 
                        <option value="expiré">Expiré</option>
                        <option value="actif">Actif</option>
                        <option value="en attente">En attente</option>
                        
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Termes:</label>
                    <textarea name="terms" id="edit_terms" rows="4" class="form-control"></textarea>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Mettre à jour
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
    // Fonction pour ouvrir la modale d'édition
    async function openEditModal(contratId) {
        try {
            // Récupérer les données du contrat
            const response = await fetch(`get_contract.php?id=${contratId}`);
            
            if (!response.ok) {
                throw new Error(`Erreur HTTP: ${response.status}`);
            }
            
            const data = await response.json();
            
            if (data.error) {
                throw new Error(data.error);
            }
            
            // Remplir le formulaire
            document.getElementById('edit_id_contrat').value = data.id_contrat;
            document.getElementById('edit_date_deb').value = data.date_deb.split(' ')[0]; // Format YYYY-MM-DD
            document.getElementById('edit_date_fin').value = data.date_fin.split(' ')[0];
            document.getElementById('edit_status').value = data.status;
            document.getElementById('edit_terms').value = data.terms || '';
            
            // Ouvrir la modale
            openModal('editModal');
            
        } catch (error) {
            console.error('Erreur:', error);
            alert('Erreur lors du chargement du contrat: ' + error.message);
        }
    }

    // Gestion de la soumission du formulaire
    document.getElementById('editForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    // Afficher un indicateur de chargement
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> En cours...';
    submitBtn.disabled = true;
    
    try {
        const formData = new FormData(this);
        const response = await fetch('modifier.php', {
            method: 'POST',
            body: formData
        });
        
        // Vérifier si la réponse est JSON
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            const text = await response.text();
            throw new Error(`Réponse inattendue: ${text.substring(0, 100)}...`);
        }
        
        const result = await response.json();
        
        if (!response.ok || result.error) {
            throw new Error(result.error || 'Erreur inconnue');
        }
        
       
        
    } catch (error) {
        console.error('Erreur:', error);
        alert('Erreur lors de la mise à jour: ' + error.message);
    } finally {
        
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    }
});

    async function generateQrCode(contratId) {
        try {
            const response = await fetch('qrcode.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ id_contrat: contratId })
            });

            const result = await response.json();

            if (!result.success) {
                throw new Error(result.error || 'Erreur lors de la génération du QR Code');
            }

            // Display the QR code in a smaller modal
            const qrModal = document.createElement('div');
            qrModal.className = 'modal';
            qrModal.style.display = 'block';
            qrModal.innerHTML = `
                <div class="modal-content" style="width: 300px; padding: 1rem; text-align: center;">
                    <span class="close" onclick="this.parentElement.parentElement.remove()" style="cursor: pointer;">&times;</span>
                    <h5>QR Code</h5>
                    <p><strong>Partenaire:</strong> ${result.partenaire}</p>
                    <img src="${result.qr_url}" alt="QR Code" style="width: 200px; height: 200px;">
                </div>
            `;
            document.body.appendChild(qrModal);
        } catch (error) {
            alert(error.message);
        }
    }
    
    function openModal(modalId) {
        document.getElementById(modalId).style.display = 'block';
    }

    function closeModal(modalId) {
        document.getElementById(modalId).style.display = 'none';
    }

    
    window.onclick = function(event) {
        if (event.target.className === 'modal') {
            event.target.style.display = 'none';
        }
    }
   
document.querySelector('#addModal form').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const form = this;
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    
    // Afficher le loader
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> En cours...';
    submitBtn.disabled = true;
    
    try {
        const formData = new FormData(form);
        const response = await fetch(form.action, {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (!result.success) {
            throw new Error(result.message || 'Erreur lors de la création');
        }
        
        alert(result.message);
        closeModal('addModal');
        window.location.reload(); // Recharger pour voir le nouveau contrat
        
    } catch (error) {
        console.error('Erreur:', error);
        alert(error.message);
    } finally {
        // Restaurer le bouton
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    }
});

document.addEventListener('DOMContentLoaded', function () {
    const editForm = document.getElementById('editForm');
    const dateDebInput = document.getElementById('edit_date_deb');
    const dateFinInput = document.getElementById('edit_date_fin');

    // Helper function to show error
    function showError(input, message) {
        const formGroup = input.closest('.form-group');
        let errorElement = formGroup.querySelector('.error-message');
        if (!errorElement) {
            errorElement = document.createElement('div');
            errorElement.className = 'error-message text-danger mt-1';
            formGroup.appendChild(errorElement);
        }
        errorElement.textContent = message;
        input.classList.add('is-invalid');
    }

    // Helper function to clear error
    function clearError(input) {
        const formGroup = input.closest('.form-group');
        const errorElement = formGroup.querySelector('.error-message');
        if (errorElement) {
            errorElement.remove();
        }
        input.classList.remove('is-invalid');
    }

    // Validation functions
    function validateDateDeb() {
        const dateDeb = dateDebInput.value.trim();
        if (!dateDeb) {
            showError(dateDebInput, 'La date de début est obligatoire.');
            return false;
        }
        clearError(dateDebInput);
        return true;
    }

    function validateDateFin() {
        const dateDeb = new Date(dateDebInput.value);
        const dateFin = new Date(dateFinInput.value);
        if (!dateFinInput.value.trim()) {
            showError(dateFinInput, 'La date de fin est obligatoire.');
            return false;
        }
        if (dateFin < dateDeb) {
            showError(dateFinInput, 'La date de fin ne peut pas être avant la date de début.');
            return false;
        }
        clearError(dateFinInput);
        return true;
    }

    // Attach blur event listeners for real-time validation
    dateDebInput.addEventListener('blur', validateDateDeb);
    dateFinInput.addEventListener('blur', validateDateFin);

    // Form submission validation
    editForm.addEventListener('submit', function (e) {
        const isDateDebValid = validateDateDeb();
        const isDateFinValid = validateDateFin();

     
    });
});

function toggleSearchFields() {
    const searchType = document.getElementById('search_type').value;
    document.getElementById('search_by_id').style.display = (searchType === 'id') ? 'block' : 'none';
    document.getElementById('search_by_status').style.display = (searchType === 'status') ? 'block' : 'none';
    document.getElementById('search_by_date_range').style.display = (searchType === 'date_range') ? 'block' : 'none';
}

// Initialize the form based on the selected search type
document.addEventListener('DOMContentLoaded', toggleSearchFields);

function togglePartenaireSearchFields() {
    const searchType = document.getElementById('search_partenaire_type').value;
    document.getElementById('search_partenaire_by_id').style.display = (searchType === 'id') ? 'block' : 'none';
    document.getElementById('search_partenaire_by_nom').style.display = (searchType === 'nom') ? 'block' : 'none';
    document.getElementById('search_partenaire_by_montant_range').style.display = (searchType === 'montant_range') ? 'block' : 'none';
}

// Initialize the form based on the selected search type
document.addEventListener('DOMContentLoaded', togglePartenaireSearchFields);
// Reconnaissance vocale
document.addEventListener('DOMContentLoaded', function() {
    const voiceSearchBtn = document.getElementById('voiceSearchBtn');
    const voiceStatus = document.getElementById('voiceStatus');
    const voiceSearchResults = document.getElementById('voiceSearchResults');
    const contractDetails = document.getElementById('contractDetails');

    if ('webkitSpeechRecognition' in window) {
        const recognition = new webkitSpeechRecognition();
        recognition.continuous = false;
        recognition.interimResults = false;
        recognition.lang = 'fr-FR';

        voiceSearchBtn.addEventListener('click', function() {
            startVoiceRecognition();
        });

        function startVoiceRecognition() {
            try {
                // Changer l'apparence du bouton pendant l'écoute
                voiceSearchBtn.innerHTML = '<i class="fas fa-microphone-slash"></i> Ecoute en cours...';
                voiceSearchBtn.classList.remove('btn-primary');
                voiceSearchBtn.classList.add('btn-danger');
                voiceStatus.textContent = "Parlez maintenant...";
                voiceStatus.style.color = "green";
                
                recognition.start();
            } catch (error) {
                console.error('Erreur reconnaissance vocale:', error);
                resetVoiceRecognition();
                voiceStatus.textContent = "Erreur: " + error.message;
                voiceStatus.style.color = "red";
            }
        }

        recognition.onresult = function(event) {
            const transcript = event.results[0][0].transcript.trim();
            voiceStatus.textContent = "Vous avez dit: " + transcript;
            voiceStatus.style.color = "blue";
            
            // Extraire un numéro de contrat (séquence de chiffres)
            const contractId = transcript.match(/\d+/)?.[0];
            
            if (contractId) {
                voiceStatus.textContent += " - ID détecté: " + contractId;
                fetchContractDetails(contractId);
            } else {
                voiceStatus.textContent += " - Aucun ID de contrat détecté";
                voiceStatus.style.color = "orange";
                resetVoiceRecognition();
            }
        };

        recognition.onerror = function(event) {
            console.error('Erreur reconnaissance:', event.error);
            voiceStatus.textContent = "Erreur: " + getErrorText(event.error);
            voiceStatus.style.color = "red";
            resetVoiceRecognition();
        };

        recognition.onend = function() {
            resetVoiceRecognition();
        };

        function resetVoiceRecognition() {
            voiceSearchBtn.innerHTML = '<i class="fas fa-microphone"></i> Commencer à parler';
            voiceSearchBtn.classList.remove('btn-danger');
            voiceSearchBtn.classList.add('btn-primary');
        }

        function getErrorText(error) {
            const errors = {
                'no-speech': 'Aucune parole détectée',
                'audio-capture': 'Problème de microphone',
                'not-allowed': 'Microphone non autorisé',
                'aborted': 'Reconnaissance interrompue',
                'network': 'Erreur réseau',
                'language-not-supported': 'Langue non supportée'
            };
            return errors[error] || 'Erreur inconnue';
        }

        async function fetchContractDetails(contractId) {
            try {
                voiceStatus.textContent = "Recherche du contrat #" + contractId + "...";
                
                const response = await fetch(`get_contract_info.php?id=${contractId}`);
                if (!response.ok) throw new Error('Erreur réseau');
                
                const data = await response.json();
                
                if (data.error) {
                    throw new Error(data.error);
                }
                
                // Afficher les résultats
                displayContractDetails(data);
                
            } catch (error) {
                console.error('Erreur:', error);
                voiceStatus.textContent = "Erreur: " + error.message;
                voiceStatus.style.color = "red";
            }
        }

        function displayContractDetails(contract) {
            voiceSearchResults.style.display = 'block';
            contractDetails.innerHTML = `
                <div class="alert alert-success">
                    <h4>Contrat #${contract.id_contrat}</h4>
                    <p><strong>Partenaire:</strong> ${contract.partenaire_nom}</p>
                    <p><strong>Date Début:</strong> ${contract.date_deb}</p>
                    <p><strong>Date Fin:</strong> ${contract.date_fin}</p>
                    <p><strong>Statut:</strong> <span class="badge badge-${getStatusBadgeClass(contract.status)}">${contract.status}</span></p>
                </div>
            `;
            
            // Faire défiler jusqu'aux résultats
            voiceSearchResults.scrollIntoView({ behavior: 'smooth' });
        }

        function getStatusBadgeClass(status) {
            return {
                'actif': 'success',
                'en attente': 'warning',
                'expiré': 'danger'
            }[status] || 'secondary';
        }

    } else {
        voiceSearchBtn.style.display = 'none';
        voiceStatus.textContent = "La reconnaissance vocale n'est pas supportée par votre navigateur";
        voiceStatus.style.color = "red";
    }
});
</script>
</body>
</html>