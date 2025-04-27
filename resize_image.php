<?php
require_once __DIR__ . '/vendor/autoload.php';

use Intervention\Image\ImageManager;

$manager = new ImageManager(['driver' => 'gd']);
$image = $manager->make('path/to/image.jpg')->resize(300, 200);

header('Content-Type: image/jpeg');
echo $image->response();
?>
