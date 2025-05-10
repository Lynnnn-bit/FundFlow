<?php
include_once '../../Controller/startupC.php';
$startupC = new startupC();
$startups = $startupC->getAllStartups(); // assume this method exists
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Liste des Startups</title>
</head>
<body>
    <h1>Liste des Startups</h1>
    <table border="1">
        <tr>
            <th>ID</th>
            <th>Nom</th>
            <th>Secteur</th>
            <th>Adresse site</th>
            <th>Logo</th>
            <th>Description</th>
            <th>Email</th>
            <th>Vidéo Présentation</th>
        </tr>
        <?php foreach ($startups as $s): ?>
        <tr>
            <td><?= $s['id_startup'] ?></td>
            <td><?= $s['nom_startup'] ?></td>
            <td><?= $s['secteur'] ?></td>
            <td><?= $s['adresse_site'] ?></td>
            <td><img src="<?= $s['logo'] ?>" width="80"></td>
            <td><?= $s['description'] ?></td>
            <td><?= $s['email'] ?></td>
            <td><img src="<?= $s['video_presentation'] ?>" width="80"></td>
        </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>
