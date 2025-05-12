<?php
include_once '../../control/startupC.php';

$startupC = new startupC();
$startups = $startupC->getAllStartups();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FundFlow - Startups</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&family=Playfair+Display:wght@500;600;700&display=swap" rel="stylesheet">
    <style>
        /* Startup Specific Styles */
        .startup-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(380px, 1fr));
            gap: 2rem;
            margin: 3rem 0;
        }
       
        .startup-card {
            background: var(--glass-bg);
            backdrop-filter: blur(12px);
            border-radius: 16px;
            padding: 2rem;
            border: 1px solid var(--glass-border);
            box-shadow: var(--shadow-md);
            transition: var(--transition);
            position: relative;
            overflow: hidden;
        }
       
        .startup-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(to right, var(--primary), var(--secondary));
        }
       
        .startup-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
            background: var(--glass-bg-light);
        }
       
        .startup-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid var(--glass-border);
        }
       
        .startup-id {
            font-size: 0.9rem;
            color: rgba(255,255,255,0.7);
            background: rgba(0,0,0,0.2);
            padding: 0.35rem 0.75rem;
            border-radius: 50px;
        }
       
        .status-badge {
            display: inline-flex;
            align-items: center;
            padding: 0.4rem 1.2rem;
            border-radius: 50px;
            font-size: 0.85rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            transition: var(--transition-fast);
        }
       
        .status-badge.actif {
            background: rgba(16, 185, 129, 0.15);
            color: var(--secondary);
            box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.3);
        }
       
        .status-badge.inactif {
            background: rgba(220, 38, 38, 0.15);
            color: #dc2626;
            box-shadow: 0 0 0 2px rgba(220, 38, 38, 0.3);
        }
       
        .status-badge:hover {
            transform: translateY(-2px);
        }
       
        .startup-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--white);
            margin-bottom: 0.5rem;
            position: relative;
        }
       
        .startup-title::after {
            content: '';
            position: absolute;
            bottom: -8px;
            left: 0;
            width: 50px;
            height: 3px;
            background: var(--primary-light);
            border-radius: 3px;
        }
       
        .startup-sector {
            display: inline-block;
            padding: 0.35rem 1rem;
            background: rgba(67, 97, 238, 0.15);
            color: var(--primary-light);
            border-radius: 50px;
            font-size: 0.85rem;
            margin-bottom: 1.5rem;
        }
       
        .startup-details p {
            margin-bottom: 1rem;
            color: rgba(255,255,255,0.9);
            line-height: 1.6;
        }
       
        .startup-details strong {
            color: var(--white);
            font-weight: 600;
        }
       
        .startup-details a {
            color: var(--primary-light);
            text-decoration: none;
            transition: var(--transition-fast);
            border-bottom: 1px dotted rgba(76, 201, 240, 0.5);
            padding-bottom: 2px;
        }
       
        .startup-details a:hover {
            color: var(--white);
            border-bottom-color: var(--primary-light);
        }
       
        .startup-media {
            margin: 1.5rem 0;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }
       
        .media-item {
            background: rgba(255,255,255,0.05);
            border-radius: 12px;
            padding: 1rem;
            text-align: center;
            border: 1px solid rgba(255,255,255,0.1);
            transition: var(--transition);
        }
       
        .media-item:hover {
            background: rgba(255,255,255,0.1);
            transform: translateY(-3px);
        }
       
        .media-item img {
            max-width: 100%;
            border-radius: 8px;
            box-shadow: var(--shadow-sm);
        }
       
        .media-item video {
            max-width: 100%;
            border-radius: 8px;
            box-shadow: var(--shadow-sm);
        }
       
        .media-label {
            display: block;
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
            color: rgba(255,255,255,0.7);
        }
       
        /* Enhanced Button Styles */
        .startup-actions {
            display: flex;
            gap: 1rem;
            margin-top: 1.5rem;
        }
       
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.8rem 1.5rem;
            border-radius: 8px;
            font-size: 0.95rem;
            font-weight: 600;
            text-decoration: none;
            transition: var(--transition);
            gap: 0.75rem;
            position: relative;
            overflow: hidden;
            z-index: 1;
            border: none;
            cursor: pointer;
        }
       
        .btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: 0.5s;
            z-index: -1;
        }
       
        .btn:hover::before {
            left: 100%;
        }
       
        .btn i {
            font-size: 1rem;
            transition: var(--transition-fast);
        }
       
        .btn:hover i {
            transform: scale(1.2);
        }
       
        .btn-primary {
            background: linear-gradient(to right, var(--primary), var(--primary-dark));
            color: white;
            box-shadow: 0 4px 15px rgba(67, 97, 238, 0.4);
        }
       
        .btn-primary:hover {
            background: linear-gradient(to right, var(--primary-dark), var(--primary));
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(67, 97, 238, 0.6);
        }
       
        .btn-secondary {
            background: rgba(255,255,255,0.1);
            color: white;
            border: 1px solid rgba(255,255,255,0.2);
        }
       
        .btn-secondary:hover {
            background: rgba(255,255,255,0.2);
            transform: translateY(-3px);
            box-shadow: 0 4px 15px rgba(255, 255, 255, 0.1);
        }
       
        .btn-danger {
            background: linear-gradient(to right, #dc2626, #b91c1c);
            color: white;
            box-shadow: 0 4px 15px rgba(220, 38, 38, 0.4);
        }
       
        .btn-danger:hover {
            background: linear-gradient(to right, #b91c1c, #dc2626);
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(220, 38, 38, 0.6);
        }
       
        .no-results {
            grid-column: 1 / -1;
            text-align: center;
            padding: 4rem;
            background: var(--glass-bg);
            border-radius: 16px;
            border: 1px dashed var(--glass-border);
        }
       
        .no-results h3 {
            font-size: 1.75rem;
            color: var(--white);
            margin-bottom: 1rem;
        }
       
        .no-results p {
            color: rgba(255,255,255,0.7);
            max-width: 500px;
            margin: 0 auto;
        }
       
        .button-center {
            text-align: center;
            margin-top: 2rem;
        }
       
        @media (max-width: 768px) {
            .startup-grid {
                grid-template-columns: 1fr;
            }
           
            .startup-media {
                grid-template-columns: 1fr;
            }
           
            .startup-actions {
                flex-direction: column;
            }
           
            .btn {
                width: 100%;
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
        <h1><i class="fas fa-lightbulb header-icon"></i> Liste des Startups</h1>
        <p>Découvrez les startups innovantes de notre plateforme</p>
    </div>

    <div class="startup-grid">
        <?php if (!empty($startups)): ?>
            <?php foreach ($startups as $s): ?>
                <?php
                    $logoFile = basename($s['logo']);
                    $videoFile = basename($s['video_presentation']);
                    $logoPath = '../backoff/uploads/' . $logoFile;
                    $videoPath = '../backoff/uploads/' . $videoFile;
                ?>
                <div class="startup-card">
                    <div class="startup-header">
                        <span class="startup-id">ID: <?= htmlspecialchars($s['id_startup']) ?></span>
                        <span class="status-badge actif">Active</span>
                    </div>
                   
                    <h3 class="startup-title"><?= htmlspecialchars($s['nom_startup']) ?></h3>
                    <span class="startup-sector"><?= htmlspecialchars($s['secteur']) ?></span>
                   
                    <div class="startup-details">
                        <p><strong>Description:</strong> <?= htmlspecialchars($s['description']) ?></p>
                        <p><strong>Email:</strong> <?= htmlspecialchars($s['email']) ?></p>
                        <p><strong>Site web:</strong> <a href="<?= htmlspecialchars($s['adresse_site']) ?>" target="_blank"><?= htmlspecialchars($s['adresse_site']) ?></a></p>
                    </div>
                   
                    <div class="startup-media">
                        <div class="media-item">
                            <span class="media-label"><i class="fas fa-image"></i> Logo</span>
                            <img src="<?= $logoPath ?>" alt="Logo startup">
                        </div>
                        <div class="media-item">
                            <span class="media-label"><i class="fas fa-video"></i> Vidéo</span>
                            <video controls>
                                <source src="<?= $videoPath ?>" type="video/mp4">
                                Votre navigateur ne supporte pas la lecture vidéo.
                            </video>
                        </div>
                    </div>
                   
                    <div class="startup-actions">
                        <a href="updatestartup.php?id=<?= $s['id_startup'] ?>" class="btn btn-primary">
                            <i class="fas fa-edit"></i> Modifier
                        </a>
                        <a href="deletestartup.php?id=<?= $s['id_startup'] ?>" class="btn btn-danger" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette startup?')">
                            <i class="fas fa-trash"></i> Supprimer
                        </a>
                        <a href="generate_qr.php?id=<?= $s['id_startup'] ?>" class="btn btn-secondary" target="_blank">
                            <i class="fas fa-qrcode"></i> QR Code
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="no-results">
                <h3>Aucune startup trouvée</h3>
                <p>Il n'y a actuellement aucune startup enregistrée sur la plateforme.</p>
            </div>
        <?php endif; ?>
    </div>
   
    <div class="button-center">
        <a href="frontoffice.php" class="btn btn-primary"><i class="fas fa-plus"></i> Ajouter un événement</a>
    </div>
</div>

<footer class="footer">
  <div class="footer-container">
    <div class="footer-col logo-col">
      <img src="assets/Logo_FundFlow.png" alt="Logo" class="footer-logo">
      <p class="footer-description">Plateforme de financement collaboratif</p>
    </div>
    <div class="footer-col links-col">
      <h4>Liens Rapides</h4>
      <ul>
        <li><a href="frontoffice.php">Accueil</a></li>
        <li><a href="#">Startups</a></li>
        <li><a href="#">Contact</a></li>
      </ul>
    </div>
    <div class="footer-col contact-col">
      <h4>Contact</h4>
      <p>Email: contact@fundflow.com</p>
    </div>
    <div class="footer-col social-col">
      <h4>Suivez-nous</h4>
      <div class="social-icons">
        <a href="#"><i class="fab fa-facebook"></i></a>
        <a href="#"><i class="fab fa-twitter"></i></a>
      </div>
    </div>
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