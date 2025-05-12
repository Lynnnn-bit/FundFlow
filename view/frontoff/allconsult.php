<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../models/utilisateur.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

try {
    $db = Config::getConnexion();
    $stmt = $db->prepare("SELECT * FROM utilisateur WHERE role = 'consultant'");
    $stmt->execute();
    $consultants = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erreur de base de données: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FundFlow - Consultants</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&family=Playfair+Display:wght@500;600;700&display=swap" rel="stylesheet">
    <style>
        /* Consultant Specific Styles */
        .consultant-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 2rem;
            margin: 3rem 0;
        }
       
        .consultant-card {
            background: var(--glass-bg);
            backdrop-filter: blur(12px);
            border-radius: 16px;
            padding: 2rem;
            border: 1px solid var(--glass-border);
            box-shadow: var(--shadow-md);
            transition: var(--transition);
            text-align: center;
            position: relative;
            overflow: hidden;
        }
       
        .consultant-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(to right, var(--primary), var(--secondary));
        }
       
        .consultant-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
            background: var(--glass-bg-light);
        }
       
        .consultant-avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid rgba(255,255,255,0.2);
            margin: 0 auto 1.5rem;
            box-shadow: var(--shadow-sm);
        }
       
        .consultant-name {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--white);
            margin-bottom: 0.5rem;
            position: relative;
        }
       
        .consultant-name::after {
            content: '';
            position: absolute;
            bottom: -8px;
            left: 50%;
            transform: translateX(-50%);
            width: 50px;
            height: 3px;
            background: var(--primary-light);
            border-radius: 3px;
        }
       
        .consultant-badge {
            display: inline-block;
            padding: 0.4rem 1.2rem;
            background: rgba(76, 201, 240, 0.15);
            color: var(--primary-light);
            border-radius: 50px;
            font-size: 0.85rem;
            font-weight: 600;
            margin-bottom: 1.5rem;
            border: 1px solid rgba(76, 201, 240, 0.3);
        }
       
        .consultant-details {
            text-align: left;
            margin-bottom: 1.5rem;
        }
       
        .consultant-details p {
            margin-bottom: 0.75rem;
            color: rgba(255,255,255,0.9);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
       
        .consultant-details i {
            width: 20px;
            color: var(--primary-light);
        }
       
        .btn-consultant {
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
            background: linear-gradient(to right, var(--secondary), var(--secondary-dark));
            color: white;
            box-shadow: 0 4px 15px rgba(16, 185, 129, 0.4);
            margin-top: 1rem;
            width: 100%;
        }
       
        .btn-consultant:hover {
            background: linear-gradient(to right, var(--secondary-dark), var(--secondary));
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(16, 185, 129, 0.6);
        }
       
        .no-consultants {
            grid-column: 1 / -1;
            text-align: center;
            padding: 4rem;
            background: var(--glass-bg);
            border-radius: 16px;
            border: 1px dashed var(--glass-border);
        }
       
        @media (max-width: 768px) {
            .consultant-grid {
                grid-template-columns: 1fr;
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
        <h1><i class="fas fa-user-tie header-icon"></i> Nos Consultants</h1>
        <p>Trouvez l'expertise dont vous avez besoin pour votre projet</p>
    </div>

    <div class="consultant-grid">
        <?php if (!empty($consultants)): ?>
            <?php foreach ($consultants as $consultant): ?>
                <div class="consultant-card">
                    <img src="assets/default-avatar.png" alt="Photo consultant" class="consultant-avatar">
                    <h3 class="consultant-name"><?= htmlspecialchars($consultant['prenom']) ?> <?= htmlspecialchars($consultant['nom']) ?></h3>
                    <span class="consultant-badge">Consultant Expert</span>
                   
                    <div class="consultant-details">
                        <p><i class="fas fa-envelope"></i> <?= htmlspecialchars($consultant['email']) ?></p>
                        <p><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($consultant['adresse']) ?></p>
                        <p><i class="fas fa-phone"></i> <?= htmlspecialchars($consultant['tel']) ?></p>
                    </div>
                   
                    <a href="addconsultation.php?consultant_id=<?= $consultant['id_utilisateur'] ?>&client_id=<?= $_SESSION['user_id'] ?? '' ?>" class="btn-consultant">
                        <i class="fas fa-calendar-check"></i> Prendre Rendez-vous
                    </a>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="no-consultants">
                <h3>Aucun consultant disponible</h3>
                <p>Il n'y a actuellement aucun consultant enregistré sur la plateforme.</p>
            </div>
        <?php endif; ?>
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
        <li><a href="#">Consultants</a></li>
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