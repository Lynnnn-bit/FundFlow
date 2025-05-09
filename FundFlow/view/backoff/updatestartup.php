<?php
include_once 'C:\xampp\htdocs\user\FundFlow\control\startupC.php';
include_once 'C:\xampp\htdocs\user\FundFlow\models\Startup.php';

$startupC = new startupC();
$error = "";
$startup = null;

if (isset($_GET['id'])) {
    $startup = $startupC->getStartupById($_GET['id']);
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["id_startup"])) {
    $id = $_POST["id_startup"];
    $nom = htmlspecialchars(trim($_POST["nom_startup"]));
    $secteur = htmlspecialchars(trim($_POST["secteur"]));
    $adresse = htmlspecialchars(trim($_POST["adresse_site"]));
    $description = htmlspecialchars(trim($_POST["description"]));
    $email = filter_var(trim($_POST["email"]), FILTER_VALIDATE_EMAIL);

    $logo = $_POST['existing_logo'];
    $video = $_POST['existing_video'];

    $targetDir = "uploads/";

    if (isset($_FILES["logo"]) && $_FILES["logo"]["error"] == 0) {
        $logoName = basename($_FILES["logo"]["name"]);
        $logo = $targetDir . time() . "_logo_" . $logoName;
        move_uploaded_file($_FILES["logo"]["tmp_name"], $logo);
    }

    if (isset($_FILES["video_presentation"]) && $_FILES["video_presentation"]["error"] == 0) {
        $videoName = basename($_FILES["video_presentation"]["name"]);
        $video = $targetDir . time() . "_video_" . $videoName;
        move_uploaded_file($_FILES["video_presentation"]["tmp_name"], $video);
    }

    if ($email) {
        $startupObj = new Startup($id, $nom, $secteur, $adresse, $logo, $description, $email, $video);
        $startupC->updateStartup($startupObj, $id);
        header("Location: addstartup.php");
        exit();
    } else {
        $error = "Email invalide.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier une Startup</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        /* Keep all CSS exactly the same */
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: linear-gradient(to right, #203a43, #3498db);
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
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 6px;
        }
        input[type="text"],
        input[type="email"],
        input[type="url"],
        textarea,
        input[type="file"] {
            width: 100%;
            padding: 10px;
            border: none;
            border-radius: 5px;
            background-color: rgba(255, 255, 255, 0.2);
            color: white;
        }
        textarea { resize: vertical; }
        button {
            background-color: #203a43;
            color: white;
            width: 100%;
            padding: 12px;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
        }
        button:hover { background-color: #1abc9c; }
        .error { color: red; text-align: center; }
    </style>
</head>
<body>

<div class="form-wrapper">
    <h2>Modifier la Startup</h2>
    <?php if (!empty($error)): ?>
        <p class="error"><?= $error ?></p>
    <?php endif; ?>
    <?php if ($startup): ?>
    <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="id_startup" value="<?= $startup['id_startup'] ?>">
        <div class="form-group">
            <label>Nom Startup</label>
            <input type="text" name="nom_startup" value="<?= htmlspecialchars($startup['nom_startup']) ?>">
        </div>
        <div class="form-group">
            <label>Secteur</label>
            <input type="text" name="secteur" value="<?= htmlspecialchars($startup['secteur']) ?>">
        </div>
        <div class="form-group">
            <label>Adresse du site</label>
            <input type="text" name="adresse_site" value="<?= htmlspecialchars($startup['adresse_site']) ?>">
        </div>
        <div class="form-group">
            <label>Logo (laisser vide pour conserver l'existant)</label>
            <input type="file" name="logo" accept="image/*">
            <input type="hidden" name="existing_logo" value="<?= htmlspecialchars($startup['logo']) ?>">
        </div>
        <div class="form-group">
            <label>Description</label>
            <textarea name="description" rows="4"><?= htmlspecialchars($startup['description']) ?></textarea>
        </div>
        <div class="form-group">
            <label>Email</label>
            <input type="text" name="email" value="<?= htmlspecialchars($startup['email']) ?>">
        </div>
        <div class="form-group">
            <label>Vidéo de Présentation (laisser vide pour conserver)</label>
            <input type="file" name="video_presentation" accept="video/*">
            <input type="hidden" name="existing_video" value="<?= htmlspecialchars($startup['video_presentation']) ?>">
        </div>
        <button type="submit">Enregistrer les modifications</button>
    </form>
    <script src="js/update-validation.js"></script>
    <?php else: ?>
        <p class="error">Startup introuvable.</p>
    <?php endif; ?>
</div>

</body>
</html>