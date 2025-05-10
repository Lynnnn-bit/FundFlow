<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../control/PartenaireController.php';

session_start();

if (!isset($_SESSION['current_partner'])) {
    header("Location: partenaire.php");
    exit();
}

$partenaireController = new PartenaireController();
$partner = $partenaireController->getPartenaireByEmail($_SESSION['current_partner']);

if ($partner) {
    if ($partenaireController->deletePartenaire($partner['id_partenaire'])) {
        unset($_SESSION['current_partner']);
        $_SESSION['success'] = "Votre demande a été supprimée";
    } else {
        $_SESSION['error'] = "Erreur lors de la suppression";
    }
} else {
    $_SESSION['error'] = "Profil non trouvé";
}

header("Location: partenaire.php");
exit();