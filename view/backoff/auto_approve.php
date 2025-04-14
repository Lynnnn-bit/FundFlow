<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../controlle/ContratController.php';
require_once __DIR__ . '/../../controlle/PartenaireController.php';

session_start();

// Admin check


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_partenaire'])) {
    $partenaireController = new PartenaireController();
    if ($partenaireController->approvePartenaire($_POST['id_partenaire'])) {
        $_SESSION['success'] = "Partenaire approuvé avec succès! Un contrat a été automatiquement créé.";
    } else {
        $_SESSION['error'] = "Erreur lors de l'approbation du partenaire";
    }
}

header("Location: contrats.php");
exit();