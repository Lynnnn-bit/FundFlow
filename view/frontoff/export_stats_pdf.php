<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../control/financecontroller.php';
require_once __DIR__ . '/../../libs/fpdf/fpdf.php';

// Debugging: Ensure no output before headers
ob_start();

$controller = new FinanceController();
$demands = $controller->getAllFinanceRequests();

// Calculate statistics
$totalDemands = count($demands);
$totalAmountRequested = array_sum(array_column($demands, 'montant_demandee'));
$totalAcceptedDemands = count(array_filter($demands, fn($d) => $d['status'] === 'accepte'));
$totalRejectedDemands = count(array_filter($demands, fn($d) => $d['status'] === 'rejete'));
$totalPendingDemands = count(array_filter($demands, fn($d) => $d['status'] === 'en_attente'));
$totalAcceptedAmount = array_sum(array_map(function ($d) {
    return $d['status'] === 'accepte' ? $d['montant_demandee'] : 0;
}, $demands));

class PDF extends FPDF
{
    // Header
    function Header()
    {
        $this->SetFont('Arial', 'B', 14);
        $this->Cell(0, 10, 'Statistiques des Demandes de Financement', 0, 1, 'C');
        $this->Ln(5);
    }

    // Footer
    function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, 'Page ' . $this->PageNo(), 0, 0, 'C');
    }

    // Add Statistics
    function AddStatistics($stats)
    {
        $this->SetFont('Arial', '', 12);
        $this->Ln(10);

        foreach ($stats as $label => $value) {
            $this->Cell(0, 10, "$label: $value", 0, 1);
        }
    }
}

try {
    // Create PDF
    $pdf = new PDF();
    $pdf->AddPage();

    // Add statistics
    $stats = [
        'Total des Demandes' => $totalDemands,
        'Montant Total Demandé (€)' => number_format($totalAmountRequested, 2),
        'Demandes Acceptées' => $totalAcceptedDemands,
        'Montant Accepté (€)' => number_format($totalAcceptedAmount, 2),
        'Demandes Rejetées' => $totalRejectedDemands,
        'Demandes en Attente' => $totalPendingDemands,
    ];
    $pdf->AddStatistics($stats);

    // Set headers to force download
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="statistiques_demandes.pdf"');

    // Output PDF
    $pdf->Output('D', 'statistiques_demandes.pdf');
    ob_end_flush(); // Ensure no extra output
} catch (Exception $e) {
    ob_end_clean(); // Clear output buffer
    echo "Une erreur s'est produite lors de la génération du PDF : " . $e->getMessage();
}
