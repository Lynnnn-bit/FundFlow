<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../controlle/financecontroller.php';
require_once __DIR__ . '/../../libs/fpdf/fpdf.php';

// Debugging: Ensure no output before headers
ob_start();

$controller = new FinanceController();
$demands = $controller->getAllFinanceRequests();

class PDF extends FPDF
{
    // Header
    function Header()
    {
        $this->SetFont('Arial', 'B', 14);
        $this->Cell(0, 10, 'Historique des Demandes de Financement', 0, 1, 'C');
        $this->Ln(5);
    }

    // Footer
    function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, 'Page ' . $this->PageNo(), 0, 0, 'C');
    }

    // Table Header
    function TableHeader()
    {
        $this->SetFont('Arial', 'B', 12);
        $this->SetFillColor(30, 60, 82);
        $this->SetTextColor(255, 255, 255);
        $this->Cell(20, 10, 'ID', 1, 0, 'C', true);
        $this->Cell(50, 10, 'Projet', 1, 0, 'C', true);
        $this->Cell(30, 10, 'Montant (€)', 1, 0, 'C', true);
        $this->Cell(30, 10, 'Durée (mois)', 1, 0, 'C', true);
        $this->Cell(30, 10, 'Statut', 1, 0, 'C', true);
        $this->Cell(30, 10, 'Réponses', 1, 1, 'C', true);
    }

    // Table Rows
    function TableRows($demands)
    {
        $this->SetFont('Arial', '', 10);
        $this->SetTextColor(0, 0, 0);
        foreach ($demands as $demand) {
            $this->Cell(20, 10, $demand['id_demande'], 1);
            $this->Cell(50, 10, $demand['projet_titre'], 1);
            $this->Cell(30, 10, number_format($demand['montant_demandee'], 2), 1);
            $this->Cell(30, 10, $demand['duree'], 1);
            $this->Cell(30, 10, ucfirst($demand['status']), 1);
            $this->Cell(30, 10, $demand['nb_reponses'], 1, 1);
        }
    }
}

try {
    // Create PDF
    $pdf = new PDF();
    $pdf->AddPage();
    $pdf->TableHeader();
    $pdf->TableRows($demands);

    // Set headers to force download
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="historique_demandes.pdf"');

    // Output PDF
    $pdf->Output('D', 'historique_demandes.pdf');
    ob_end_flush(); // Ensure no extra output
} catch (Exception $e) {
    ob_end_clean(); // Clear output buffer
    echo "Une erreur s'est produite lors de la génération du PDF : " . $e->getMessage();
}
