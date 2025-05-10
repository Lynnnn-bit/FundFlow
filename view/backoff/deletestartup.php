<?php
include_once 'C:\xampp\htdocs\user\FundFlow\control\startupC.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['id_startup'];

    $startupC = new startupC();
    try {
        $startup = $startupC->getStartupById($id);
        if ($startup) {
            $startupC->deleteStartup($id);
            header("Location: addStartup.php"); 
            exit();
        } else {
            echo "<script>alert('La startup avec l\'ID $id n\'existe pas.'); window.location.href = 'addStartup.php';</script>";
        }
    } catch (Exception $e) {
        echo "<script>alert('Erreur lors de la suppression: " . $e->getMessage() . "'); window.location.href = 'addStartup.php';</script>";
    }
} else {
    echo "<script>alert('ID manquant pour la suppression.'); window.location.href = 'addStartup.php';</script>";
}
?>
