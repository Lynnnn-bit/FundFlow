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
        if ($controller->deleteFeedback($id_feedback)) {
            $_SESSION['success'] = "Feedback supprimé avec succès!";
        } else {
            throw new Exception("Erreur lors de la suppression du feedback");
        }
    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
    }
    header("Location: feedback.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supprimer Feedback</title>
    <link rel="stylesheet" href="cssback/feedback.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .main-container {
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
            background-color: #2c3e50;
            border-radius: 8px;
            color: white;
        }
        
        ul {
            list-style-type: none;
            padding: 0;
        }
        
        li {
            margin-bottom: 10px;
        }
        
        strong {
            display: inline-block;
            width: 120px;
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
        
        .btn-danger {
            background-color: #e74c3c;
            color: white;
        }
        
        .btn-secondary {
            background-color: #95a5a6;
            color: white;
        }
    </style>
</head>
<body>
    <div class="main-container">
        <h1><i class="fas fa-trash"></i> Supprimer Feedback</h1>
        <p>Êtes-vous sûr de vouloir supprimer le feedback suivant ?</p>
        <ul>
            <li><strong>ID Feedback:</strong> <?php echo htmlspecialchars($feedback['id_feedback']); ?></li>
            <li><strong>ID Consultation:</strong> <?php echo htmlspecialchars($feedback['id_consultation']); ?></li>
            <li><strong>Note:</strong> <?php echo htmlspecialchars($feedback['note']); ?></li>
        </ul>
        <form method="POST">
            <button type="submit" class="btn btn-danger">Confirmer la suppression</button>
            <a href="feedback.php" class="btn btn-secondary">Annuler</a>
        </form>
    </div>
</body>
</html>