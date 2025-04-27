<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../controlle/ContratController.php';

header('Content-Type: application/json');

try {
    // Récupération de l'ID du contrat
    $input = json_decode(file_get_contents('php://input'), true);
    $contratId = $input['id_contrat'] ?? null;

    if (!$contratId) {
        throw new Exception('ID de contrat manquant', 400);
    }

    $contratController = new ContratController();
    $contrat = $contratController->getContract($contratId);

    if (!$contrat) {
        throw new Exception('Contrat non trouvé', 404);
    }

    // Construction des données du QR Code
    $qrData = json_encode([
        'CONTRAT_ID' => $contrat['id_contrat'],
        'PARTENAIRE' => $contrat['partenaire_nom'],
        'DATE_DEBUT' => $contrat['date_deb'],
        'DATE_FIN' => $contrat['date_fin'],
        'STATUT' => $contrat['status']
    ]);

    $qrUrl = "https://api.qrserver.com/v1/create-qr-code/?" . http_build_query([
        'size' => '300x300',
        'data' => $qrData
    ]);

    echo json_encode([
        'success' => true,
        'qr_url' => $qrUrl,
        'partenaire' => $contrat['partenaire_nom']
    ]);

} catch (Exception $e) {
    http_response_code($e->getCode() ?: 500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>