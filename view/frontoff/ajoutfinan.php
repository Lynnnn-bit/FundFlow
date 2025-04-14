<?php
require_once '../config/config.php';

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $id_demande = $_POST['id_demande'] ?? null;
    $id_project = $_POST['id_project'] ?? null;
    $montant = $_POST['montant'] ?? null;
    $duree = $_POST['duree'] ?? null;
    $status = $_POST['status'] ?? null;

    // Validate inputs
    $errors = [];
    
    if (empty($id_demande) || !is_numeric($id_demande)) {
        $errors[] = "ID demande invalide";
    }
    
    if (empty($id_project) || !is_numeric($id_project)) {
        $errors[] = "ID projet invalide";
    }
    
    if (empty($montant) || !is_numeric($montant) || $montant < 10000 || $montant > 10000000) {
        $errors[] = "Montant invalide (doit être entre 10 000 € et 10 000 000 €)";
    }
    
    if (empty($duree) || !is_numeric($duree) || $duree < 6 || $duree > 60) {
        $errors[] = "Durée invalide (doit être entre 6 et 60 mois)";
    }
    
    if (empty($status)) {
        $errors[] = "Statut invalide";
    }

    // If no errors, process the form
    if (empty($errors)) {
        try {
            $db = Config::getConnexion();
            
            // First get the user_id from the project
            $stmt = $db->prepare("SELECT id_utilisateur FROM projet WHERE id_projet = :id_project");
            $stmt->bindParam(':id_project', $id_project, PDO::PARAM_INT);
            $stmt->execute();
            
            $project = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$project || !isset($project['id_utilisateur'])) {
                throw new Exception("Projet introuvable");
            }
            
            $id_utilisateur = $project['id_utilisateur'];

            // Now insert the funding request with specified id_demande
            $sql = "INSERT INTO demande_financement 
                    (id_demande, id_project, id_utilisateur, duree, montant_demandee, status) 
                    VALUES 
                    (:id_demande, :id_project, :id_utilisateur, :duree, :montant, :status)";
            
            $stmt = $db->prepare($sql);
            
            // Bind parameters
            $stmt->bindParam(':id_demande', $id_demande, PDO::PARAM_INT);
            $stmt->bindParam(':id_project', $id_project, PDO::PARAM_INT);
            $stmt->bindParam(':id_utilisateur', $id_utilisateur, PDO::PARAM_INT);
            $stmt->bindParam(':duree', $duree, PDO::PARAM_INT);
            $stmt->bindParam(':montant', $montant);
            $stmt->bindParam(':status', $status);
            
            // Execute query
            if ($stmt->execute()) {
                header('Location: finance.php?success=Demande+de+financement+enregistrée+avec+succès');
                exit;
            } else {
                $errors[] = "Erreur lors de l'enregistrement";
            }
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) { // Duplicate entry error
                $errors[] = "Cet ID demande existe déjà";
            } else {
                $errors[] = "Erreur de base de données: " . $e->getMessage();
            }
            error_log("Database error: " . $e->getMessage());
        } catch (Exception $e) {
            $errors[] = $e->getMessage();
        }
    }
    
    // If there were errors, redirect back with error messages
    $_SESSION['form_errors'] = $errors;
    $_SESSION['form_data'] = $_POST;
    header('Location: finance.php');
    exit;
} else {
    // If not a POST request, redirect to form
    header('Location: finance.php');
    exit;
}