<?php
include_once '../../Controller/EvennementC.php';
include_once '../../Model/Evennement.php';
include_once '../../Controller/StartupC.php';

$evenementC = new EvennementC();
$startupC = new StartupC();
$startups = $startupC->getAllStartups();

$error = "";
$evenement = null;

if (isset($_GET['id'])) {
    $evenement = $evenementC->getEvenementById($_GET['id']);
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["id_evenement"])) {
    $id = $_POST["id_evenement"];
    $id_startup = $_POST["id_startup"];
    $nom = htmlspecialchars(trim($_POST["nom"]));
    $type = $_POST["type"];
    $date_evenement = $_POST["date_evenement"];
    $horaire = $_POST["horaire"];
    $nb_place = intval($_POST["nb_place"]);

    $affiche = $_POST['existing_affiche'];
    $targetDir = "uploads/";

    if (isset($_FILES["affiche"]) && $_FILES["affiche"]["error"] == 0) {
        $afficheName = basename($_FILES["affiche"]["name"]);
        $affiche = $targetDir . time() . "_affiche_" . $afficheName;
        move_uploaded_file($_FILES["affiche"]["tmp_name"], $affiche);
    }

    if (!empty($nom) && !empty($type) && $nb_place > 0 && !empty($id_startup)) {
        $evenementObj = new Evennement($id, $id_startup, $date_evenement, $type, $horaire, $nb_place, $affiche, $nom);
        try {
            $evenementC->updateEvenement($evenementObj, $id);
            header("Location: addStartup.php");
            exit();
        } catch (Exception $e) {
            $error = "Erreur lors de la mise à jour : " . $e->getMessage();
        }
    } else {
        $error = "Veuillez remplir correctement tous les champs.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier un Événement</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: linear-gradient(to right, #141e30, #243b55);
        }
        .form-wrapper {
            background-color: rgba(255, 255, 255, 0.05);
            padding: 30px;
            border-radius: 8px;
            width: 90%;
            max-width: 500px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.4);
            margin: 40px auto;
            color: white;
        }
        h2 { text-align: center; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 6px; }
        input[type="text"],
        input[type="number"],
        input[type="date"],
        input[type="time"],
        select,
        input[type="file"] {
            width: 100%;
            padding: 10px;
            border: none;
            border-radius: 5px;
            background-color: rgba(255, 255, 255, 0.2);
            color: white;
        }
        button {
            background-color: #1abc9c;
            color: white;
            width: 100%;
            padding: 12px;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
        }
        button:hover { background-color: #16a085; }
        .error { color: red; text-align: center; }
    </style>
</head>
<body>

<div class="form-wrapper">
    <h2>Modifier l'Événement</h2>
    <?php if (!empty($error)): ?>
        <p class="error"><?= $error ?></p>
    <?php endif; ?>
    <?php if ($evenement): ?>
    <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="id_evenement" value="<?= $evenement['id_evenement'] ?>">

        <!-- id_startup -->
        <div class="form-group">
            <label for="id_startup">Startup associée</label>
            <select name="id_startup" required>
                <option disabled selected>-- Choisir une startup --</option>
                <?php foreach ($startups as $startup): ?>
                    <option value="<?= $startup['id_startup'] ?>" 
                        <?= ($evenement['id_startup'] == $startup['id_startup']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($startup['nom_startup']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- date_evenement -->
        <div class="form-group">
            <label>Date de l'événement</label>
            <input type="date" name="date_evenement" value="<?= $evenement['date_evenement'] ?>" required>
        </div>

        <!-- type -->
        <div class="form-group">
            <label>Type</label>
            <select name="type" required>
                <option value="présentiel" <?= $evenement['type'] == 'présentiel' ? 'selected' : '' ?>>Présentiel</option>
                <option value="en ligne" <?= $evenement['type'] == 'en ligne' ? 'selected' : '' ?>>En ligne</option>
            </select>
        </div>

        <!-- horaire -->
        <div class="form-group">
            <label>Horaire</label>
            <input type="time" name="horaire" value="<?= $evenement['horaire'] ?>" required>
        </div>

        <!-- nb_place -->
        <div class="form-group">
            <label>Nombre de places</label>
            <input type="number" name="nb_place" min="1" value="<?= $evenement['nb_place'] ?>" required>
        </div>

        <!-- affiche -->
        <div class="form-group">
            <label>Affiche (laisser vide pour conserver)</label>
            <input type="file" name="affiche" accept="image/*">
            <input type="hidden" name="existing_affiche" value="<?= htmlspecialchars($evenement['affiche']) ?>">
        </div>

        <!-- nom -->
        <div class="form-group">
            <label>Nom de l'événement</label>
            <input type="text" name="nom" value="<?= htmlspecialchars($evenement['nom']) ?>" required>
        </div>

        <button type="submit">Enregistrer les modifications</button>
    </form>
    <?php else: ?>
        <p class="error">Événement introuvable.</p>
    <?php endif; ?>
</div>

</body>
</html>
