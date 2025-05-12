<?php
require_once '../../config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Récupérer l'ID du consultant depuis l'URL
$consultant_id = $_GET['consultant_id'] ?? null;
$client_id = $_GET['client_id'] ?? null;

// Vérifier si les IDs sont valides
if (!$consultant_id || !$client_id) {
    die("ID de consultant ou client manquant");
}

try {
    // Récupérer les infos du consultant
    $db = Config::getConnexion();
    $stmt = $db->prepare("SELECT * FROM utilisateur WHERE id_utilisateur = :id");
    $stmt->bindParam(':id', $consultant_id);
    $stmt->execute();
    $consultant = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$consultant) {
        die("Consultant non trouvé");
    }
} catch (PDOException $e) {
    die("Erreur de base de données: " . $e->getMessage());
}

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $sql = "INSERT INTO consultation 
                (id_consultation, id_utilisateur1, id_utilisateur2, date_consultation, heure_deb, heure_fin, tarif)
                VALUES 
                (:id_consultation, :id_utilisateur1, :id_utilisateur2, :date_consultation, :heure_deb, :heure_fin, :tarif)";
        
        $stmt = $db->prepare($sql);
        
        $id_consultation = uniqid();
        
        $stmt->bindParam(':id_consultation', $id_consultation);
        $stmt->bindParam(':id_utilisateur1', $client_id, PDO::PARAM_INT);
        $stmt->bindParam(':id_utilisateur2', $consultant_id, PDO::PARAM_INT);
        $stmt->bindParam(':date_consultation', $_POST['date_consultation']);
        $stmt->bindParam(':heure_deb', $_POST['heure_deb']);
        $stmt->bindParam(':heure_fin', $_POST['heure_fin']);
        $stmt->bindParam(':tarif', $_POST['tarif']);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = "Consultation créée avec succès!";
            header('Location: mesconsultations.php');
            exit;
        } else {
            $errorInfo = $stmt->errorInfo();
            $_SESSION['error'] = "Erreur lors de l'enregistrement: " . $errorInfo[2];
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = "Erreur de base de données: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prendre un Rendez-vous</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&family=Playfair+Display:wght@500;600;700&display=swap" rel="stylesheet">
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
                        <li><a href="historique.php">Mes demandes</a></li>
                    <?php endif; ?>
                    <li><a href="allconsult.php">Consultation</a></li>
                    <li><a href="connexion.php?logout=1" class="logout">Déconnexion</a></li>
                </ul>
            </div>
        </div>
    </header>

    <div class="page-header animate__animated animate__fadeInDown">
        <h1><i class="fas fa-calendar-check header-icon"></i> Prendre un Rendez-vous</h1>
        <p>Planifiez une consultation avec un expert</p>
    </div>

    <div class="consultant-info consultant-card">
        <h3 class="consultant-name"><?= htmlspecialchars($consultant['nom']) ?> <?= htmlspecialchars($consultant['prenom']) ?></h3>
        <span class="consultant-badge">Consultant Expert</span>
        <div class="consultant-details">
            <p><i class="fas fa-envelope"></i> <?= htmlspecialchars($consultant['email']) ?></p>
        </div>
    </div>

    <form id="consultationForm" method="post" class="login-container">
        <input type="hidden" name="id_utilisateur1" value="<?= $client_id ?>">
        <input type="hidden" name="id_utilisateur2" value="<?= $consultant_id ?>">

        <div class="form-group">
            <label for="date_consultation">Date:</label>
            <input type="date" id="date_consultation" name="date_consultation" class="form-control">
        </div>

        <div class="form-group">
            <label for="heure_deb">Heure de début:</label>
            <input type="time" id="heure_deb" name="heure_deb" class="form-control">
        </div>

        <div class="form-group">
            <label for="heure_fin">Heure de fin:</label>
            <input type="time" id="heure_fin" name="heure_fin" class="form-control">
        </div>

        <div class="form-group">
            <label for="tarif">Tarif (€):</label>
            <input type="number" id="tarif" name="tarif" value="50" class="form-control">
        </div>

        <button type="submit" class="btn btn-login">Confirmer le Rendez-vous</button>
    </form>
</div>

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
</script>
</body>
</html>