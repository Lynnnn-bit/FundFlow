<?php
session_start();
require_once __DIR__ . '/../../config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: connexion.php");
    exit();
}

$db = Config::getConnexion();
$user = null;

// Fetch user data
try {
    $stmt = $db->prepare("SELECT * FROM utilisateur WHERE id_utilisateur = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erreur de base de données : " . $e->getMessage());
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = $_POST['nom'];
    $prenom = $_POST['prenom'];
    $email = $_POST['email'];
    $adresse = $_POST['adresse'];
    $tel = $_POST['tel'];

    try {
        $stmt = $db->prepare("UPDATE utilisateur SET 
            nom = ?, 
            prenom = ?, 
            email = ?, 
            adresse = ?, 
            tel = ? 
            WHERE id_utilisateur = ?");
        
        $stmt->execute([$nom, $prenom, $email, $adresse, $tel, $_SESSION['user_id']]);
        header("Location: profiles.php");
        exit();
    } catch (PDOException $e) {
        die("Erreur de mise à jour : " . $e->getMessage());
    }
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
            </nav>
        </header>

    <div class="container">
        <!-- Keep the same header navigation -->
        <header class="navbar">
            <!-- ... same header content as profile.php ... -->
        </header>

        <main>
            <div class="profile-container">
                <h1>Modifier Profil</h1>
                <form method="post">
                    <table class="profile-table">
                        <tr>
                            <th>Nom</th>
                            <td><input type="text" name="nom" value="<?= htmlspecialchars($user['nom']) ?>" required></td>
                        </tr>
                        <tr>
                            <th>Prénom</th>
                            <td><input type="text" name="prenom" value="<?= htmlspecialchars($user['prenom']) ?>" required></td>
                        </tr>
                        <tr>
                            <th>Email</th>
                            <td><input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required></td>
                        </tr>
                        <tr>
                            <th>Adresse</th>
                            <td><input type="text" name="adresse" value="<?= htmlspecialchars($user['adresse']) ?>"></td>
                        </tr>
                        <tr>
                            <th>Téléphone</th>
                            <td><input type="tel" name="tel" value="<?= htmlspecialchars($user['tel']) ?>"></td>
                        </tr>
                    </table>
                    <div class="button-group">
                        <button type="submit" class="btn">Enregistrer</button>
                        
                        <a href="profiles.php" class="btn btn-danger">Annuler</a>
                    </div>
                </form>
            </div>
        </main>
    </div>
</body>
</html>