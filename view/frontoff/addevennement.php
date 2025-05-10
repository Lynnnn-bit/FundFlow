<?php
include_once '../../control/EvennementC.php';
include_once '../../model/Evennement.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_startup = $_POST['id_startup'];
    $date_evenement = $_POST['date_evenement'];
    $type = $_POST['type'];
    $horaire = $_POST['horaire'];
    $nb_place = $_POST['nb_place'];
    $nom = $_POST['nom'];

    // Gestion du fichier image
    $target_dir = "../admin/uploads/";
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    $file_name = basename($_FILES["affiche"]["name"]);
    $target_file = $target_dir . $file_name;

    if (move_uploaded_file($_FILES["affiche"]["tmp_name"], $target_file)) {
        $affiche = "uploads/" . $file_name;

        $evenement = new Evennement($id_startup, $date_evenement, $type, $horaire, $nb_place, $affiche, $nom);
        $evenementC = new EvennementC();
        $evenementC->ajouterEvenement($evenement);

        header("Location: frontoffice.php");  // Redirection
        exit();
    } else {
        echo "Erreur lors de l'upload du fichier.";
        header("Location: frontoffice.php");  // Redirection
        exit();
    }
}
?>
