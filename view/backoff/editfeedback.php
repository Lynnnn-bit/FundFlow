<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../control/feedbackcontroller.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$controller = new FeedbackController();
$id_feedback = isset($_GET['id']) ? (int)$_GET['id'] : null;

if (!$id_feedback) {
    $_SESSION['error'] = "ID de feedback manquant";
    header("Location: feedback.php");
    exit;
}

$feedback = $controller->getFeedbackById($id_feedback);

if (!$feedback) {
    $_SESSION['error'] = "Feedback introuvable";
    header("Location: feedback.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $note = isset($_POST['note']) ? (int)$_POST['note'] : 0;
        
        if ($note < 1 || $note > 5) {
            throw new Exception("La note doit être entre 1 et 5");
        }
        
        if ($controller->updateFeedback($id_feedback, $note)) {
            $_SESSION['success'] = "Feedback mis à jour avec succès!";
            header("Location: feedback.php");
            exit;
        } else {
            throw new Exception("Erreur lors de la mise à jour du feedback");
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
    <title>Modifier Feedback</title>
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
            <h1><i class="fas fa-edit"></i> Modifier Feedback</h1>
        </div>
        <?php if (isset($error_message)): ?>
            <div style="color: red; background-color: #f8d7da; padding: 10px; border-radius: 5px; border: 1px solid #f5c6cb;">
                <?= htmlspecialchars($error_message) ?>
            </div>
        <?php endif; ?>
        <form method="POST" id="editFeedbackForm">
            <div class="form-group">
                <label class="form-label">Consultation ID</label>
                <input type="text" class="form-control" 
                       value="<?php echo isset($feedback['id_consultation']) ? htmlspecialchars($feedback['id_consultation']) : ''; ?>" disabled>
            </div>
            <div class="form-group">
                <label for="note" class="form-label">Note (1-5)</label>
                <input type="text" name="note" id="note" class="form-control"
                       value="<?php echo isset($feedback['note']) ? htmlspecialchars($feedback['note']) : ''; ?>">
                <div id="noteError" class="error">La note doit être un nombre entre 1 et 5.</div>
            </div>
            <button type="submit" class="btn btn-primary">Mettre à jour</button>
            <a href="feedback.php" class="btn btn-secondary">Annuler</a>
        </form>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var form = document.getElementById('editFeedbackForm');
            var noteInput = document.getElementById('note');
            var noteError = document.getElementById('noteError');

            function validateNote() {
                var noteValue = noteInput.value.trim();
                var isValid = noteValue !== '' && !isNaN(noteValue) && noteValue >= 1 && noteValue <= 5;
                
                if (!isValid) {
                    noteError.style.display = 'block';
                    noteInput.classList.add('error');
                } else {
                    noteError.style.display = 'none';
                    noteInput.classList.remove('error');
                }
                
                return isValid;
            }

            // Validate on input change
            noteInput.addEventListener('input', function() {
                validateNote();
            });

            // Validate on form submission
            form.addEventListener('submit', function (event) {
                var isNoteValid = validateNote();
                
                if (!isNoteValid) {
                    event.preventDefault();
                }
            });
        });
    </script>
</body>
</html>