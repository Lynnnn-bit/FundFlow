<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../control/financecontroller.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: connexion.php");
    exit();
}

$userId = $_SESSION['user_id']; // Get the logged-in user's ID
$controller = new FinanceController();
$existingDemands = $controller->getFinanceRequestsByUser($userId); // Fetch only the user's demands

// Handle sorting
$sortColumn = $_GET['sort_column'] ?? 'id_demande';
$sortOrder = $_GET['sort_order'] ?? 'asc';
$searchQuery = $_GET['search'] ?? '';

if (in_array($sortColumn, ['id_demande', 'projet_titre', 'montant_demandee', 'duree', 'status'])) {
    usort($existingDemands, function ($a, $b) use ($sortColumn, $sortOrder) {
        if ($sortOrder === 'asc') {
            return $a[$sortColumn] <=> $b[$sortColumn];
        } else {
            return $b[$sortColumn] <=> $a[$sortColumn];
        }
    });
}

// Filter by search query
if (!empty($searchQuery)) {
    $existingDemands = array_filter($existingDemands, function($demand) use ($searchQuery) {
        return stripos($demand['projet_titre'], $searchQuery) !== false || 
               stripos($demand['description'], $searchQuery) !== false;
    });
}

// Handle PDF export
if (isset($_GET['export_pdf'])) {
    require_once __DIR__ . '/../../libs/fpdf/fpdf.php';
    ob_start();
    $pdf = new FPDF();
    $pdf->AddPage();
    $pdf->SetFont('Arial', 'B', 16);
    $pdf->Cell(0, 10, 'Historique des Demandes', 0, 1, 'C');
    $pdf->Ln(10);
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(20, 10, 'ID', 1);
    $pdf->Cell(40, 10, 'Projet', 1);
    $pdf->Cell(40, 10, 'Montant', 1);
    $pdf->Cell(30, 10, 'Durée', 1);
    $pdf->Cell(30, 10, 'Statut', 1);
    $pdf->Cell(30, 10, 'Date', 1);
    $pdf->Ln();
    $pdf->SetFont('Arial', '', 12);
    foreach ($existingDemands as $demand) {
        $pdf->Cell(20, 10, $demand['id_demande'], 1);
        $pdf->Cell(40, 10, substr($demand['projet_titre'], 0, 20), 1);
        $pdf->Cell(40, 10, number_format($demand['montant_demandee'], 2) . ' €', 1);
        $pdf->Cell(30, 10, $demand['duree'] . ' mois', 1);
        $pdf->Cell(30, 10, ucfirst($demand['status']), 1);
        $pdf->Cell(30, 10, $demand['date_creation'], 1);
        $pdf->Ln();
    }
    ob_end_clean();
    $pdf->Output('D', 'Historique_Demandes.pdf');
    exit;
}

$totalDemands = count($existingDemands);
$totalAmount = array_sum(array_column($existingDemands, 'montant_demandee'));
$acceptedDemands = count(array_filter($existingDemands, fn($d) => $d['status'] === 'accepte'));
$rejectedDemands = count(array_filter($existingDemands, fn($d) => $d['status'] === 'rejete'));
$pendingDemands = count(array_filter($existingDemands, fn($d) => $d['status'] === 'en_attente'));
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FundFlow - Historique des Demandes</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&family=Playfair+Display:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <style>
        /* History Specific Styles */
        .history-container {
            margin-top: 2rem;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border-radius: 16px;
            padding: 1.5rem;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            background: rgba(255, 255, 255, 0.15);
        }

        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
        }

        .stat-icon.bg-blue {
            background: rgba(67, 97, 238, 0.2);
            color: #4361ee;
        }

        .stat-icon.bg-green {
            background: rgba(16, 185, 129, 0.2);
            color: #10b981;
        }

        .stat-icon.bg-orange {
            background: rgba(249, 115, 22, 0.2);
            color: #f97316;
        }

        .stat-icon.bg-red {
            background: rgba(239, 68, 68, 0.2);
            color: #ef4444;
        }

        .stat-content h3 {
            font-size: 0.9rem;
            color: rgba(255,255,255,0.8);
            margin-bottom: 0.5rem;
        }

        .stat-value {
            font-size: 1.5rem;
            font-weight: 600;
            color: white;
        }

        .action-buttons {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
            flex-wrap: wrap;
        }

        .filter-panel {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border-radius: 16px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .filter-form {
            display: flex;
            gap: 1rem;
            margin-bottom: 1rem;
            flex-wrap: wrap;
        }

        .search-group {
            flex: 1;
            min-width: 300px;
            position: relative;
        }

        .search-group input {
            width: 100%;
            padding: 0.75rem 1rem 0.75rem 2.5rem;
            background: rgba(255,255,255,0.9);
            border: 1px solid rgba(255,255,255,0.3);
            border-radius: 12px;
            font-size: 1rem;
            color: var(--dark);
        }

        .search-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gray);
        }

        .search-btn {
            padding: 0.75rem 1.5rem;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 12px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .search-btn:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
        }

        .filter-group {
            min-width: 200px;
        }

        .select-wrapper {
            position: relative;
        }

        .select-wrapper select {
            width: 100%;
            padding: 0.75rem 1.5rem;
            background: rgba(255,255,255,0.9);
            border: 1px solid rgba(255,255,255,0.3);
            border-radius: 12px;
            font-size: 1rem;
            color: var(--dark);
            appearance: none;
            cursor: pointer;
        }

        .select-arrow {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gray);
            pointer-events: none;
        }

        .table-container {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border-radius: 16px;
            padding: 1.5rem;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            overflow-x: auto;
        }

        .demand-table {
            width: 100%;
            border-collapse: collapse;
            color: white;
        }

        .demand-table th {
            text-align: left;
            padding: 1rem;
            background: rgba(0,0,0,0.1);
            border-bottom: 1px solid rgba(255,255,255,0.1);
            font-weight: 600;
        }

        .demand-table td {
            padding: 1rem;
            border-bottom: 1px solid rgba(255,255,255,0.05);
        }

        .demand-table tr:last-child td {
            border-bottom: none;
        }

        .demand-table tr:hover {
            background: rgba(255,255,255,0.05);
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            padding: 0.25rem 0.75rem;
            border-radius: 50px;
            font-size: 0.75rem;
            font-weight: 500;
        }

        .status-badge.pending {
            background: rgba(249, 115, 22, 0.2);
            color: #f97316;
        }

        .status-badge.approved {
            background: rgba(16, 185, 129, 0.2);
            color: #10b981;
        }

        .status-badge.rejected {
            background: rgba(239, 68, 68, 0.2);
            color: #ef4444;
        }

        .status-dot {
            display: inline-block;
            width: 8px;
            height: 8px;
            border-radius: 50%;
            margin-right: 0.5rem;
        }

        .status-badge.pending .status-dot {
            background: #f97316;
        }

        .status-badge.approved .status-dot {
            background: #10b981;
        }

        .status-badge.rejected .status-dot {
            background: #ef4444;
        }

        .actions-cell {
            display: flex;
            gap: 0.5rem;
        }

        .btn-sm {
            padding: 0.5rem 0.75rem;
            border-radius: 8px;
            font-size: 0.8rem;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .btn-sm:hover {
            transform: translateY(-2px);
        }

        .btn-info {
            background: rgba(59, 130, 246, 0.2);
            color: #3b82f6;
        }

        .btn-info:hover {
            background: rgba(59, 130, 246, 0.3);
        }

        .btn-warning {
            background: rgba(234, 179, 8, 0.2);
            color: #eab308;
        }

        .btn-warning:hover {
            background: rgba(234, 179, 8, 0.3);
        }

        .btn-danger {
            background: rgba(239, 68, 68, 0.2);
            color: #ef4444;
        }

        .btn-danger:hover {
            background: rgba(239, 68, 68, 0.3);
        }

        .no-results {
            text-align: center;
            padding: 3rem;
            background: rgba(255,255,255,0.05);
            border-radius: 16px;
            border: 1px dashed rgba(255,255,255,0.2);
        }

        .no-results-illustration {
            font-size: 3rem;
            color: rgba(255,255,255,0.2);
            margin-bottom: 1.5rem;
        }

        .no-results h3 {
            font-size: 1.5rem;
            margin-bottom: 1rem;
            color: white;
        }

        .no-results p {
            color: rgba(255,255,255,0.7);
            margin-bottom: 1.5rem;
            max-width: 500px;
            margin-left: auto;
            margin-right: auto;
        }

        @media (max-width: 768px) {
            .filter-form {
                flex-direction: column;
            }
            
            .search-group {
                min-width: 100%;
            }
            
            .actions-cell {
                flex-direction: column;
            }
        }

        /* Button Container Styles */
        .action-buttons {
            display: flex;
            gap: 15px;
            margin: 25px 0;
            flex-wrap: wrap;
            justify-content: center;
        }

        /* Base Button Styles */
        .action-btn {
            position: relative;
            padding: 12px 25px;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            min-width: 180px;
            overflow: hidden;
            color: white;
        }

        /* Button Hover Effects */
        .action-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
        }

        .action-btn:active {
            transform: translateY(1px);
        }

        /* Button Before Pseudo-element (for animation) */
        .action-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: 0.5s;
        }

        .action-btn:hover::before {
            left: 100%;
        }

        /* Individual Button Colors */
        .btn-new {
            background: linear-gradient(135deg, #4e54c8, #8f94fb);
            border-left: 4px solid #8f94fb;
        }

        .btn-history {
            background: linear-gradient(135deg, #11998e, #38ef7d);
            border-left: 4px solid #38ef7d;
        }

        .btn-stats {
            background: linear-gradient(135deg, #f46b45, #eea849);
            border-left: 4px solid #eea849;
        }

        .btn-chatbot {
            background: linear-gradient(135deg, #8E2DE2, #4A00E0);
            border-left: 4px solid #4A00E0;
        }

        /* Button Icons */
        .action-btn i {
            margin-right: 8px;
            font-size: 18px;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .action-buttons {
                flex-direction: column;
                align-items: center;
            }
            
            .action-btn {
                width: 100%;
                max-width: 250px;
            }
        }
        /* Enhanced Styling for "Mon compte" Dropdown */
        .profile-menu-container {
            position: relative;
        }

        .profile-menu-btn {
            background: linear-gradient(135deg, #3a56d4, #10b981);
            color: white;
            border: none;
            border-radius: 50px;
            padding: 0.75rem 1.5rem;
            font-size: 0.95rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .profile-menu-btn:hover {
            background: linear-gradient(135deg, #10b981, #3a56d4);
            transform: translateY(-2px);
            box-shadow: 0 8px 12px rgba(0, 0, 0, 0.2);
        }

        .profile-menu {
            display: none;
            position: absolute;
            top: 100%;
            right: 0;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 12px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
            list-style: none;
            padding: 0.5rem 0;
            margin: 0;
            z-index: 10;
            animation: fadeIn 0.3s ease;
        }

        .profile-menu li {
            padding: 0.5rem 1rem;
            transition: background 0.3s ease;
        }

        .profile-menu li a {
            text-decoration: none;
            color: #333;
            font-weight: 500;
            display: block;
            transition: color 0.3s ease, background 0.3s ease;
        }

        .profile-menu li:hover {
            background: rgba(16, 185, 129, 0.1);
        }

        .profile-menu li a:hover {
            color: #10b981;
        }

        .profile-menu li a.logout {
            color: #dc2626;
            font-weight: 600;
        }

        .profile-menu li a.logout:hover {
            color: white;
            background: #dc2626;
        }

        .profile-menu-container:hover .profile-menu {
            display: block;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>
<body>
<div class="background-effect"></div>
<div class="particles-container" id="particles-js"></div>

<div class="dashboard-container">
<header class="navbar">
        <div class="logo-container">
            <a href="acceuil2.php">
                <img src="assets/Logo_FundFlow.png" alt="FundFlow Logo" class="brand-logo">
            </a>
        </div>
        
        <div class="nav-links">
            <a href="acceuil2.php" class="nav-link"><i class="fas fa-home"></i> Accueil</a>
            <a href="apropos.html" class="nav-link"><i class="fas fa-info-circle"></i> À propos</a>
            <a href="contact.html" class="nav-link"><i class="fas fa-envelope"></i> Contact</a>
            <a href="events.php" class="nav-link"><i class="fas fa-calendar-alt"></i> Événements</a>
            <a href="partenaire.php" class="nav-link"><i class="fas fa-handshake"></i> Partenariats</a>
            
            <div class="profile-menu-container">
                <button class="profile-menu-btn">Mon compte ▼</button>
                <ul class="profile-menu">
                    <li><a href="profiles.php">Profil</a></li>
                    <?php if ($_SESSION['user']['role'] === 'investisseur'): ?>
                        <li><a href="demands_list.php">Liste des demandes</a></li>
                    <?php endif; ?>
                    <?php if ($_SESSION['user']['role'] === 'entrepreneur'): ?>
                        <li><a href="mesprojet.php">Mes projets</a></li>
                        <li><a href="historique.php">mes demandes</a></li>
                    <?php endif; ?>
                    <li><a href="allconsult.php">Consultation</a></li>
                    <li><a href="connexion.php?logout=1" class="logout">Déconnexion</a></li>
                </ul>
            </div>
        </div>
    </header>

    <div class="page-header animate__animated animate__fadeInDown">
        <h1><i class="fas fa-history header-icon"></i> Historique des Demandes</h1>
        <p>Consultez l'historique complet de vos demandes de financement</p>
    </div>

    <div class="history-container">
        <!-- Stats Cards -->
        <div class="stats-grid">
            <div class="stat-card animate__animated animate__fadeIn">
                <div class="stat-icon bg-blue">
                    <i class="fas fa-file-invoice-dollar"></i>
                </div>
                <div class="stat-content">
                    <h3>Total des Demandes</h3>
                    <div class="stat-value"><?= $totalDemands ?></div>
                </div>
            </div>
            <div class="stat-card animate__animated animate__fadeIn animate__delay-1s">
                <div class="stat-icon bg-green">
                    <i class="fas fa-euro-sign"></i>
                </div>
                <div class="stat-content">
                    <h3>Montant Total</h3>
                    <div class="stat-value"><?= number_format($totalAmount, 2) ?> €</div>
                </div>
            </div>
            <div class="stat-card animate__animated animate__fadeIn animate__delay-2s">
                <div class="stat-icon bg-orange">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-content">
                    <h3>En Attente</h3>
                    <div class="stat-value"><?= $pendingDemands ?></div>
                </div>
            </div>
            <div class="stat-card animate__animated animate__fadeIn animate__delay-3s">
                <div class="stat-icon bg-green">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-content">
                    <h3>Acceptées</h3>
                    <div class="stat-value"><?= $acceptedDemands ?></div>
                </div>
            </div>
            <div class="stat-card animate__animated animate__fadeIn animate__delay-4s">
                <div class="stat-icon bg-red">
                    <i class="fas fa-times-circle"></i>
                </div>
                <div class="stat-content">
                    <h3>Rejetées</h3>
                    <div class="stat-value"><?= $rejectedDemands ?></div>
                </div>
            </div>
        </div>

        <div class="action-buttons">
            <a href="financemet.php" class="action-btn btn-new animate__animated animate__fadeIn">
                <i class="fas fa-plus-circle"></i> Nouvelle Demande
            </a>
            <a href="historique.php" class="action-btn btn-history animate__animated animate__fadeIn animate__delay-1s">
                <i class="fas fa-history"></i> Historique
            </a>
            <a href="statistiquesf.php" class="action-btn btn-stats animate__animated animate__fadeIn animate__delay-2s">
                <i class="fas fa-chart-pie"></i> Statistiques
            </a>
            <a href="chatbot.php" class="action-btn btn-chatbot animate__animated animate__fadeIn animate__delay-3s">
                <i class="fas fa-robot"></i> Chatbot
            </a>
            <!-- Add Export PDF Button -->
            <a href="?export_pdf=true" class="btn btn-primary">
                <i class="fas fa-file-pdf"></i> Exporter en PDF
            </a>
        </div>

        <!-- Search and Filter -->
        <div class="filter-panel animate__animated animate__fadeIn">
            <form method="GET" class="filter-form">
                <div class="search-group">
                    <i class="fas fa-search search-icon"></i>
                    <input type="text" name="search" placeholder="Rechercher des demandes..." value="<?= htmlspecialchars($searchQuery) ?>">
                    <button type="submit" class="search-btn">Rechercher</button>
                </div>
                <div class="filter-group">
                    <div class="select-wrapper">
                        <select name="sort_column" onchange="this.form.submit()">
                            <option value="id_demande" <?= $sortColumn === 'id_demande' ? 'selected' : '' ?>>ID Demande</option>
                            <option value="projet_titre" <?= $sortColumn === 'projet_titre' ? 'selected' : '' ?>>Nom du Projet</option>
                            <option value="montant_demandee" <?= $sortColumn === 'montant_demandee' ? 'selected' : '' ?>>Montant</option>
                            <option value="duree" <?= $sortColumn === 'duree' ? 'selected' : '' ?>>Durée</option>
                            <option value="status" <?= $sortColumn === 'status' ? 'selected' : '' ?>>Statut</option>
                        </select>
                        <i class="fas fa-chevron-down select-arrow"></i>
                    </div>
                </div>
                <div class="filter-group">
                    <div class="select-wrapper">
                        <select name="sort_order" onchange="this.form.submit()">
                            <option value="asc" <?= $sortOrder === 'asc' ? 'selected' : '' ?>>Croissant</option>
                            <option value="desc" <?= $sortOrder === 'desc' ? 'selected' : '' ?>>Décroissant</option>
                        </select>
                        <i class="fas fa-chevron-down select-arrow"></i>
                    </div>
                </div>
            </form>
        </div>

        <!-- Demands Table -->
        <div class="table-container animate__animated animate__fadeInUp">
            <?php if (!empty($existingDemands)): ?>
                <table class="demand-table">
                    <thead>
                        <tr>
                            <th>ID Demande</th>
                            <th>Projet</th>
                            <th>Montant</th>
                            <th>Durée</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($existingDemands as $demand): ?>
                            <tr>
                                <td><?= htmlspecialchars($demand['id_demande']) ?></td>
                                <td><?= htmlspecialchars($demand['projet_titre']) ?></td>
                                <td><?= number_format($demand['montant_demandee'], 2) ?> €</td>
                                <td><?= htmlspecialchars($demand['duree']) ?> mois</td>
                                <td>
                                    <span class="status-badge <?= 
                                        $demand['status'] == 'accepte' ? 'approved' : 
                                        ($demand['status'] == 'rejete' ? 'rejected' : 'pending') ?>">
                                        <span class="status-dot"></span>
                                        <?= ucfirst($demand['status']) ?>
                                    </span>
                                </td>
                                <td class="actions-cell">
                                    <a href="responses.php?demande_id=<?= $demand['id_demande'] ?>" class="btn-sm btn-info" title="Voir les réponses">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="financemet.php?edit_id=<?= $demand['id_demande'] ?>" class="btn-sm btn-warning" title="Modifier">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="financemet.php?delete_id=<?= $demand['id_demande'] ?>" class="btn-sm btn-danger" 
                                       onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette demande ?');" title="Supprimer">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="no-results animate__animated animate__fadeIn">
                    <div class="no-results-illustration">
                        <i class="fas fa-folder-open"></i>
                    </div>
                    <h3>Aucune demande trouvée</h3>
                    <p>Essayez d'ajuster vos critères de recherche ou créez une nouvelle demande</p>
                    <a href="financemet.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Créer une demande
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<footer class="footer">
  <div class="footer-container">
    <div class="footer-col logo-col">
      <img src="assets/Logo_FundFlow.png" alt="Company Logo" class="footer-logo">
      <p class="footer-description">Plateforme de financement collaboratif</p>
    </div>
    <div class="footer-col links-col">
      <h4>Liens Rapides</h4>
      <ul>
        <li><a href="financemet.php">Accueil</a></li>
        <li><a href="#">À propos</a></li>
        <li><a href="#">Services</a></li>
        <li><a href="#">Blog</a></li>
        <li><a href="#">Contact</a></li>
      </ul>
    </div>
    <div class="footer-col contact-col">
      <h4>Contactez-nous</h4>
      <p>123 Rue de Finance, Paris 75001</p>
      <p>Email: <a href="mailto:contact@fundflow.com">contact@fundflow.com</a></p>
      <p>Tél: +33 1 23 45 67 89</p>
    </div>
    <div class="footer-col social-col">
      <h4>Suivez-nous</h4>
      <div class="social-icons">
        <a href="#" aria-label="Facebook"><i class="fab fa-facebook"></i></a>
        <a href="#" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
        <a href="#" aria-label="LinkedIn"><i class="fab fa-linkedin"></i></a>
        <a href="#" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
      </div>
    </div>
  </div>
  <div class="footer-legal">
    <a href="#">Politique de confidentialité</a> |
    <a href="#">Conditions d'utilisation</a> |
    <span>&copy; 2025 FundFlow. Tous droits réservés.</span>
  </div>
</footer>

<script src="https://cdn.jsdelivr.net/particles.js/2.0.0/particles.min.js"></script>
<script>
// Initialize particles.js
particlesJS("particles-js", {
    "particles": {
        "number": {
            "value": 60,
            "density": {
                "enable": true,
                "value_area": 800
            }
        },
        "color": {
            "value": "#4cc9f0"
        },
        "shape": {
            "type": "circle",
            "stroke": {
                "width": 0,
                "color": "#000000"
            }
        },
        "opacity": {
            "value": 0.5,
            "random": true,
            "anim": {
                "enable": true,
                "speed": 1,
                "opacity_min": 0.1,
                "sync": false
            }
        },
        "size": {
            "value": 3,
            "random": true,
            "anim": {
                "enable": true,
                "speed": 2,
                "size_min": 0.1,
                "sync": false
            }
        },
        "line_linked": {
            "enable": true,
            "distance": 150,
            "color": "#4cc9f0",
            "opacity": 0.4,
            "width": 1
        },
        "move": {
            "enable": true,
            "speed": 1,
            "direction": "none",
            "random": true,
            "straight": false,
            "out_mode": "out",
            "bounce": false,
            "attract": {
                "enable": true,
                "rotateX": 600,
                "rotateY": 1200
            }
        }
    },
    "interactivity": {
        "detect_on": "canvas",
        "events": {
            "onhover": {
                "enable": true,
                "mode": "grab"
            },
            "onclick": {
                "enable": true,
                "mode": "push"
            },
            "resize": true
        },
        "modes": {
            "grab": {
                "distance": 140,
                "line_linked": {
                    "opacity": 1
                }
            },
            "push": {
                "particles_nb": 4
            }
        }
    },
    "retina_detect": true
});

function handleMenu(select) {
    const value = select.value;
    if (value === 'logout') {
        window.location.href = 'connexion.php?logout=1';
    } else if (value) {
        window.location.href = value + '.php';
    }
    select.value = ''; // Réinitialiser la sélection
}

// Search functionality
document.querySelector('.search-group input').addEventListener('input', function() {
    const searchTerm = this.value.toLowerCase();
    const rows = document.querySelectorAll('.demand-table tbody tr');
    
    rows.forEach(row => {
        const projectName = row.querySelector('td:nth-child(2)').textContent.toLowerCase();
        row.style.display = projectName.includes(searchTerm) ? '' : 'none';
    });
});
</script>
</body>
</html>