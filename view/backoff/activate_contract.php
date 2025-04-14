<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../controlle/ContratController.php';


session_start();



if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_contrat'])) {
    $contratController = new ContratController();
    if ($contratController->updateContract(
        $_POST['id_contrat'],
        null,
        null,
        null,
        'actif'
    )) {
        $_SESSION['success'] = "Contrat activé avec succès!";
    } else {
        $_SESSION['error'] = "Erreur lors de l'activation du contrat";
    }
}

header("Location: contrats.php");
exit();