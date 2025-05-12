<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../models/response.php';
require_once __DIR__ . '/../../control/responsecontroller.php';
require_once __DIR__ . '/../../control/financecontroller.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: connexion.php");
    exit();
}

$responseController = new ResponseController();
$financeController = new FinanceController();

// Handle accept and delete actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['accept_response'])) {
        try {
            if ($responseController->acceptResponse($_POST['response_id'])) {
                // Check if the sum of accepted responses meets the requested amount
                $acceptedResponses = $responseController->getAcceptedResponsesByDemande($_POST['demande_id']);
                $totalAcceptedAmount = array_sum(array_column($acceptedResponses, 'montant_accorde'));

                $demande = $financeController->getFinanceRequestById($_POST['demande_id']);
                if ($totalAcceptedAmount >= $demande['montant_demandee']) {
                    $financeController->updateDemandeStatus($_POST['demande_id'], 'accepte');
                }

                $_SESSION['success'] = "Réponse acceptée avec succès!";
            }
        } catch (Exception $e) {
            $_SESSION['error'] = $e->getMessage();
        }
        header("Location: responses.php?demande_id=" . $_POST['demande_id']);
        exit();
    } elseif (isset($_POST['delete_response'])) {
        try {
            $responseController->rejectResponse($_POST['response_id']);
            $_SESSION['success'] = "Réponse rejetée et supprimée avec succès!";
        } catch (Exception $e) {
            $_SESSION['error'] = $e->getMessage();
        }
        header("Location: responses.php?demande_id=" . $_POST['demande_id']);
        exit();
    }
}

// Get current demande and responses
$demande_id = $_GET['demande_id'] ?? null;
if (!$demande_id) {
    header("Location: financemet.php");
    exit();
}

$demande = $financeController->getFinanceRequestById($demande_id);
if (!$demande) {
    $_SESSION['error'] = "Demande de financement introuvable";
    header("Location: financemet.php");
    exit();
}

$responses = $responseController->getResponsesByDemande($demande_id);

// Display messages from session
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
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FundFlow - Réponses</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&family=Playfair+Display:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <style>
        /* Responses Specific Styles */
        .responses-container {
            margin-top: 2rem;
        }

        .finance-card {
            background: var(--glass-bg);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border-radius: 16px;
            overflow: hidden;
            margin-bottom: 2rem;
            box-shadow: var(--shadow-lg);
            border: 1px solid var(--glass-border);
            transition: all 0.3s ease;
        }

        .finance-card:hover {
            transform: translateY(-5px);
            background: var(--glass-bg-light);
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1.5rem;
            background: rgba(0,0,0,0.1);
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }

        .card-header h3 {
            margin: 0;
            color: white;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            padding: 0.25rem 0.75rem;
            border-radius: 50px;
            font-size: 0.75rem;
            font-weight: 500;
        }

        .status-badge.en_attente {
            background: rgba(249, 115, 22, 0.2);
            color: #f97316;
        }

        .status-badge.actif, .status-badge.accepte {
            background: rgba(16, 185, 129, 0.2);
            color: #10b981;
        }

        .status-badge.termine, .status-badge.rejete {
            background: rgba(59, 130, 246, 0.2);
            color: #3b82f6;
        }

        .status-dot {
            display: inline-block;
            width: 8px;
            height: 8px;
            border-radius: 50%;
            margin-right: 0.5rem;
        }

        .status-badge.en_attente .status-dot {
            background: #f97316;
        }

        .status-badge.actif .status-dot, .status-badge.accepte .status-dot {
            background: #10b981;
        }

        .status-badge.termine .status-dot, .status-badge.rejete .status-dot {
            background: #3b82f6;
        }

        .card-body {
            padding: 1.5rem;
        }

        .detail-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
        }

        .detail-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .detail-icon {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            background: rgba(255,255,255,0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary-light);
        }

        .detail-icon.bg-blue {
            background: rgba(67, 97, 238, 0.2);
            color: #4361ee;
        }

        .detail-icon.bg-green {
            background: rgba(16, 185, 129, 0.2);
            color: #10b981;
        }

        .detail-icon.bg-orange {
            background: rgba(249, 115, 22, 0.2);
            color: #f97316;
        }

        .detail-icon.bg-purple {
            background: rgba(139, 92, 246, 0.2);
            color: #8b5cf6;
        }

        .detail-label {
            font-size: 0.75rem;
            color: rgba(255,255,255,0.6);
        }

        .detail-value {
            font-size: 0.9rem;
            font-weight: 500;
            color: white;
        }

        .responses-section {
            background: var(--glass-bg);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border-radius: 16px;
            padding: 1.5rem;
            box-shadow: var(--shadow-lg);
            border: 1px solid var(--glass-border);
            margin-bottom: 2rem;
        }

        .section-header {
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .section-header h3 {
            color: white;
            margin: 0;
        }

        .responses-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1.5rem;
        }

        .response-card {
            background: rgba(255,255,255,0.05);
            border-radius: 12px;
            padding: 1.5rem;
            border: 1px solid rgba(255,255,255,0.1);
            transition: all 0.3s ease;
        }

        .response-card:hover {
            background: rgba(255,255,255,0.1);
            transform: translateY(-3px);
        }

        .response-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .response-header h4 {
            color: white;
            margin: 0;
            font-size: 1rem;
        }

        .response-status .badge {
            padding: 0.25rem 0.75rem;
            border-radius: 50px;
            font-size: 0.75rem;
            font-weight: 500;
        }

        .badge.bg-green {
            background: rgba(16, 185, 129, 0.2);
            color: #10b981;
        }

        .badge.bg-orange {
            background: rgba(249, 115, 22, 0.2);
            color: #f97316;
        }

        .badge.bg-red {
            background: rgba(239, 68, 68, 0.2);
            color: #ef4444;
        }

        .response-amount {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--primary-light);
        }

        .response-message {
            margin-bottom: 1.5rem;
            color: rgba(255,255,255,0.8);
            font-size: 0.9rem;
            line-height: 1.6;
        }

        .response-message p {
            margin: 0;
        }

        .response-actions {
            display: flex;
            gap: 0.75rem;
            justify-content: flex-end;
        }

        .no-responses {
            text-align: center;
            padding: 3rem;
            background: rgba(255,255,255,0.05);
            border-radius: 16px;
            border: 1px dashed rgba(255,255,255,0.2);
        }

        .no-responses-icon {
            font-size: 3rem;
            color: rgba(255,255,255,0.2);
            margin-bottom: 1.5rem;
        }

        .no-responses h4 {
            font-size: 1.5rem;
            margin-bottom: 1rem;
            color: white;
        }

        .no-responses p {
            color: rgba(255,255,255,0.7);
            margin-bottom: 1.5rem;
        }

        @media (max-width: 768px) {
            .detail-grid {
                grid-template-columns: 1fr;
            }
            
            .response-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }
            
            .response-actions {
                justify-content: flex-start;
                flex-wrap: wrap;
            }
        }

        /* Base Button Style */
        .btn-return {
            position: relative;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 12px 24px;
            background: linear-gradient(135deg, #4361ee 0%, #3a56d4 100%);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 500;
            text-decoration: none;
            cursor: pointer;
            box-shadow: 0 4px 15px rgba(67, 97, 238, 0.4);
            transition: all 0.4s cubic-bezier(0.25, 0.8, 0.25, 1);
            overflow: hidden;
            z-index: 1;
        }

        /* Icon Style */
        .btn-return i {
            margin-right: 10px;
            font-size: 18px;
            transition: transform 0.3s ease;
        }

        /* Hover Effects */
        .btn-return:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(67, 97, 238, 0.6);
            background: linear-gradient(135deg, #3a56d4 0%, #4361ee 100%);
        }

        .btn-return:hover i {
            transform: translateX(-4px);
        }

        /* Ripple Effect */
        .btn-return::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 5px;
            height: 5px;
            background: rgba(255, 255, 255, 0.5);
            opacity: 0;
            border-radius: 100%;
            transform: scale(1, 1) translate(-50%);
            transform-origin: 50% 50%;
            z-index: -1;
        }

        .btn-return:hover::after {
            animation: ripple 1.2s ease-out;
        }

        @keyframes ripple {
            0% {
                transform: scale(0, 0);
                opacity: 0.5;
            }
            100% {
                transform: scale(25, 25);
                opacity: 0;
            }
        }

        /* Optional: Glass Morphism Variant */
        .btn-return.glass {
            background: rgba(67, 97, 238, 0.2);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        }

        .btn-return.glass:hover {
            background: rgba(67, 97, 238, 0.3);
        }

        /* Optional: Animated Border Variant */
        .btn-return.animated-border {
            background: transparent;
            box-shadow: none;
            border: 2px solid transparent;
            background-clip: padding-box;
        }

        .btn-return.animated-border::before {
            content: '';
            position: absolute;
            top: -2px;
            left: -2px;
            right: -2px;
            bottom: -2px;
            z-index: -2;
            background: linear-gradient(135deg, #4361ee, #4cc9f0, #4361ee);
            background-size: 200% 200%;
            border-radius: inherit;
            animation: gradientBorder 3s ease infinite;
        }

        @keyframes gradientBorder {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
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
        <h1><i class="fas fa-reply-all header-icon"></i> Réponses</h1>
        <p>Gestion des réponses pour la demande #<?= $demande['id_demande'] ?></p>
    </div>

    <a href="financemet.php" class="btn-return animated-border animate__animated animate__fadeIn">
        <i class="fas fa-arrow-left"></i> Retour aux demandes
    </a>

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

    <div class="responses-container">
        <div class="finance-card animate__animated animate__fadeInUp">
            <div class="card-header">
                <h3>Détails de la demande</h3>
                <span class="status-badge <?= $demande['status'] ?>">
                    <span class="status-dot"></span>
                    <?= ucfirst($demande['status']) ?>
                </span>
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
                            <i class="fas fa-user-tie"></i>
                        </div>
                        <div>
                            <span class="detail-label">Porteur</span>
                            <span class="detail-value"><?= htmlspecialchars($demande['nom'] . ' ' . $demande['prenom']) ?></span>
                        </div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-icon bg-orange">
                            <i class="fas fa-euro-sign"></i>
                        </div>
                        <div>
                            <span class="detail-label">Montant Demandé</span>
                            <span class="detail-value"><?= number_format($demande['montant_demandee'], 2) ?> €</span>
                        </div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-icon bg-purple">
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                        <div>
                            <span class="detail-label">Durée</span>
                            <span class="detail-value"><?= $demande['duree'] ?> mois</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="responses-section animate__animated animate__fadeInUp">
            <div class="section-header">
                <i class="fas fa-list"></i>
                <h3>Réponses reçues</h3>
            </div>

            <?php if (count($responses) > 0): ?>
                <div class="responses-grid">
                    <?php foreach ($responses as $response): ?>
                    <div class="response-card">
                        <div class="response-header">
                            <div>
                                <h4>Réponse du <?= date('d/m/Y', strtotime($response['date_reponse'])) ?></h4>
                                <div class="response-status">
                                    <span class="badge <?= $response['status'] == 'accepte' ? 'bg-green' : 
                                                        ($response['status'] == 'rejete' ? 'bg-red' : 'bg-orange') ?>">
                                        <?= ucfirst($response['status']) ?>
                                    </span>
                                </div>
                            </div>
                            <div class="response-amount">
                                <?= number_format($response['montant_accorde'], 2) ?> €
                            </div>
                        </div>
                        
                        <?php if (!empty($response['message'])): ?>
                        <div class="response-message">
                            <p><?= nl2br(htmlspecialchars($response['message'])) ?></p>
                        </div>
                        <?php endif; ?>
                        
                        <div class="response-actions">
                            <?php if ($response['status'] == 'en_attente'): ?>
                            <form method="POST" class="d-inline">
                                <input type="hidden" name="response_id" value="<?= $response['id_reponse'] ?>">
                                <input type="hidden" name="demande_id" value="<?= $demande_id ?>">
                                <button type="submit" name="accept_response" class="btn btn-success">
                                    <i class="fas fa-check"></i> Accepter
                                </button>
                            </form>
                            <?php endif; ?>
                            
                            <form method="POST" class="d-inline">
                                <input type="hidden" name="response_id" value="<?= $response['id_reponse'] ?>">
                                <input type="hidden" name="demande_id" value="<?= $demande_id ?>">
                                <button type="submit" name="delete_response" class="btn btn-danger" 
                                    onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette réponse ?');">
                                    <i class="fas fa-trash"></i> Supprimer
                                </button>
                            </form>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="no-responses">
                    <div class="no-responses-icon">
                        <i class="fas fa-inbox"></i>
                    </div>
                    <h4>Aucune réponse pour cette demande</h4>
                    <p>Vous n'avez pas encore reçu de réponse pour cette demande de financement.</p>
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