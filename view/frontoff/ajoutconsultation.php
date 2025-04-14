<?php
require_once __DIR__ . '/../../config.php';

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data with proper null checks for older PHP versions
    $id_consultation = isset($_POST['id_consultation']) ? $_POST['id_consultation'] : null;
    $id_utilisateur1 = isset($_POST['id_utilisateur1']) ? $_POST['id_utilisateur1'] : null;
    $id_utilisateur2 = isset($_POST['id_utilisateur2']) ? $_POST['id_utilisateur2'] : null;
    $heure_deb = isset($_POST['heure_deb']) ? $_POST['heure_deb'] : null;
    $heure_fin = isset($_POST['heure_fin']) ? $_POST['heure_fin'] : null;
    $tarif = isset($_POST['tarif']) ? $_POST['tarif'] : null;

    // Validate inputs
    $errors = array();
   
    if (empty($id_consultation) || !is_numeric($id_consultation)) {
        $errors[] = "ID consultation invalide";
    }
   
    if (empty($id_utilisateur1) || !is_numeric($id_utilisateur1)) {
        $errors[] = "Consultant invalide";
    }
   
    if (empty($id_utilisateur2) || !is_numeric($id_utilisateur2)) {
        $errors[] = "Client invalide";
    }
   
    if (empty($heure_deb) || empty($heure_fin)) {
        $errors[] = "Heures invalides";
    }
   
    if (empty($tarif) || !is_numeric($tarif) || $tarif < 50 || $tarif > 1000) {
        $errors[] = "Tarif invalide (doit être entre 50 € et 1000 €)";
    }

    // If no errors, process the form
    if (empty($errors)) {
        try {
            $db = Config::getConnexion();
           
            $sql = "INSERT INTO consultation
                    (id_consultation, id_utilisateur1, id_utilisateur2, heure_deb, heure_fin, tarif)
                    VALUES
                    (:id_consultation, :id_utilisateur1, :id_utilisateur2, :heure_deb, :heure_fin, :tarif)";
           
            $stmt = $db->prepare($sql);
           
            // Bind parameters
            $stmt->bindParam(':id_consultation', $id_consultation, PDO::PARAM_INT);
            $stmt->bindParam(':id_utilisateur1', $id_utilisateur1, PDO::PARAM_INT);
            $stmt->bindParam(':id_utilisateur2', $id_utilisateur2, PDO::PARAM_INT);
            $stmt->bindParam(':heure_deb', $heure_deb);
            $stmt->bindParam(':heure_fin', $heure_fin);
            $stmt->bindParam(':tarif', $tarif);
           
            // Execute query
            if ($stmt->execute()) {
                header('Location: consultation.php?success=Consultation+enregistrée+avec+succès');
                exit();
            } else {
                $errors[] = "Erreur lors de l'enregistrement";
            }
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) { // Duplicate entry error
                $errors[] = "Cet ID consultation existe déjà";
            } else {
                $errors[] = "Erreur de base de données: " . $e->getMessage();
            }
            error_log("Database error: " . $e->getMessage());
        } catch (Exception $e) {
            $errors[] = $e->getMessage();
        }
    }
   
    // Start session if not already started
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // If there were errors, redirect back with error messages
    $_SESSION['form_errors'] = $errors;
    $_SESSION['form_data'] = $_POST;
    header('Location: consultation.php');
    exit();
} else {
    // If not a POST request, redirect to form
    header('Location: consultation.php');
    exit();
}