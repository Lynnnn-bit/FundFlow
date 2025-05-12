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

$controller = new FinanceController();

// Handle delete action
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['delete_id'])) {
    try {
        $deleteId = $_GET['delete_id'];
        if ($controller->deleteFinanceRequest($deleteId)) {
            $_SESSION['success'] = "Demande supprimée avec succès!";
            header("Location: financemet.php");
            exit();
        }
    } catch (Exception $e) {
        $error_message = $e->getMessage();
    }
}

// Handle edit form display
$editMode = false;
$financeToEdit = null;
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['edit_id'])) {
    $editMode = true;
    $editId = $_GET['edit_id'];
    $financeToEdit = $controller->getFinanceRequestById($editId);
}

// Fetch only the projects related to the logged-in user
$userId = $_SESSION['user_id']; // Get the logged-in user's ID
$projects = $controller->getProjects($userId);

// Fetch all existing demands
$existingDemands = $controller->getAllFinanceRequests();

// Generate a new unique ID
$newId = 1;
if (!empty($existingDemands)) {
    $maxId = max(array_column($existingDemands, 'id_demande'));
    $newId = $maxId + 1;
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
    try {
        $id_demande = $_POST['id_demande'];
        $id_project = $_POST['id_project'];
        $montant_demandee = $_POST['montant'];
        $duree = $_POST['duree'];
        $status = $editMode ? $_POST['status'] : 'en_attente';

        $project = $controller->getProjectById($id_project);
        if (!$project) {
            $projectError = "Veuillez sélectionner un projet valide.";
        } else {
            $finance = new Finance($id_project, $userId, $duree, $montant_demandee, $status, $id_demande); // Use $userId here

            if (isset($_POST['is_edit']) && $_POST['is_edit'] === 'true') {
                if ($controller->updateFinanceRequest($finance)) {
                    $_SESSION['success'] = "Demande mise à jour avec succès! (ID: $id_demande)";
                }
            } else {
                if ($controller->createFinanceRequest($finance)) {
                    $_SESSION['success'] = "Demande enregistrée avec succès! (ID: $id_demande)";
                }
            }

            header("Location: financemet.php");
            exit();
        }
    } catch (Exception $e) {
        $error_message = $e->getMessage();
    }
}

// Display success message from session
if (isset($_SESSION['success'])) {
    $success_message = $_SESSION['success'];
    unset($_SESSION['success']);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FundFlow - Gestion des Financements</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&family=Playfair+Display:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <style>
        /* Finance Specific Styles */
        .finance-container {
            margin-top: 2rem;
        }

        .action-buttons {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
            flex-wrap: wrap;
        }

        .finance-form-container {
            background: var(--glass-bg);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid var(--glass-border);
            box-shadow: var(--shadow-lg);
            border-radius: 16px;
            padding: 2rem;
            margin-bottom: 2rem;
        }

        .form-title {
            font-size: 1.75rem;
            margin-bottom: 1.5rem;
            color: white;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.75rem;
            color: rgba(255,255,255,0.9);
            font-weight: 500;
        }

        .form-control {
            width: 100%;
            padding: 1rem;
            background: rgba(255,255,255,0.9);
            border: 1px solid rgba(255,255,255,0.3);
            border-radius: 12px;
            font-size: 1rem;
            color: var(--dark);
            transition: var(--transition);
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.3);
        }

        .select-wrapper {
            position: relative;
        }

        .select-wrapper select {
            width: 100%;
            padding: 1rem 1.5rem;
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

        .error-message {
            color: #ff6b6b;
            font-size: 0.875rem;
            margin-top: 0.5rem;
        }

        .form-actions {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
        }

        .submit-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.75rem;
            padding: 1rem 2rem;
            background: linear-gradient(to right, var(--primary), var(--primary-dark));
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            box-shadow: 0 4px 15px rgba(67, 97, 238, 0.4);
        }

        .submit-btn:hover {
            background: linear-gradient(to right, var(--primary-dark), var(--primary));
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(67, 97, 238, 0.6);
        }

        .cancel-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.75rem;
            padding: 1rem 2rem;
            background: rgba(255,255,255,0.1);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
        }

        .cancel-btn:hover {
            background: rgba(255,255,255,0.2);
            transform: translateY(-3px);
        }

        @media (max-width: 768px) {
            .form-actions {
                flex-direction: column;
            }
            
            .submit-btn, .cancel-btn {
                width: 100%;
                justify-content: center;
            }
        }

        /* Button Container Styles */
        .action-buttons {
            display: flex;
            gap: 15px;
            margin: 25px 0;
            flex-wrap: wrap;
            justify-content: center;
        }

        /* Base Button Styles */
        .action-btn {
            position: relative;
            padding: 12px 25px;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            min-width: 180px;
            overflow: hidden;
            color: white;
        }

        /* Button Hover Effects */
        .action-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
        }

        .action-btn:active {
            transform: translateY(1px);
        }

        /* Button Before Pseudo-element (for animation) */
        .action-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: 0.5s;
        }

        .action-btn:hover::before {
            left: 100%;
        }

        /* Individual Button Colors */
        .btn-new {
            background: linear-gradient(135deg, #4e54c8, #8f94fb);
            border-left: 4px solid #8f94fb;
        }

        .btn-history {
            background: linear-gradient(135deg, #11998e, #38ef7d);
            border-left: 4px solid #38ef7d;
        }

        .btn-stats {
            background: linear-gradient(135deg, #f46b45, #eea849);
            border-left: 4px solid #eea849;
        }

        .btn-chatbot {
            background: linear-gradient(135deg, #8E2DE2, #4A00E0);
            border-left: 4px solid #4A00E0;
        }

        /* Button Icons */
        .action-btn i {
            margin-right: 8px;
            font-size: 18px;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .action-buttons {
                flex-direction: column;
                align-items: center;
            }
            
            .action-btn {
                width: 100%;
                max-width: 250px;
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
                        <li><a href="historique.php">mes demandes</a></li>
                    <?php endif; ?>
                    <li><a href="allconsult.php">Consultation</a></li>
                    <li><a href="connexion.php?logout=1" class="logout">Déconnexion</a></li>
                </ul>
            </div>
        </div>
    </header>

    <div class="page-header animate__animated animate__fadeInDown">
        <h1><i class="fas fa-file-invoice-dollar header-icon"></i> <?= $editMode ? 'Modifier' : 'Nouvelle' ?> Demande de Financement</h1>
        <p>Gérez vos demandes de financement</p>
    </div>

    <div class="finance-container">
        <div class="action-buttons">
            <a href="financemet.php" class="action-btn btn-new animate__animated animate__fadeIn">
                <i class="fas fa-plus-circle"></i> Nouvelle Demande
            </a>
            <a href="historique.php" class="action-btn btn-history animate__animated animate__fadeIn animate__delay-1s">
                <i class="fas fa-history"></i> Historique
            </a>
            <a href="statistiquesf.php" class="action-btn btn-stats animate__animated animate__fadeIn animate__delay-2s">
                <i class="fas fa-chart-pie"></i> Statistiques
            </a>
            <a href="chatbot.php" class="action-btn btn-chatbot animate__animated animate__fadeIn animate__delay-3s">
                <i class="fas fa-robot"></i> Chatbot
            </a>
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

        <div class="finance-form-container animate__animated animate__fadeInUp">
            <h2 class="form-title"><i class="fas fa-<?= $editMode ? 'edit' : 'plus-circle' ?>"></i> <?= $editMode ? 'Modifier la demande' : 'Créer une nouvelle demande' ?></h2>
            
            <form method="POST">
                <input type="hidden" name="id_demande" value="<?= $editMode ? $financeToEdit['id_demande'] : $newId ?>">
                <input type="hidden" name="is_edit" value="<?= $editMode ? 'true' : 'false' ?>">
                
                <div class="form-group">
                    <label class="form-label"><i class="fas fa-project-diagram"></i> Projet *</label>
                    <div class="select-wrapper">
                        <select class="form-control" name="id_project" required>
                            <option value="">Sélectionnez un projet</option>
                            <?php foreach ($projects as $project): ?>
                                <option value="<?= $project['id_projet'] ?>" 
                                    <?= ($editMode && $financeToEdit['id_project'] == $project['id_projet']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($project['titre']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <i class="fas fa-chevron-down select-arrow"></i>
                    </div>
                    <?php if (isset($projectError)): ?>
                        <div class="error-message"><?= htmlspecialchars($projectError) ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label class="form-label"><i class="fas fa-euro-sign"></i> Montant (€) *</label>
                    <input type="number" class="form-control" name="montant" required
                           value="<?= $editMode ? $financeToEdit['montant_demandee'] : '' ?>">
                </div>

                <div class="form-group">
                    <label class="form-label"><i class="fas fa-calendar-alt"></i> Durée (mois) *</label>
                    <input type="number" class="form-control" name="duree" min="6" max="60" 
                           value="<?= $editMode ? $financeToEdit['duree'] : '24' ?>" required>
                </div>

                <?php if ($editMode): ?>
                <div class="form-group">
                    <label class="form-label"><i class="fas fa-check-circle"></i> Statut *</label>
                    <div class="select-wrapper">
                        <select class="form-control" name="status" required>
                            <option value="en_attente" <?= $financeToEdit['status'] == 'en_attente' ? 'selected' : '' ?>>En attente</option>
                            <option value="accepte" <?= $financeToEdit['status'] == 'accepte' ? 'selected' : '' ?>>Accepté</option>
                            <option value="rejete" <?= $financeToEdit['status'] == 'rejete' ? 'selected' : '' ?>>Rejeté</option>
                        </select>
                        <i class="fas fa-chevron-down select-arrow"></i>
                    </div>
                </div>
                <?php endif; ?>

                <div class="form-actions">
                    <button type="submit" name="submit" class="submit-btn">
                        <i class="fas fa-<?= $editMode ? 'save' : 'paper-plane' ?>"></i>
                        <?= $editMode ? 'Mettre à jour' : 'Soumettre' ?>
                    </button>
                    <?php if ($editMode): ?>
                        <a href="financemet.php" class="cancel-btn">
                            <i class="fas fa-times"></i> Annuler
                        </a>
                    <?php endif; ?>
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