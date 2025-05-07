<?php
// --- Pas d'espace avant ce <?php ---

require_once 'C:\xampp\htdocs\user\FundFlow\vendor\autoload.php';
require_once 'C:\xampp\htdocs\user\FundFlow\models\Startup.php';
require_once 'C:\xampp\htdocs\user\FundFlow\control\startupC.php';

use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\SvgWriter;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelHigh;

// Récupérer l'ID depuis l'URL
$startupId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Si aucun id, afficher un message
if ($startupId <= 0) {
    echo "Aucune startup spécifiée.";
    exit;
}

// Aller chercher les infos de la startup depuis la base de données
$startupC = new startupC();
$startup = $startupC->getStartupById($startupId);

if (!$startup) {
    echo "Startup non trouvée.";
    exit;
}

// Contenu du QR code (format JSON avec toutes les infos demandées)
$qrContent = json_encode([
    'Nom' => $startup['nom_startup'],
    'Secteur' => $startup['secteur'],
    'Site Web' => $startup['adresse_site'],
    'Description' => $startup['description'],
    'Email' => $startup['email']
], JSON_UNESCAPED_UNICODE);

$result = Builder::create()
    ->writer(new SvgWriter())
    ->data($qrContent)  // Données ici sont le contenu JSON
    ->encoding(new Encoding('UTF-8'))
    ->errorCorrectionLevel(new ErrorCorrectionLevelHigh())
    ->size(300)
    ->margin(10)
    ->build();

// Maintenant afficher une vraie page HTML qui affiche le QR code
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Code Startup</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f4f9;
            color: #333;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            text-align: center;
        }

        h1 {
            font-size: 24px;
            margin-bottom: 20px;
            color: #333;
        }

        .container {
            background-color: #ffffff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            text-align: center;
            width: 100%;
            max-width: 400px;
        }

        .qr-code {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            margin-top: 20px;
            display: inline-block;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .footer {
            margin-top: 20px;
            font-size: 14px;
            color: #888;
        }

        .footer a {
            color: #007bff;
            text-decoration: none;
        }
    </style>
</head>
<body>

<div class="container">
    <h1>QR Code de la Startup: <?php echo htmlspecialchars($startup['nom_startup']); ?></h1>

    <div class="qr-code">
        <?php echo $result->getString(); ?>
    </div>

    <div class="footer">
        <p>Scannez ce code avec votre application de scan pour obtenir les détails de la startup.</p>
    </div>
</div>

</body>
</html>
