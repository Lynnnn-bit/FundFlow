<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../controlle/financecontroller.php';
require_once __DIR__ . '/../../libs/fpdf/fpdf.php';

$financeController = new FinanceController();
$demandes = $financeController->getAllFinanceRequests();

class PDF extends FPDF
{
    function Header()
    {
        $this->SetFont('Arial', 'B', 14);
        $this->Cell(0, 10, 'Liste des Demandes de Financement', 0, 1, 'C');
        $this->Ln(5);
    }

    function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, 'Page ' . $this->PageNo(), 0, 0, 'C');
    }

    function AddTable($demandes)
    {
        $this->SetFont('Arial', 'B', 12);
        $this->SetFillColor(30, 60, 82);
        $this->SetTextColor(255, 255, 255);
        $this->Cell(20, 10, 'ID', 1, 0, 'C', true);
        $this->Cell(50, 10, 'Projet', 1, 0, 'C', true);
        $this->Cell(30, 10, 'Montant (€)', 1, 0, 'C', true);
        $this->Cell(30, 10, 'Durée (mois)', 1, 0, 'C', true);
        $this->Cell(30, 10, 'Statut', 1, 1, 'C', true);

        $this->SetFont('Arial', '', 10);
        $this->SetTextColor(0, 0, 0);
        foreach ($demandes as $demand) {
            $this->Cell(20, 10, $demand['id_demande'], 1);
            $this->Cell(50, 10, $demand['projet_titre'], 1);
            $this->Cell(30, 10, number_format($demand['montant_demandee'], 2), 1);
            $this->Cell(30, 10, $demand['duree'], 1);
            $this->Cell(30, 10, ucfirst($demand['status']), 1, 1);
        }
    }
}

$pdf = new PDF();
$pdf->AddPage();
$pdf->AddTable($demandes);

header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="liste_demandes.pdf"');
$pdf->Output('D', 'liste_demandes.pdf');
