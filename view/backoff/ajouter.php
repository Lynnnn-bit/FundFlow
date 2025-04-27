<?php
require_once __DIR__ . '/../../controlle/ContratController.php';

// Initialisation
header('Content-Type: application/json');
$response = ['success' => false, 'message' => ''];

try {
    // Vérification de la requête
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['add_contract'])) {
        throw new Exception('Requête invalide');
    }

    // Récupération des données
    $id_partenaire = $_POST['id_partenaire'] ?? null;
    $date_deb = $_POST['date_deb'] ?? null;
    $date_fin = $_POST['date_fin'] ?? null;
    $terms = $_POST['terms'] ?? '';
    $status = $_POST['status'] ?? 'en attente';

    // Validation des données
    if (empty($id_partenaire) || empty($date_deb) || empty($date_fin)) {
        throw new Exception('Tous les champs obligatoires doivent être remplis');
    }

    // Vérification des dates
    if (strtotime($date_fin) < strtotime($date_deb)) {
        throw new Exception('La date de fin doit être postérieure à la date de début');
    }

    // Création du contrat
    $contratController = new ContratController();
    $success = $contratController->createContract($id_partenaire, $date_deb, $date_fin, $terms, $status);

    if (!$success) {
        throw new Exception('Échec de la création du contrat');
    }

    $response['success'] = true;
    $response['message'] = 'Contrat créé avec succès';

} catch (Exception $e) {
    http_response_code(400);
    $response['message'] = $e->getMessage();
}

// Retour de la réponse
echo json_encode($response);