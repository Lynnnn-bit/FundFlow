<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);


require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../models/utilisateur.php';  // Fixed path
require_once __DIR__ . '/../../control/utilisateurcontroller.php';  // Fixed path


if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$controller = new UtilisateurController();

// Handle delete action
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['delete_id'])) {
    try {
        $deleteId = $_GET['delete_id'];
        if ($controller->deleteUser($deleteId)) {
            $_SESSION['success'] = "Utilisateur supprimé avec succès!";
            header("Location: utilisateurmet.php");
            exit();
        }
    } catch (Exception $e) {
        $error_message = $e->getMessage();
    }
}

// Handle edit form display
$editMode = false;
$userToEdit = null;
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['edit_id'])) {
    $editMode = true;
    $editId = $_GET['edit_id'];
    $userToEdit = $controller->getUserById($editId);
}

// Get all users and next ID
$users = $controller->getAllUsers();
$newId = $controller->getNextUserId();

// Filter users by search ID if provided
$filteredUsers = $users;
if (isset($_GET['search_id']) && $_GET['search_id'] !== '') {
    $searchId = (int)$_GET['search_id'];
    $filteredUsers = array_filter($users, fn($user) => $user['id_utilisateur'] == $searchId);
}

// Sort users if sorting is requested
if (isset($_GET['sort'])) {
    if ($_GET['sort'] === 'asc') {
        usort($filteredUsers, fn($a, $b) => $a['id_utilisateur'] - $b['id_utilisateur']);
    } elseif ($_GET['sort'] === 'desc') {
        usort($filteredUsers, fn($a, $b) => $b['id_utilisateur'] - $a['id_utilisateur']);
    }
}

// Handle PDF export
if (isset($_GET['export_pdf'])) {
    require_once __DIR__ . '/../../libs/fpdf/fpdf.php';

    $pdf = new FPDF();
    $pdf->AddPage();
    $pdf->SetFont('Arial', 'B', 12);

    // Title
    $pdf->Cell(0, 10, 'Liste des Utilisateurs', 0, 1, 'C');
    $pdf->Ln(10);

    // Table header
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(20, 10, 'ID', 1);
    $pdf->Cell(40, 10, 'Nom', 1);
    $pdf->Cell(40, 10, 'Prenom', 1);
    $pdf->Cell(60, 10, 'Email', 1);
    $pdf->Cell(30, 10, 'Role', 1);
    $pdf->Ln();

    // Table rows
    $pdf->SetFont('Arial', '', 10);
    foreach ($filteredUsers as $user) {
        $pdf->Cell(20, 10, $user['id_utilisateur'], 1);
        $pdf->Cell(40, 10, $user['nom'], 1);
        $pdf->Cell(40, 10, $user['prenom'], 1);
        $pdf->Cell(60, 10, $user['email'], 1);
        $pdf->Cell(30, 10, ucfirst($user['role']), 1);
        $pdf->Ln();
    }

    // Output PDF
    $pdf->Output('D', 'Liste_Utilisateurs.pdf');
    exit();
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
    try {
        $id_utilisateur = $_POST['id_utilisateur'];
        $nom = $_POST['nom'];
        $prenom = $_POST['prenom'];
        $email = $_POST['email'];
        $mdp = $_POST['mdp'];
        $role = $_POST['role'];
        $status = $_POST['status'];
        $adresse = $_POST['adresse'];
        $tel = $_POST['tel'];
        
        $user = new Utilisateur(
            $nom, $prenom, $email, $mdp, $role, $status, $adresse, $tel, 
            $id_utilisateur, $editMode ? $userToEdit['date_creation'] : null
        );
        
        if (isset($_POST['is_edit']) && $_POST['is_edit'] === 'true') {
            if ($controller->updateUser($user)) {
                $_SESSION['success'] = "Utilisateur mis à jour avec succès! (ID: $id_utilisateur)";
            }
        } else {
            if ($controller->createUser($user)) {
                $_SESSION['success'] = "Utilisateur créé avec succès! (ID: $id_utilisateur)";
            }
        }
        
        header("Location: utilisateurmet.php");
        exit();
    } catch (Exception $e) {
        $error_message = $e->getMessage();
    }
}
// Validation du téléphone (vide ou 8 chiffres)
if (!empty($tel) && !preg_match('/^\d{8}$/', $tel)) {
    $errors[] = "Le téléphone doit contenir exactement 8 chiffres";
}
// Display success message from session
if (isset($_SESSION['success'])) {
    $success_message = $_SESSION['success'];
    unset($_SESSION['success']);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FundFlow - Gestion des Utilisateurs</title>
    <link rel="stylesheet" href="css/styleutilisateur.css?v=1.1">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
   
</head>
<body>
    <div class="sidebar">
        <h2>Menu</h2>
        <a href="utilisateurmet.php">Gestion des Utilisateurs</a>
        <a href="adduser.php">Ajouter un Utilisateur</a>
        <a href="allconsult.php">Liste des Consultants</a>
        <a href="contact.html">Contact</a>
        <a href="apropos.html">À propos</a>
        <a href="accueil.html">Accueil</a>
    </div>

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
  
    <div class="main-container">
        <div class="header-section">
            <h1><i class="fas fa-users"></i> Gestion des Utilisateurs</h1>
            <div class="stats-container">
                <div class="stat-card">
                    <div class="stat-value"><?= count($users) ?></div>
                    <div class="stat-label">Utilisateurs total</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value"><?= count(array_filter($users, fn($u) => $u['role'] === 'entrepreneur')) ?></div>
                    <div class="stat-label">Entrepreneurs</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value"><?= count(array_filter($users, fn($u) => $u['role'] === 'investisseur')) ?></div>
                    <div class="stat-label">Investisseurs</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value"><?= count(array_filter($users, fn($u) => $u['role'] === 'consultant')) ?></div>
                    <div class="stat-label">Consultants</div>
                </div>
            </div>
        </div>
    <a href="adduser.php"><button type="submit" name="submit" class="btn btn-primary">
                        <i class="fas fa-<?= $editMode ? 'save' : 'user-plus' ?>"></i>
                        <?= $editMode = 'Créer' ?>
                    </button></a>

                    <form method="GET" action="utilisateurmet.php" class="search-form">
                <div class="input-group">
                    <input type="number" name="search_id" class="form-control" placeholder="Rechercher par ID" 
                           value="<?= isset($_GET['search_id']) ? htmlspecialchars($_GET['search_id']) : '' ?>">
                    <button type="submit" class="btn btn-info">
                        <i class="fas fa-search"></i> Rechercher
                    </button>
                    <?php if (isset($_GET['search_id'])): ?>
                        <a href="utilisateurmet.php" class="btn btn-danger">
                            <i class="fas fa-times"></i> Annuler
                        </a>
                    <?php endif; ?>
                </div>
            </form>
                    <!-- Boutons de tri -->
            <a href="utilisateurmet.php?sort=asc">
                <button type="button" class="btn btn-secondary">
                    <i class="fas fa-sort-amount-up"></i> Tri croissant
                </button>
            </a>
            <a href="utilisateurmet.php?sort=desc">
                <button type="button" class="btn btn-secondary">
                    <i class="fas fa-sort-numeric-down-alt"></i> Tri décroissant
                </button>
            </a>

            <!-- Export PDF Button -->
            <div class="export-pdf">
                <a href="utilisateurmet.php?export_pdf=1" class="btn btn-success">
                    <i class="fas fa-file-pdf"></i> Exporter en PDF
                </a>
            </div>
        </div>

        <div class="table-container">
            <h3 class="text-center mb-3"><i class="fas fa-list-ol"></i> Liste des Utilisateurs</h3>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Nom</th>
                            <th>Prénom</th>
                            <th>Email</th>
                            <th>Rôle</th>
                            <th>Statut</th>
                            <th>Création</th>
                            <th>Image</th> <!-- Added column for image -->
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($filteredUsers as $user): ?>
                        <tr>
                            <td><?= $user['id_utilisateur'] ?></td>
                            <td><?= htmlspecialchars($user['nom']) ?></td>
                            <td><?= htmlspecialchars($user['prenom']) ?></td>
                            <td><?= htmlspecialchars($user['email']) ?></td>
                            <td>
                                <span class="badge <?= 
                                    $user['role'] == 'admin' ? 'bg-danger' :
                                    ($user['role'] == 'consultant' ? 'bg-info' :
                                    ($user['role'] == 'investisseur' ? 'bg-warning' : 'bg-success'))
                                ?>">
                                    <?= ucfirst($user['role']) ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge <?= 
                                    $user['status'] == 'actif' ? 'bg-success' :
                                    ($user['status'] == 'inactif' ? 'bg-secondary' : 'bg-warning')
                                ?>">
                                    <?= ucfirst($user['status']) ?>
                                </span>
                            </td>
                            <td><?= date('d/m/Y', strtotime($user['date_creation'])) ?></td>
                            <td>
                                <img src="<?= htmlspecialchars($user['image'] ?: 'assets/default-avatar.png') ?>" 
                                     alt="Image de <?= htmlspecialchars($user['nom']) ?>" 
                                     style="width: 50px; height: 50px; border-radius: 50%; object-fit: cover;">
                            </td> <!-- Display user image -->
                            <td class="action-buttons">
                                <a href="userlist.php?edit_id=<?= $user['id_utilisateur'] ?>"
                                   class="btn btn-warning btn-sm"
                                   title="Modifier">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="utilisateurmet.php?delete_id=<?= $user['id_utilisateur'] ?>"
                                   class="btn btn-danger btn-sm"
                                   onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur?')"
                                   title="Supprimer">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <script src="js/jsaddutili..js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>