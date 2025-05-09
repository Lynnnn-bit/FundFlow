<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../control/feedbackcontroller.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$controller = new FeedbackController();
$consultations = $controller->getAllConsultations();

// Préselection via URL
$preselected_consultation = isset($_GET['id_consultation']) ? intval($_GET['id_consultation']) : null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Récupération de l'ID de la consultation
        $id_consultation = isset($_POST['id_consultation']) ? intval($_POST['id_consultation']) : null;
        $note = isset($_POST['note']) ? intval($_POST['note']) : null;

        // Validation
        $valid_consultation_ids = array_column($consultations, 'id_consultation');
        if (!in_array($id_consultation, $valid_consultation_ids)) {
            throw new Exception("Consultation invalide");
        }

        if ($note < 1 || $note > 5) {
            throw new Exception("La note doit être entre 1 et 5");
        }

        if ($controller->createFeedback($id_consultation, $note)) {
            $_SESSION['success'] = "Feedback ajouté avec succès!";
            header("Location: feedback.php");
            exit;
        } else {
            throw new Exception("Erreur lors de l'ajout du feedback");
        }
    } catch (Exception $e) {
        $error_message = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter un Feedback</title>
    <link rel="stylesheet" href="cssback/feedback.css">
    <link rel="stylesheet" href="../frontoff/css/navbar.css">
    <style>
        body {
            background-color: #2c3e50;
            color: white;
            font-family: Arial, sans-serif;
        }

        .main-container {
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
            background-color: #34495e;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        h1 {
            color: white;
            text-align: center;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-control {
            width: 100%;
            padding: 10px;
            border-radius: 4px;
            border: 1px solid #455b73;
            background-color: #3d566e;
            color: white;
        }

        .form-control:focus {
            border-color: #17a2b8;
            outline: none;
            box-shadow: 0 0 5px rgba(23, 162, 184, 0.5);
        }

        .btn {
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            font-size: 16px;
        }

        .btn-primary {
            background-color: #17a2b8;
            color: white;
        }

        .btn-primary:hover {
            background-color: #138496;
        }

        .btn-secondary {
            background-color: #6c757d;
            color: white;
        }

        .btn-secondary:hover {
            background-color: #5a6268;
        }

        .error {
            color: #e74c3c;
            font-size: 14px;
            margin-top: 5px;
        }

        .preselected {
            background-color: #3d566e;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 15px;
            color: white;
        }

        .navbar {
            background-color: #2c3e50;
            padding: 10px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .navbar .brand-name {
            color: white;
            font-size: 24px;
            font-weight: bold;
        }

        .navbar nav a {
            color: white;
            margin-left: 15px;
            text-decoration: none;
            font-size: 16px;
        }

        .navbar nav a.active {
            font-weight: bold;
            color: #17a2b8;
        }

        .navbar nav a:hover {
            color: #17a2b8;
        }
    </style>
</head>
<body>
<header class="navbar">
    <div class="logo-container">
        <span class="brand-name">FundFlow</span>
    </div>
    <nav>
        <a href="feedback.php" class="active"><i class="fas fa-comment-alt"></i> Feedbacks</a>
        <a href="../frontoff/apropos.html"><i class="fas fa-info-circle"></i> À propos</a>
        <a href="../frontoff/contact.html"><i class="fas fa-envelope"></i> Contact</a>
        <a href="../frontoff/accueil.html" class="logout"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
    </nav>
</header>

<div class="main-container">
    <h1><i class="fas fa-plus-circle"></i> Ajouter un Feedback</h1>

    <?php if (isset($error_message)): ?>
        <div style="color: red; background-color: #2c2c2c; padding: 10px; border-radius: 5px; border: 1px solid red;">
            <?= htmlspecialchars($error_message) ?>
        </div>
    <?php endif; ?>

    <?php if ($preselected_consultation): ?>
        <div class="preselected">
            <p>Vous ajoutez un feedback pour la consultation #<?= htmlspecialchars($preselected_consultation) ?></p>
        </div>
    <?php endif; ?>

    <form id="feedbackForm" method="POST">
        <?php if ($preselected_consultation): ?>
            <input type="hidden" name="id_consultation" value="<?= htmlspecialchars($preselected_consultation) ?>">
        <?php endif; ?>

        <div class="form-group" <?= $preselected_consultation ? 'style="display:none;"' : '' ?>>
            <label for="id_consultation">Consultation</label>
            <select name="id_consultation" id="id_consultation" class="form-control">
                <option value="">Sélectionnez une consultation</option>
                <?php foreach ($consultations as $consultation): ?>
                    <?php if (isset($consultation['id_consultation'])): ?>
                        <option value="<?= htmlspecialchars($consultation['id_consultation']) ?>"
                            <?= ($preselected_consultation && $consultation['id_consultation'] == $preselected_consultation) ? 'selected' : '' ?>>
                            Consultation #<?= htmlspecialchars($consultation['id_consultation']) ?>
                        </option>
                    <?php endif; ?>
                <?php endforeach; ?>
            </select>
            <div id="idConsultationError" class="error">Veuillez sélectionner une consultation.</div>
        </div>

        <div class="form-group">
            <label for="note">Note (1-5)</label>
            <input type="number" name="note" id="note" class="form-control" min="1" max="5" required>
            <div id="noteError" class="error">La note doit être un nombre entre 1 et 5.</div>
        </div>

        <button type="submit" class="btn btn-primary">Enregistrer</button>
        <a href="feedback.php" class="btn btn-secondary">Annuler</a>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('feedbackForm');
    const idConsultation = document.getElementById('id_consultation');
    const noteInput = document.getElementById('note');
    const idConsultationError = document.getElementById('idConsultationError');
    const noteError = document.getElementById('noteError');

    <?php if (!$preselected_consultation): ?>
        idConsultation.addEventListener('change', validateConsultation);
    <?php endif; ?>

    noteInput.addEventListener('input', validateNote);

    form.addEventListener('submit', function (event) {
        <?php if (!$preselected_consultation): ?>
            const isConsultationValid = validateConsultation();
        <?php else: ?>
            const isConsultationValid = true;
        <?php endif; ?>
        const isNoteValid = validateNote();

        if (!isConsultationValid || !isNoteValid) {
            event.preventDefault();
        }
    });

    function validateConsultation() {
        if (idConsultation.value.trim() === '') {
            showError(idConsultation, idConsultationError);
            return false;
        } else {
            hideError(idConsultation, idConsultationError);
            return true;
        }
    }

    function validateNote() {
        const noteValue = noteInput.value.trim();
        if (noteValue === '' || isNaN(noteValue) || noteValue < 1 || noteValue > 5) {
            showError(noteInput, noteError);
            return false;
        } else {
            hideError(noteInput, noteError);
            return true;
        }
    }

    function showError(input, errorElement) {
        input.classList.add('input-error');
        errorElement.style.display = 'block';
    }

    function hideError(input, errorElement) {
        input.classList.remove('input-error');
        errorElement.style.display = 'none';
    }
});
</script>
</body>
</html>
