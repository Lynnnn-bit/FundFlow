<?php
require_once __DIR__ . '/../../controlle/ContratController.php';

// Définir le header JSON avant toute sortie
header('Content-Type: application/json');

$contratController = new ContratController();

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['id_contrat'])) {
        throw new Exception('Requête invalide');
    }

    $id_contrat = $_POST['id_contrat'];
    $date_deb = $_POST['date_deb'] ?? null;
    $date_fin = $_POST['date_fin'] ?? null;
    $terms = $_POST['terms'] ?? null;
    $status = $_POST['status'] ?? null;
    
    $success = $contratController->updateContract($id_contrat, $date_deb, $date_fin, $terms, $status);
    
    if (!$success) {
        throw new Exception('Échec de la mise à jour dans le contrôleur');
    }
    
    echo json_encode(['success' => true]);
    exit();

} catch (Exception $e) {
    // Journaliser l'erreur côté serveur
    error_log("Erreur dans modifier.php: " . $e->getMessage());
    
    // Retourner l'erreur au client
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
    exit();
}
?>