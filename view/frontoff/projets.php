<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../control/ProjectController.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: connexion.php");
    exit();
}

$userId = $_SESSION['user_id']; // Get the logged-in user's ID

$controller = new ProjectController();

// Handle delete action
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['delete_id'])) {
    try {
        $deleteId = $_GET['delete_id'];
        if ($controller->deleteProject($deleteId)) {
            $_SESSION['success'] = "Projet supprimé avec succès!";
            header("Location: projets.php");
            exit();
        }
    } catch (Exception $e) {
        $error_message = $e->getMessage();
    }
}

// Handle edit form display
$editMode = false;
$projectToEdit = null;
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['edit_id'])) {
    $editMode = true;
    $editId = $_GET['edit_id'];
    $projectToEdit = $controller->getProjectById($editId);
}

// Fetch only the projects related to the logged-in user
$projects = $controller->getProjectsByUserId($userId); // Ensure this method exists in your controller
$categories = $controller->getCategories();

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
    try {
        $id_projet = $_POST['id_projet'] ?? null;
        $titre = trim($_POST['titre']);
        $description = trim($_POST['description']);
        $montant_cible = $_POST['montant_cible'];
        $duree = $_POST['duree'];
        $id_categorie = $_POST['id_categorie'] ?: null;
        $status = $_POST['status'] ?? 'en_attente';
        
        // Validate title
        if (empty($titre)) {
            throw new Exception("Le titre est obligatoire");
        }
        if (strlen($titre) > 100) {
            throw new Exception("Le titre ne doit pas dépasser 100 caractères");
        }
        
        // Validate description
        if (empty($description)) {
            throw new Exception("La description est obligatoire");
        }
        if (strlen($description) > 2000) {
            throw new Exception("La description ne doit pas dépasser 2000 caractères");
        }
        
        // Create or update the project
        $project = new Project(
            $userId, // Use the logged-in user's ID
            $titre, 
            $description, 
            $montant_cible, 
            $duree, 
            $id_categorie, 
            $status, 
            $id_projet
        );                
        if (isset($_POST['is_edit']) && $_POST['is_edit'] === 'true') {
            if ($controller->updateProject($project)) {
                $_SESSION['success'] = "Projet mis à jour avec succès! (ID: $id_projet)";
            }
        } else {
            if ($controller->createProject($project)) {
                $_SESSION['success'] = "Projet créé avec succès!";
            }
        }
        
        header("Location: projets.php");
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
    <title>FundFlow - Gestion des Projets</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&family=Playfair+Display:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <style>
        /* Projects Management Specific Styles */
        .projects-management-container {
            max-width: 1200px;
            margin: 2rem auto;
        }

        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border-radius: 16px;
            padding: 1.5rem;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            text-align: center;
        }

        .stat-value {
            font-size: 2rem;
            font-weight: 600;
            color: white;
            margin-bottom: 0.5rem;
        }

        .stat-label {
            font-size: 0.9rem;
            color: rgba(255,255,255,0.8);
        }

        .project-form-container {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-radius: 16px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            color: rgba(255,255,255,0.9);
            font-weight: 500;
        }

        .form-control {
            width: 100%;
            padding: 0.75rem 1rem;
            background: rgba(255,255,255,0.9);
            border: 1px solid rgba(255,255,255,0.3);
            border-radius: 8px;
            font-size: 1rem;
            color: var(--dark);
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.3);
        }

        .form-select {
            width: 100%;
            padding: 0.75rem 1rem;
            background: rgba(255,255,255,0.9);
            border: 1px solid rgba(255,255,255,0.3);
            border-radius: 8px;
            font-size: 1rem;
            color: var(--dark);
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='%23212529' viewBox='0 0 16 16'%3E%3Cpath d='M7.247 11.14 2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 1rem center;
            background-size: 16px 12px;
        }

        .form-actions {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
        }

        .btn-primary {
            background: var(--primary);
            color: white;
        }

        .btn-primary:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(67, 97, 238, 0.4);
        }

        .btn-secondary {
            background: rgba(255,255,255,0.1);
            color: white;
        }

        .btn-secondary:hover {
            background: rgba(255,255,255,0.2);
            transform: translateY(-2px);
        }

        .projects-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .projects-table th {
            background: rgba(0,0,0,0.2);
            color: white;
            padding: 1rem;
            text-align: left;
        }

        .projects-table td {
            padding: 1rem;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            color: rgba(255,255,255,0.9);
        }

        .projects-table tr:last-child td {
            border-bottom: none;
        }

        .projects-table tr:hover td {
            background: rgba(255,255,255,0.05);
        }

        .badge {
            display: inline-block;
            padding: 0.35rem 0.65rem;
            font-size: 0.75rem;
            font-weight: 600;
            line-height: 1;
            text-align: center;
            white-space: nowrap;
            vertical-align: baseline;
            border-radius: 50px;
        }

        .badge-success {
            background: rgba(16, 185, 129, 0.2);
            color: #10b981;
        }

        .badge-warning {
            background: rgba(249, 115, 22, 0.2);
            color: #f97316;
        }

        .badge-info {
            background: rgba(59, 130, 246, 0.2);
            color: #3b82f6;
        }

        .badge-danger {
            background: rgba(220, 38, 38, 0.2);
            color: #dc2626;
        }

        .badge-secondary {
            background: rgba(156, 163, 175, 0.2);
            color: #9ca3af;
        }

        .action-buttons {
            display: flex;
            gap: 0.5rem;
        }

        .btn-sm {
            padding: 0.375rem 0.75rem;
            font-size: 0.875rem;
        }

        .btn-warning {
            background: rgba(234, 179, 8, 0.2);
            color: #eab308;
        }

        .btn-warning:hover {
            background: rgba(234, 179, 8, 0.3);
        }

        .btn-danger {
            background: rgba(220, 38, 38, 0.2);
            color: #dc2626;
        }

        .btn-danger:hover {
            background: rgba(220, 38, 38, 0.3);
        }

        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .alert-success {
            background: rgba(16, 185, 129, 0.2);
            color: #10b981;
            border-left: 4px solid #10b981;
        }

        .alert-danger {
            background: rgba(220, 38, 38, 0.2);
            color: #dc2626;
            border-left: 4px solid #dc2626;
        }

        @media (max-width: 768px) {
            .projects-table {
                display: block;
                overflow-x: auto;
            }
            
            .form-actions {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
<div class="background-effect"></div>
<div class="particles-container" id="particles-js"></div>

<div class="dashboard-container">
<header class="navbar">
        <div class="logo-container">
            <a href="acceuil2.php">
                <img src="assets/Logo_FundFlow.png" alt="FundFlow Logo" class="brand-logo">
            </a>
        </div>
        
        <div class="nav-links">
            <a href="acceuil2.php" class="nav-link"><i class="fas fa-home"></i> Accueil</a>
            <a href="apropos.html" class="nav-link"><i class="fas fa-info-circle"></i> À propos</a>
            <a href="contact.html" class="nav-link"><i class="fas fa-envelope"></i> Contact</a>
            <a href="events.php" class="nav-link"><i class="fas fa-calendar-alt"></i> Événements</a>
            <a href="partenaire.php" class="nav-link"><i class="fas fa-handshake"></i> Partenariats</a>
            
            <div class="profile-menu-container">
                <button class="profile-menu-btn">Mon compte ▼</button>
                <ul class="profile-menu">
                    <li><a href="profiles.php">Profil</a></li>
                    <?php if ($_SESSION['user']['role'] === 'investisseur'): ?>
                        <li><a href="demands_list.php">Liste des demandes</a></li>
                    <?php endif; ?>
                    <?php if ($_SESSION['user']['role'] === 'entrepreneur'): ?>
                        <li><a href="mesprojet.php">Mes projets</a></li>
                        <li><a href="historique.php">mes demandes</a></li>
                    <?php endif; ?>
                    <li><a href="allconsult.php">Consultation</a></li>
                    <li><a href="connexion.php?logout=1" class="logout">Déconnexion</a></li>
                </ul>
            </div>
        </div>
    </header>

    <div class="projects-management-container">
        <div class="page-header animate__animated animate__fadeInDown">
            <h1><i class="fas fa-project-diagram"></i> Gestion des Projets</h1>
            <p>Créez et gérez vos projets de financement</p>
        </div>

        <div class="stats-container animate__animated animate__fadeIn">
            <div class="stat-card">
                <div class="stat-value"><?= count($projects) ?></div>
                <div class="stat-label">Projets total</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?= count(array_filter($projects, fn($p) => $p['status'] === 'en_attente')) ?></div>
                <div class="stat-label">En attente</div>
            </div>
            <div class="stat-card">
                <div class="stat-value">€<?= number_format(array_sum(array_column($projects, 'montant_cible')) / 1000000, 1) ?>M</div>
                <div class="stat-label">Total demandé</div>
            </div>
        </div>

        <div class="project-form-container animate__animated animate__fadeIn">
            <h2><?= $editMode ? 'Modifier' : 'Nouveau' ?> Projet</h2>
            
            <?php if (isset($success_message)): ?>
                <div class="alert alert-success animate__animated animate__fadeIn">
                    <i class="fas fa-check-circle"></i> <?= htmlspecialchars($success_message) ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($error_message)): ?>
                <div class="alert alert-danger animate__animated animate__fadeIn">
                    <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error_message) ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <input type="hidden" name="id_projet" value="<?= $editMode ? $projectToEdit['id_projet'] : '' ?>">
                <input type="hidden" name="is_edit" value="<?= $editMode ? 'true' : 'false' ?>">
                
                <div class="form-group">
                    <label class="form-label"><i class="fas fa-heading"></i> Titre *</label>
                    <input type="text" class="form-control" name="titre" 
                           value="<?= $editMode ? htmlspecialchars($projectToEdit['titre']) : '' ?>" required>
                </div>

                <div class="form-group">
                    <label class="form-label"><i class="fas fa-align-left"></i> Description *</label>
                    <textarea class="form-control" name="description" rows="4" required><?= 
                        $editMode ? htmlspecialchars($projectToEdit['description']) : '' 
                    ?></textarea>
                </div>

                <div class="form-group">
                    <label class="form-label"><i class="fas fa-euro-sign"></i> Montant Cible (€) *</label>
                    <input type="number" class="form-control" name="montant_cible" min="10000" max="10000000" 
                           value="<?= $editMode ? $projectToEdit['montant_cible'] : '500000' ?>" required>
                </div>

                <div class="form-group">
                    <label class="form-label"><i class="fas fa-calendar-alt"></i> Durée (mois) *</label>
                    <input type="number" class="form-control" name="duree" min="6" max="60" 
                           value="<?= $editMode ? $projectToEdit['duree'] : '24' ?>" required>
                </div>

                <div class="form-group">
                    <label class="form-label"><i class="fas fa-tag"></i> Catégorie</label>
                    <select class="form-select" name="id_categorie" required>
                        <option value="">Sélectionnez une catégorie</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?= $category['id_categorie'] ?>" 
                                <?= ($editMode && $projectToEdit['id_categorie'] == $category['id_categorie']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($category['nom_categorie']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <?php if ($editMode): ?>
                    <div class="form-group">
                        <label class="form-label"><i class="fas fa-info-circle"></i> Statut *</label>
                        <select class="form-select" name="status" required>
                            <option value="en_attente" <?= $projectToEdit['status'] == 'en_attente' ? 'selected' : '' ?>>En attente</option>
                            <option value="actif" <?= $projectToEdit['status'] == 'actif' ? 'selected' : '' ?>>Actif</option>
                            <option value="inactif" <?= $projectToEdit['status'] == 'inactif' ? 'selected' : '' ?>>Inactif</option>
                            <option value="termine" <?= $projectToEdit['status'] == 'termine' ? 'selected' : '' ?>>Terminé</option>
                            <option value="rejete" <?= $projectToEdit['status'] == 'rejete' ? 'selected' : '' ?>>Rejeté</option>
                        </select>
                    </div>
                <?php endif; ?>

                <div class="form-actions">
                    <button type="submit" name="submit" class="btn btn-primary">
                        <i class="fas fa-<?= $editMode ? 'save' : 'plus' ?>"></i>
                        <?= $editMode ? 'Mettre à jour' : 'Créer' ?>
                    </button>
                    <?php if ($editMode): ?>
                        <a href="projets.php" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Annuler
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <div class="project-form-container animate__animated animate__fadeIn">
            <h2><i class="fas fa-list-ol"></i> Liste des Projets</h2>
            <table class="projects-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Titre</th>
                        <th>Description</th>
                        <th>Montant</th>
                        <th>Durée</th>
                        <th>Catégorie</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($projects as $project): ?>
                        <tr>
                            <td><?= $project['id_projet'] ?></td>
                            <td><?= htmlspecialchars($project['titre']) ?></td>
                            <td><?= htmlspecialchars(substr($project['description'], 0, 50)) ?>...</td>
                            <td><?= number_format($project['montant_cible'], 2) ?> €</td>
                            <td><?= $project['duree'] ?> mois</td>
                            <td><?= htmlspecialchars($project['nom_categorie'] ?? 'N/A') ?></td>
                            <td>
                                <span class="badge <?= 
                                    $project['status'] == 'actif' ? 'badge-success' : 
                                    ($project['status'] == 'termine' ? 'badge-info' : 
                                    ($project['status'] == 'rejete' ? 'badge-danger' : 
                                    ($project['status'] == 'inactif' ? 'badge-secondary' : 'badge-warning')))
                                ?>">
                                    <?= ucfirst($project['status']) ?>
                                </span>
                            </td>
                            <td class="action-buttons">
                                <a href="projets.php?edit_id=<?= $project['id_projet'] ?>" 
                                   class="btn btn-sm btn-warning"
                                   title="Modifier">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="projets.php?delete_id=<?= $project['id_projet'] ?>" 
                                   class="btn btn-sm btn-danger" 
                                   onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce projet?')"
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

<footer class="footer">
  <div class="footer-container">
    <div class="footer-col logo-col">
      <img src="assets/Logo_FundFlow.png" alt="Company Logo" class="footer-logo">
      <p class="footer-description">Plateforme de financement collaboratif</p>
    </div>
    <div class="footer-col links-col">
      <h4>Liens Rapides</h4>
      <ul>
        <li><a href="financemet.php">Accueil</a></li>
        <li><a href="#">À propos</a></li>
        <li><a href="#">Services</a></li>
        <li><a href="#">Blog</a></li>
        <li><a href="#">Contact</a></li>
      </ul>
    </div>
    <div class="footer-col contact-col">
      <h4>Contactez-nous</h4>
      <p>123 Rue de Finance, Paris 75001</p>
      <p>Email: <a href="mailto:contact@fundflow.com">contact@fundflow.com</a></p>
      <p>Tél: +33 1 23 45 67 89</p>
    </div>
    <div class="footer-col social-col">
      <h4>Suivez-nous</h4>
      <div class="social-icons">
        <a href="#" aria-label="Facebook"><i class="fab fa-facebook"></i></a>
        <a href="#" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
        <a href="#" aria-label="LinkedIn"><i class="fab fa-linkedin"></i></a>
        <a href="#" aria-label="Instagram"><i class="fas fa-instagram"></i></a>
      </div>
    </div>
  </div>
  <div class="footer-legal">
    <a href="#">Politique de confidentialité</a> |
    <a href="#">Conditions d'utilisation</a> |
    <span>&copy; 2025 FundFlow. Tous droits réservés.</span>
  </div>
</footer>

<script src="https://cdn.jsdelivr.net/particles.js/2.0.0/particles.min.js"></script>
<script>
// Initialize particles.js
particlesJS("particles-js", {
    "particles": {
        "number": {
            "value": 60,
            "density": {
                "enable": true,
                "value_area": 800
            }
        },
        "color": {
            "value": "#4cc9f0"
        },
        "shape": {
            "type": "circle",
            "stroke": {
                "width": 0,
                "color": "#000000"
            }
        },
        "opacity": {
            "value": 0.5,
            "random": true,
            "anim": {
                "enable": true,
                "speed": 1,
                "opacity_min": 0.1,
                "sync": false
            }
        },
        "size": {
            "value": 3,
            "random": true,
            "anim": {
                "enable": true,
                "speed": 2,
                "size_min": 0.1,
                "sync": false
            }
        },
        "line_linked": {
            "enable": true,
            "distance": 150,
            "color": "#4cc9f0",
            "opacity": 0.4,
            "width": 1
        },
        "move": {
            "enable": true,
            "speed": 1,
            "direction": "none",
            "random": true,
            "straight": false,
            "out_mode": "out",
            "bounce": false,
            "attract": {
                "enable": true,
                "rotateX": 600,
                "rotateY": 1200
            }
        }
    },
    "interactivity": {
        "detect_on": "canvas",
        "events": {
            "onhover": {
                "enable": true,
                "mode": "grab"
            },
            "onclick": {
                "enable": true,
                "mode": "push"
            },
            "resize": true
        },
        "modes": {
            "grab": {
                "distance": 140,
                "line_linked": {
                    "opacity": 1
                }
            },
            "push": {
                "particles_nb": 4
            }
        }
    },
    "retina_detect": true
});

function handleMenu(select) {
    const value = select.value;
    if (value === 'logout') {
        window.location.href = 'connexion.php?logout=1';
    } else if (value) {
        window.location.href = value + '.php';
    }
    select.value = ''; // Réinitialiser la sélection
}

document.addEventListener('DOMContentLoaded', function () {
    const form = document.querySelector('form');
    const titre = form.querySelector('input[name="titre"]');
    const description = form.querySelector('textarea[name="description"]');
    const montantCible = form.querySelector('input[name="montant_cible"]');
    const duree = form.querySelector('input[name="duree"]');
    const idCategorie = form.querySelector('select[name="id_categorie"]');
    const titreError = document.createElement('div');
    const descriptionError = document.createElement('div');

    titreError.style.color = 'red';
    titreError.style.fontSize = '0.9rem';
    titreError.style.marginTop = '0.5rem';
    titreError.style.display = 'none';
    titreError.textContent = "Le titre est obligatoire.";

    descriptionError.style.color = 'red';
    descriptionError.style.fontSize = '0.9rem';
    descriptionError.style.marginTop = '0.5rem';
    descriptionError.style.display = 'none';
    descriptionError.textContent = "La description est obligatoire.";

    titre.parentNode.appendChild(titreError);
    description.parentNode.appendChild(descriptionError);

    form.addEventListener('submit', function (event) {
        let isValid = true;

        // Validate Titre
        if (!titre.value.trim()) {
            titreError.style.display = 'block';
            isValid = false;
        } else {
            titreError.style.display = 'none';
        }

        // Validate Description
        if (!description.value.trim()) {
            descriptionError.style.display = 'block';
            isValid = false;
        } else {
            descriptionError.style.display = 'none';
        }

        // Validate Montant Cible
        if (!montantCible.value || montantCible.value < 10000 || montantCible.value > 10000000) {
            alert("Le montant cible doit être compris entre 10 000 et 10 000 000.");
            isValid = false;
        }

        // Validate Durée
        if (!duree.value || duree.value < 6 || duree.value > 60) {
            alert("La durée doit être comprise entre 6 et 60 mois.");
            isValid = false;
        }

        // Validate Catégorie
        if (!idCategorie.value) {
            alert("Veuillez sélectionner une catégorie.");
            isValid = false;
        }

        if (!isValid) {
            event.preventDefault();
        }
    });
});
</script>
</body>
</html>