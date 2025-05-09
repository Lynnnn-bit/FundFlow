<?php
// deletestartup.php
session_start();
require_once '../../control/startupC.php';

// Vérifier si l'ID est présent dans l'URL
if (!isset($_GET['id'])) {
    $_SESSION['error'] = "ID de startup manquant";
    header('Location: startup.php');
    exit();
}

$id = (int)$_GET['id'];
$startupC = new startupC();

try {
    // Récupérer les informations de la startup avant suppression
    $startup = $startupC->getStartupById($id);
    
    if (!$startup) {
        throw new Exception("Startup introuvable");
    }

    // Supprimer les fichiers associés
    $uploadDir = '../admin/';
    
    // Supprimer le logo s'il existe
    if (!empty($startup['logo']) && file_exists($uploadDir . $startup['logo'])) {
        unlink($uploadDir . $startup['logo']);
    }
    
    // Supprimer la vidéo si elle existe
    if (!empty($startup['video_presentation']) && file_exists($uploadDir . $startup['video_presentation'])) {
        unlink($uploadDir . $startup['video_presentation']);
    }

    // Supprimer la startup de la base de données
    $result = $startupC->supprimerStartup($id);
    
    if ($result) {
        $_SESSION['success'] = "Startup supprimée avec succès";
    } else {
        throw new Exception("Échec de la suppression de la startup");
    }

} catch (Exception $e) {
    $_SESSION['error'] = $e->getMessage();
}

// Rediriger vers la page des startups
header('Location: startup.php');
exit();
?>