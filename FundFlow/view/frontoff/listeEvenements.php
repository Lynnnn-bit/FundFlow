<?php
include_once '../../Control/evenementC.php'; // Ensure the correct path
$evenementC = new EvenementC();
$evenements = $evenementC->getAllEvenements(); // Assume this method exists and fetches events from the database
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Liste des Événements</title>
</head>
<body>
    <h1>Liste des Événements</h1>
    <table border="1">
        <tr>
            <th>ID</th>
            <th>Nom de l'Événement</th>
            <th>Type</th>
            <th>Date</th>
            <th>Horaire</th>
            <th>Nombre de Places</th>
            <th>Affiche</th>
            <th>ID Startup</th>
        </tr>
        <?php foreach ($evenements as $e): ?>
        <tr>
            <td><?= $e['id_evenement'] ?></td>
            <td><?= $e['nom'] ?></td>
            <td><?= $e['type'] ?></td>
            <td><?= $e['date_evenement'] ?></td>
            <td><?= $e['horaire'] ?></td>
            <td><?= $e['nb_place'] ?></td>
            <td><img src="../../uploads/affiches/<?= $e['affiche'] ?>" width="80"></td>
            <td><?= $e['id_startup'] ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>
