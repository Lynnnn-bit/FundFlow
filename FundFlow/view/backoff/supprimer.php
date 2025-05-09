<?php
require_once __DIR__ . '/../../control/ContratController.php';
$contratController = new ContratController();
$supp=$contratController->deleteContract($_POST['id_contrat']);
header("Location: contrats.php");
exit();
?>
