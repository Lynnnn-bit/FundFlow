<?php
require_once '../../config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $db = Config::getConnexion();
        
        $sql = "INSERT INTO consultation 
                (id_consultation, id_utilisateur1, id_utilisateur2, date_consultation, heure_deb, heure_fin, tarif)
                VALUES 
                (:id_consultation, :id_utilisateur1, :id_utilisateur2, :date_consultation, :heure_deb, :heure_fin, :tarif)";
        
        $stmt = $db->prepare($sql);
        
        $stmt->bindParam(':id_consultation', $_POST['id_consultation'], PDO::PARAM_INT);
        $stmt->bindParam(':id_utilisateur1', $_POST['id_utilisateur1'], PDO::PARAM_INT);
        $stmt->bindParam(':id_utilisateur2', $_POST['id_utilisateur2'], PDO::PARAM_INT);
        $stmt->bindParam(':date_consultation', $_POST['date_consultation']);
        $stmt->bindParam(':heure_deb', $_POST['heure_deb']);
        $stmt->bindParam(':heure_fin', $_POST['heure_fin']);
        $stmt->bindParam(':tarif', $_POST['tarif']);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = "Consultation créée avec succès!";
        } else {
            $errorInfo = $stmt->errorInfo();
            $_SESSION['error'] = "Erreur lors de l'enregistrement: " . $errorInfo[2];
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = "Erreur de base de données: " . $e->getMessage();
        error_log("Database error: " . $e->getMessage());
    }
}

header('Location: consultation.php');
exit;
?>
<script src="consultation.js"></script>

