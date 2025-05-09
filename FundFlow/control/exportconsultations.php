<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/consultationcontroller.php';
require_once __DIR__ . '/../libs/fpdf/fpdf.php';

class PDF extends FPDF
{
    function Header()
    {
        $this->SetFont('Arial', 'B', 16);
        $this->Cell(0, 10, utf8_decode('Liste des Consultations'), 0, 1, 'C');
        $this->Ln(10);
    }

    function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, utf8_decode('Page ') . $this->PageNo(), 0, 0, 'C');
    }
}

$controller = new ConsultationController();
$consultations = $controller->getAllConsultations();

$pdf = new PDF();
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 11);

// Table header
$pdf->SetFillColor(230, 230, 230);
$pdf->Cell(20, 10, 'ID', 1, 0, 'C', true);
$pdf->Cell(35, 10, 'Consultant', 1, 0, 'C', true);
$pdf->Cell(35, 10, 'Client', 1, 0, 'C', true);
$pdf->Cell(30, 10, 'Date', 1, 0, 'C', true);
$pdf->Cell(25, 10, utf8_decode('Début'), 1, 0, 'C', true);
$pdf->Cell(25, 10, 'Fin', 1, 0, 'C', true);
$pdf->Cell(25, 10, 'Tarif (€)', 1, 1, 'C', true);

// Table content
$pdf->SetFont('Arial', '', 10);
foreach ($consultations as $consultation) {
    $consultant = $controller->getUserById($consultation['id_utilisateur1']);
    $client = $controller->getUserById($consultation['id_utilisateur2']);
    
    $id = $consultation['id_consultation'];
    $consultantName = utf8_decode($consultant['prenom'] . ' ' . $consultant['nom']);
    $clientName = utf8_decode($client['prenom'] . ' ' . $client['nom']);
    $date = isset($consultation['date_consultation']) ? date('Y-m-d', strtotime($consultation['date_consultation'])) : 'N/A';
    $heureDeb = isset($consultation['heure_deb']) ? $consultation['heure_deb'] : 'N/A';
    $heureFin = isset($consultation['heure_fin']) ? $consultation['heure_fin'] : 'N/A';
    $tarif = isset($consultation['tarif']) ? number_format($consultation['tarif'], 2, ',', ' ') : 'N/A';

    $pdf->Cell(20, 8, $id, 1, 0, 'C');
    $pdf->Cell(35, 8, $consultantName, 1);
    $pdf->Cell(35, 8, $clientName, 1);
    $pdf->Cell(30, 8, $date, 1);
    $pdf->Cell(25, 8, $heureDeb, 1);
    $pdf->Cell(25, 8, $heureFin, 1);
    $pdf->Cell(25, 8, $tarif, 1, 1, 'R');
}

$pdf->Output('D', 'consultations.pdf');
exit;
