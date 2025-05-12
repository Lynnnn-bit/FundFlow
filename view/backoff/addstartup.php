<?php
// Include necessary files
include_once __DIR__ . '/../../control/startupC.php';
include_once __DIR__ . '/../../models/Startup.php';
include_once __DIR__ . '/../../control/EvennementC.php';

// Initialize variables
$formValues = [
    'nom_startup' => '',
    'secteur' => '',
    'adresse_site' => '',
    'description' => '',
    'email' => ''
];

$startupC = new startupC();
$searchSecteur = isset($_GET['search_secteur']) ? trim($_GET['search_secteur']) : null;
$startups = $searchSecteur ? $startupC->getStartupsBySecteur($searchSecteur) : $startupC->getAllStartups();

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
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

    // Handle logo upload
    if (!empty($_FILES["logo"]["name"])) {
        $logoName = time() . "_" . basename($_FILES["logo"]["name"]);
        $targetLogo = $targetDir . $logoName;
        if (!move_uploaded_file($_FILES["logo"]["tmp_name"], $targetLogo)) {
            $uploadSuccess = false;
        }
    }

    // Handle video upload
    if (!empty($_FILES["video_presentation"]["name"])) {
        $videoName = time() . "_" . basename($_FILES["video_presentation"]["name"]);
        $targetVideo = $targetDir . $videoName;
        if (!move_uploaded_file($_FILES["video_presentation"]["tmp_name"], $targetVideo)) {
            $uploadSuccess = false;
        }
    }

    // Save startup if uploads are successful
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FundFlow - Ajouter une Startup</title>
    <link rel="stylesheet" href="../Frontoff/css/stylebackof.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .admin-background {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -2;
            background: linear-gradient(135deg, #f5f7ff 0%, #e8ecff 100%);
            overflow: hidden;
        }

        .admin-background::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('../Frontoff/assets/Logo_FundFlow.png') center/30% no-repeat;
            opacity: 0.03;
            pointer-events: none;
        }

        .admin-background::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle at 20% 30%, rgba(67, 97, 238, 0.05) 0%, transparent 50%),
                        radial-gradient(circle at 80% 70%, rgba(16, 185, 129, 0.05) 0%, transparent 50%);
        }

        .form-container, .table-container {
            background: #ffffff;
            border-radius: 12px;
            padding: 2rem;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
        }

        .form-container h2, .table-container h2 {
            font-size: 1.8rem;
            color: #4361ee;
            margin-bottom: 1.5rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #212529;
        }

        .form-control {
            width: 100%;
            padding: 0.8rem;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s;
        }

        .form-control:focus {
            border-color: #4361ee;
            outline: none;
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.2);
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.8rem 1.5rem;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            border: none;
        }

        .btn-primary {
            background: #4361ee;
            color: white;
        }

        .btn-primary:hover {
            background: #3a56d4;
        }

        .btn-secondary {
            background: #adb5bd;
            color: white;
        }

        .btn-secondary:hover {
            background: #868e96;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }

        .table th, .table td {
            padding: 0.8rem;
            text-align: left;
            border-bottom: 1px solid #e9ecef;
        }

        .table th {
            background-color: #f8f9fa;
            font-weight: 600;
        }

        .table tr:hover {
            background-color: #f1f3f5;
        }

        .action-buttons a {
            margin-right: 0.5rem;
        }

        .badge {
            display: inline-block;
            padding: 0.4rem 0.8rem;
            border-radius: 12px;
            font-size: 0.9rem;
            font-weight: 600;
        }

        .badge-success {
            background: #28a745;
            color: white;
        }

        .badge-danger {
            background: #dc3545;
            color: white;
        }

        .badge-warning {
            background: #ffc107;
            color: white;
        }
    </style>
</head>
<body>
    <button class="sidebar-toggle">
        <i class="fas fa-bars"></i>
    </button>

    <div class="admin-background"></div>

    <div class="admin-container">
        <!-- Sidebar Navigation -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <img src="../Frontoff/assets/Logo_FundFlow.png" alt="FundFlow Logo" class="sidebar-logo">
                <button class="sidebar-close">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <nav class="sidebar-menu">
                <ul>
                    <li><a href="backoffice.php"><i class="fas fa-tachometer-alt"></i> Tableau de bord</a></li>
                    <li><a href="utilisateurmet.php"><i class="fas fa-users"></i> Utilisateurs</a></li>
                    <li><a href="categories.php"><i class="fas fa-project-diagram"></i> Catégories</a></li>
                    <li>
                        <a href="#" class="toggle-submenu"><i class="fas fa-hand-holding-usd"></i> Financements</a>
                        <ul class="submenu">
                            <li><a href="statistics.php"><i class="fas fa-chart-pie"></i> Statistiques</a></li>
                            <li><a href="demands.php"><i class="fas fa-file-invoice-dollar"></i> Demandes</a></li>
                        </ul>
                    </li>
                    <li><a href="feedback.php"><i class="fas fa-comments"></i> Feedbacks</a></li>
                    <li><a href="contrats.php"><i class="fas fa-handshake"></i> Contrats</a></li>
                    <li class="active"><a href="addstartup.php"><i class="fas fa-rocket"></i> Startups</a></li>
                </ul>
            </nav>
            <div class="sidebar-footer">
                <p>FundFlow Admin v1.0</p>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <header class="top-nav">
                <h1><i class="fas fa-rocket"></i> Ajouter une Startup</h1>
            </header>

            <div class="main-container">
                <!-- Add Startup Form -->
                <section class="form-container">
                    <h2>Ajouter une nouvelle Startup</h2>
                    <form method="POST" enctype="multipart/form-data">
                        <div class="form-group">
                            <label for="nom_startup" class="form-label">Nom de la Startup</label>
                            <input type="text" id="nom_startup" name="nom_startup" class="form-control" value="<?= htmlspecialchars($formValues['nom_startup']) ?>">
                        </div>
                        <div class="form-group">
                            <label for="secteur" class="form-label">Secteur d'activité</label>
                            <input type="text" id="secteur" name="secteur" class="form-control" value="<?= htmlspecialchars($formValues['secteur']) ?>">
                        </div>
                        <div class="form-group">
                            <label for="adresse_site" class="form-label">Site web</label>
                            <input type="text" id="adresse_site" name="adresse_site" class="form-control" value="<?= htmlspecialchars($formValues['adresse_site']) ?>">
                        </div>
                        <div class="form-group">
                            <label for="logo" class="form-label">Logo (image)</label>
                            <input type="file" id="logo" name="logo" class="form-control">
                        </div>
                        <div class="form-group">
                            <label for="description" class="form-label">Description</label>
                            <textarea id="description" name="description" class="form-control"><?= htmlspecialchars($formValues['description']) ?></textarea>
                        </div>
                        <div class="form-group">
                            <label for="email" class="form-label">Email de contact</label>
                            <input type="text" id="email" name="email" class="form-control" value="<?= htmlspecialchars($formValues['email']) ?>">
                        </div>
                        <div class="form-group">
                            <label for="video_presentation" class="form-label">Vidéo de présentation</label>
                            <input type="file" id="video_presentation" name="video_presentation" class="form-control" accept="video/*">
                        </div>
                        <button type="submit" class="btn btn-primary">Enregistrer</button>
                    </form>
                </section>

                <!-- Startup List -->
                <section class="table-container">
                    <h2>Liste des Startups</h2>
                    <form method="GET" class="search-form">
                        <div class="input-group">
                            <input type="text" name="search_secteur" class="form-control" placeholder="Rechercher par secteur" value="<?= htmlspecialchars($searchSecteur) ?>">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i> Rechercher
                            </button>
                            <?php if ($searchSecteur): ?>
                                <a href="addStartup.php" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> Réinitialiser
                                </a>
                            <?php endif; ?>
                        </div>
                    </form>
                    <div class="table-responsive">
                        <table class="table">
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
                                <?php if (!empty($startups)): ?>
                                    <?php foreach ($startups as $startup): ?>
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
                                                    <img src="<?= htmlspecialchars($startup['logo']) ?>" alt="Logo" style="max-width: 60px; border-radius: 4px;">
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="action-buttons">
                                                    <a href="updateStartup.php?id=<?= $startup['id_startup'] ?>" class="btn btn-warning btn-sm">
                                                        <i class="fas fa-edit"></i> Modifier
                                                    </a>
                                                    <form method="POST" action="deleteStartup.php" style="display:inline;">
                                                        <input type="hidden" name="id_startup" value="<?= $startup['id_startup'] ?>">
                                                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette startup ?')">
                                                            <i class="fas fa-trash"></i> Supprimer
                                                        </button>
                                                    </form>
                                                </div>
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
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="9" class="text-center">Aucune startup trouvée</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>
        </main>
    </div>

    <script>
        // Sidebar toggle functionality
        const sidebar = document.querySelector('.sidebar');
        const sidebarToggle = document.querySelector('.sidebar-toggle');
        const sidebarClose = document.querySelector('.sidebar-close');
        const mainContent = document.querySelector('.main-content');

        const sidebarState = localStorage.getItem('sidebarState');
        if (sidebarState === 'collapsed') {
            sidebar.classList.add('collapsed');
            mainContent.classList.add('expanded');
        }

        function toggleSidebar() {
            sidebar.classList.toggle('collapsed');
            mainContent.classList.toggle('expanded');
            const isCollapsed = sidebar.classList.contains('collapsed');
            localStorage.setItem('sidebarState', isCollapsed ? 'collapsed' : 'expanded');
        }

        sidebarToggle.addEventListener('click', toggleSidebar);
        sidebarClose.addEventListener('click', toggleSidebar);

        document.addEventListener('click', (e) => {
            if (window.innerWidth <= 991 && 
                !sidebar.contains(e.target) && 
                !sidebarToggle.contains(e.target) &&
                !sidebar.classList.contains('collapsed')) {
                toggleSidebar();
            }
        });

        document.querySelectorAll('.toggle-submenu').forEach(item => {
            item.addEventListener('click', function(e) {
                e.preventDefault();
                const submenu = this.nextElementSibling;
                submenu.classList.toggle('active');
            });
        });
    </script>
</body>
</html>
