<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../control/ProjectController.php';

// Check session if needed
session_start();

// Redirect if not logged in (if required)
if (!isset($_SESSION['user_id'])) {
    header("Location: connexion.php");
    exit();
}

$controller = new ProjectController();
$projectId = $_GET['id'] ?? null;

if (!$projectId) {
    die("ID du projet manquant.");
}

$project = $controller->getProjectById($projectId);

if (!$project) {
    die("Projet introuvable.");
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FundFlow - Détails du Projet</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&family=Playfair+Display:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <style>
        /* Project Details Specific Styles */
        .project-details-container {
            max-width: 1000px;
            margin: 2rem auto;
            padding: 2rem;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-radius: 24px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            animation: fadeIn 0.5s ease-out;
        }

        .project-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid rgba(255,255,255,0.2);
        }

        .project-title {
            font-size: 2.5rem;
            color: white;
            margin: 0;
            font-family: 'Playfair Display', serif;
        }

        .project-status {
            display: inline-flex;
            align-items: center;
            padding: 0.5rem 1.5rem;
            border-radius: 50px;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.9rem;
        }

        .status-active {
            background: rgba(16, 185, 129, 0.2);
            color: #10b981;
            border: 1px solid rgba(16, 185, 129, 0.3);
        }

        .status-pending {
            background: rgba(249, 115, 22, 0.2);
            color: #f97316;
            border: 1px solid rgba(249, 115, 22, 0.3);
        }

        .status-completed {
            background: rgba(59, 130, 246, 0.2);
            color: #3b82f6;
            border: 1px solid rgba(59, 130, 246, 0.3);
        }

        .project-content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
        }

        .project-description {
            color: rgba(255,255,255,0.9);
            line-height: 1.8;
            font-size: 1.1rem;
        }

        .project-meta {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
        }

        .meta-card {
            background: rgba(255,255,255,0.05);
            border-radius: 12px;
            padding: 1.5rem;
            border: 1px solid rgba(255,255,255,0.1);
        }

        .meta-label {
            font-size: 0.9rem;
            color: rgba(255,255,255,0.7);
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .meta-value {
            font-size: 1.5rem;
            font-weight: 600;
            color: white;
        }

        .meta-value.amount {
            color: var(--primary-light);
        }

        .project-image {
            width: 100%;
            border-radius: 16px;
            margin-top: 2rem;
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
        }

        .project-actions {
            display: flex;
            gap: 1rem;
            margin-top: 3rem;
            justify-content: flex-end;
        }

        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            background: rgba(255,255,255,0.1);
            color: white;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .back-btn:hover {
            background: var(--primary);
            transform: translateY(-2px);
        }

        .invest-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            background: var(--secondary);
            color: white;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .invest-btn:hover {
            background: var(--secondary-dark);
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(16, 185, 129, 0.4);
        }

        @media (max-width: 768px) {
            .project-content {
                grid-template-columns: 1fr;
            }
            
            .project-meta {
                grid-template-columns: 1fr;
            }
            
            .project-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }
            
            .project-title {
                font-size: 2rem;
            }
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>
<div class="background-effect"></div>
<div class="particles-container" id="particles-js"></div>

<div class="dashboard-container">
    <header class="navbar">
        <div class="logo-container">
            <img src="assets/Logo_FundFlow.png" alt="FundFlow Logo" class="brand-logo">
        </div>
        
        <div class="nav-links">
            <a href="apropos.html" class="nav-link"><i class="fas fa-info-circle"></i> À propos</a>
            <a href="contact.html" class="nav-link"><i class="fas fa-envelope"></i> Contact</a>
            <a href="partenaire.php" class="nav-link"><i class="fas fa-handshake"></i> Partenariats</a>
            <a href="events.php" class="nav-link"><i class="fas fa-calendar-alt"></i> Événements</a>
            <a href="startup.php" class="nav-link"><i class="fas fa-lightbulb"></i> Startups</a>
            
            <select onchange="handleMenu(this)" class="profile-menu">
                <option value="">Mon compte ▼</option>
                <option value="profiles">Profil</option>
                <option value="mesprojet">Mes projets</option>
                <option value="startup">Startups</option>
                <option value="events">Événements</option>
                <option value="partenaire">Partenariats</option>
                <option value="logout">Déconnexion</option>
            </select>
        </div>
    </header>

    <div class="project-details-container">
        <div class="project-header">
            <h1 class="project-title"><?= htmlspecialchars($project['titre']) ?></h1>
            <span class="project-status status-<?= strtolower($project['statut'] ?? 'pending') ?>">
                <?= ucfirst($project['statut'] ?? 'En attente') ?>
            </span>
        </div>

        <div class="project-content">
            <div>
                <h3>Description du Projet</h3>
                <p class="project-description"><?= htmlspecialchars($project['description']) ?></p>
                
                <?php if (!empty($project['image_url'])): ?>
                <img src="<?= htmlspecialchars($project['image_url']) ?>" alt="Project Image" class="project-image">
                <?php endif; ?>
            </div>
            
            <div class="project-meta">
                <div class="meta-card">
                    <div class="meta-label"><i class="fas fa-euro-sign"></i> Montant Cible</div>
                    <div class="meta-value amount"><?= number_format($project['montant_cible'], 2) ?> €</div>
                </div>
                
                <div class="meta-card">
                    <div class="meta-label"><i class="fas fa-clock"></i> Durée</div>
                    <div class="meta-value"><?= $project['duree'] ?> mois</div>
                </div>
                
                <div class="meta-card">
                    <div class="meta-label"><i class="fas fa-layer-group"></i> Catégorie</div>
                    <div class="meta-value"><?= htmlspecialchars($project['nom_categorie'] ?? 'N/A') ?></div>
                </div>
                
                
            </div>
        </div>

        <div class="project-actions">
            <a href="mesprojet.php" class="back-btn"><i class="fas fa-arrow-left"></i> Retour aux projets</a>
            <a href="invest.php?id=<?= $projectId ?>" class="invest-btn"><i class="fas fa-hand-holding-usd"></i> Investir</a>
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