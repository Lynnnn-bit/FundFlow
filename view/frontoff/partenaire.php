<?php
ob_start(); // Start output buffering to prevent premature output

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../control/PartenaireController.php';

session_start();

$partenaireController = new PartenaireController();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['submit'])) {
        $partenaire = new Partenaire(
            $_POST['nom'],
            $_POST['email'],
            $_POST['telephone'],
            $_POST['montant'],
            $_POST['description']
        );

        $result = $partenaireController->createPartenaire($partenaire);

        if ($result) {
            $_SESSION['current_partner'] = $_POST['email'];
            header("Location: partner_dashboard.php");
            exit();
        } else {
            $_SESSION['error'] = "Erreur lors de la création";
        }
    } elseif (isset($_POST['update_request'])) {
        $data = [
            'nom' => $_POST['nom'],
            'email' => $_POST['email'],
            'telephone' => $_POST['telephone'],
            'montant' => $_POST['montant'],
            'description' => $_POST['description']
        ];

        if ($partenaireController->updatePartenaire($_POST['id_partenaire'], $data)) {
            $_SESSION['success'] = "Demande mise à jour avec succès!";
            header("Location: partner_dashboard.php");
            exit();
        } else {
            $_SESSION['error'] = "Erreur lors de la mise à jour";
        }
    } elseif (isset($_POST['add_contract'])) {
        $result = $partenaireController->addContractForPartner(
            $_POST['id_partenaire'],
            $_POST['date_deb'],
            $_POST['date_fin'],
            $_POST['terms'],
            'en attente'
        );

        if ($result) {
            $_SESSION['success'] = "Contrat ajouté avec succès!";
        } else {
            $_SESSION['error'] = "Erreur lors de l'ajout du contrat";
        }
        header("Location: partenaire.php");
        exit();
    } elseif (isset($_POST['update_contract'])) {
        $result = $partenaireController->updatePartnerContract(
            $_POST['id_contrat'],
            $_POST['id_partenaire'],
            $_POST['date_deb'],
            $_POST['date_fin'],
            $_POST['terms'],
            $_POST['status']
        );

        if ($result) {
            $_SESSION['success'] = "Contrat mis à jour avec succès!";
        } else {
            $_SESSION['error'] = "Erreur lors de la mise à jour du contrat";
        }
        header("Location: partner_dashboard.php");
        exit();
    } elseif (isset($_POST['delete_contract'])) {
        $result = $partenaireController->deletePartnerContract(
            $_POST['id_contrat'],
            $_POST['id_partenaire']
        );

        if ($result) {
            $_SESSION['success'] = "Contrat supprimé avec succès!";
        } else {
            $_SESSION['error'] = "Erreur lors de la suppression du contrat";
        }
        header("Location: partenaire.php");
        exit();
    }
}

// Handle partner login to view their request
if (isset($_GET['view_request']) && isset($_GET['email'])) {
    $partner = $partenaireController->getPartenaireByEmail($_GET['email']);
    if ($partner && !$partner['is_approved']) {
        $_SESSION['current_partner'] = $partner['email'];
        $_SESSION['current_partner_id'] = $partner['id_partenaire'];
        header("Location: partner_dashboard.php");
        exit();
    } else {
        $_SESSION['error'] = "Demande non trouvée ou déjà approuvée";
        header("Location: partenaire.php");
        exit();
    }
}

// Get partner's contracts
$contracts = [];
if (isset($_SESSION['current_partner_id'])) {
    $contracts = $partenaireController->getPartnerContracts($_SESSION['current_partner_id']);
}

// Display messages
$success_message = $_SESSION['success'] ?? null;
$error_message = $_SESSION['error'] ?? null;
unset($_SESSION['success'], $_SESSION['error']);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FundFlow - Devenir Partenaire</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&family=Playfair+Display:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <style>
        .partner-container {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(12px);
            border-radius: 24px;
            padding: 3rem;
            margin: 2rem 0;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .partner-form {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 2rem;
        }

        .form-divider {
            grid-column: 1 / -1;
            border-top: 1px solid rgba(255,255,255,0.1);
            margin: 1rem 0;
        }

        .followup-card {
            background: rgba(255,255,255,0.05);
            border-radius: 16px;
            padding: 2rem;
            margin-top: 2rem;
            transition: transform 0.3s ease;
        }

        .followup-card:hover {
            transform: translateY(-3px);
        }

        @media (max-width: 768px) {
            .partner-form {
                grid-template-columns: 1fr;
            }
           
            .partner-container {
                padding: 1.5rem;
            }
        }
    </style>
</head>
<body>
<div class="background-effect"></div>
<div class="particles-container" id="particles-js"></div>

<div class="dashboard-container">
    <!-- Navbar Identique à profiles.php -->
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
        <h1><i class="fas fa-handshake header-icon"></i> Devenir Partenaire</h1>
        <p>Rejoignez notre réseau de partenaires privilégiés</p>
    </div>

    <div class="partner-container animate__animated animate__fadeIn">
        <?php if ($success_message): ?>
            <div class="alert alert-success animate__animated animate__fadeIn">
                <i class="fas fa-check-circle"></i> <?= htmlspecialchars($success_message) ?>
            </div>
        <?php endif; ?>
       
        <?php if ($error_message): ?>
            <div class="alert alert-danger animate__animated animate__fadeIn">
                <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error_message) ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['current_partner'])): ?>
            <?php header("Location: partner_dashboard.php"); exit(); ?>
        <?php else: ?>
            <form method="POST" id="partnerForm" class="partner-form">
                <div class="form-group">
                    <label class="form-label"><i class="fas fa-building"></i> Nom de l'entreprise</label>
                    <input type="text" class="form-control" name="nom" required>
                </div>
               
                <div class="form-group">
                    <label class="form-label"><i class="fas fa-envelope"></i> Email</label>
                    <input type="email" class="form-control" name="email" required>
                </div>
               
                <div class="form-group">
                    <label class="form-label"><i class="fas fa-phone"></i> Téléphone</label>
                    <input type="text" class="form-control" name="telephone" required>
                    <small class="form-text">Format: 8 chiffres (ex: 0612345678)</small>
                </div>
               
                <div class="form-group">
                    <label class="form-label"><i class="fas fa-euro-sign"></i> Montant investi</label>
                    <input type="text" class="form-control" name="montant" placeholder="1 000,00" required>
                    <small class="form-text">Entre 1 000€ et 1 000 000€</small>
                </div>
               
                <div class="form-divider"></div>
               
                <div class="form-group" style="grid-column: 1 / -1">
                    <label class="form-label"><i class="fas fa-align-left"></i> Description</label>
                    <textarea class="form-control" name="description" rows="5" required></textarea>
                    <small class="form-text">Décrivez votre entreprise et vos motivations</small>
                </div>

                <div class="profile-actions" style="grid-column: 1 / -1">
                    <button type="submit" name="submit" class="btn btn-primary">
                        <i class="fas fa-paper-plane"></i> Soumettre la demande
                    </button>
                </div>
            </form>

            <div class="followup-card animate__animated animate__fadeInUp">
                <h3><i class="fas fa-search"></i> Suivi de demande existante</h3>
                <form method="GET" class="partner-form">
                    <div class="form-group" style="grid-column: 1 / -1">
                        <label class="form-label"><i class="fas fa-envelope"></i> Email de suivi</label>
                        <input type="email" name="email" class="form-control" placeholder="contact@entreprise.com" required>
                    </div>
                    <button type="submit" name="view_request" class="btn btn-secondary">
                        <i class="fas fa-search"></i> Rechercher ma demande
                    </button>
                </form>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Footer identique à profiles.php -->
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
// Configuration identique à profiles.php
particlesJS("particles-js", {
    "particles": {
        "number": { "value": 60, "density": { "enable": true, "value_area": 800 } },
        "color": { "value": "#4cc9f0" },
        "shape": { "type": "circle" },
        "opacity": { "value": 0.5, "random": true },
        "size": { "value": 3, "random": true },
        "line_linked": { "enable": true, "distance": 150, "color": "#4cc9f0", "opacity": 0.4, "width": 1 },
        "move": { "enable": true, "speed": 1, "direction": "none" }
    },
    "interactivity": {
        "detect_on": "canvas",
        "events": {
            "onhover": { "enable": true, "mode": "grab" },
            "onclick": { "enable": true, "mode": "push" },
            "resize": true
        },
        "modes": {
            "grab": { "distance": 140, "line_linked": { "opacity": 1 } },
            "push": { "particles_nb": 4 }
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
    select.value = '';
}

// Validation de formulaire
document.getElementById('partnerForm').addEventListener('submit', function(e) {
    const tel = this.querySelector('[name="telephone"]');
    const montant = this.querySelector('[name="montant"]');
   
    if (!/^\d{8}$/.test(tel.value)) {
        showError(tel, 'Numéro invalide (8 chiffres requis)');
        e.preventDefault();
    }
   
    const numericValue = parseFloat(montant.value.replace(/[^\d,]/g, '').replace(',', '.'));
    if (isNaN(numericValue) || numericValue < 1000 || numericValue > 1000000) {
        showError(montant, 'Montant invalide (1 000€ à 1 000 000€)');
        e.preventDefault();
    }
});

function showError(field, message) {
    field.classList.add('is-invalid');
    const error = document.createElement('div');
    error.className = 'invalid-feedback';
    error.textContent = message;
    field.parentNode.appendChild(error);
}
</script>
</body>
</html>
<?php ob_end_flush(); ?>