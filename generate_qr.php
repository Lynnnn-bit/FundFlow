<?php
require_once __DIR__ . '/vendor/autoload.php';

use Endroid\QrCode\Builder\Builder;

$qrCode = Builder::create()
    ->data('http://localhost/fund/view/Frontoff/project-details.php?id=1')
    ->size(300)
    ->margin(10)
    ->build();

header('Content-Type: ' . $qrCode->getMimeType());
echo $qrCode->getString();
?>
