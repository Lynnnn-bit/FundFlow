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
    <title>Liste des Consultants</title>
    <link rel="stylesheet" href="css/styleallconsu.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
   
</head>
<body>
    <header class="navbar">
        <div class="logo-container">
            <img src="assets/logo.png" alt="FundFlow" height="60">
        </div>
        <nav>
        <a href="apropos.html"><i class="fas fa-info-circle"></i> À propos</a>
            <a href="contact.html"><i class="fas fa-envelope"></i> Contact</a>
            <a href="accueil.html" class="logout"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
        </nav>
    </header>
<body>
    <div class="container" style="display: flex; justify-content: center; align-items: center; flex-direction: column; min-height: 100vh; background-color: #e9ecef; padding: 30px;">
        <h1 style="margin-bottom: 25px; color: #212529; font-family: Arial, sans-serif;">Prendre un Rendez-vous</h1>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="error" style="color: #dc3545; margin-bottom: 20px; font-weight: bold;"><?= $_SESSION['error'] ?></div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['success'])): ?>
            <div class="success" style="color: #28a745; margin-bottom: 20px; font-weight: bold;"><?= $_SESSION['success'] ?></div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>
        
        <div class="consultant-info" style="margin-bottom: 25px; text-align: center; background: #fff; padding: 15px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);">
            <h3 style="margin-bottom: 10px; color: #495057;">Consultant: <?= htmlspecialchars($consultant['nom']) ?> <?= htmlspecialchars($consultant['prenom']) ?></h3>
            <p style="margin: 0; color: #6c757d;">Email: <?= htmlspecialchars($consultant['email']) ?></p>
        </div>
        
        <div id="error-messages" style="color: #dc3545; font-weight: bold; margin-bottom: 20px; display: none;"></div>
        
        <form id="consultationForm" method="post" style="width: 100%; max-width: 450px; background: #f8f9fa; padding: 25px; border-radius: 8px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);">
            <input type="hidden" name="id_utilisateur1" value="<?= $client_id ?>">
            <input type="hidden" name="id_utilisateur2" value="<?= $consultant_id ?>">
            
            <div class="form-group" style="margin-bottom: 20px;">
                <label for="date_consultation" style="display: block; margin-bottom: 8px; color: #212529; font-weight: bold;">Date:</label>
                <input type="date" id="date_consultation" name="date_consultation" style="width: 100%; padding: 10px; border: 1px solid #ced4da; border-radius: 4px; font-size: 14px;">
            </div>
            
            <div class="form-group" style="margin-bottom: 20px;">
                <label for="heure_deb" style="display: block; margin-bottom: 8px; color: #212529; font-weight: bold;">Heure de début:</label>
                <input type="time" id="heure_deb" name="heure_deb" style="width: 100%; padding: 10px; border: 1px solid #ced4da; border-radius: 4px; font-size: 14px;">
            </div>
            
            <div class="form-group" style="margin-bottom: 20px;">
                <label for="heure_fin" style="display: block; margin-bottom: 8px; color: #212529; font-weight: bold;">Heure de fin:</label>
                <input type="time" id="heure_fin" name="heure_fin" style="width: 100%; padding: 10px; border: 1px solid #ced4da; border-radius: 4px; font-size: 14px;">
            </div>
            
            <div class="form-group" style="margin-bottom: 25px;">
                <label for="tarif" style="display: block; margin-bottom: 8px; color: #212529; font-weight: bold;">Tarif (€):</label>
                <input type="number" id="tarif" name="tarif" value="50" style="width: 100%; padding: 10px; border: 1px solid #ced4da; border-radius: 4px; font-size: 14px;">
            </div>
            
            <button type="submit" style="width: 100%; background-color: #007bff; color: white; padding: 12px; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; font-weight: bold;">Confirmer le Rendez-vous</button>
        </form>
        
        <script>
            document.getElementById('consultationForm').addEventListener('submit', function(event) {
                const errorMessages = [];
                const dateConsultation = document.getElementById('date_consultation').value;
                const heureDeb = document.getElementById('heure_deb').value;
                const heureFin = document.getElementById('heure_fin').value;
                const tarif = document.getElementById('tarif').value;

                const today = new Date();
                const selectedDate = new Date(dateConsultation + 'T00:00:00'); // Ensure time is set for comparison

                if (!dateConsultation) {
                    errorMessages.push('Veuillez sélectionner une date.');
                } else if (selectedDate < today.setHours(0, 0, 0, 0)) {
                    errorMessages.push('La date doit être supérieure ou égale à la date d\'aujourd\'hui.');
                }

                if (!heureDeb) {
                    errorMessages.push('Veuillez sélectionner une heure de début.');
                }

                if (!heureFin) {
                    errorMessages.push('Veuillez sélectionner une heure de fin.');
                }

                if (heureDeb && heureFin && heureDeb >= heureFin) {
                    errorMessages.push('L\'heure de fin doit être après l\'heure de début.');
                }

                if (!tarif || tarif <= 0) {
                    errorMessages.push('Veuillez entrer un tarif valide.');
                }

                if (errorMessages.length > 0) {
                    const errorContainer = document.getElementById('error-messages');
                    errorContainer.innerHTML = errorMessages.join('<br>');
                    errorContainer.style.display = 'block';
                    event.preventDefault();
                } else {
                    document.getElementById('error-messages').style.display = 'none';
                }
            });
        </script>
    </div>
</body>
</html>