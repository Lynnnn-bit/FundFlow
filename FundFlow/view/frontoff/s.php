<?php
if (!isset($_GET['key'])) {
    die("Key not found.");
}

$key = $_GET['key'];
// Assuming we can decode or map the key to an ID in your database (this is simplified)
// For instance, you might map "abc123" to the actual startup ID.

$id = ... // Find the startup ID based on the short key

// Redirect to the full URL of the startup detail page
header("Location: https://localhost/startup_detail.php?id=" . $id);
exit();
?>
