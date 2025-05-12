<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../models/utilisateur.php';
require_once __DIR__ . '/../../control/utilisateurcontroller.php';

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
    <title>FundFlow - <?= $editMode ? 'Modifier' : 'Ajouter' ?> Utilisateur</title>
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
        
        .form-container {
            max-width: 900px;
            margin: 0 auto;
            padding: 2rem;
            background: var(--white);
            border-radius: 12px;
            box-shadow: var(--shadow-sm);
        }
        
        .form-title {
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid var(--light-gray);
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .form-title i {
            color: var(--primary);
            background: rgba(67, 97, 238, 0.1);
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 10px rgba(67, 97, 238, 0.1);
        }
        
        .form-actions {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 1px solid var(--light-gray);
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-label {
            display: block;
            margin-bottom: 0.7rem;
            font-weight: 600;
            color: var(--dark);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .form-control {
            width: 100%;
            padding: 0.85rem 1.25rem;
            border: 1px solid var(--light-gray);
            border-radius: 8px;
            font-size: 1rem;
            transition: var(--transition);
            background: var(--white);
            box-shadow: inset 0 1px 3px rgba(0,0,0,0.05);
        }
        
        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.2), inset 0 1px 3px rgba(0,0,0,0.05);
        }
        
        .form-select {
            appearance: none;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%236c757d' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2 5l6 6 6-6'/%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 0.75rem center;
            background-size: 16px 12px;
        }
        
        .form-row {
            display: flex;
            gap: 1.5rem;
            margin-bottom: 1.5rem;
        }
        
        .form-row .form-group {
            flex: 1;
            margin-bottom: 0;
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
                    <li class="active"><a href="utilisateurmet.php"><i class="fas fa-users"></i> Utilisateurs</a></li>
                    <li><a href="categories.php"><i class="fas fa-project-diagram"></i> Catégories</a></li>
                    <li>
                        <a href="#" class="toggle-submenu"><i class="fas fa-hand-holding-usd"></i> Financements</a>
                        <ul class="submenu">
                            <li><a href="statistics.php"><i class="fas fa-chart-pie"></i> Statistiques</a></li>
                            <li><a href="demands.php"><i class="fas fa-file-invoice-dollar"></i> Demandes</a></li>
                        </ul>
                    </li>
                    <li><a href="#"><i class="fas fa-comments"></i> Consultations</a></li>
                    <li><a href="contrats.php"><i class="fas fa-handshake"></i> Contrats</a></li>
                    <li><a href="#"><i class="fas fa-rocket"></i> Startups</a></li>
                </ul>
            </nav>
            <div class="sidebar-footer">
                <p>FundFlow Admin v1.0</p>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <header class="top-nav">
                <button class="menu-toggle">
                    <i class="fas fa-bars"></i>
                </button>
            </header>

            <div class="main-container">
                <div class="page-header">
                    <h1><i class="fas fa-user-<?= $editMode ? 'edit' : 'plus' ?>"></i> <?= $editMode ? 'Modifier' : 'Ajouter' ?> un Utilisateur</h1>
                    <div class="header-actions">
                        <a href="utilisateurmet.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Retour
                        </a>
                    </div>
                </div>

                <?php if (isset($success_message)): ?>
                    <div class="alert alert-success"><?= $success_message ?></div>
                <?php endif; ?>

                <?php if (isset($error_message)): ?>
                    <div class="alert alert-danger"><?= $error_message ?></div>
                <?php endif; ?>

                <div class="form-container">
                    <form method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="id_utilisateur" value="<?= $editMode ? $userToEdit['id_utilisateur'] : $newId ?>">
                        <input type="hidden" name="is_edit" value="<?= $editMode ? 'true' : 'false' ?>">
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label"><i class="fas fa-user"></i> Nom *</label>
                                <input type="text" class="form-control" name="nom" 
                                       value="<?= $editMode ? htmlspecialchars($userToEdit['nom']) : '' ?>" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label"><i class="fas fa-user"></i> Prénom *</label>
                                <input type="text" class="form-control" name="prenom" 
                                       value="<?= $editMode ? htmlspecialchars($userToEdit['prenom']) : '' ?>" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label"><i class="fas fa-envelope"></i> Email *</label>
                            <input type="email" class="form-control" name="email" 
                                   value="<?= $editMode ? htmlspecialchars($userToEdit['email']) : '' ?>" required>
                        </div>

                        <?php if (!$editMode): ?>
                        <div class="form-group">
                            <label class="form-label"><i class="fas fa-lock"></i> Mot de passe *</label>
                            <input type="password" class="form-control" name="mdp" required>
                        </div>
                        <?php endif; ?>

                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label"><i class="fas fa-user-tag"></i> Rôle *</label>
                                <select class="form-control form-select" name="role" required>
                                    <option value="">Sélectionnez un rôle</option>
                                    <option value="entrepreneur" <?= ($editMode && $userToEdit['role'] == 'entrepreneur') ? 'selected' : '' ?>>Entrepreneur</option>
                                    <option value="investisseur" <?= ($editMode && $userToEdit['role'] == 'investisseur') ? 'selected' : '' ?>>Investisseur</option>
                                    <option value="consultant" <?= ($editMode && $userToEdit['role'] == 'consultant') ? 'selected' : '' ?>>Consultant</option>
                                    <option value="admin" <?= ($editMode && $userToEdit['role'] == 'admin') ? 'selected' : '' ?>>Administrateur</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label"><i class="fas fa-info-circle"></i> Statut *</label>
                                <select class="form-control form-select" name="status" required>
                                    <option value="">Sélectionnez un statut</option>
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
                                <?= $editMode ? 'Mettre à jour' : 'Créer l\'utilisateur' ?>
                            </button>
                            <a href="utilisateurmet.php" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Annuler
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Toggle sidebar with persistent state
        const sidebar = document.querySelector('.sidebar');
        const sidebarToggle = document.querySelector('.sidebar-toggle');
        const sidebarClose = document.querySelector('.sidebar-close');
        const mainContent = document.querySelector('.main-content');

        // Check localStorage for saved state
        const sidebarState = localStorage.getItem('sidebarState');

        // Initialize sidebar state
        if (sidebarState === 'collapsed') {
            sidebar.classList.add('collapsed');
            mainContent.classList.add('expanded');
        }

        // Toggle function
        function toggleSidebar() {
            sidebar.classList.toggle('collapsed');
            mainContent.classList.toggle('expanded');
            
            // Save state to localStorage
            const isCollapsed = sidebar.classList.contains('collapsed');
            localStorage.setItem('sidebarState', isCollapsed ? 'collapsed' : 'expanded');
        }

        // Event listeners
        sidebarToggle.addEventListener('click', toggleSidebar);
        sidebarClose.addEventListener('click', toggleSidebar);

        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', (e) => {
            if (window.innerWidth <= 991 && 
                !sidebar.contains(e.target) && 
                !sidebarToggle.contains(e.target) &&
                !sidebar.classList.contains('collapsed')) {
                toggleSidebar();
            }
        });

        // Toggle submenus
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