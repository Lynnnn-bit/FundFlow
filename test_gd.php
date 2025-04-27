<?php
if (function_exists('ImageCreate')) {
    echo "GD library is enabled and ImageCreate() is available.";
} else {
    echo "GD library is not enabled or ImageCreate() is unavailable.";
}
?>