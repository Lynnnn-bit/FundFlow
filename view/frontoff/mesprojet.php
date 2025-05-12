<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../control/ProjectController.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: connexion.php");
    exit();
}

$userId = $_SESSION['user_id']; // Get the logged-in user's ID

$controller = new ProjectController();

// Fetch only the projects related to the logged-in user
$projects = $controller->getProjectsByUserId($userId); // Ensure this method exists in your controller

// Recalculate statistics for the filtered projects
$totalProjects = count($projects);
$totalFunding = array_sum(array_map(fn($p) => $p['montant_cible'] ?? 0, $projects));
$projectsByStatus = array_reduce($projects, function ($carry, $project) {
    $status = $project['status'] ?? 'unknown';
    $carry[$status] = ($carry[$status] ?? 0) + 1;
    return $carry;
}, []);

// Handle PDF export
if (isset($_GET['export_pdf'])) {
    require_once __DIR__ . '/../../libs/fpdf/fpdf.php';
    ob_start();
    $pdf = new FPDF();
    $pdf->AddPage();
    $pdf->SetFont('Arial', 'B', 16);
    $pdf->Cell(0, 10, 'Liste des Projets', 0, 1, 'C');
    $pdf->Ln(10);
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(20, 10, 'ID', 1);
    $pdf->Cell(40, 10, 'Titre', 1);
    $pdf->Cell(60, 10, 'Description', 1);
    $pdf->Cell(30, 10, 'Montant', 1);
    $pdf->Cell(30, 10, 'Statut', 1);
    $pdf->Cell(30, 10, 'Durée', 1);
    $pdf->Ln();
    $pdf->SetFont('Arial', '', 12);
    foreach ($projects as $project) {
        $statut = $project['status'] ?? 'N/A';
        $pdf->Cell(20, 10, $project['id_projet'], 1);
        $pdf->Cell(40, 10, substr($project['titre'], 0, 20), 1);
        $pdf->Cell(60, 10, substr($project['description'] ?? 'N/A', 0, 40), 1);
        $pdf->Cell(30, 10, number_format($project['montant_cible'], 2) . ' €', 1);
        $pdf->Cell(30, 10, ucfirst($statut), 1);
        $pdf->Cell(30, 10, $project['duree'] . ' mois', 1);
        $pdf->Ln();
    }
    ob_end_clean();
    $pdf->Output('D', 'Liste_Projets.pdf');
    exit;
}

// Handle PDF comparison
if (isset($_GET['compare_pdf'])) {
    $project1Id = $_GET['project1'] ?? null;
    $project2Id = $_GET['project2'] ?? null;

    if ($project1Id && $project2Id) {
        $project1 = array_filter($projects, fn($p) => $p['id_projet'] == $project1Id);
        $project2 = array_filter($projects, fn($p) => $p['id_projet'] == $project2Id);

        if (!empty($project1) && !empty($project2)) {
            $project1 = array_values($project1)[0];
            $project2 = array_values($project2)[0];

            require_once __DIR__ . '/../../libs/fpdf/fpdf.php';
            $pdf = new FPDF();
            $pdf->AddPage();
            $pdf->SetFont('Arial', 'B', 16);
            $pdf->Cell(0, 10, 'Comparaison des Projets', 0, 1, 'C');
            $pdf->Ln(10);

            $pdf->SetFont('Arial', 'B', 12);
            $pdf->Cell(95, 10, 'Projet 1', 1, 0, 'C');
            $pdf->Cell(95, 10, 'Projet 2', 1, 1, 'C');

            $fields = ['Titre', 'Description', 'Montant Cible', 'Durée', 'Statut'];
            foreach ($fields as $field) {
                $pdf->SetFont('Arial', 'B', 10);
                $pdf->Cell(40, 10, $field, 1);
                $pdf->SetFont('Arial', '', 10);
                $pdf->Cell(55, 10, $project1[strtolower(str_replace(' ', '_', $field))] ?? 'N/A', 1);
                $pdf->Cell(55, 10, $project2[strtolower(str_replace(' ', '_', $field))] ?? 'N/A', 1);
                $pdf->Ln();
            }

            $pdf->Output('D', 'Comparaison_Projets.pdf');
            exit;
        }
    }
}

// Handle search and sorting
$searchTerm = $_GET['search'] ?? '';
$montantSortOrder = $_GET['montant_sort'] ?? '';

if (!empty($searchTerm)) {
    $projects = array_filter($projects, fn($p) => stripos($p['titre'], $searchTerm) !== false || stripos($p['description'], $searchTerm) !== false);
}
if ($montantSortOrder === 'asc') {
    usort($projects, fn($a, $b) => ($a['montant_cible'] ?? 0) <=> ($b['montant_cible'] ?? 0));
} elseif ($montantSortOrder === 'desc') {
    usort($projects, fn($a, $b) => ($b['montant_cible'] ?? 0) <=> ($a['montant_cible'] ?? 0));
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FundFlow - Mes Projets</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&family=Playfair+Display:wght@500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Montserrat', sans-serif;
            background: linear-gradient(135deg, #3a56d4 0%, #10b981 100%);
            background-size: 200% 200%;
            animation: gradientBG 12s ease infinite;
            color: white;
            margin: 0;
            padding: 0;
        }

        @keyframes gradientBG {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1.5rem 3rem;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
        }

        .brand-logo {
            height: 50px;
        }

        .nav-links {
            display: flex;
            gap: 1.5rem;
        }

        .nav-link {
            color: white;
            text-decoration: none;
            font-weight: 500;
            padding: 0.75rem 1.25rem;
            border-radius: 50px;
            transition: all 0.3s ease;
        }

        .nav-link:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateY(-2px);
        }

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
        }

        .profile-menu-btn:hover {
            background: linear-gradient(135deg, #10b981, #3a56d4);
            transform: translateY(-2px);
        }

        .page-header {
            text-align: center;
            margin: 2rem 0;
        }

        .page-header h1 {
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }

        .page-header p {
            font-size: 1.2rem;
            color: rgba(255, 255, 255, 0.8);
        }

        .projects-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
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
            border-radius: 16px;
            padding: 1.5rem;
            text-align: center;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        }

        .stat-card h3 {
            font-size: 1.2rem;
            margin-bottom: 0.5rem;
        }

        .stat-card p {
            font-size: 1.5rem;
            font-weight: 600;
        }

        .projects-list ul {
            list-style: none;
            padding: 0;
        }

        .projects-list li {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 16px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        }

        .projects-list h3 {
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
        }

        .projects-list p {
            margin: 0.5rem 0;
            color: rgba(255, 255, 255, 0.9);
        }

        .action-buttons {
            text-align: center;
            margin-bottom: 2rem;
        }

        .btn {
            padding: 0.75rem 1.5rem;
            background: linear-gradient(135deg, #3a56d4, #10b981);
            color: white;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            margin: 0.5rem;
            display: inline-block;
            transition: all 0.3s ease;
        }

        .btn:hover {
            transform: translateY(-2px);
            background: linear-gradient(135deg, #10b981, #3a56d4);
        }

        .overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            z-index: 1000;
            display: none;
        }

        .map-container {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 90%;
            max-width: 800px;
            height: 70vh;
            background: white;
            border-radius: 16px;
            z-index: 1001;
            display: none;
            overflow: hidden;
        }

        .map-container iframe {
            width: 100%;
            height: 100%;
            border: none;
        }

        .close-btn {
            position: absolute;
            top: 1rem;
            right: 1rem;
            padding: 0.5rem 1rem;
            background: #dc2626;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            z-index: 1002;
        }

        .close-btn:hover {
            background: #b91c1c;
        }

        @media (max-width: 768px) {
            .navbar {
                flex-direction: column;
                gap: 1rem;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .projects-container {
                padding: 1rem;
            }
        }

        /* Style for search input */
        .search-group input {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.9);
            font-size: 1rem;
            color: #333;
            transition: all 0.3s ease;
        }

        .search-group input:focus {
            outline: none;
            border-color: #3a56d4;
            box-shadow: 0 0 0 3px rgba(58, 86, 212, 0.3);
        }

        .search-btn {
            padding: 0.75rem 1.5rem;
            background: linear-gradient(135deg, #3a56d4, #10b981);
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .search-btn:hover {
            background: linear-gradient(135deg, #10b981, #3a56d4);
            transform: translateY(-2px);
        }

        /* Style for dropdowns */
        .select-wrapper select {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.9);
            font-size: 1rem;
            color: #333;
            appearance: none;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .select-wrapper select:focus {
            outline: none;
            border-color: #3a56d4;
            box-shadow: 0 0 0 3px rgba(58, 86, 212, 0.3);
        }

        .select-wrapper {
            position: relative;
        }

        .select-wrapper .select-arrow {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #333;
            pointer-events: none;
        }

        /* Style for compare form dropdowns */
        .compare-form select {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.9);
            font-size: 1rem;
            color: #333;
            appearance: none;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .compare-form select:focus {
            outline: none;
            border-color: #3a56d4;
            box-shadow: 0 0 0 3px rgba(58, 86, 212, 0.3);
        }

        .compare-form button {
            padding: 0.75rem 1.5rem;
            background: linear-gradient(135deg, #3a56d4, #10b981);
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .compare-form button:hover {
            background: linear-gradient(135deg, #10b981, #3a56d4);
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
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

    <div class="page-header">
        <h1><i class="fas fa-project-diagram"></i> Mes Projets</h1>
        <p>Gérez et suivez vos projets de financement</p>
    </div>

    <div class="projects-container">
        <div class="stats-grid">
            <div class="stat-card">
                <h3>Projets Total</h3>
                <p><?= $totalProjects ?></p>
            </div>
            <div class="stat-card">
                <h3>Total Demandé</h3>
                <p>€<?= number_format($totalFunding, 2) ?></p>
            </div>
            <div class="stat-card">
                <h3>En Attente</h3>
                <p><?= $projectsByStatus['en_attente'] ?? 0 ?></p>
            </div>
            <div class="stat-card">
                <h3>Actifs</h3>
                <p><?= $projectsByStatus['actif'] ?? 0 ?></p>
            </div>
            <div class="stat-card">
                <h3>Terminés</h3>
                <p><?= $projectsByStatus['termine'] ?? 0 ?></p>
            </div>
        </div>

        <div class="action-buttons">
            <a href="projets.php" class="btn"><i class="fas fa-plus"></i> Créer un nouveau projet</a>
            <a href="?export_pdf=true" class="btn"><i class="fas fa-file-pdf"></i> Exporter en PDF</a>
            <button id="show-map-btn" class="btn"><i class="fas fa-map-marker-alt"></i> Localisation</button>
        </div>

        <!-- Map Modal -->
        <div class="overlay" id="overlay"></div>
        <div class="map-container" id="map-container">
            <button class="close-btn" id="close-map-btn">Fermer</button>
            <iframe 
                src="https://www.google.com/maps?q=36.846917,10.195694&hl=fr&z=16&output=embed" 
                allowfullscreen>
            </iframe>
        </div>

        <div class="filter-panel">
            <form method="GET" class="filter-form">
                <div class="search-group">
                    <i class="fas fa-search search-icon"></i>
                    <input type="text" name="search" placeholder="Rechercher un projet..." value="<?= htmlspecialchars($searchTerm) ?>">
                    <button type="submit" class="search-btn">Rechercher</button>
                </div>
                <div class="filter-group">
                    <div class="select-wrapper">
                        <select name="montant_sort" onchange="this.form.submit()">
                            <option value="">Trier par Montant</option>
                            <option value="asc" <?= $montantSortOrder === 'asc' ? 'selected' : '' ?>>Montant Croissant</option>
                            <option value="desc" <?= $montantSortOrder === 'desc' ? 'selected' : '' ?>>Montant Décroissant</option>
                        </select>
                        <i class="fas fa-chevron-down select-arrow"></i>
                    </div>
                </div>
            </form>
            
            <form method="GET" action="" class="compare-form">
                <div class="select-wrapper">
                    <select name="project1">
                        <option value="">Sélectionnez Projet 1</option>
                        <?php foreach ($projects as $project): ?>
                            <option value="<?= $project['id_projet'] ?>"><?= htmlspecialchars($project['titre']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <i class="fas fa-chevron-down select-arrow"></i>
                </div>
                <div class="select-wrapper">
                    <select name="project2">
                        <option value="">Sélectionnez Projet 2</option>
                        <?php foreach ($projects as $project): ?>
                            <option value="<?= $project['id_projet'] ?>"><?= htmlspecialchars($project['titre']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <i class="fas fa-chevron-down select-arrow"></i>
                </div>
                <button type="submit" name="compare_pdf" class="btn btn-primary" style="padding: 0.75rem 1.5rem; background: linear-gradient(135deg, #3a56d4, #10b981); color: white; border-radius: 8px; text-decoration: none; font-weight: 600;">
                    <i class="fas fa-file-pdf"></i> Comparer en PDF
                </button>
            </form>
        </div>

        <div class="projects-list">
            <?php if (empty($projects)): ?>
                <p>Aucun projet trouvé.</p>
            <?php else: ?>
                <ul>
                    <?php foreach ($projects as $project): ?>
                        <li>
                            <h3><?= htmlspecialchars($project['titre']) ?></h3>
                            <p><?= htmlspecialchars($project['description']) ?></p>
                            <p>Montant Cible: €<?= number_format($project['montant_cible'], 2) ?></p>
                            <p>Durée: <?= $project['duree'] ?> mois</p>
                            <p>Statut: <?= ucfirst($project['status']) ?></p>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    </div>
</div>
<script>
    // Map modal functionality
    const showMapBtn = document.getElementById('show-map-btn');
    const closeMapBtn = document.getElementById('close-map-btn');
    const mapContainer = document.getElementById('map-container');
    const overlay = document.getElementById('overlay');

    showMapBtn.addEventListener('click', (e) => {
        e.preventDefault();
        mapContainer.style.display = 'block';
        overlay.style.display = 'block';
    });

    closeMapBtn.addEventListener('click', () => {
        mapContainer.style.display = 'none';
        overlay.style.display = 'none';
    });

    overlay.addEventListener('click', () => {
        mapContainer.style.display = 'none';
        overlay.style.display = 'none';
    });
</script>
</body>
</html>