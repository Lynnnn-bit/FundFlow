<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../Controller/consultationcontroller.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$controller = new ConsultationController();

// Handle delete action
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['delete_id'])) {
    try {
        $deleteId = $_GET['delete_id'];
        if ($controller->deleteConsultation($deleteId)) {
            $_SESSION['success'] = "Consultation supprimée avec succès!";
            header("Location: consultation.php");
            exit();
        }
    } catch (Exception $e) {
        $error_message = $e->getMessage();
    }
}

// Handle edit form display
$editMode = false;
$consultationToEdit = null;
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['edit_id'])) {
    $editMode = true;
    $editId = $_GET['edit_id'];
    $consultationToEdit = $controller->getConsultationById($editId);
}

// Fetch all consultations
$existingConsultations = $controller->getAllConsultations();

// Generate a new unique ID
$newId = 1;
if (!empty($existingConsultations)) {
    $maxId = max(array_column($existingConsultations, 'id_consultation'));
    $newId = $maxId + 1;
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
    try {
        $id_consultation = $_POST['id_consultation'];
        $id_utilisateur1 = $_POST['id_utilisateur1'];
        $id_utilisateur2 = $_POST['id_utilisateur2'];
        $heure_deb = $_POST['heure_deb'];
        $heure_fin = $_POST['heure_fin'];
        $tarif = $_POST['tarif'];

        $consultation = new Consultation($id_utilisateur1, $id_utilisateur2, $heure_deb, $heure_fin, $tarif, $id_consultation);

        if (isset($_POST['is_edit']) && $_POST['is_edit'] === 'true') {
            if ($controller->updateConsultation($consultation)) {
                $_SESSION['success'] = "Consultation mise à jour avec succès! (ID: $id_consultation)";
            }
        } else {
            if ($controller->createConsultation($consultation)) {
                $_SESSION['success'] = "Consultation enregistrée avec succès! (ID: $id_consultation)";
            }
        }

        header("Location: consultation.php");
        exit();
    } catch (Exception $e) {
        $error_message = $e->getMessage();
    }
}

if (isset($_SESSION['success'])) {
    $success_message = $_SESSION['success'];
    unset($_SESSION['success']);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>FundFlow - Gestion des Consultations</title>
    <link rel="stylesheet" href="css/consultation.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <header class="navbar">
        <div class="logo-container">
            <span class="brand-name">FundFlow</span>
        </div>
        <nav>
            <a href="#"><i class="fas fa-info-circle"></i> À propos</a>
            <a href="#"><i class="fas fa-envelope"></i> Contact</a>
            <a href="#" class="logout"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
        </nav>
    </header>

    <div class="main-container">
        <div class="header-section">
            <h1><i class="fas fa-calendar-alt"></i> Gestion des Consultations</h1>
            <div class="stats-container">
                <div class="stat-card">
                    <div class="stat-value"><?= count($existingConsultations) ?></div>
                    <div class="stat-label">Consultations totales</div>
                </div>
            </div>
        </div>

        <div class="finance-form-container">
            <h2 class="text-center mb-4"><?= $editMode ? 'Modifier' : 'Nouvelle' ?> Consultation</h2>

            <?php if (isset($success_message)): ?>
                <div class="alert alert-success"><?= htmlspecialchars($success_message) ?></div>
            <?php endif; ?>

            <?php if (isset($error_message)): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error_message) ?></div>
            <?php endif; ?>

            <form method="POST">
                <input type="hidden" name="id_consultation" value="<?= $editMode ? $consultationToEdit['id_consultation'] : $newId ?>">
                <input type="hidden" name="is_edit" value="<?= $editMode ? 'true' : 'false' ?>">

                <div class="form-group">
                    <label><i class="fas fa-user-md"></i> ID Utilisateur 1 *</label>
                    <input type="text" name="id_utilisateur1" value="<?= $editMode ? $consultationToEdit['id_utilisateur1'] : '' ?>" required>
                </div>

                <div class="form-group">
                    <label><i class="fas fa-user"></i> ID Utilisateur 2 *</label>
                    <input type="text" name="id_utilisateur2" value="<?= $editMode ? $consultationToEdit['id_utilisateur2'] : '' ?>" required>
                </div>

                <div class="form-group">
                    <label><i class="fas fa-clock"></i> Heure Début *</label>
                    <input type="datetime-local" name="heure_deb" value="<?= $editMode ? $consultationToEdit['heure_deb'] : '' ?>" required>
                </div>

                <div class="form-group">
                    <label><i class="fas fa-clock"></i> Heure Fin *</label>
                    <input type="datetime-local" name="heure_fin" value="<?= $editMode ? $consultationToEdit['heure_fin'] : '' ?>" required>
                </div>

                <div class="form-group">
                    <label><i class="fas fa-euro-sign"></i> Tarif (€) *</label>
                    <input type="number" name="tarif" value="<?= $editMode ? $consultationToEdit['tarif'] : '' ?>" step="0.01" required>
                </div>

                <div class="form-actions">
                    <button type="submit" name="submit" class="btn btn-primary">
                        <i class="fas fa-<?= $editMode ? 'save' : 'plus' ?>"></i>
                        <?= $editMode ? 'Mettre à jour' : 'Ajouter' ?>
                    </button>
                    <?php if ($editMode): ?>
                        <a href="consultation.php" class="btn btn-secondary"><i class="fas fa-times"></i> Annuler</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <div class="table-container">
            <h3 class="text-center mb-3"><i class="fas fa-table"></i> Liste des Consultations</h3>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Utilisateur 1</th>
                        <th>Utilisateur 2</th>
                        <th>Début</th>
                        <th>Fin</th>
                        <th>Tarif (€)</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($existingConsultations as $consultation): ?>
                        <tr>
                            <td><?= $consultation['id_consultation'] ?></td>
                            <td><?= $consultation['id_utilisateur1'] ?></td>
                            <td><?= $consultation['id_utilisateur2'] ?></td>
                            <td><?= $consultation['heure_deb'] ?></td>
                            <td><?= $consultation['heure_fin'] ?></td>
                            <td><?= number_format($consultation['tarif'], 2) ?> €</td>
                            <td class="action-buttons">
                                <a href="consultation.php?edit_id=<?= $consultation['id_consultation'] ?>" class="btn btn-warning btn-sm">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="consultation.php?delete_id=<?= $consultation['id_consultation'] ?>" class="btn btn-danger btn-sm"
                                   onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette consultation ?')">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
