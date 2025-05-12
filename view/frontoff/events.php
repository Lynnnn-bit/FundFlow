<?php
// events.php
session_start();
include_once '../../control/startupC.php';
include_once '../../control/EvennementC.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: connexion.php");
    exit();
}

$evenementC = new EvennementC();
$startupC = new startupC();

$sort = isset($_GET['sort']) ? $_GET['sort'] : null;
$evenements = $evenementC->getAllEvenements();

if ($sort === 'places') {
    usort($evenements, function($a, $b) {
        return $a['nb_place'] - $b['nb_place'];
    });
}

$startups = $startupC->getAllStartups();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FundFlow - Événements</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&family=Playfair+Display:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <style>
        /* Events Specific Styles */
        .events-container {
            max-width: 1200px;
            margin: 2rem auto;
        }

        .action-buttons {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
            flex-wrap: wrap;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background: var(--primary);
            color: white;
        }

        .btn-primary:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(67, 97, 238, 0.4);
        }

        .btn-secondary {
            background: rgba(255,255,255,0.1);
            color: white;
        }

        .btn-secondary:hover {
            background: rgba(255,255,255,0.2);
            transform: translateY(-2px);
        }

        .btn-info {
            background: rgba(59, 130, 246, 0.2);
            color: #3b82f6;
        }

        .btn-info:hover {
            background: rgba(59, 130, 246, 0.3);
            color: white;
        }

        .btn-success {
            background: var(--secondary);
            color: white;
        }

        .btn-success:hover {
            background: var(--secondary-dark);
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(16, 185, 129, 0.4);
        }

        .event-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 1.5rem;
        }

        .event-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: all 0.3s ease;
        }

        .event-card:hover {
            transform: translateY(-5px);
            background: rgba(255, 255, 255, 0.15);
        }

        .event-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }

        .event-content {
            padding: 1.5rem;
        }

        .event-content h3 {
            font-size: 1.5rem;
            margin-bottom: 1rem;
            color: white;
        }

        .event-details {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .detail-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            color: rgba(255,255,255,0.8);
        }

        .detail-item i {
            color: var(--primary-light);
        }

        .event-actions {
            display: flex;
            gap: 0.75rem;
            flex-wrap: wrap;
        }

        .event-actions form {
            flex: 1;
            min-width: 100px;
        }

        .event-actions button {
            width: 100%;
            padding: 0.5rem;
            border: none;
            border-radius: 8px;
            font-size: 0.9rem;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .event-actions button:hover {
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

        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .alert-success {
            background: rgba(16, 185, 129, 0.2);
            color: #10b981;
            border-left: 4px solid #10b981;
        }

        .alert-danger {
            background: rgba(220, 38, 38, 0.2);
            color: #dc2626;
            border-left: 4px solid #dc2626;
        }

        @media (max-width: 768px) {
            .event-grid {
                grid-template-columns: 1fr;
            }
            
            .action-buttons {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
                justify-content: center;
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
                        <li><a href="historique.php">mes demandes</a></li>
                    <?php endif; ?>
                    <li><a href="allconsult.php">Consultation</a></li>
                    <li><a href="connexion.php?logout=1" class="logout">Déconnexion</a></li>
                </ul>
            </div>
        </div>
    </header>

    <div class="events-container">
        <div class="page-header animate__animated animate__fadeInDown">
            <h1><i class="fas fa-calendar-alt"></i> Événements à venir</h1>
            <p>Découvrez et participez à nos événements exclusifs</p>
        </div>

        <?php if(isset($_SESSION['success'])): ?>
            <div class="alert alert-success animate__animated animate__fadeIn">
                <i class="fas fa-check-circle"></i> <?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>

        <?php if(isset($_SESSION['error'])): ?>
            <div class="alert alert-danger animate__animated animate__fadeIn">
                <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <div class="action-buttons animate__animated animate__fadeIn">
            <a href="generate_pdf.php" class="btn btn-primary">
                <i class="fas fa-file-pdf"></i> Exporter en PDF
            </a>
            <a href="?sort=places" class="btn btn-secondary">
                <i class="fas fa-sort-amount-down"></i> Trier par places
            </a>
            <?php if ($sort === 'places'): ?>
                <a href="events.php" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Annuler le tri
                </a>
            <?php endif; ?>
            <a href="statistiques.php" class="btn btn-info">
                <i class="fas fa-chart-pie"></i> Statistiques
            </a>
            <a href="frontoffice.php" class="btn btn-success">
                <i class="fas fa-plus"></i> Ajouter un événement
            </a>
        </div>

        <div class="event-grid">
            <?php if(!empty($evenements)): ?>
                <?php foreach($evenements as $event): ?>
                    <div class="event-card animate__animated animate__fadeInUp">
                        <?php if(!empty($event['affiche'])): ?>
                            <img src="../admin/<?= htmlspecialchars($event['affiche']) ?>" alt="Affiche de l'événement" class="event-image">
                        <?php endif; ?>
                        
                        <div class="event-content">
                            <h3><?= htmlspecialchars($event['nom']) ?></h3>
                            <div class="event-details">
                                <div class="detail-item">
                                    <i class="fas fa-calendar-day"></i>
                                    <span><?= htmlspecialchars($event['date_evenement']) ?></span>
                                </div>
                                <div class="detail-item">
                                    <i class="fas fa-clock"></i>
                                    <span><?= htmlspecialchars($event['horaire']) ?></span>
                                </div>
                                <div class="detail-item">
                                    <i class="fas fa-tag"></i>
                                    <span><?= htmlspecialchars($event['type']) ?></span>
                                </div>
                                <div class="detail-item">
                                    <i class="fas fa-users"></i>
                                    <span><?= htmlspecialchars($event['nb_place']) ?> places</span>
                                </div>
                            </div>
                            
                            <div class="event-actions">
                                <form method="GET" action="updateevents.php">
                                    <input type="hidden" name="id" value="<?= $event['id_evenement'] ?>">
                                    <button type="submit" class="btn btn-sm btn-primary">
                                        <i class="fas fa-edit"></i> Modifier
                                    </button>
                                </form>

                                <form method="POST" action="deleteevents.php" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cet événement ?')">
                                    <input type="hidden" name="id_evenement" value="<?= $event['id_evenement'] ?>">
                                    <button type="submit" class="btn btn-sm btn-danger">
                                        <i class="fas fa-trash"></i> Supprimer
                                    </button>
                                </form>

                                <form method="GET" action="participer.php">
                                    <input type="hidden" name="id_evenement" value="<?= $event['id_evenement'] ?>">
                                    <button type="submit" class="btn btn-sm btn-success">
                                        <i class="fas fa-user-plus"></i> Participer
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="no-results animate__animated animate__fadeIn">
                    <div class="no-results-illustration">
                        <i class="fas fa-calendar-times"></i>
                    </div>
                    <h3>Aucun événement prévu</h3>
                    <p>Il n'y a aucun événement programmé pour le moment.</p>
                    <a href="frontoffice.php" class="refresh-btn"><i class="fas fa-plus"></i> Créer un événement</a>
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