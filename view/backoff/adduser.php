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

if (isset($_GET['edit_id'])) {
    $editMode = true;
    $editId = $_GET['edit_id'];

    // Récupérez les données de l'utilisateur à modifier
    $controller = new UtilisateurController();
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
        $image = 'assets/default-avatar.png'; // Default image

        // Handle image upload
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
            $fileType = $_FILES['image']['type'];

            if (in_array($fileType, $allowedTypes)) {
                $uploadDir = __DIR__ . '/../../uploads/users/';
                if (!file_exists($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }

                $extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
                $filename = uniqid() . '.' . $extension;
                $destination = $uploadDir . $filename;

                if (move_uploaded_file($_FILES['image']['tmp_name'], $destination)) {
                    $image = 'uploads/users/' . $filename; // Store relative path
                } else {
                    $errors[] = "Erreur lors du téléchargement de l'image.";
                }
            } else {
                $errors[] = "Type de fichier non autorisé. Seuls JPEG, PNG et GIF sont acceptés.";
            }
        }

        $user = new Utilisateur(
            $nom, $prenom, $email, $mdp, $role, $status, $adresse, $tel, 
            $id_utilisateur, $editMode ? $userToEdit['date_creation'] : null, $image
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
    <link rel="stylesheet" href="cssback\styleutili.css">
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
        <a href="../frontoff/accueil.html">Accueil</a>
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
       <!-- <div class="header-section">-->
        <div class="management-section">
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
        

        <!--<div class="finance-form-container">-->
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
    <label class="form-label"><i class="fas fa-image"></i> Image de profil <?= ($editMode && $userToEdit['role'] === 'consultant') ? '<span class="required-indicator">*</span>' : '' ?></label>
    <input type="file" class="form-control" name="image" accept="image/jpeg, image/png, image/gif">
    <?php if ($editMode && !empty($userToEdit['image'])): ?>
        <small class="text-muted">Image actuelle: <?= htmlspecialchars(basename($userToEdit['image'])) ?></small>
        <input type="hidden" name="existing_image" value="<?= htmlspecialchars($userToEdit['image']) ?>">
    <?php endif; ?>
</div>

                <div class="form-actions">
                    <button type="submit" name="submit" class="btn btn-primary">
                        <i class="fas fa-<?= $editMode ? 'save' : 'user-plus' ?>"></i>
                        <?= $editMode ? 'Mettre à jour' : 'Créer' ?>
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