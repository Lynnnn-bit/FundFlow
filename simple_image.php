<?php
require_once __DIR__ . '/SimpleImage.php';

use claviska\SimpleImage;

$image = new SimpleImage();
$image->fromFile('path/to/image.jpg')
      ->resize(300, 200)
      ->toScreen();
?>
