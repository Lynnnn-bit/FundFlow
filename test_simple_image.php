<?php
require_once __DIR__ . '/libs/SimpleImage/src/claviska/SimpleImage.php';

use claviska\SimpleImage;

try {
    $image = new SimpleImage();
    $image->fromFile('path/to/image.jpg') // Replace with the path to your image
          ->resize(300, 200)
          ->toScreen();
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage();
}
?>