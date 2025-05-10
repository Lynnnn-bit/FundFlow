<?php
// Check if 'id' is passed as a parameter
if (!isset($_GET['id'])) {
    die("ID startup manquant.");
}

$id = htmlspecialchars($_GET['id']);

// Function to generate a short key (using md5 or uniqid)
function generateShortLink($id) {
    return substr(md5($id), 0, 6); // Generate a short 6-character link
}

// Generate the short link based on the startup ID
$shortLink = generateShortLink($id);

// Full URL of the startup detail page
$fullUrl = "https://votresite.com/startup_detail.php?id=" . $id;

// Create the short URL (e.g., https://votresite.com/s/abc123)
$shortUrl = "https://votresite.com/s/" . $shortLink;

// Display the generated short URL
echo "<h2>Votre URL courte est :</h2>";
echo "<p><a href=\"$shortUrl\" target=\"_blank\">$shortUrl</a></p>";
echo "<p>Cliquez sur le lien ci-dessus pour accéder à la page de la startup.</p>";
?>
