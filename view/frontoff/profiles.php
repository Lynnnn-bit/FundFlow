<?php
session_start();
require_once __DIR__ . '/../../config.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: connexion.php");
    exit();
}

// Handle delete action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    try {
        $db = Config::getConnexion();
        $stmt = $db->prepare("DELETE FROM utilisateur WHERE id_utilisateur = ?");
        $stmt->execute([$_SESSION['user_id']]);
        session_destroy();
        header("Location: accueil.html");
        exit();
    } catch (PDOException $e) {
        die("Erreur lors de la suppression : " . $e->getMessage());
    }
}

// Fetch user data
try {
    $db = Config::getConnexion();
    $stmt = $db->prepare("SELECT * FROM utilisateur WHERE id_utilisateur = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        die("Utilisateur non trouvé.");
    }
} catch (PDOException $e) {
    die("Erreur de base de données : " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FundFlow - Profil</title>
    <link rel="stylesheet" href="css/styleprofiles.css">>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
    <div class="container">
        <header class="navbar">
            <img src="assets/logo.png" alt="FundFlow" height="60">
            <nav>
                <a href="apropos.html"><i class="fas fa-info-circle"></i> A propos</a>
                <a href="contact.html">Contact </a>
                <select onchange="if(this.value) window.location.href=this.value; this.selectedIndex = 0;" class="profile-menu">
          <option value="">Mon compte ▼</option>
          <option value="profiles.php">Profil</option>
          <option value="projets.php">Mes projets</option>
          <option value="accueil.html">Déconnexion</option>
        </select>
                </select>
            </nav>
        </header>

        <main>
            <div class="profile-container">
                <h1>Mon Profil</h1>
                <table class="profile-table">
                    <tr>
                        <th>Nom</th>
                        <td><?= htmlspecialchars($user['nom']) ?></td>
                    </tr>
                    <tr>
                        <th>Prénom</th>
                        <td><?= htmlspecialchars($user['prenom']) ?></td>
                    </tr>
                    <tr>
                        <th>Email</th>
                        <td><?= htmlspecialchars($user['email']) ?></td>
                    </tr>
                    <tr>
                        <th>Rôle</th>
                        <td><?= htmlspecialchars($user['role']) ?></td>
                    </tr>
                    <tr>
                        <th>Adresse</th>
                        <td><?= htmlspecialchars($user['adresse']) ?></td>
                    </tr>
                    <tr>
                        <th>Téléphone</th>
                        <td><?= htmlspecialchars($user['tel']) ?></td>
                    </tr>
                    <tr>
                        <th>Date d'inscription</th>
                        <td><?= htmlspecialchars($user['date_creation']) ?></td>
                    </tr>
                    <tr>
                        <th>Statut</th>
                        <td><?= htmlspecialchars($user['status']) ?></td>
                    </tr>
                </table>

                <div class="button-group">
    <!-- Edit Button -->
    <a href="edit_profile.php" class="btn">Modifier le Profil</a>
    
    <!-- Delete Form -->
    <form method="post" action="accueil.html" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer votre compte définitivement ?');">
        <input type="hidden" name="action" value="delete">
        <button type="submit" class="btn btn-danger">Supprimer le Compte</button>
    </form>
</div>
            </div>
            
        </main>
    </div>

    
</body>
</html>