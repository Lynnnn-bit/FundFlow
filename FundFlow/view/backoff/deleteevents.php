<?php
include_once '../../Controller/EvennementC.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['id_evenement'];

    $evenementC = new EvennementC();
    try {
        $evenement = $evenementC->getEvenementById($id);
        if ($evenement) {
            $evenementC->deleteEvenement($id);
            header("Location: frontoffice.php"); 
            exit();
        } else {
            echo "<script>alert('L\'évènement avec l\'ID $id n\'existe pas.'); window.location.href = 'frontoffice.php';</script>";
        }
    } catch (Exception $e) {
        echo "<script>alert('Erreur lors de la suppression: " . $e->getMessage() . "'); window.location.href = 'frontoffice.php';</script>";
    }
} else {
    echo "<script>alert('ID manquant pour la suppression.'); window.location.href = 'frontoffice.php';</script>";
}
?>
