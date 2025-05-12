<?php
// ===== Error Reporting =====
error_reporting(E_ALL);
ini_set('display_errors', 1);

// ===== Required Files =====
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../models/response.php';
require_once __DIR__ . '/../../control/responsecontroller.php';
require_once __DIR__ . '/../../control/financecontroller.php';

// ===== Session Management =====
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ===== Controllers =====
$responseController = new ResponseController();
$financeController = new FinanceController();

// ===== Get Demande ID =====
$demande_id = $_GET['demande_id'] ?? null;
if (!$demande_id) {
    header("Location: demands_list.php");
    exit();
}

// ===== Fetch Demande Details =====
$demande = $financeController->getFinanceRequestById($demande_id);
if (!$demande) {
    $_SESSION['error'] = "Demande de financement introuvable";
    header("Location: demands_list.php");
    exit();
}

// ===== Calculate Remaining Amount =====
$totalAcceptedResponses = array_sum(array_map(function ($response) {
    return $response['montant_accorde'];
}, $responseController->getAcceptedResponsesByDemande($demande_id)));
$remainingAmount = $demande['montant_demandee'] - $totalAcceptedResponses;

// ===== Handle Form Submission =====
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
    try {
        $montant_accorde = $_POST['montant_accorde'];
        $message = $_POST['message'];
        $date_reponse = date('Y-m-d'); // Automatically set the current date

        if ($montant_accorde > $remainingAmount) {
            throw new Exception("Le montant accordé dépasse le montant restant requis.");
        }

        // Create new Response object
        $response = new Response(
            $demande_id,
            'accepte', // Default decision is "accepte"
            $message,
            $montant_accorde,
            $date_reponse
        );

        if ($responseController->createResponse($response)) {
            $_SESSION['success'] = "Réponse enregistrée avec succès!";
            header("Location: demands_list.php?demande_id=$demande_id");
            exit();
        }
    } catch (Exception $e) {
        $error_message = $e->getMessage();
    }
}

// ===== Display Messages =====
if (isset($_SESSION['success'])) {
    $success_message = $_SESSION['success'];
    unset($_SESSION['success']);
}
if (isset($_SESSION['error'])) {
    $error_message = $_SESSION['error'];
    unset($_SESSION['error']);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <!-- ===== Meta Tags ===== -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FundFlow - Nouvelle Réponse</title>

    <!-- ===== Stylesheets ===== -->
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&family=Playfair+Display:wght@500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <!-- ===== Background Effects ===== -->
    <div class="background-effect"></div>
    <div class="particles-container" id="particles-js"></div>

    <!-- ===== Navbar ===== -->
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
                        <li><a href="historique.php">Mes demandes</a></li>
                    <?php endif; ?>
                    <li><a href="allconsult.php">Consultation</a></li>
                    <li><a href="connexion.php?logout=1" class="logout">Déconnexion</a></li>
                </ul>
            </div>
        </div>
    </header>

    <!-- ===== Main Container ===== -->
    <div class="main-container">
        <!-- ===== Page Header ===== -->
        <div class="page-header animate__animated animate__fadeInDown">
            <h1><i class="fas fa-reply header-icon"></i> Nouvelle Réponse</h1>
            <p>Répondez à la demande de financement #<?= $demande_id ?></p>
            <div class="header-decoration"></div>
        </div>

        <!-- ===== Back Button ===== -->
        <a href="demands_list.php?demande_id=<?= $demande_id ?>" class="btn btn-secondary mb-4">
            <i class="fas fa-arrow-left"></i> Retour aux demandes
        </a>

        <!-- ===== Success/Error Messages ===== -->
        <?php if (isset($success_message)): ?>
            <div class="alert alert-success animate__animated animate__fadeIn">
                <i class="fas fa-check-circle"></i> <?= htmlspecialchars($success_message) ?>
            </div>
        <?php endif; ?>
        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger animate__animated animate__fadeIn">
                <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error_message) ?>
            </div>
        <?php endif; ?>

        <!-- ===== Demande Details ===== -->
        <div class="finance-card animate__animated animate__fadeInUp">
            <div class="card-header">
                <h3>Détails de la demande</h3>
            </div>
            <div class="card-body">
                <div class="detail-grid">
                    <div class="detail-item">
                        <div class="detail-icon bg-blue">
                            <i class="fas fa-project-diagram"></i>
                        </div>
                        <div>
                            <span class="detail-label">Projet</span>
                            <span class="detail-value"><?= htmlspecialchars($demande['projet_titre']) ?></span>
                        </div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-icon bg-green">
                            <i class="fas fa-euro-sign"></i>
                        </div>
                        <div>
                            <span class="detail-label">Montant demandé</span>
                            <span class="detail-value"><?= number_format($demande['montant_demandee'], 2) ?> €</span>
                        </div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-icon bg-orange">
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                        <div>
                            <span class="detail-label">Durée</span>
                            <span class="detail-value"><?= $demande['duree'] ?> mois</span>
                        </div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-icon bg-purple">
                            <i class="fas fa-wallet"></i>
                        </div>
                        <div>
                            <span class="detail-label">Montant restant</span>
                            <span class="detail-value"><?= number_format($remainingAmount, 2) ?> €</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ===== Response Form ===== -->
        <div class="finance-form-container animate__animated animate__fadeInUp">
            <form method="POST" id="responseForm">
                <input type="hidden" name="id_demande" value="<?= $demande_id ?>">
                
                <div class="form-group">
                    <label class="form-label"><i class="fas fa-hand-holding-usd"></i> Montant accordé (€) *</label>
                    <div class="input-with-icon">
                        <i class="fas fa-euro-sign input-icon"></i>
                        <input type="number" name="montant_accorde" class="form-control" 
                               min="0" max="<?= $remainingAmount ?>" step="100" required
                               placeholder="Entrez le montant à accorder">
                    </div>
                    <div class="form-hint">Maximum: <?= number_format($remainingAmount, 2) ?> €</div>
                    <div class="error-message" id="montantError"></div>
                </div>
                
                <div class="form-group">
                    <label class="form-label"><i class="fas fa-comment-dots"></i> Message *</label>
                    <textarea name="message" class="form-control" rows="5" 
                              placeholder="Ajoutez un message à l'emprunteur..." required></textarea>
                    <div class="error-message" id="messageError"></div>
                </div>
                
                <div class="form-actions">
                    <button type="submit" name="submit" class="btn btn-primary">
                        <i class="fas fa-paper-plane"></i> Envoyer la réponse
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ===== Footer ===== -->
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
                    <a href="#" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                </div>
            </div>
        </div>
        <div class="footer-legal">
            <a href="#">Politique de confidentialité</a> |
            <a href="#">Conditions d'utilisation</a> |
            <span>&copy; 2025 FundFlow. Tous droits réservés.</span>
        </div>
    </footer>

    <!-- ===== Scripts ===== -->
    <script src="https://cdn.jsdelivr.net/particles.js/2.0.0/particles.min.js"></script>
    <script>
        // Initialize particles.js
        particlesJS("particles-js", {
            "particles": {
                "number": {
                    "value": 80,
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

        // Form validation
        document.getElementById('responseForm').addEventListener('submit', function(e) {
            const montantInput = this.querySelector('input[name="montant_accorde"]');
            const messageInput = this.querySelector('textarea[name="message"]');
            const montantError = document.getElementById('montantError');
            const messageError = document.getElementById('messageError');
            let isValid = true;

            // Reset errors
            montantError.textContent = '';
            messageError.textContent = '';

            // Validate amount
            if (!montantInput.value || parseFloat(montantInput.value) <= 0) {
                montantError.textContent = 'Veuillez entrer un montant valide.';
                isValid = false;
            } else if (parseFloat(montantInput.value) > <?= $remainingAmount ?>) {
                montantError.textContent = 'Le montant ne peut pas dépasser <?= number_format($remainingAmount, 2) ?> €.';
                isValid = false;
            }

            // Validate message
            if (!messageInput.value.trim()) {
                messageError.textContent = 'Veuillez entrer un message.';
                isValid = false;
            }

            if (!isValid) {
                e.preventDefault();
            }
        });
    </script>
</body>
</html>