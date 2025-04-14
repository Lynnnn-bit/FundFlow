<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../controllers/PartenaireController.php';

session_start();

if (!isset($_SESSION['current_partner'])) {
    header("Location: partenaire.php");
    exit();
}

$partenaireController = new PartenaireController();
$partner = $partenaireController->getPartenaireByEmail($_SESSION['current_partner']);

if ($partner) {
    $partenaireController->deletePartenaire($partner['id_partenaire']);
    unset($_SESSION['current_partner']);
    $_SESSION['success'] = "Votre profil partenaire a été supprimé avec succès";
}

header("Location: partenaire.php");
exit();