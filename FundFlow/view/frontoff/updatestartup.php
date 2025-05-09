<?php
session_start();
include_once '../../control/startupC.php';

$startupC = new startupC();
$uploadDir = '../admin/'; // Directory where files are stored

// Get startup ID from URL
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$startup = $startupC->getStartupById($id);

if (!$startup) {
    $_SESSION['error'] = "Startup introuvable";
    header('Location: startup.php');
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $data = [
            'id_startup' => $id,
            'nom_startup' => $_POST['nom_startup'],
            'secteur' => $_POST['secteur'],
            'adresse_site' => $_POST['adresse_site'],
            'description' => $_POST['description'],
            'email' => $_POST['email'],
            'logo' => $startup['logo'], // Keep existing by default
            'video_presentation' => $startup['video_presentation'] // Keep existing by default
        ];

        // Handle logo upload
        if (!empty($_FILES['logo']['name'])) {
            if (!empty($startup['logo']) && file_exists($uploadDir.$startup['logo'])) {
                unlink($uploadDir.$startup['logo']);
            }
            $logoName = uniqid().'_'.basename($_FILES['logo']['name']);
            move_uploaded_file($_FILES['logo']['tmp_name'], $uploadDir.$logoName);
            $data['logo'] = $logoName;
        }

        // Handle video upload
        if (!empty($_FILES['video_presentation']['name'])) {
            if (!empty($startup['video_presentation']) && file_exists($uploadDir.$startup['video_presentation'])) {
                unlink($uploadDir.$startup['video_presentation']);
            }
            $videoName = uniqid().'_'.basename($_FILES['video_presentation']['name']);
            move_uploaded_file($_FILES['video_presentation']['tmp_name'], $uploadDir.$videoName);
            $data['video_presentation'] = $videoName;
        }

        // Update startup
        $startupC->modifierStartup($data);
        $_SESSION['success'] = "Startup mise à jour avec succès";
        header('Location: startup.php');
        exit();

    } catch(Exception $e) {
        $_SESSION['error'] = $e->getMessage();
        header("Location: updatestartup.php?id=$id");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier Startup</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        /* Style CSS : même que toi */
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: linear-gradient(to right, #141e30, #243b55);
            color: white;
        }
        .navbar {
            background: linear-gradient(to right, #0f2027, #203a43, #2c5364);
            padding: 20px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .navbar img {
            height: 50px;
        }
        .nav-links {
            display: flex;
            gap: 20px;
        }
        .nav-link {
            color: white;
            text-decoration: none;
        }
        .form-wrapper {
            background-color: rgba(255, 255, 255, 0.05);
            padding: 30px;
            border-radius: 8px;
            width: 90%;
            max-width: 600px;
            margin: 40px auto;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.4);
        }
        h2 {
            text-align: center;
            color: #1abc9c;
            margin-bottom: 25px;
        }
        .form-group {
            margin-bottom: 20px;
            position: relative;
        }
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
        }
        input[type="text"],
        input[type="url"],
        input[type="email"],
        textarea,
        input[type="file"] {
            width: 100%;
            padding: 10px;
            border: none;
            border-radius: 5px;
            background-color: rgba(255, 255, 255, 0.2);
            color: white;
        }
        textarea {
            min-height: 100px;
            resize: vertical;
        }
        .error-message {
            color: #e74c3c;
            font-size: 14px;
            margin-top: 5px;
            display: none;
        }
        button {
            background-color: #1abc9c;
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 5px;
            cursor: pointer;
            width: 100%;
            font-size: 16px;
            margin-top: 20px;
        }
        button:hover {
            background-color: #16a085;
        }
        .btn-cancel {
            display: block;
            text-align: center;
            margin-top: 15px;
            color: #e74c3c;
            text-decoration: none;
        }
        .current-file {
            margin-top: 10px;
            font-size: 14px;
            color: #ccc;
        }
        .current-file img, .current-file video {
            max-width: 200px;
            display: block;
            margin-top: 10px;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <div class="navbar">
        <img src="asset/img/logo.png" alt="Logo">
        <div class="nav-links">
            <a href="frontoffice.php" class="nav-link">Accueil</a>
            <a href="events.php" class="nav-link">Events</a>
            <a href="startup.php" class="nav-link">Startups</a>
            <a href="#" class="nav-link">Contact</a>
        </div>
    </div>

    <div class="form-wrapper">
        <h2>Modifier la Startup</h2>

        <?php if(isset($_SESSION['error'])): ?>
            <div class="error">
                <?= $_SESSION['error']; unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <form id="startupForm" method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label>Nom de la startup</label>
                <input type="text" name="nom_startup" value="<?= htmlspecialchars($startup['nom_startup']) ?>">
                <div class="error-message" id="error-nom"></div>
            </div>

            <div class="form-group">
                <label>Secteur d'activité</label>
                <input type="text" name="secteur" value="<?= htmlspecialchars($startup['secteur']) ?>">
                <div class="error-message" id="error-secteur"></div>
            </div>

            <div class="form-group">
                <label>Adresse du site web</label>
                <input type="text" name="adresse_site" value="<?= htmlspecialchars($startup['adresse_site']) ?>">
                <div class="error-message" id="error-site"></div>
            </div>

            <div class="form-group">
                <label>Description</label>
                <textarea name="description"><?= htmlspecialchars($startup['description']) ?></textarea>
                <div class="error-message" id="error-description"></div>
            </div>

            <div class="form-group">
                <label>Email</label>
                <input type="text" name="email" value="<?= htmlspecialchars($startup['email']) ?>">
                <div class="error-message" id="error-email"></div>
            </div>

            <div class="form-group">
                <label>Logo (laisser vide pour conserver l'actuel)</label>
                <input type="file" name="logo" accept="image/*">
                <?php if(!empty($startup['logo'])): ?>
                    <div class="current-file">
                        <span>Logo actuel:</span>
                        <img src="../admin/<?= $startup['logo'] ?>" alt="Logo actuel">
                    </div>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label>Vidéo de présentation (laisser vide pour conserver l'actuelle)</label>
                <input type="file" name="video_presentation" accept="video/*">
                <?php if(!empty($startup['video_presentation'])): ?>
                    <div class="current-file">
                        <span>Vidéo actuelle:</span>
                        <video controls style="max-width: 100%;">
                            <source src="../admin/<?= $startup['video_presentation'] ?>" type="video/mp4">
                        </video>
                    </div>
                <?php endif; ?>
            </div>

            <button type="submit">Enregistrer les modifications</button>
            <a href="startup.php" class="btn-cancel">Annuler</a>
        </form>
    </div>

    <script>
        document.getElementById('startupForm').addEventListener('submit', function(event) {
            let valid = true;

            const nom = document.querySelector('input[name="nom_startup"]');
            const secteur = document.querySelector('input[name="secteur"]');
            const site = document.querySelector('input[name="adresse_site"]');
            const description = document.querySelector('textarea[name="description"]');
            const email = document.querySelector('input[name="email"]');

            // Clear previous errors
            document.querySelectorAll('.error-message').forEach(e => e.style.display = 'none');

            if (nom.value.trim() === '') {
                document.getElementById('error-nom').textContent = "Veuillez entrer un nom.";
                document.getElementById('error-nom').style.display = 'block';
                valid = false;
            }
            if (secteur.value.trim() === '') {
                document.getElementById('error-secteur').textContent = "Veuillez entrer un secteur.";
                document.getElementById('error-secteur').style.display = 'block';
                valid = false;
            }
            if (site.value.trim() === '' || !site.value.startsWith('http')) {
                document.getElementById('error-site').textContent = "Veuillez entrer une URL valide (commençant par http).";
                document.getElementById('error-site').style.display = 'block';
                valid = false;
            }
            if (description.value.trim() === '') {
                document.getElementById('error-description').textContent = "Veuillez entrer une description.";
                document.getElementById('error-description').style.display = 'block';
                valid = false;
            }
            if (email.value.trim() === '' || !email.value.includes('@')) {
                document.getElementById('error-email').textContent = "Veuillez entrer un email valide.";
                document.getElementById('error-email').style.display = 'block';
                valid = false;
            }

            if (!valid) {
                event.preventDefault();
            }
        });
    </script>
</body>
</html>
