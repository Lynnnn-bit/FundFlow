<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../control/ContratController.php';

header('Content-Type: application/json');

try {
    if (!isset($_GET['id'])) {
        throw new Exception('ID de contrat manquant');
    }

    $contratController = new ContratController();
    $contract = $contratController->getContract($_GET['id']);

    if (!$contract) {
        throw new Exception('Contrat non trouvé');
    }

    echo json_encode([
        'success' => true,
        'id_contrat' => $contract['id_contrat'],
        'partenaire_nom' => $contract['partenaire_nom'] ?? 'N/A',
        'date_deb' => date('d/m/Y', strtotime($contract['date_deb'])),
        'date_fin' => date('d/m/Y', strtotime($contract['date_fin'])),
        'status' => $contract['status'],
        'terms' => $contract['terms'] ?? ''
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}