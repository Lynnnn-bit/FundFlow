<?php
// events.php
session_start();
include_once '../../control/startupC.php';
include_once '../../control/EvennementC.php';

$evenementC = new EvennementC();
$startupC = new startupC();

$sort = isset($_GET['sort']) ? $_GET['sort'] : null;
$evenements = $evenementC->getAllEvenements();

if ($sort === 'places') {
    usort($evenements, function($a, $b) {
        return $a['nb_place'] - $b['nb_place'];
    });
}

$startups = $startupC->getAllStartups();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Événements</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        /* Ton style ici – inchangé */
        body {
            margin: 0;
            font-family: 'Arial', sans-serif;
            background: linear-gradient(to right, #141e30, #243b55);
            color: white;
        }
        .navbar { background: linear-gradient(to right, #0f2027, #203a43, #2c5364); padding: 20px 30px; display: flex; justify-content: space-between; align-items: center; }
        .navbar img { height: 50px; }
        .nav-links { display: flex; gap: 20px; }
        .nav-link { color: white; text-decoration: none; font-weight: 500; }
        .container { width: 90%; margin: 30px auto; }
        .event-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 25px; padding: 20px 0; }
        .event-card { background: rgba(255, 255, 255, 0.1); border-radius: 10px; padding: 20px; backdrop-filter: blur(10px); border: 1px solid rgba(255, 255, 255, 0.2); }
        .event-card img { width: 100%; height: 200px; object-fit: cover; border-radius: 5px; margin-bottom: 15px; }
        .btn-delete, .btn-update, .btn-pdf, .btn-sort { padding: 8px 20px; border-radius: 5px; border: none; cursor: pointer; transition: background 0.3s; color: white; }
        .btn-delete { background: #e74c3c; }
        .btn-update { background: #3498db; }
        .btn-pdf { background: #f39c12; text-decoration: none; display: inline-block; }
        .btn-sort { background: #2ecc71; text-decoration: none; display: inline-block; }
        .btn-delete:hover { background: #c0392b; }
        .btn-update:hover { background: #2980b9; }
        .btn-pdf:hover { background: #e67e22; }
        .btn-sort:hover { background: #27ae60; }
        .alert { padding: 15px; margin: 20px 0; border-radius: 5px; text-align: center; }
        .alert-success { background: #dff0d8; color: #2c3e50; }
        .alert-error { background: #f8d7da; color: #721c24; }
        .button-group { display: flex; gap: 10px; justify-content: center; margin-top: 15px; flex-wrap: wrap; }
        .action-buttons { display: flex; justify-content: center; gap: 15px; margin-bottom: 20px; flex-wrap: wrap; }
        .back-to-frontoffice { text-align: center; margin-top: 20px; }
        .btn-back { display: inline-block; padding: 10px 20px; background-color: #007bff; color: #fff; text-decoration: none; border-radius: 5px; font-size: 16px; }
        .btn-back:hover { background-color: #0056b3; }
    </style>
</head>
<body>
    <div class="navbar">
        <img src="assets/logo.png" alt="Logo">
        <div class="nav-links">
            <a href="frontoffice.php" class="nav-link">Accueil</a>
            <a href="#" class="nav-link">Contact</a>
        </div>
    </div>

    <div class="container">
        <?php if(isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <?= $_SESSION['success']; unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>

        <?php if(isset($_SESSION['error'])): ?>
            <div class="alert alert-error">
                <?= $_SESSION['error']; unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <h1>Événements à venir</h1>

        <div class="action-buttons">
            <a href="generate_pdf.php" class="btn-pdf">
                <i class="fas fa-file-pdf"></i> Exporter en PDF
            </a>
            <a href="?sort=places" class="btn-sort">
                <i class="fas fa-sort-amount-down"></i> Trier par nombre de places
            </a>
            <?php if ($sort === 'places'): ?>
                <a href="events.php" class="btn-sort" style="background-color: #95a5a6;">
                    <i class="fas fa-times"></i> Annuler le tri
                </a>
            <?php endif; ?>
            <a href="statistiques.php" class="btn-sort">
                <i class="fas fa-chart-pie"></i> Statistiques
            </a>
        </div>

        <div class="event-grid">
            <?php if(!empty($evenements)): ?>
                <?php foreach($evenements as $event): ?>
                    <div class="event-card">
                        <?php if(!empty($event['affiche'])): ?>
                            <img src="../admin/<?= htmlspecialchars($event['affiche']) ?>" alt="Affiche de l'événement">
                        <?php endif; ?>

                        <h2><?= htmlspecialchars($event['nom']) ?></h2>
                        <p><strong>Date :</strong> <?= htmlspecialchars($event['date_evenement']) ?></p>
                        <p><strong>Type :</strong> <?= htmlspecialchars($event['type']) ?></p>
                        <p><strong>Horaire :</strong> <?= htmlspecialchars($event['horaire']) ?></p>
                        <p><strong>Places disponibles :</strong> <?= htmlspecialchars($event['nb_place']) ?></p>

                        <div class="button-group">
                            <form method="GET" action="updateevents.php">
                                <input type="hidden" name="id" value="<?= $event['id_evenement'] ?>">
                                <button type="submit" class="btn-update">
                                    <i class="fas fa-edit"></i> Modifier
                                </button>
                            </form>

                            <form method="POST" action="deleteevents.php" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cet événement ?')">
                                <input type="hidden" name="id_evenement" value="<?= $event['id_evenement'] ?>">
                                <button type="submit" class="btn-delete">
                                    <i class="fas fa-trash"></i> Supprimer
                                </button>
                            </form>

                            <!-- Nouveau bouton Participer -->
                            <form method="GET" action="participer.php">
                                <input type="hidden" name="id_evenement" value="<?= $event['id_evenement'] ?>">
                                <button type="submit" class="btn-update">
                                    <i class="fas fa-user-plus"></i> Participer
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>Aucun événement prévu pour le moment.</p>
            <?php endif; ?>
        </div>

        <div class="back-to-frontoffice">
            <a href="frontoffice.php" class="btn-back">Ajouter evennement</a>
        </div>
    </div>
</body>
</html>
