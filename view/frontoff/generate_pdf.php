<?php
ob_start(); // Start output buffering

require_once('tcpdf/tcpdf.php'); // Adjust path if needed
include_once '../../control/EvennementC.php';

$evenementC = new EvennementC();
$evenements = $evenementC->getAllEvenements();

$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Your Name');
$pdf->SetTitle('Liste des événements');
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
$pdf->AddPage();
$pdf->SetFont('helvetica', '', 12);

$html = '<h1>Liste des Événements</h1>';
foreach ($evenements as $event) {
    $html .= '
        <strong>Nom:</strong> ' . htmlspecialchars($event['nom']) . '<br>
        <strong>Date:</strong> ' . htmlspecialchars($event['date_evenement']) . '<br>
        <strong>Type:</strong> ' . htmlspecialchars($event['type']) . '<br>
        <strong>Horaire:</strong> ' . htmlspecialchars($event['horaire']) . '<br>
        <strong>Places:</strong> ' . htmlspecialchars($event['nb_place']) . '<br><br>
    ';
}

$pdf->writeHTML($html, true, false, true, false, '');

ob_end_clean(); // Clear output buffer before sending PDF
$pdf->Output('evenements.pdf', 'I');
exit;
