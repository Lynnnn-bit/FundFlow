<?php
require_once __DIR__ . '/../../config.php';

// Vérification de session si nécessaire
session_start();

// Redirect to login if not authenticated
if (!isset($_SESSION['user_id'])) {
    header("Location: connexion.php");
    exit();
}

// Rest of your existing acceuil2.php code...
// Traitement de déconnexion
/*if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    session_destroy();
    header('Location: connexion.php');
    exit();
}*/
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>FundFlow - Accueil</title>
  <link rel="stylesheet" href="css/styleacc2.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <script>
    function handleMenu(select) {
      const value = select.value;
      if (value === 'logout') {
        window.location.href = '?action=logout';
      } else if (value) {
        window.location.href = value + '.php';
      }
      select.value = ''; // Réinitialiser la sélection
    }
  </script>
</head>
<body>
  <div class="container">
    <header class="navbar">
      <img src="assets/logo.png" alt="FundFlow" height="60">
      <nav>
      <a href="apropos.html"><i class="fas fa-info-circle"></i> À propos</a>
      <a href="contact.html"><i class="fas fa-envelope"></i> Contact</a>
        
        <!-- Menu déroulant -->
        <select onchange="handleMenu(this)" class="profile-menu">
          <option value="">Mon compte ▼</option>
          <option value="profiles">Profil</option>
          <option value="mesprojets">Mes projets</option>
          <option value="logout"> Déconnexion</option>
        </select>
      </nav>
    </header>

    <main class="main-section">
      <div class="left">
        <h1><span>Connecter l'innovation</span><br>avec le capital</h1>
        <p>FundFlow est la plateforme qui relie les investisseurs aux entrepreneurs <br> et startups à la recherche de financement. Transformez vos idées en réalité <br> ou diversifiez votre portefeuille d'investissement.</p>
        
      </div>
      <div class="right">
        <img src="assets/meeting.png" alt="Illustration de réunion">
      </div>
    </main>
  </div>
</body>
</html>