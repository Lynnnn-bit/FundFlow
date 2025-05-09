<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../models/utilisateur.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

try {
    $db = Config::getConnexion();
    $stmt = $db->prepare("SELECT * FROM utilisateur WHERE role = 'consultant'");
    $stmt->execute();
    $consultants = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erreur de base de données: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liste des Consultants</title>
    <link rel="stylesheet" href="css/styleallconsu.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <header class="navbar">
        <div class="logo-container">
            <img src="assets/logo.png" alt="FundFlow" height="60">
        </div>
        <nav>
            <a href="apropos.html"><i class="fas fa-info-circle"></i> À propos</a>
            <a href="contact.html"><i class="fas fa-envelope"></i> Contact</a>
            <a href="accueil.html" class="logout"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
        </nav>
    </header>

    <main>
        <h1 class="text-center">Liste des Consultants</h1>
        <div class="consultant-container">
            <?php foreach ($consultants as $consultant): ?>
                <div class="consultant-card">
                    <img src="assets/default-avatar.png" alt="Photo de <?= htmlspecialchars($consultant['nom']) ?>">
                    <h3><?= htmlspecialchars($consultant['nom']) ?> <?= htmlspecialchars($consultant['prenom']) ?></h3>
                    <p>Email: <?= htmlspecialchars($consultant['email']) ?></p>
                    <p>Adresse: <?= htmlspecialchars($consultant['adresse']) ?></p>
                    <p>Téléphone: <?= htmlspecialchars($consultant['tel']) ?></p>
                    <a href="addconsultation.php?consultant_id=<?= $consultant['id_utilisateur'] ?>&client_id=<?= $_SESSION['user_id'] ?? '' ?>" class="btn-rendezvous">
                        <link rel="stylesheet" href="css/consultation.css">
                        Prendre Rendez-vous
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </main>
</body>
</html>