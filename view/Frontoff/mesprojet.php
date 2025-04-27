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

// Capture the search term from the request
$searchTerm = $_GET['search'] ?? null;

// Filter projects based on the search term
if ($searchTerm) {
    $projects = array_filter($projects, function ($project) use ($searchTerm) {
        return stripos($project['titre'], $searchTerm) !== false ||
               stripos($project['description'], $searchTerm) !== false;
    });
}

// Capture the sorting order for ID and montant
$idSortOrder = $_GET['id_sort'] ?? null;
$montantSortOrder = $_GET['montant_sort'] ?? null;

// Sort projects based on the sorting order
if ($idSortOrder === 'asc') {
    usort($projects, fn($a, $b) => $a['id_projet'] <=> $b['id_projet']);
} elseif ($idSortOrder === 'desc') {
    usort($projects, fn($a, $b) => $b['id_projet'] <=> $a['id_projet']);
} elseif ($montantSortOrder === 'asc') {
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
        .button-container a, .button-container button {
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
        .button-container a:hover, .button-container button:hover {
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

    <!-- Buttons for Statistics, PDF Export, and Localisation -->
    <div class="button-container">
        <a href="statistique.php"><i class="fas fa-chart-bar"></i> Voir les Statistiques</a>
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

    <!-- Search Bar and Sorting Buttons -->
    <div class="search-container" style="text-align: center; margin-bottom: 1.5rem;">
        <form method="GET" class="search-box" style="display: inline-block;">
            <input type="text" name="search" placeholder="Rechercher un projet..." value="<?= htmlspecialchars($searchTerm) ?>">
            <button type="submit"><i class="fas fa-search"></i> Rechercher</button>
        </form>
        <div class="sort-buttons" style="display: inline-block;">
            <a href="?id_sort=asc" class="btn btn-primary <?= $idSortOrder === 'asc' ? 'active' : '' ?>">
                <i class="fas fa-sort-numeric-up"></i> Trier par ID croissant
            </a>
            <a href="?id_sort=desc" class="btn btn-primary <?= $idSortOrder === 'desc' ? 'active' : '' ?>">
                <i class="fas fa-sort-numeric-down"></i> Trier par ID décroissant
            </a>
            <a href="?montant_sort=asc" class="btn btn-primary <?= $montantSortOrder === 'asc' ? 'active' : '' ?>">
                <i class="fas fa-sort-amount-up"></i> Trier par montant croissant
            </a>
            <a href="?montant_sort=desc" class="btn btn-primary <?= $montantSortOrder === 'desc' ? 'active' : '' ?>">
                <i class="fas fa-sort-amount-down"></i> Trier par montant décroissant
            </a>
        </div>
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
                    <a href="projet-details.php?id=<?= $project['id_projet'] ?>">Voir le projet</a>
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
