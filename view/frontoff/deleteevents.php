<?php
include_once '../../Control/EvennementC.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['id_evenement'];

    $evenementC = new EvennementC();
    try {
        $evenement = $evenementC->getEvenementById($id);
        if ($evenement) {
            $evenementC->deleteEvenements($id);
            header("Location: events.php"); 
            exit();
        } else {
            echo "<script>alert('L\'évènement avec l\'ID $id n\'existe pas.'); window.location.href = 'events.php';</script>";
        }
    } catch (Exception $e) {
        echo "<script>alert('Erreur lors de la suppression: " . $e->getMessage() . "'); window.location.href = 'events.php';</script>";
    }
} else {
    echo "<script>alert('ID manquant pour la suppression.'); window.location.href = 'events.php';</script>";
}
?>
