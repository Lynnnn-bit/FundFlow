<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../control/ContratController.php';
require_once __DIR__ . '/../../control/PartenaireController.php';
require_once __DIR__ . '/../../vendor/fpdf/fpdf.php'; // Ensure FPDF is installed

$contratController = new ContratController();
$partenaireController = new PartenaireController();

$contrats = $contratController->getAllContracts();
$unapprovedPartenaires = $partenaireController->getUnapprovedPartenaires();

class PDF extends FPDF {
    // Header
    function Header() {
        $this->SetFont('Arial', 'B', 14);
        $this->Cell(0, 10, 'Rapport des Contrats et Demandes de Partenariat', 0, 1, 'C');
        $this->Ln(10);
    }

    // Footer
    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, 'Page ' . $this->PageNo(), 0, 0, 'C');
    }
}

$pdf = new PDF();
$pdf->AddPage();
$pdf->SetFont('Arial', '', 12);

// Add Demandes de Partenariat
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 10, 'Demandes de Partenariat', 0, 1);
$pdf->Ln(5);

$pdf->SetFont('Arial', '', 10);
$pdf->SetFillColor(200, 220, 255);
$pdf->Cell(20, 10, 'ID', 1, 0, 'C', true);
$pdf->Cell(40, 10, 'Nom', 1, 0, 'C', true);
$pdf->Cell(50, 10, 'Email', 1, 0, 'C', true);
$pdf->Cell(30, 10, 'Téléphone', 1, 0, 'C', true);
$pdf->Cell(25, 10, 'Montant', 1, 0, 'C', true);
$pdf->Cell(50, 10, 'Description', 1, 1, 'C', true);

foreach ($unapprovedPartenaires as $partner) {
    $pdf->Cell(20, 10, $partner['id_partenaire'], 1);
    $pdf->Cell(40, 10, $partner['nom'], 1);
    $pdf->Cell(50, 10, $partner['email'], 1);
    $pdf->Cell(30, 10, $partner['telephone'], 1);
    $pdf->Cell(25, 10, $partner['montant'] . ' €', 1);
    $pdf->Cell(50, 10, substr($partner['description'], 0, 30) . '...', 1, 1);
}

// Add Contrats
$pdf->Ln(10);
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 10, 'Contrats', 0, 1);
$pdf->Ln(5);

$pdf->SetFont('Arial', '', 10);
$pdf->SetFillColor(200, 220, 255);
$pdf->Cell(20, 10, 'ID', 1, 0, 'C', true);
$pdf->Cell(40, 10, 'Partenaire', 1, 0, 'C', true);
$pdf->Cell(30, 10, 'Date Début', 1, 0, 'C', true);
$pdf->Cell(30, 10, 'Date Fin', 1, 0, 'C', true);
$pdf->Cell(30, 10, 'Statut', 1, 0, 'C', true);
$pdf->Cell(40, 10, 'Créé le', 1, 1, 'C', true);

foreach ($contrats as $contrat) {
    $pdf->Cell(20, 10, $contrat['id_contrat'], 1);
    $pdf->Cell(40, 10, $contrat['partenaire_nom'], 1);
    $pdf->Cell(30, 10, date('d/m/Y', strtotime($contrat['date_deb'])), 1);
    $pdf->Cell(30, 10, date('d/m/Y', strtotime($contrat['date_fin'])), 1);
    $pdf->Cell(30, 10, ucfirst($contrat['status']), 1);
    $pdf->Cell(40, 10, date('d/m/Y H:i', strtotime($contrat['created_at'])), 1, 1);
}

// Output the PDF for download
$pdf->Output('D', 'rapport_contrats_demandes.pdf');
