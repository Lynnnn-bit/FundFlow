<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../controlle/ProjectController.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$controller = new ProjectController();

// Fetch all projects
$projects = $controller->getAllProjects();

// Calculate statistics
$totalProjects = count($projects);
$totalFunding = array_sum(array_column($projects, 'montant_cible'));

// Fix: Ensure all projects have a valid 'montant_cible' value
$totalFunding = array_sum(array_map(function ($project) {
    return isset($project['montant_cible']) ? (float)$project['montant_cible'] : 0;
}, $projects));

// Fix: Calculate the number of projects by status
$projectsByStatus = array_reduce($projects, function ($carry, $project) {
    $status = $project['status'] ?? 'unknown';
    $carry[$status] = ($carry[$status] ?? 0) + 1;
    return $carry;
}, []);

// Handle PDF export for all projects
if (isset($_GET['export_pdf'])) {
    require_once __DIR__ . '/../../libs/fpdf/fpdf.php'; // Ensure FPDF is included

    // Start output buffering to prevent premature output
    ob_start();

    $pdf = new FPDF();
    $pdf->AddPage();
    $pdf->SetFont('Arial', 'B', 16);

    // Title
    $pdf->Cell(0, 10, 'Liste des Projets', 0, 1, 'C');
    $pdf->Ln(10);

    // Table Header
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(20, 10, 'ID', 1);
    $pdf->Cell(40, 10, 'Titre', 1);
    $pdf->Cell(60, 10, 'Description', 1);
    $pdf->Cell(30, 10, 'Montant', 1);
    $pdf->Cell(30, 10, 'Statut', 1);
    $pdf->Cell(30, 10, 'Durée', 1);
    $pdf->Ln();

    // Table Data
    $pdf->SetFont('Arial', '', 12);
    foreach ($projects as $project) {
        $statut = $project['statut'] ?? 'N/A'; // Handle missing "statut" key
        $pdf->Cell(20, 10, $project['id_projet'], 1);
        $pdf->Cell(40, 10, substr($project['titre'], 0, 20), 1);
        $pdf->Cell(60, 10, substr($project['description'] ?? 'N/A', 0, 40), 1);
        $pdf->Cell(30, 10, number_format($project['montant_cible'], 2) . ' €', 1);
        $pdf->Cell(30, 10, ucfirst($statut), 1);
        $pdf->Cell(30, 10, $project['duree'] . ' mois', 1);
        $pdf->Ln();
    }

    // Output the PDF
    ob_end_clean(); // Clear the output buffer
    $pdf->Output('D', 'Liste_Projets.pdf');
    exit;
}

// Handle PDF comparison for two selected projects
if (isset($_GET['compare_pdf']) && isset($_GET['project1']) && isset($_GET['project2'])) {
    require_once __DIR__ . '/../../libs/fpdf/fpdf.php'; // Ensure FPDF is included

    $project1 = $controller->getProjectById($_GET['project1']);
    $project2 = $controller->getProjectById($_GET['project2']);

    // Start output buffering to prevent premature output
    ob_start();

    $pdf = new FPDF();
    $pdf->AddPage();
    $pdf->SetFont('Arial', 'B', 16);

    // Title
    $pdf->Cell(0, 10, 'Comparaison des Projets', 0, 1, 'C');
    $pdf->Ln(10);

    // Table Header
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(60, 10, 'Attribut', 1, 0, 'C');
    $pdf->Cell(65, 10, 'Projet 1', 1, 0, 'C');
    $pdf->Cell(65, 10, 'Projet 2', 1, 1, 'C');

    // Table Rows
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(60, 10, 'Titre', 1);
    $pdf->Cell(65, 10, $project1['titre'], 1);
    $pdf->Cell(65, 10, $project2['titre'], 1);
    $pdf->Ln();

    $pdf->Cell(60, 10, 'Description', 1);
    $pdf->Cell(65, 10, substr($project1['description'], 0, 30) . '...', 1);
    $pdf->Cell(65, 10, substr($project2['description'], 0, 30) . '...', 1);
    $pdf->Ln();

    $pdf->Cell(60, 10, 'Montant Cible (€)', 1);
    $pdf->Cell(65, 10, number_format($project1['montant_cible'], 2), 1);
    $pdf->Cell(65, 10, number_format($project2['montant_cible'], 2), 1);
    $pdf->Ln();

    $pdf->Cell(60, 10, 'Durée (mois)', 1);
    $pdf->Cell(65, 10, $project1['duree'], 1);
    $pdf->Cell(65, 10, $project2['duree'], 1);
    $pdf->Ln();

    // Output the PDF
    ob_end_clean(); // Clear the output buffer
    $pdf->Output('D', 'Comparaison_Projets.pdf');
    exit;
}

// Capture the search term from the request
$searchTerm = $_GET['search'] ?? null;

// Filter projects based on the search term
if ($searchTerm) {
    $projects = array_filter($projects, function ($project) use ($searchTerm) {
        return stripos($project['titre'], $searchTerm) !== false || stripos($project['description'], $searchTerm) !== false;
    });
}

// Capture the sorting order for montant
$montantSortOrder = $_GET['montant_sort'] ?? null;

// Sort projects based on the sorting order
if ($montantSortOrder === 'asc') {
    usort($projects, fn($a, $b) => $a['montant_cible'] <=> $b['montant_cible']);
} elseif ($montantSortOrder === 'desc') {
    usort($projects, fn($a, $b) => $b['montant_cible'] <=> $a['montant_cible']);
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mes Projets</title>
    <link rel="stylesheet" href="css/stylecatego.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .button-container {
            text-align: center;
            margin-bottom: 2rem;
        }
        .button-container a, .button-container button, .button-container select {
            display: inline-block;
            margin: 0 0.5rem;
            padding: 0.75rem 1.5rem;
            background: linear-gradient(45deg, #00c9a7, #00ffc8);
            color: #fff;
            border-radius: 4px;
            text-decoration: none;
            font-weight: bold;
            font-size: 1rem;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
        }
        .button-container a:hover, .button-container button:hover, .button-container select:hover {
            background: linear-gradient(45deg, #00ffc8, #00c9a7);
            transform: translateY(-3px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }
        .projects-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
            margin: 2rem auto;
            max-width: 1200px;
        }
        .project-card {
            background: #1e293b;
            border-radius: 8px;
            padding: 1.5rem;
            color: #fff;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            transition: transform 0.3s ease;
        }
        .project-card:hover {
            transform: translateY(-5px);
        }
        .project-title {
            font-size: 1.5rem;
            font-weight: bold;
            color: #00ffc8;
            margin-bottom: 0.5rem;
        }
        .project-description {
            font-size: 1rem;
            margin-bottom: 1rem;
            color: #cbd5e1;
        }
        .project-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 1rem;
        }
        .project-info span {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .project-actions {
            text-align: center;
        }
        .project-actions a {
            display: inline-block;
            margin: 0.5rem;
            padding: 0.5rem 1rem;
            border-radius: 4px;
            text-decoration: none;
            color: #fff;
            background: #00ffc8;
            transition: background 0.3s ease;
        }
        .project-actions a:hover {
            background: #00c9a7;
        }
        .map-container {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 80%;
            height: 80%;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            z-index: 1000;
        }
        .map-container iframe {
            width: 100%;
            height: 100%;
            border: none;
            border-radius: 8px;
        }
        .map-container .close-btn {
            position: absolute;
            top: 10px;
            right: 10px;
            background: #ff5e57;
            color: #fff;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 4px;
            cursor: pointer;
        }
        .map-container .close-btn:hover {
            background: #e04b4b;
        }
        .overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 999;
        }
        .stats-container {
            display: flex;
            justify-content: center;
            gap: 1.5rem;
            margin: 2rem auto;
            max-width: 1200px;
        }
        .stat-card {
            background: #1e293b;
            border-radius: 8px;
            padding: 1.5rem;
            color: #fff;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            text-align: center;
            flex: 1;
        }
        .stat-value {
            font-size: 2rem;
            font-weight: bold;
            color: #00ffc8;
            margin-bottom: 0.5rem;
        }
        .stat-label {
            font-size: 1rem;
            color: #cbd5e1;
        }
    </style>
</head>
<body>
<header class="navbar">
    <div class="logo-container">
        <span class="brand-name">FundFlow</span>
    </div>
    <nav>
        <a href="#"><i class="fas fa-info-circle"></i> À propos</a>
        <a href="#"><i class="fas fa-envelope"></i> Contact</a>
        <a href="login.php"><i class="fas fa-sign-in-alt"></i> Connexion</a>
    </nav>
</header>

<main>
    <section class="hero-section">
        <h1><i class="fas fa-rocket"></i> Découvrez des projets innovants</h1>
        <p>Soutenez des idées qui changent le monde</p>
    </section>

    <!-- Statistics Section -->
    <div class="stats-container">
        <div class="stat-card">
            <div class="stat-value"><?= $totalProjects ?></div>
            <div class="stat-label">Projets total</div>
        </div>
        <div class="stat-card">
            <div class="stat-value">€<?= number_format($totalFunding, 2) ?></div>
            <div class="stat-label">Total demandé</div>
        </div>
        <div class="stat-card">
            <div class="stat-value"><?= $projectsByStatus['en_attente'] ?? 0 ?></div>
            <div class="stat-label">En attente</div>
        </div>
        <div class="stat-card">
            <div class="stat-value"><?= $projectsByStatus['actif'] ?? 0 ?></div>
            <div class="stat-label">Actifs</div>
        </div>
        <div class="stat-card">
            <div class="stat-value"><?= $projectsByStatus['termine'] ?? 0 ?></div>
            <div class="stat-label">Terminés</div>
        </div>
    </div>

    <!-- Buttons for PDF Export and Localisation -->
    <div class="button-container">
        <a href="?export_pdf=true"><i class="fas fa-file-pdf"></i> Exporter en PDF</a>
        <button id="show-map-btn"><i class="fas fa-map-marker-alt"></i> Localisation</button>
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

    <!-- Enhanced Search -->
    <div class="search-container" style="text-align: center; margin-bottom: 1.5rem;">
        <form method="GET" class="search-box" style="display: inline-block;">
            <input type="text" name="search" placeholder="Rechercher un projet..." value="<?= htmlspecialchars($searchTerm) ?>" class="btn">
            <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Rechercher</button>
        </form>
        <form method="GET" style="display: inline-block;">
            <select name="montant_sort" onchange="this.form.submit()" class="btn">
                <option value="">Trier par Montant</option>
                <option value="asc" <?= $montantSortOrder === 'asc' ? 'selected' : '' ?>>Montant Croissant</option>
                <option value="desc" <?= $montantSortOrder === 'desc' ? 'selected' : '' ?>>Montant Décroissant</option>
            </select>
        </form>
        <form method="GET" action="" style="display: inline-block;">
            <select name="project1" class="btn">
                <option value="">Sélectionnez Projet 1</option>
                <?php foreach ($projects as $project): ?>
                    <option value="<?= $project['id_projet'] ?>"><?= htmlspecialchars($project['titre']) ?></option>
                <?php endforeach; ?>
            </select>
            <select name="project2" class="btn">
                <option value="">Sélectionnez Projet 2</option>
                <?php foreach ($projects as $project): ?>
                    <option value="<?= $project['id_projet'] ?>"><?= htmlspecialchars($project['titre']) ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit" name="compare_pdf" class="btn btn-primary">
                <i class="fas fa-file-pdf"></i> Comparer en PDF
            </button>
        </form>
    </div>

    <div class="projects-grid">
        <?php foreach ($projects as $project): ?>
            <div class="project-card">
                <div class="project-title"><?= htmlspecialchars($project['titre']) ?></div>
                <div class="project-description"><?= htmlspecialchars($project['description'] ?? 'N/A') ?></div>
                <div class="project-info">
                    <span><i class="fas fa-euro-sign"></i> <?= number_format($project['montant_cible'], 2) ?> €</span>
                    <span><i class="fas fa-clock"></i> <?= $project['duree'] ?> mois</span>
                </div>
                <div class="project-actions">
                    <a href="projet-details.php?id=<?= $project['id_projet'] ?>" class="btn btn-primary">
                        <i class="fas fa-eye"></i> Voir le projet
                    </a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</main>

<script>
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
