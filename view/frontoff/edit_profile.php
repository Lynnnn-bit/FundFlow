<?php
session_start();
require_once __DIR__ . '/../../config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: connexion.php");
    exit();
}

$db = Config::getConnexion();
$user = null;

try {
    $stmt = $db->prepare("SELECT * FROM utilisateur WHERE id_utilisateur = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erreur de base de données : " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = $_POST['nom'];
    $prenom = $_POST['prenom'];
    $email = $_POST['email'];
    $adresse = $_POST['adresse'];
    $tel = $_POST['tel'];

    try {
        $stmt = $db->prepare("UPDATE utilisateur SET 
            nom = ?, 
            prenom = ?, 
            email = ?, 
            adresse = ?, 
            tel = ? 
            WHERE id_utilisateur = ?");
        
        $stmt->execute([$nom, $prenom, $email, $adresse, $tel, $_SESSION['user_id']]);
        header("Location: profiles.php");
        exit();
    } catch (PDOException $e) {
        die("Erreur de mise à jour : " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FundFlow - Modifier Profil</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&family=Playfair+Display:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <style>
        /* Edit Profile Specific Styles */
        .edit-profile-container {
            max-width: 800px;
            margin: 2rem auto;
        }

        .profile-form {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-radius: 16px;
            padding: 2rem;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            color: rgba(255,255,255,0.9);
            font-weight: 500;
        }

        .form-control {
            width: 100%;
            padding: 0.75rem 1rem;
            background: rgba(255,255,255,0.9);
            border: 1px solid rgba(255,255,255,0.3);
            border-radius: 8px;
            font-size: 1rem;
            color: var(--dark);
            transition: all 0.3s ease;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.3);
        }

        .form-actions {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
        }

        @media (max-width: 768px) {
            .profile-form {
                padding: 1.5rem;
            }
            
            .form-actions {
                flex-direction: column;
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

    <div class="edit-profile-container">
        <div class="page-header animate__animated animate__fadeInDown">
            <h1><i class="fas fa-user-edit"></i> Modifier Profil</h1>
            <p>Mettez à jour vos informations personnelles</p>
        </div>

        <div class="profile-form animate__animated animate__fadeInUp">
            <form method="POST">
                <div class="form-group">
                    <label class="form-label"><i class="fas fa-user"></i> Nom *</label>
                    <input type="text" class="form-control" name="nom" value="<?= htmlspecialchars($user['nom']) ?>" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label"><i class="fas fa-user"></i> Prénom *</label>
                    <input type="text" class="form-control" name="prenom" value="<?= htmlspecialchars($user['prenom']) ?>" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label"><i class="fas fa-envelope"></i> Email *</label>
                    <input type="email" class="form-control" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label"><i class="fas fa-map-marker-alt"></i> Adresse</label>
                    <input type="text" class="form-control" name="adresse" value="<?= htmlspecialchars($user['adresse']) ?>">
                </div>
                
                <div class="form-group">
                    <label class="form-label"><i class="fas fa-phone"></i> Téléphone</label>
                    <input type="tel" class="form-control" name="tel" value="<?= htmlspecialchars($user['tel']) ?>">
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Enregistrer
                    </button>
                    <a href="profiles.php" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Annuler
                    </a>
                </div>
            </form>
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