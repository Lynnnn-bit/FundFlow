<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../Controller/feedbackcontroller.php';

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
    <link rel="stylesheet" href="css/feedback.css">
    <link rel="stylesheet" href="../Frontoffice/css/navbar.css">
    <style>
        .main-container {
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
            background-color: #2c3e50;
            border-radius: 8px;
            color: white;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-control {
            width: 100%;
            padding: 8px;
            border-radius: 4px;
            border: 1px solid #ddd;
        }
        .btn {
            padding: 8px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }
        .btn-primary {
            background-color: #3498db;
            color: white;
        }
        .btn-secondary {
            background-color: #95a5a6;
            color: white;
        }
        .error {
            color: red;
            font-size: 14px;
            display: none;
            margin-top: 5px;
        }
        .input-error {
            border: 2px solid red !important;
        }
        .preselected {
            background-color: #f8f9fa;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 15px;
            color: #000;
        }
    </style>
</head>
<body>
<header class="navbar">
    <div class="logo-container">
        <span class="brand-name">FundFlow</span>
    </div>
    <nav>
        <a href="../Frontoffice/consultation.php"><i class="fas fa-calendar-plus"></i> Nouvelle Consultation</a>
        <a href="../Frontoffice/mesconsultations.php"><i class="fas fa-list"></i> Mes Consultations</a>
        <a href="feedback.php" class="active"><i class="fas fa-comment-alt"></i> Feedbacks</a>
        <a href="about.php"><i class="fas fa-info-circle"></i> À propos</a>
        <a href="contact.php"><i class="fas fa-envelope"></i> Contact</a>
        <a href="logout.php" class="logout"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
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
