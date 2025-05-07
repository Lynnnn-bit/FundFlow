<?php
include_once 'C:\xampp\htdocs\user\FundFlow\control\startupC.php';
include_once 'C:\xampp\htdocs\user\FundFlow\models\Startup.php';
include_once 'C:\xampp\htdocs\user\FundFlow\control\EvennementC.php';

$formValues = [
    'nom_startup' => '',
    'secteur' => '',
    'adresse_site' => '',
    'description' => '',
    'email' => ''
];

$startupC = new startupC();

// Handle search by secteur
$searchSecteur = isset($_GET['search_secteur']) ? trim($_GET['search_secteur']) : null;
$startups = $searchSecteur ? $startupC->getStartupsBySecteur($searchSecteur) : $startupC->getAllStartups();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $formValues = [
        'nom_startup' => $_POST["nom_startup"] ?? '',
        'secteur' => $_POST["secteur"] ?? '',
        'adresse_site' => $_POST["adresse_site"] ?? '',
        'description' => $_POST["description"] ?? '',
        'email' => $_POST["email"] ?? ''
    ];

    $targetDir = "uploads/";
    if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);

    $uploadSuccess = true;
    $targetLogo = $targetVideo = '';

    if (!empty($_FILES["logo"]["name"])) {
        $logoName = time() . "_" . basename($_FILES["logo"]["name"]);
        $targetLogo = $targetDir . $logoName;
        if (!move_uploaded_file($_FILES["logo"]["tmp_name"], $targetLogo)) {
            $uploadSuccess = false;
        }
    }

    if (!empty($_FILES["video_presentation"]["name"])) {
        $videoName = time() . "_" . basename($_FILES["video_presentation"]["name"]);
        $targetVideo = $targetDir . $videoName;
        $videoTmpName = $_FILES["video_presentation"]["tmp_name"];
        if (is_uploaded_file($videoTmpName)) {
            if (!move_uploaded_file($videoTmpName, $targetVideo)) {
                $uploadSuccess = false;
            }
        } else {
            $uploadSuccess = false;
        }
    }

    if ($uploadSuccess && !empty($targetLogo) && !empty($targetVideo)) {
        try {
            $startup = new Startup(
                null,
                $formValues['nom_startup'],
                $formValues['secteur'],
                $formValues['adresse_site'],
                $targetLogo,
                $formValues['description'],
                $formValues['email'],
                $targetVideo
            );
            $startupC->createStartup($startup);
            header("Location: addStartup.php");
            exit();
        } catch (Exception $e) {
            $error = "Erreur: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ajouter une Startup</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        body { margin: 0; font-family: Arial, sans-serif; background: linear-gradient(to right, #141e30, #243b55); color: white; }
        .navbar { background: linear-gradient(to right, #0f2027, #203a43, #2c5364); padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; }
        .navbar img { height: 40px; }
        .nav-links { display: flex; gap: 15px; }
        .nav-link { color: white; text-decoration: none; font-weight: 500; font-size: 14px; }
        .container { width: 95%; max-width: 1200px; margin: 20px auto; display: flex; flex-direction: column; gap: 20px; }
        .form-wrapper { background-color: rgba(255, 255, 255, 0.05); padding: 20px; border-radius: 8px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.3); }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: 600; font-size: 14px; }
        input[type="text"], textarea, input[type="file"] { width: 100%; padding: 8px; border: none; border-radius: 4px; background-color: rgba(255, 255, 255, 0.2); color: white; font-size: 14px; }
        textarea { min-height: 80px; resize: vertical; }
        button[type="submit"] { background-color: #1abc9c; color: white; border: none; padding: 10px 15px; border-radius: 4px; cursor: pointer; width: 100%; font-size: 14px; }
        button[type="submit"]:hover { background-color: #16a085; }
        .startup-list { background-color: rgba(255, 255, 255, 0.05); padding: 20px; border-radius: 8px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.3); display: flex; flex-direction: column; align-items: center; }
        table { width: 90%; border-collapse: collapse; margin-top: 15px; font-size: 13px; }
        th, td { padding: 8px 10px; text-align: left; border-bottom: 1px solid rgba(255, 255, 255, 0.1); line-height: 1.3; }
        th { background-color: rgba(255, 255, 255, 0.1); font-size: 13px; }
        .action-button { background-color: #3498db; color: white; padding: 5px 8px; border-radius: 3px; text-decoration: none; display: inline-block; margin-right: 3px; font-size: 12px; }
        .action-button.delete { background-color: #e74c3c; }
        .action-button:hover { opacity: 0.9; }
        .search-container { margin-bottom: 15px; display: flex; gap: 8px; width: 90%; justify-content: center; }
        .search-container input { padding: 8px; border-radius: 4px; border: none; background-color: rgba(255, 255, 255, 0.2); color: white; width: 180px; font-size: 13px; }
        .search-container button { padding: 8px 15px; background-color: #2ecc71; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 13px; }
        .search-container button:hover { background-color: #27ae60; }
        .reset-search { padding: 8px 15px; background-color: #95a5a6; color: white; border: none; border-radius: 4px; cursor: pointer; text-decoration: none; margin-left: 8px; font-size: 13px; }
        .reset-search:hover { background-color: #7f8c8d; }
        img { max-width: 60px; border-radius: 3px; }
        .search-reset-container { display: flex; align-items: center; gap: 8px; }
        h2 { font-size: 18px; margin-top: 0; margin-bottom: 15px; }
        input:valid, textarea:valid { border: none !important; }
        .error-message { color: #ff4444 !important; font-size: 0.85em !important; margin-top: 5px !important; }
        input:invalid, textarea:invalid { border: 1px solid #ff4444 !important; }
    </style>
</head>
<body>

<div class="navbar">
    <img src="asset/logo.png" alt="Logo">
    <div class="nav-links">
        <a href="addStartup.php" class="nav-link">Startups</a>
        <a href="events.php" class="nav-link">Events</a>
    </div>
</div>

<div class="container">
    <div class="form-wrapper">
        <h2>Ajouter une nouvelle Startup</h2>
        <form method="POST" enctype="multipart/form-data" id="startupForm">
            <div class="form-group">
                <label>Nom de la Startup</label>
                <input type="text" name="nom_startup" value="<?= htmlspecialchars($formValues['nom_startup']) ?>">
            </div>

            <div class="form-group">
                <label>Secteur d'activité</label>
                <input type="text" name="secteur" value="<?= htmlspecialchars($formValues['secteur']) ?>">
            </div>

            <div class="form-group">
                <label>Site web</label>
                <input type="text" name="adresse_site" value="<?= htmlspecialchars($formValues['adresse_site']) ?>">
            </div>

            <div class="form-group">
                <label>Logo (image)</label>
                <input type="file" name="logo">
            </div>

            <div class="form-group">
                <label>Description</label>
                <textarea name="description"><?= htmlspecialchars($formValues['description']) ?></textarea>
            </div>

            <div class="form-group">
                <label>Email de contact</label>
                <input type="text" name="email" value="<?= htmlspecialchars($formValues['email']) ?>">
            </div>

            <div class="form-group">
                <label>Vidéo de présentation</label>
                <input type="file" name="video_presentation" accept="video/*">
            </div>

            <button type="submit">Enregistrer</button>
        </form>
    </div>

    <div class="startup-list">
        <h2>Liste des Startups</h2>
        <div class="search-container">
            <form method="GET" action="addStartup.php" class="search-reset-container">
                <input type="text" name="search_secteur" placeholder="Rechercher par secteur"
                       value="<?= $searchSecteur ? htmlspecialchars($searchSecteur) : '' ?>">
                <button type="submit"><i class="fas fa-search"></i> Rechercher</button>
                <?php if ($searchSecteur): ?>
                    <a href="addStartup.php" class="reset-search"><i class="fas fa-times"></i> Réinitialiser</a>
                <?php endif; ?>
            </form>
        </div>

        <?php if (!empty($startups)): ?>
            <table>
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Nom</th>
                    <th>Secteur</th>
                    <th>Site Web</th>
                    <th>Description</th>
                    <th>Email</th>
                    <th>Logo</th>
                    <th>Actions</th>
                    <th>Événements</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($startups as $startup): ?>
                    <?php if ($startup): ?>
                        <tr>
                            <td><?= htmlspecialchars($startup['id_startup']) ?></td>
                            <td><?= htmlspecialchars($startup['nom_startup']) ?></td>
                            <td><?= htmlspecialchars($startup['secteur']) ?></td>
                            <td>
                                <a href="<?= htmlspecialchars($startup['adresse_site']) ?>" target="_blank">
                                    <?= htmlspecialchars($startup['adresse_site']) ?>
                                </a>
                            </td>
                            <td><?= htmlspecialchars($startup['description']) ?></td>
                            <td><?= htmlspecialchars($startup['email']) ?></td>
                            <td>
                                <?php if (!empty($startup['logo'])): ?>
                                    <img src="<?= htmlspecialchars($startup['logo']) ?>" alt="Logo">
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="updateStartup.php?id=<?= $startup['id_startup'] ?>" class="action-button">
                                    <i class="fas fa-edit"></i> Modifier
                                </a>
                                <form method="POST" action="deleteStartup.php" style="display:inline;">
                                    <input type="hidden" name="id_startup" value="<?= $startup['id_startup'] ?>">
                                    <button type="submit" class="action-button delete"
                                            onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette startup ?')">
                                        <i class="fas fa-trash"></i> Supprimer
                                    </button>
                                </form>
                            </td>
                            <td>
                                <?php
                                $evenementC = new EvennementC();
                                $events = $evenementC->getEvenementsByStartup($startup['id_startup']);
                                if (!empty($events)):
                                    foreach ($events as $event): ?>
                                        <div style="margin-bottom: 5px; font-size: 12px;">
                                            <p><?= htmlspecialchars($event['nom']) ?> - <?= htmlspecialchars($event['date_evenement']) ?></p>
                                        </div>
                                    <?php endforeach;
                                else: ?>
                                    <p style="font-size: 12px;">Aucun événement</p>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endif; ?>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p><?= $searchSecteur ? "Aucune startup trouvée dans ce secteur." : "Aucune startup enregistrée pour le moment." ?></p>
        <?php endif; ?>
    </div>
</div>

<script src="js/validation.js"></script>
<script src="js/update-validation.js"></script>
</body>
</html>
