<?php
require_once __DIR__ . '/../../config.php';

// Vérification de session si nécessaire
session_start();

// Redirect to login if not authenticated
if (!isset($_SESSION['user_id'])) {
    header("Location: connexion.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FundFlow - Acceuil</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&family=Playfair+Display:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <style>
        /* Dashboard Specific Styles */
        body {
            background: linear-gradient(135deg, #3a56d4 0%, #10b981 100%);
            background-size: 200% 200%;
            animation: gradientBG 12s ease infinite;
            font-family: 'Montserrat', sans-serif;
            min-height: 100vh;
        }

        .dashboard-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 2rem;
        }

        /* Navbar */
        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1.5rem 0;
            margin-bottom: 3rem;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-radius: 16px;
            padding: 1.5rem 2rem;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            border: 1px solid rgba(255,255,255,0.2);
        }

        .logo-container {
            display: flex;
            align-items: center;
        }

        .brand-logo {
            height: 50px;
            width: auto;
        }

        .nav-links {
            display: flex;
            gap: 1.5rem;
            align-items: center;
        }

        .nav-link {
            color: white;
            text-decoration: none;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.25rem;
            border-radius: 50px;
            transition: all 0.3s ease;
        }

        .nav-link:hover {
            background: rgba(255,255,255,0.2);
            transform: translateY(-2px);
        }

        .nav-link i {
            font-size: 1.1rem;
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

        /* Hero Section */
        .dashboard-hero {
            display: flex;
            align-items: center;
            gap: 4rem;
            margin-top: 3rem;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-radius: 24px;
            padding: 4rem;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            margin-bottom: 3rem;
        }

        .hero-text {
            flex: 1;
        }

        .hero-title {
            font-size: 3rem;
            font-weight: 700;
            line-height: 1.2;
            margin-bottom: 1.5rem;
            color: white;
            font-family: 'Playfair Display', serif;
        }

        .hero-title span {
            background: linear-gradient(to right, var(--primary-light), var(--secondary));
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .hero-subtitle {
            font-size: 1.25rem;
            color: rgba(255,255,255,0.9);
            margin-bottom: 2rem;
            line-height: 1.6;
        }

        .hero-image {
            flex: 1;
            text-align: center;
        }

        .hero-img {
            max-width: 100%;
            height: auto;
            border-radius: 16px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.15);
        }

        /* Quick Actions */
        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
            margin-bottom: 3rem;
        }

        .action-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border-radius: 16px;
            padding: 2rem;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: all 0.3s ease;
            text-align: center;
            color: white;
        }

        .action-card:hover {
            transform: translateY(-5px);
            background: rgba(255, 255, 255, 0.15);
            box-shadow: 0 12px 40px rgba(0,0,0,0.2);
        }

        .action-icon {
            font-size: 2.5rem;
            margin-bottom: 1.5rem;
            color: var(--primary-light);
        }

        .action-title {
            font-size: 1.5rem;
            margin-bottom: 1rem;
            font-weight: 600;
        }

        .action-description {
            font-size: 1rem;
            opacity: 0.9;
            margin-bottom: 1.5rem;
        }

        .action-btn {
            display: inline-block;
            padding: 0.75rem 1.5rem;
            background: rgba(255,255,255,0.2);
            color: white;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .action-btn:hover {
            background: var(--primary);
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(67, 97, 238, 0.4);
        }

        /* Background Effects */
        .background-effect {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle at 20% 30%, rgba(58, 86, 212, 0.3) 0%, transparent 50%),
                        radial-gradient(circle at 80% 70%, rgba(16, 185, 129, 0.3) 0%, transparent 50%);
            z-index: -2;
        }

        .particles-container {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
        }

        @keyframes gradientBG {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        /* Responsive Design */
        @media (max-width: 1200px) {
            .dashboard-hero {
                flex-direction: column;
                text-align: center;
                padding: 3rem;
            }
            
            .hero-text {
                margin-bottom: 3rem;
            }
            
            .hero-title {
                font-size: 2.5rem;
            }
        }

        @media (max-width: 768px) {
            .navbar {
                flex-direction: column;
                gap: 1rem;
                padding: 1.5rem;
            }
            
            .nav-links {
                flex-direction: column;
                width: 100%;
            }
            
            .nav-link {
                width: 100%;
                justify-content: center;
            }
            
            .profile-menu-btn {
                width: 100%;
                text-align: center;
            }
            
            .dashboard-hero {
                padding: 2rem;
            }
            
            .hero-title {
                font-size: 2rem;
            }
            
            .quick-actions {
                grid-template-columns: 1fr;
            }
        }
        /* Add this to your existing CSS in acceuil2.php */
@media (max-width: 1200px) {
    .nav-links {
        gap: 1rem;
    }
}

@media (max-width: 992px) {
    .nav-links {
        flex-wrap: wrap;
        justify-content: center;
    }
    
    .nav-link {
        padding: 0.5rem 1rem;
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

    <div class="dashboard-hero animate__animated animate__fadeIn">
        <div class="hero-text">
            <h1 class="hero-title">Bienvenue sur votre <span>tableau de bord</span></h1>
            <p class="hero-subtitle">FundFlow est la plateforme qui relie les investisseurs aux entrepreneurs et startups à la recherche de financement. Transformez vos idées en réalité ou diversifiez votre portefeuille d'investissement.</p>
        </div>
        <div class="hero-image">
            <img src="assets/meeting.png" alt="Illustration de réunion" class="hero-img">
        </div>
    </div>

    <div class="quick-actions">
        <?php if ($_SESSION['user']['role'] === 'entrepreneur'): ?>
            <div class="action-card animate__animated animate__fadeInUp">
                <div class="action-icon">
                    <i class="fas fa-lightbulb"></i>
                </div>
                <h3 class="action-title">Projets</h3>
                <p class="action-description">Gérez vos projets innovants et suivez leur progression</p>
                <a href="mesprojet.php" class="action-btn">Voir mes projets</a>
            </div>
        <?php endif; ?>
        <?php if ($_SESSION['user']['role'] === 'investisseur'): ?>
            <div class="action-card animate__animated animate__fadeInUp animate__delay-1s">
                <div class="action-icon">
                    <i class="fas fa-chart-line"></i>
                </div>
                <h3 class="action-title">Investissements</h3>
                <p class="action-description">Découvrez des opportunités d'investissement prometteuses</p>
                <a href="demands_list.php" class="action-btn">Explorer</a>
            </div>
        <?php endif; ?>
        
        <div class="action-card animate__animated animate__fadeInUp animate__delay-2s">
            <div class="action-icon">
                <i class="fas fa-users"></i>
            </div>
            <h3 class="action-title">Réseautage</h3>
            <p class="action-description">Connectez-vous avec d'autres professionnels et investisseurs</p>
            <a href="events.php" class="action-btn">Voir les événements</a>
        </div>
    </div>
</div>

<footer class="footer">
  <div class="footer-container">
    <div class="footer-col logo-col">
      <img src="assets/Logo_FundFlow.png" alt="Company Logo" class="footer-logo">
      <p class="footer-description">Short tagline or description of the company.</p>
    </div>
    <div class="footer-col links-col">
      <h4>Quick Links</h4>
      <ul>
        <li><a href="#">Home</a></li>
        <li><a href="#">About</a></li>
        <li><a href="#">Services</a></li>
        <li><a href="#">Blog</a></li>
        <li><a href="#">Contact</a></li>
      </ul>
    </div>
    <div class="footer-col contact-col">
      <h4>Contact Us</h4>
      <p>1234 Street Rd, Suite 1234</p>
      <p>City, State 12345</p>
      <p>Phone: (123) 456-7890</p>
      <p>Email: <a href="mailto:info@example.com">info@example.com</a></p>
    </div>
    <div class="footer-col social-col">
      <h4>Follow Us</h4>
      <div class="social-icons">
        <a href="#" aria-label="Facebook"><i class="fab fa-facebook"></i></a>
        <a href="#" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
        <a href="#" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
        <a href="#" aria-label="LinkedIn"><i class="fab fa-linkedin"></i></a>
      </div>
    </div>
  </div>
  <div class="footer-legal">
    <a href="#">Privacy Policy</a> |
    <a href="#">Terms of Service</a> |
    <span>&copy; 2025 Your Company</span>
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