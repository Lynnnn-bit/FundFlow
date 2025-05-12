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

$financeController = new FinanceController();

// Gestion de la recherche et du tri
$searchQuery = $_GET['search'] ?? '';
$sortColumn = $_GET['sort_column'] ?? 'id_demande';
$sortOrder = $_GET['sort_order'] ?? 'asc';

// Récupération initiale des demandes
$demands = $financeController->getAllFinanceRequests();

// Filtrage des demandes
if (!empty($searchQuery)) {
    $demands = array_filter($demands, function ($demand) use ($searchQuery) {
        return stripos($demand['projet_titre'], $searchQuery) !== false;
    });
}

// Tri des demandes
if (!empty($sortColumn) && in_array($sortColumn, ['id_demande', 'projet_titre', 'montant_demandee', 'duree', 'status'])) {
    usort($demands, function ($a, $b) use ($sortColumn, $sortOrder) {
        if ($sortOrder === 'asc') {
            return $a[$sortColumn] <=> $b[$sortColumn];
        } else {
            return $b[$sortColumn] <=> $a[$sortColumn];
        }
    });
}

// Affichage des messages de session
if (isset($_SESSION['success'])) {
    $success_message = $_SESSION['success'];
    unset($_SESSION['success']);
}
if (isset($_SESSION['error'])) {
    $error_message = $_SESSION['error'];
    unset($_SESSION['error']);
}

$totalDemands = count($demands);
$totalAmount = array_sum(array_map(fn($d) => $d['montant_demandee'] ?? 0, $demands));
$demandsByStatus = array_reduce($demands, function ($carry, $demand) {
    $status = $demand['status'] ?? 'unknown';
    $carry[$status] = ($carry[$status] ?? 0) + 1;
    return $carry;
}, []);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FundFlow - Demandes de Financement</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&family=Playfair+Display:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <style>
        /* Demands Specific Styles */
        .demands-container {
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

        .stat-icon.bg-purple {
            background: rgba(139, 92, 246, 0.2);
            color: #8b5cf6;
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

        .request-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.5rem;
        }

        .request-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: all 0.3s ease;
        }

        .request-card:hover {
            transform: translateY(-5px);
            background: rgba(255, 255, 255, 0.15);
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 1.5rem;
            background: rgba(0,0,0,0.1);
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }

        .request-id {
            font-size: 0.8rem;
            color: rgba(255,255,255,0.7);
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

        .card-body {
            padding: 1.5rem;
        }

        .project-title {
            font-size: 1.25rem;
            margin-bottom: 1rem;
            color: white;
        }

        .project-description {
            position: relative;
            margin-bottom: 1.5rem;
            color: rgba(255,255,255,0.8);
            font-size: 0.9rem;
            line-height: 1.6;
        }

        .project-description i {
            position: absolute;
            top: 0;
            left: 0;
            color: rgba(255,255,255,0.2);
            font-size: 1.5rem;
        }

        .project-description p {
            padding-left: 1.5rem;
        }

        .request-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        .detail-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .detail-icon {
            width: 36px;
            height: 36px;
            border-radius: 8px;
            background: rgba(255,255,255,0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary-light);
        }

        .detail-label {
            font-size: 0.75rem;
            color: rgba(255,255,255,0.6);
        }

        .detail-value {
            font-size: 0.9rem;
            font-weight: 500;
            color: white;
        }

        .card-footer {
            padding: 1rem 1.5rem;
            border-top: 1px solid rgba(255,255,255,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .invest-meter {
            flex: 1;
            height: 8px;
            background: rgba(255,255,255,0.1);
            border-radius: 4px;
            overflow: hidden;
            margin-right: 1rem;
        }

        .meter-bar {
            height: 100%;
            background: linear-gradient(to right, var(--primary), var(--primary-light));
            border-radius: 4px;
            transition: width 0.5s ease;
        }

        .meter-text {
            font-size: 0.75rem;
            color: rgba(255,255,255,0.8);
        }

        .respond-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            background: rgba(255,255,255,0.1);
            color: white;
            border-radius: 8px;
            text-decoration: none;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }

        .respond-btn:hover {
            background: var(--primary);
            transform: translateY(-2px);
        }

        .no-results {
            grid-column: 1 / -1;
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

        .refresh-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            background: var(--primary);
            color: white;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .refresh-btn:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
        }

        @media (max-width: 768px) {
            .filter-form {
                flex-direction: column;
            }
            
            .search-group {
                min-width: 100%;
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
                        <li><a href="historique.php">Mes demandes</a></li>
                    <?php endif; ?>
                    <li><a href="allconsult.php">Consultation</a></li>
                    <li><a href="connexion.php?logout=1" class="logout">Déconnexion</a></li>
                </ul>
            </div>
        </div>
    </header>

    <div class="page-header animate__animated animate__fadeInDown">
        <h1><i class="fas fa-file-invoice-dollar header-icon"></i> Demandes de Financement</h1>
        <p>Explorez et gérez les opportunités d'investissement</p>
    </div>

    <div class="demands-container">
        <!-- Stats Cards -->
        <div class="stats-grid">
            <div class="stat-card animate__animated animate__fadeIn">
                <div class="stat-icon bg-blue">
                    <i class="fas fa-file-invoice-dollar"></i>
                </div>
                <div class="stat-content">
                    <h3>Demandes Total</h3>
                    <div class="stat-value"><?= $totalDemands ?></div>
                </div>
            </div>
            <div class="stat-card animate__animated animate__fadeIn animate__delay-1s">
                <div class="stat-icon bg-green">
                    <i class="fas fa-euro-sign"></i>
                </div>
                <div class="stat-content">
                    <h3>Total Demandé</h3>
                    <div class="stat-value">€<?= number_format($totalAmount, 2) ?></div>
                </div>
            </div>
            
            </div>
        </div>

        <?php if (isset($success_message)): ?>
            <div class="alert alert-success animate__animated animate__fadeIn">
                <i class="fas fa-check-circle"></i> <?= htmlspecialchars($success_message) ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($error_message)): ?>
            <div class="alert alert-error animate__animated animate__fadeIn">
                <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error_message) ?>
            </div>
        <?php endif; ?>

        <!-- Search and Filter -->
        <div class="filter-panel animate__animated animate__fadeIn">
            <form method="GET" class="filter-form">
                <div class="search-group">
                    <i class="fas fa-search search-icon"></i>
                    <input type="text" name="search" placeholder="Rechercher des projets..." value="<?= htmlspecialchars($searchQuery) ?>">
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

        <!-- Demands Grid -->
        <div class="request-grid">
            <?php if (empty($demands)): ?>
                <div class="no-results animate__animated animate__fadeIn">
                    <div class="no-results-illustration">
                        <i class="fas fa-folder-open"></i>
                    </div>
                    <h3>Aucune demande trouvée</h3>
                    <p>Essayez d'ajuster vos critères de recherche</p>
                    <button class="refresh-btn" onclick="window.location.reload()">
                        <i class="fas fa-sync-alt"></i> Actualiser
                    </button>
                </div>
            <?php else: ?>
                <?php foreach ($demands as $demand): ?>
                    <div class="request-card animate__animated animate__fadeInUp">
                        <div class="card-header">
                            <span class="request-id">#<?= htmlspecialchars($demand['id_demande']) ?></span>
                            <span class="status-badge <?= 
                                $demand['status'] == 'accepte' ? 'approved' : 
                                ($demand['status'] == 'rejete' ? 'rejected' : 'pending') ?>">
                                <span class="status-dot"></span>
                                <?= ucfirst($demand['status']) ?>
                            </span>
                        </div>
                        <div class="card-body">
                            <h3 class="project-title"><?= htmlspecialchars($demand['projet_titre']) ?></h3>
                            
                            <div class="request-details">
                                <div class="detail-item">
                                    <div class="detail-icon">
                                        <i class="fas fa-euro-sign"></i>
                                    </div>
                                    <div>
                                        <span class="detail-label">Montant</span>
                                        <span class="detail-value">€<?= number_format($demand['montant_demandee'], 2) ?></span>
                                    </div>
                                </div>
                                <div class="detail-item">
                                    <div class="detail-icon">
                                        <i class="fas fa-calendar-alt"></i>
                                    </div>
                                    <div>
                                        <span class="detail-label">Durée</span>
                                        <span class="detail-value"><?= htmlspecialchars($demand['duree']) ?> mois</span>
                                    </div>
                                </div>
                                <div class="detail-item">
                                    <div class="detail-icon">
                                        <i class="fas fa-comments"></i>
                                    </div>
                                    <div>
                                        <span class="detail-label">Réponses</span>
                                        <span class="detail-value"><?= htmlspecialchars($demand['nb_reponses']) ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <a href="new_response.php?demande_id=<?= $demand['id_demande'] ?>" class="respond-btn">
                                <i class="fas fa-paper-plane"></i> Répondre
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
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
        <a href="#" aria-label="Instagram"><i class="fas fa-instagram"></i></a>
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
</script>
</body>
</html>