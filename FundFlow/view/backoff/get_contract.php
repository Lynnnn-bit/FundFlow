<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../control/ContratController.php';

header('Content-Type: application/json');

$contratController = new ContratController();
$contract = $contratController->getContract($_GET['id']);

if ($contract) {
    echo json_encode($contract);
} else {
    http_response_code(404);
    echo json_encode(['error' => 'Contract not found']);
}