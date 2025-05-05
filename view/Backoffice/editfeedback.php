<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../Controller/feedbackcontroller.php';

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
        
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
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
            margin-right: 10px;
        }
        
        .btn-primary {
            background-color: #3498db;
            color: white;
        }
        
        .btn-secondary {
            background-color: #95a5a6;
            color: white;
        }
        
        .alert {
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 4px;
        }
        
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .error {
            border: 2px solid red !important;
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
        <h1><i class="fas fa-edit"></i> Modifier Feedback</h1>
        
        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>
        
        <form method="POST" id="editFeedbackForm">
            <div class="form-group">
                <label>Consultation ID</label>
                <input type="text" class="form-control" 
                       value="<?php echo isset($feedback['id_consultation']) ? htmlspecialchars($feedback['id_consultation']) : ''; ?>" disabled>
            </div>
            
            <div class="form-group">
                <label for="note">Note (1-5)</label>
                <input type="text" name="note" id="note" class="form-control"
                       value="<?php echo isset($feedback['note']) ? htmlspecialchars($feedback['note']) : ''; ?>">
                <div id="noteError" style="color: red; font-size: 14px; display: none;">La note doit être un nombre entre 1 et 5.</div>
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