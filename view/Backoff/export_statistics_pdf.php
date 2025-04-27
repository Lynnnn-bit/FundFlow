<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../controlle/financecontroller.php';
require_once __DIR__ . '/../../libs/fpdf/fpdf.php';

$financeController = new FinanceController();
$demandes = $financeController->getAllFinanceRequests();

// Calculate statistics
$totalDemands = count($demandes);
$totalAmountRequested = array_sum(array_column($demandes, 'montant_demandee'));
$totalAcceptedDemands = count(array_filter($demandes, fn($d) => $d['status'] === 'accepte'));
$totalRejectedDemands = count(array_filter($demandes, fn($d) => $d['status'] === 'rejete'));
$totalPendingDemands = count(array_filter($demandes, fn($d) => $d['status'] === 'en_attente'));
$totalAcceptedAmount = array_sum(array_map(function ($d) {
    return $d['status'] === 'accepte' ? $d['montant_demandee'] : 0;
}, $demandes));

class PDF extends FPDF
{
    function Header()
    {
        $this->SetFont('Arial', 'B', 14);
        $this->Cell(0, 10, 'Statistiques des Demandes de Financement', 0, 1, 'C');
        $this->Ln(5);
    }

    function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, 'Page ' . $this->PageNo(), 0, 0, 'C');
    }

    function AddStatistics($stats)
    {
        $this->SetFont('Arial', '', 12);
        $this->Ln(10);

        foreach ($stats as $label => $value) {
            $this->Cell(0, 10, "$label: $value", 0, 1);
        }
    }
}

$pdf = new PDF();
$pdf->AddPage();

$stats = [
    'Total des Demandes' => $totalDemands,
    'Montant Total Demandé (€)' => number_format($totalAmountRequested, 2),
    'Demandes Acceptées' => $totalAcceptedDemands,
    'Montant Accepté (€)' => number_format($totalAcceptedAmount, 2),
    'Demandes Rejetées' => $totalRejectedDemands,
    'Demandes en Attente' => $totalPendingDemands,
];
$pdf->AddStatistics($stats);

header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="statistiques_demandes.pdf"');
$pdf->Output('D', 'statistiques_demandes.pdf');
