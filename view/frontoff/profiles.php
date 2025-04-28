<?php
session_start();
require_once __DIR__ . '/../../config.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: connexion.php");
    exit();
}

// Fetch user data
try {
    $db = Config::getConnexion();
    $stmt = $db->prepare("SELECT * FROM utilisateur WHERE id_utilisateur = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        die("Utilisateur non trouvé. Vérifiez que l'utilisateur existe dans la base de données.");
    }
} catch (PDOException $e) {
    die("Erreur de base de données: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FundFlow - Profil</title>
    <link rel="stylesheet" href="css/styleprofile.css">
    
</head>
<body>
    <div class="container">
        <header class="navbar">
            <img src="assets/logo.png" alt="FundFlow" height="60">
            <nav>
                <a href="apropos.php"><i class="fas fa-info-circle"></i> A propos</a>
                <a href="contact.php">Contact</a>
                <select onchange="handleMenu(this)" class="profile-menu">
                    <option value="">Mon compte ▼</option>
                    <option value="profiles">Profil</option>
                    <option value="mesprojets">Mes projets</option>
                    <option value="logout">Déconnexion</option>
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
            </div>
        </main>
    </div>

    <script>
        function handleMenu(select) {
            const value = select.value;
            if (value === 'logout') {
                window.location.href = '?action=logout';
            } else if(value) {
                window.location.href = value + '.php';
            }
            select.value = '';
        }
    </script>
</body>
</html>