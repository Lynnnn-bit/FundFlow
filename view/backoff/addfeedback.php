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
    <link rel="stylesheet" href="../Frontoff/css/stylebackof.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #f5f7ff 0%, #e8ecff 100%);
            font-family: 'Montserrat', sans-serif;
            color: #212529;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .card {
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 600px;
            padding: 2rem;
        }

        .card-header {
            text-align: center;
            margin-bottom: 1.5rem;
        }

        .card-header h1 {
            font-size: 1.8rem;
            color: #4361ee;
            margin: 0;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #212529;
        }

        .form-control {
            width: 100%;
            padding: 0.8rem;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s;
        }

        .form-control:focus {
            border-color: #4361ee;
            outline: none;
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.2);
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.8rem 1.5rem;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            border: none;
        }

        .btn-primary {
            background: #4361ee;
            color: white;
        }

        .btn-primary:hover {
            background: #3a56d4;
        }

        .btn-secondary {
            background: #adb5bd;
            color: white;
        }

        .btn-secondary:hover {
            background: #868e96;
        }

        .error {
            color: #e74c3c;
            font-size: 0.9rem;
            margin-top: 0.5rem;
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="card-header">
            <h1><i class="fas fa-plus-circle"></i> Ajouter un Feedback</h1>
        </div>
        <?php if (isset($error_message)): ?>
            <div style="color: red; background-color: #f8d7da; padding: 10px; border-radius: 5px; border: 1px solid #f5c6cb;">
                <?= htmlspecialchars($error_message) ?>
            </div>
        <?php endif; ?>
        <form id="feedbackForm" method="POST">
            <?php if ($preselected_consultation): ?>
                <input type="hidden" name="id_consultation" value="<?= htmlspecialchars($preselected_consultation) ?>">
            <?php endif; ?>
            <div class="form-group" <?= $preselected_consultation ? 'style="display:none;"' : '' ?>>
                <label for="id_consultation" class="form-label">Consultation</label>
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
                <label for="note" class="form-label">Note (1-5)</label>
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
