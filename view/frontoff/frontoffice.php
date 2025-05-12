<?php
include_once 'C:\xampp\htdocs\user\FundFlow\control\startupC.php';
include_once 'C:\xampp\htdocs\user\FundFlow\control\EvennementC.php';

session_start();

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: connexion.php");
    exit();
}

$startupC = new startupC();
$evenementC = new EvennementC();
$startups = $startupC->getAllStartups();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FundFlow - Front Office</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&family=Playfair+Display:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <style>
        /* Front Office Specific Styles */
        .frontoffice-container {
            max-width: 1200px;
            margin: 2rem auto;
        }

        .startup-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 2rem;
        }

        .startup-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-radius: 16px;
            padding: 2rem;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: all 0.3s ease;
        }

        .startup-card:hover {
            transform: translateY(-5px);
            background: rgba(255, 255, 255, 0.15);
        }

        .startup-card h3 {
            font-size: 1.5rem;
            margin-bottom: 1rem;
            color: white;
            border-bottom: 1px solid rgba(255,255,255,0.2);
            padding-bottom: 0.5rem;
        }

        .startup-detail {
            margin-bottom: 1rem;
            color: rgba(255,255,255,0.9);
        }

        .startup-detail strong {
            color: white;
            font-weight: 500;
        }

        .startup-logo {
            max-width: 150px;
            height: auto;
            border-radius: 8px;
            margin: 1rem 0;
        }

        .startup-video {
            max-width: 100%;
            border-radius: 8px;
            margin: 1rem 0;
        }

        .btn-ajouter {
            display: inline-block;
            padding: 0.75rem 1.5rem;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 1rem;
        }

        .btn-ajouter:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(67, 97, 238, 0.4);
        }

        .evenement-form {
            margin-top: 2rem;
            padding: 1.5rem;
            background: rgba(0,0,0,0.1);
            border-radius: 12px;
            border: 1px solid rgba(255,255,255,0.1);
        }

        .evenement-form h3 {
            margin-bottom: 1.5rem;
            color: white;
        }

        .event-form {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1rem;
        }

        .event-form label {
            color: rgba(255,255,255,0.9);
            font-weight: 500;
            margin-bottom: 0.5rem;
        }

        .event-form input,
        .event-form select {
            width: 100%;
            padding: 0.75rem 1rem;
            background: rgba(255,255,255,0.9);
            border: 1px solid rgba(255,255,255,0.3);
            border-radius: 8px;
            font-size: 1rem;
            color: var(--dark);
        }

        .error-message {
            color: #f87171;
            font-size: 0.875rem;
            margin-top: 0.25rem;
            display: none;
        }

        @media (max-width: 768px) {
            .startup-grid {
                grid-template-columns: 1fr;
            }
            
            .startup-card {
                padding: 1.5rem;
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

    <div class="frontoffice-container">
        <div class="page-header animate__animated animate__fadeInDown">
            <h1><i class="fas fa-rocket"></i> Liste des Startups</h1>
            <p>Découvrez les startups disponibles et organisez des événements</p>
        </div>

        <div class="startup-grid">
            <?php foreach ($startups as $s): ?>
                <?php
                    $logoFile = basename($s['logo']);
                    $videoFile = basename($s['video_presentation']);
                    $logoPath = '../backoff/uploads/' . $logoFile;
                    $videoPath = '../backoff/uploads/' . $videoFile;
                ?>

                <div class="startup-card animate__animated animate__fadeInUp">
                    <h3><?= htmlspecialchars($s['nom_startup']) ?></h3>
                    
                    <div class="startup-detail">
                        <strong>Secteur:</strong> <?= htmlspecialchars($s['secteur']) ?>
                    </div>
                    
                    <div class="startup-detail">
                        <strong>Site web:</strong> 
                        <a href="<?= htmlspecialchars($s['adresse_site']) ?>" target="_blank" style="color: var(--primary-light);">
                            <?= htmlspecialchars($s['adresse_site']) ?>
                        </a>
                    </div>
                    
                    <div class="startup-detail">
                        <strong>Description:</strong> <?= htmlspecialchars($s['description']) ?>
                    </div>
                    
                    <div class="startup-detail">
                        <strong>Email:</strong> <?= htmlspecialchars($s['email']) ?>
                    </div>

                    <?php if(!empty($logoFile)): ?>
                        <div class="startup-detail">
                            <strong>Logo:</strong><br>
                            <img src="<?= htmlspecialchars($logoPath) ?>" alt="Logo startup" class="startup-logo">
                        </div>
                    <?php endif; ?>
                    
                    <?php if(!empty($videoFile)): ?>
                        <div class="startup-detail">
                            <strong>Vidéo de Présentation:</strong><br>
                            <video controls class="startup-video">
                                <source src="<?= htmlspecialchars($videoPath) ?>" type="video/mp4">
                                Votre navigateur ne supporte pas la lecture de vidéos.
                            </video>
                        </div>
                    <?php endif; ?>

                    <button class="btn-ajouter" onclick="toggleForm('<?= $s['id_startup'] ?>')">
                        <i class="fas fa-plus"></i> Ajouter Évènement
                    </button>

                    <div class="evenement-form" id="form_<?= $s['id_startup'] ?>" style="display: none;">
                        <h3>Ajouter un Évènement</h3>
                        <form method="POST" enctype="multipart/form-data" action="addevennement.php" class="event-form">
                            <input type="hidden" name="id_startup" value="<?= $s['id_startup'] ?>">

                            <label>Date de l'évènement</label>
                            <input type="date" name="date_evenement" required>
                            <div class="error-message">Veuillez choisir une date valide.</div>

                            <label>Type</label>
                            <select name="type" required>
                                <option value="">-- Choisir un type --</option>
                                <option value="Présentiel">Présentiel</option>
                                <option value="En ligne">En ligne</option>
                            </select>
                            <div class="error-message">Veuillez choisir un type.</div>

                            <label>Horaire</label>
                            <input type="time" name="horaire" required>
                            <div class="error-message">Veuillez entrer un horaire.</div>

                            <label>Nombre de places</label>
                            <input type="number" name="nb_place" required>
                            <div class="error-message">Veuillez entrer un nombre de places.</div>

                            <label>Affiche (image)</label>
                            <input type="file" name="affiche" accept="image/*" required>
                            <div class="error-message">Veuillez ajouter une affiche.</div>

                            <label>Nom de l'évènement</label>
                            <input type="text" name="nom" required>
                            <div class="error-message">Veuillez entrer un nom d'évènement.</div>

                            <button type="submit" class="btn-ajouter">
                                <i class="fas fa-save"></i> Enregistrer
                            </button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
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

// Afficher / cacher formulaire événement
function toggleForm(startupId) {
    const form = document.getElementById('form_' + startupId);
    const button = document.querySelector(`button[onclick="toggleForm('${startupId}')"]`);
    form.style.display = form.style.display === 'none' ? 'block' : 'none';
    button.disabled = form.style.display === 'block';
}

// Validation pour ajouter un événement
document.querySelectorAll('.event-form').forEach(function(form) {
    form.addEventListener('submit', function(e) {
        let valid = true;
        const fields = {
            date_evenement: form.querySelector('input[name="date_evenement"]'),
            type: form.querySelector('select[name="type"]'),
            horaire: form.querySelector('input[name="horaire"]'),
            nb_place: form.querySelector('input[name="nb_place"]'),
            affiche: form.querySelector('input[name="affiche"]'),
            nom: form.querySelector('input[name="nom"]')
        };
        const errorMessages = form.querySelectorAll('.error-message');

        errorMessages.forEach(msg => msg.style.display = 'none');

        function showError(field, index) {
            if (!field.value || (field.type === "file" && field.files.length === 0)) {
                errorMessages[index].style.display = 'block';
                valid = false;
            }
        }

        showError(fields.date_evenement, 0);
        showError(fields.type, 1);
        showError(fields.horaire, 2);
        showError(fields.nb_place, 3);
        showError(fields.affiche, 4);
        showError(fields.nom, 5);

        const selectedDate = new Date(fields.date_evenement.value);
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        if (fields.date_evenement.value && selectedDate < today) {
            errorMessages[0].style.display = 'block';
            valid = false;
        }

        if (!valid) {
            e.preventDefault();
        }
    });
});
</script>
</body>
</html>