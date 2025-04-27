<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../controlle/ContratController.php';
require_once __DIR__ . '/../../controlle/PartenaireController.php';

session_start();

// Admin check
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_partenaire'])) {
    $partenaireController = new PartenaireController();
    $contratController = new ContratController();

    try {
        // Approve the partner
        if ($partenaireController->approvePartenaire($_POST['id_partenaire'])) {
            error_log("Partner approved successfully: ID " . $_POST['id_partenaire']); // Logging approval

            // Create a contract for the approved partner
            $contractCreated = $contratController->createContract(
                $_POST['id_partenaire'],
                date('Y-m-d'), // Start date
                date('Y-m-d', strtotime('+1 year')), // End date
                'Standard contract terms', // Terms
                'en attente' // Status
            );

            if ($contractCreated) {
                error_log("Contract created successfully for partner ID: " . $_POST['id_partenaire']); // Logging contract creation
                $_SESSION['success'] = "Partenaire approuvé avec succès! Un contrat a été automatiquement créé.";
            } else {
                error_log("Failed to create contract for partner ID: " . $_POST['id_partenaire']); // Logging failure
                $_SESSION['error'] = "Partenaire approuvé, mais échec de la création du contrat.";
            }
        } else {
            error_log("Failed to approve partner ID: " . $_POST['id_partenaire']); // Logging failure
            $_SESSION['error'] = "Erreur lors de l'approbation du partenaire.";
        }
    } catch (Exception $e) {
        error_log("Error during auto-approval: " . $e->getMessage()); // Logging exception
        $_SESSION['error'] = "Une erreur s'est produite lors de l'approbation.";
    }
}

header("Location: contrats.php");
exit();