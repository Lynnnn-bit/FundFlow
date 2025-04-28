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
    <link rel="stylesheet" href="css/styleutilisateur.css">
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

        <div class="finance-form-container">
            <h2 class="text-center mb-4"><?= $editMode ? 'Modifier' : 'Nouvel' ?> Utilisateur</h2>
            
            <?php if (isset($success_message)): ?>
                <div class="alert alert-success"><?= htmlspecialchars($success_message) ?></div>
            <?php endif; ?>
            
            <?php if (isset($error_message)): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error_message) ?></div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="id_utilisateur" value="<?= $editMode ? $userToEdit['id_utilisateur'] : $newId ?>">
                <input type="hidden" name="is_edit" value="<?= $editMode ? 'true' : 'false' ?>">
                
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label class="form-label"><i class="fas fa-user" placeholder="Tapez votre nom"></i> Nom *</label>
                        <input type="text" class="form-control" name="nom" 
                               value="<?= $editMode ? htmlspecialchars($userToEdit['nom']) : '' ?>" >
                    </div>
                    <div class="form-group col-md-6">
                        <label class="form-label" ><i class="fas fa-user"></i> Prénom *</label>
                        <input type="text" class="form-control" name="prenom" 
                               value="<?= $editMode ? htmlspecialchars($userToEdit['prenom']) : '' ?>" >
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" ><i class="fas fa-envelope" ></i> Email *</label>
                    <input type="email" class="form-control" name="email" 
                           value="<?= $editMode ? htmlspecialchars($userToEdit['email']) : '' ?>">
                </div>

                <?php if (!$editMode): ?>
                <div class="form-group">
                    <label class="form-label" ><i class="fas fa-lock"></i> Mot de passe *</label>
                    <input type="password" class="form-control" name="mdp" >
                </div>
                <?php endif; ?>

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label class="form-label"><i class="fas fa-user-tag"></i> Rôle *</label>
                        <select class="form-select" name="role" >
                            <option value="">Tapez votre Role</option>
                            <option value="entrepreneur" <?= ($editMode && $userToEdit['role'] == 'entrepreneur') ? 'selected' : '' ?>>Entrepreneur</option>
                            <option value="investisseur" <?= ($editMode && $userToEdit['role'] == 'investisseur') ? 'selected' : '' ?>>Investisseur</option>
                            <option value="consultant" <?= ($editMode && $userToEdit['role'] == 'consultant') ? 'selected' : '' ?>>Consultant</option>
                            <option value="admin" <?= ($editMode && $userToEdit['role'] == 'admin') ? 'selected' : '' ?>>Administrateur</option>
                        </select>
                    </div>
                    <div class="form-group col-md-6">
                        <label class="form-label"><i class="fas fa-info-circle"></i> Statut *</label>
                        <select class="form-select" name="status" >
                            <option value="">Tapez votre Status</option>
                            <option value="actif" <?= ($editMode && $userToEdit['status'] == 'actif') ? 'selected' : '' ?>>Actif</option>
                            <option value="inactif" <?= ($editMode && $userToEdit['status'] == 'inactif') ? 'selected' : '' ?>>Inactif</option>
                            <option value="suspendu" <?= ($editMode && $userToEdit['status'] == 'suspendu') ? 'selected' : '' ?>>Suspendu</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label"><i class="fas fa-map-marker-alt"></i> Adresse</label>
                    <input type="text" class="form-control" name="adresse" 
                           value="<?= $editMode ? htmlspecialchars($userToEdit['adresse']) : '' ?>">
                </div>

                <div class="form-group">
                    <label class="form-label"><i class="fas fa-phone"></i> Téléphone</label>
                    <input type="tel" class="form-control" name="tel" id="tel"
                           value="<?= $editMode ? htmlspecialchars($userToEdit['tel']) : '' ?>">
                               
                </div>

                <div class="form-group">
                    <label class="form-label"><i class="fas fa-image"></i> Image de profil</label>
                    <input type="file" class="form-control" name="image" id="image" accept="image/jpeg, image/png, image/gif">
                </div>

                <div class="form-actions">
                    <button type="submit" name="submit" class="btn btn-primary">
                        <i class="fas fa-<?= $editMode ? 'save' : 'user-plus' ?>"></i>
                        <?= $editMode = 'Mettre à jour'  ?>
                    </button>
                    <?php if ($editMode): ?>
                        <a href="utilisateurmet.php" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Annuler
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        
        </div>
    </div>
    <script src="js/jsaddutili..js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>