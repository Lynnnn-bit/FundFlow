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

            <form method="POST">
                <input type="hidden" name="id_utilisateur" value="<?= $editMode ? $userToEdit['id_utilisateur'] : $newId ?>">
                <input type="hidden" name="is_edit" value="<?= $editMode ? 'true' : 'false' ?>">
                
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label class="form-label"><i class="fas fa-user" placeholder="Tapez votre nom"></i> Nom *</label>
                        <input type="text" class="form-control" name="nom" 
                               value="<?= $editMode ? htmlspecialchars($userToEdit['nom']) : '' ?>" required>
                    </div>
                    <div class="form-group col-md-6">
                        <label class="form-label" ><i class="fas fa-user"></i> Prénom *</label>
                        <input type="text" class="form-control" name="prenom" 
                               value="<?= $editMode ? htmlspecialchars($userToEdit['prenom']) : '' ?>" required>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" ><i class="fas fa-envelope" ></i> Email *</label>
                    <input type="email" class="form-control" name="email" 
                           value="<?= $editMode ? htmlspecialchars($userToEdit['email']) : '' ?>" required>
                </div>

                <?php if (!$editMode): ?>
                <div class="form-group">
                    <label class="form-label" ><i class="fas fa-lock"></i> Mot de passe *</label>
                    <input type="password" class="form-control" name="mdp" required>
                </div>
                <?php endif; ?>

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label class="form-label"><i class="fas fa-user-tag"></i> Rôle *</label>
                        <select class="form-select" name="role" required>
                            <!--<option value="">Tapez votre Role</option>-->
                            <option value="entrepreneur" <?= ($editMode && $userToEdit['role'] == 'entrepreneur') ? 'selected' : '' ?>>Entrepreneur</option>
                            <option value="investisseur" <?= ($editMode && $userToEdit['role'] == 'investisseur') ? 'selected' : '' ?>>Investisseur</option>
                            <option value="consultant" <?= ($editMode && $userToEdit['role'] == 'consultant') ? 'selected' : '' ?>>Consultant</option>
                            <option value="admin" <?= ($editMode && $userToEdit['role'] == 'admin') ? 'selected' : '' ?>>Administrateur</option>
                        </select>
                    </div>
                    <div class="form-group col-md-6">
                        <label class="form-label"><i class="fas fa-info-circle"></i> Statut *</label>
                        <select class="form-select" name="status" required>
                            <!--<option value="">Tapez votre Status</option>-->
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
                    <input type="text" class="form-control" name="tel" 
                           value="<?= $editMode ? htmlspecialchars($userToEdit['tel']) : '' ?>">
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
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
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
                                <td class="action-buttons">
                                    <a href="utilisateurmet.php?edit_id=<?= $user['id_utilisateur'] ?>"
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