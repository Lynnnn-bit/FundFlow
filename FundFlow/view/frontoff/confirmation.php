<?php
ob_start(); // Active la mise en tampon de sortie

require_once('tcpdf/tcpdf.php'); // ajuste le chemin selon ton projet
include_once '../../control/EvennementC.php';
$evenementC = new EvennementC();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = $_POST['nom'];
    $prenom = $_POST['prenom'];
    $id = $_POST['id_evenement'];

    $event = $evenementC->getEvenementById($id);

    if (!$event || $event['nb_place'] <= 0) {
        die("Événement complet ou introuvable.");
    }

    // Décrémenter les places disponibles
    $evenementC->decrementerPlaces($id);

    // Création du PDF
    $pdf = new TCPDF();
    $pdf->AddPage();
    $pdf->SetFont('helvetica', 'B', 16);

    $pdf->Cell(0, 10, "Confirmation de participation", 0, 1, 'C');
    $pdf->Ln(10);
    $pdf->SetFont('helvetica', '', 12);
    $pdf->Cell(0, 10, "Nom : $nom", 0, 1);
    $pdf->Cell(0, 10, "Prénom : $prenom", 0, 1);
    $pdf->Cell(0, 10, "Événement : " . $event['nom'], 0, 1);
    $pdf->Cell(0, 10, "Date : " . $event['date_evenement'], 0, 1);
    $pdf->Cell(0, 10, "Horaire : " . $event['horaire'], 0, 1);

    ob_end_clean(); // Supprime toute sortie précédente
    $pdf->Output('confirmation.pdf', 'I'); // Affiche le PDF dans le navigateur
}
?>
