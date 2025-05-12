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

// Filter users by search term if provided
$filteredUsers = $users;
if (isset($_GET['search_term']) && $_GET['search_term'] !== '') {
    $searchTerm = strtolower(trim($_GET['search_term']));
    $filteredUsers = array_filter($users, function($user) use ($searchTerm) {
        return (strpos(strtolower($user['nom']), $searchTerm)) !== false ||
               (is_numeric($searchTerm) && $user['id_utilisateur'] == $searchTerm);
    });
}

// Sort users if sorting is requested
if (isset($_GET['sort'])) {
    if ($_GET['sort'] === 'asc') {
        usort($filteredUsers, fn($a, $b) => strcmp($a['nom'], $b['nom']));
    } elseif ($_GET['sort'] === 'desc') {
        usort($filteredUsers, fn($a, $b) => strcmp($b['nom'], $a['nom']));
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

// Nombre d'utilisateurs par page
$usersPerPage = 5;

// Page actuelle (par défaut 1 si non spécifiée)
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($currentPage < 1) {
    $currentPage = 1;
}

// Calcul du nombre total d'utilisateurs et de pages
$totalUsers = count($filteredUsers);
$totalPages = max(1, ceil($totalUsers / $usersPerPage));

// Vérification que la page demandée existe
if ($currentPage > $totalPages) {
    $currentPage = $totalPages;
}

// Extraction des utilisateurs pour la page courante
$startIndex = ($currentPage - 1) * $usersPerPage;
$paginatedUsers = array_slice($filteredUsers, $startIndex, $usersPerPage);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FundFlow - Utilisateurs</title>
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
        
        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background: var(--white);
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: var(--shadow-sm);
            transition: var(--transition);
            border-left: 4px solid var(--primary);
        }
        
        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            color: var(--dark);
            background: linear-gradient(to right, var(--primary), var(--secondary));
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .stat-label {
            font-size: 0.95rem;
            color: var(--gray);
        }
        
        .pagination {
            display: flex;
            justify-content: center;
            margin-top: 2rem;
            gap: 0.5rem;
        }
        
        .pagination a {
            padding: 0.5rem 1rem;
            border: 1px solid var(--light-gray);
            border-radius: 6px;
            color: var(--dark);
            text-decoration: none;
            transition: var(--transition);
        }
        
        .pagination a:hover, .pagination a.active {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
        }
        
        .pagination a.disabled {
            pointer-events: none;
            opacity: 0.5;
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
                    <li><a href="feedback.php"><i class="fas fa-comments"></i> Feedbacks</a></li>
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
                    <h1><i class="fas fa-users"></i> Gestion des Utilisateurs</h1>
                    <div class="header-actions">
                        <form method="GET" class="search-form">
                            <div class="input-group">
                                <input type="text" name="search_term" class="form-control" placeholder="Rechercher..." value="<?= isset($_GET['search_term']) ? htmlspecialchars($_GET['search_term']) : '' ?>">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search"></i>
                                </button>
                                <?php if (isset($_GET['search_term'])): ?>
                                    <a href="utilisateurmet.php" class="btn btn-danger">
                                        <i class="fas fa-times"></i>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </form>
                    </div>
                </div>

                <?php if (isset($success_message)): ?>
                    <div class="alert alert-success"><?= $success_message ?></div>
                <?php endif; ?>

                <?php if (isset($error_message)): ?>
                    <div class="alert alert-danger"><?= $error_message ?></div>
                <?php endif; ?>

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

                <section class="card">
                    <div class="card-header">
                        <h3><i class="fas fa-list"></i> Liste des Utilisateurs</h3>
                        <div class="card-header-actions">
                            <a href="adduser.php" class="btn btn-primary btn-sm">
                                <i class="fas fa-user-plus"></i> Créer
                            </a>
                            <a href="utilisateurmet.php?export_pdf=1" class="btn btn-success btn-sm">
                                <i class="fas fa-file-pdf"></i> PDF
                            </a>
                            <a href="utilisateurmet.php?sort=asc" class="btn btn-secondary btn-sm">
                                <i class="fas fa-sort-amount-up"></i>
                            </a>
                            <a href="utilisateurmet.php?sort=desc" class="btn btn-secondary btn-sm">
                                <i class="fas fa-sort-amount-down"></i>
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
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
                                    <?php foreach ($paginatedUsers as $user): ?>
                                        <tr>
                                            <td><?= $user['id_utilisateur'] ?></td>
                                            <td><?= htmlspecialchars($user['nom']) ?></td>
                                            <td><?= htmlspecialchars($user['prenom']) ?></td>
                                            <td><?= htmlspecialchars($user['email']) ?></td>
                                            <td>
                                                <span class="badge <?= 
                                                    $user['role'] == 'admin' ? 'badge-danger' :
                                                    ($user['role'] == 'consultant' ? 'badge-info' :
                                                    ($user['role'] == 'investisseur' ? 'badge-warning' : 'badge-success'))
                                                ?>">
                                                    <?= ucfirst($user['role']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge <?= 
                                                    $user['status'] == 'actif' ? 'badge-success' :
                                                    ($user['status'] == 'inactif' ? 'badge-secondary' : 'badge-warning')
                                                ?>">
                                                    <?= ucfirst($user['status']) ?>
                                                </span>
                                            </td>
                                            <td><?= date('d/m/Y', strtotime($user['date_creation'])) ?></td>
                                            <td>
                                                <div class="action-buttons">
                                                    <a href="adduser.php?edit_id=<?= $user['id_utilisateur'] ?>" 
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
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="pagination">
                            <?php if ($totalPages > 1): ?>
                                <!-- Lien première page -->
                                <a href="?page=1<?= isset($_GET['search_term']) ? '&search_term='.urlencode($_GET['search_term']) : '' ?>" <?= ($currentPage == 1) ? 'class="disabled"' : '' ?>>&laquo;</a>

                                <!-- Lien page précédente -->
                                <a href="?page=<?= ($currentPage > 1) ? $currentPage - 1 : 1 ?><?= isset($_GET['search_term']) ? '&search_term='.urlencode($_GET['search_term']) : '' ?>" <?= ($currentPage == 1) ? 'class="disabled"' : '' ?>>&lsaquo;</a>

                                <!-- Numéros de page -->
                                <?php 
                                $startPage = max(1, $currentPage - 2);
                                $endPage = min($totalPages, $currentPage + 2);
                                
                                for ($i = $startPage; $i <= $endPage; $i++): ?>
                                    <a href="?page=<?= $i ?><?= isset($_GET['search_term']) ? '&search_term='.urlencode($_GET['search_term']) : '' ?>" <?= ($i == $currentPage) ? 'class="active"' : '' ?>><?= $i ?></a>
                                <?php endfor; ?>

                                <!-- Lien page suivante -->
                                <a href="?page=<?= ($currentPage < $totalPages) ? $currentPage + 1 : $totalPages ?><?= isset($_GET['search_term']) ? '&search_term='.urlencode($_GET['search_term']) : '' ?>" <?= ($currentPage == $totalPages) ? 'class="disabled"' : '' ?>>&rsaquo;</a>

                                <!-- Lien dernière page -->
                                <a href="?page=<?= $totalPages ?><?= isset($_GET['search_term']) ? '&search_term='.urlencode($_GET['search_term']) : '' ?>" <?= ($currentPage == $totalPages) ? 'class="disabled"' : '' ?>>&raquo;</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </section>
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