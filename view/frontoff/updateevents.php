<?php
// updateevents.php
session_start();
require_once '../../control/EvennementC.php';

$eventC = new EvennementC();
$uploadDir = '../uploads/';

// Récupérer l'événement
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$event = $eventC->getEvenementById($id);

if (!$event) {
    $_SESSION['error'] = "Événement introuvable";
    header('Location: events.php');
    exit();
}

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $data = [
            'id' => $id,
            'nom' => $_POST['nom'],
            'date_evenement' => $_POST['date_evenement'],
            'type' => $_POST['type'],
            'horaire' => $_POST['horaire'],
            'nb_place' => (int)$_POST['nb_place'],
            'affiche' => $event['affiche']
        ];

        // Gestion de l'affiche
        if (!empty($_FILES['affiche']['name'])) {
            if (!empty($event['affiche']) && file_exists($uploadDir.$event['affiche'])) {
                unlink($uploadDir.$event['affiche']);
            }

            $fileName = uniqid().'_'.basename($_FILES['affiche']['name']);
            move_uploaded_file($_FILES['affiche']['tmp_name'], $uploadDir.$fileName);
            $data['affiche'] = $fileName;
        }

        $eventC->modifierEvennement($data);
        $_SESSION['success'] = "Événement mis à jour !";
        header('Location: events.php');
        exit();

    } catch(Exception $e) {
        $_SESSION['error'] = $e->getMessage();
        header("Location: updateevents.php?id=$id");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier Événement</title>
    <style>
        /* (style unchanged, same as yours) */
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: linear-gradient(to right, #141e30, #243b55);
        }
        .form-wrapper {
            background-color: rgba(255, 255, 255, 0.05);
            padding: 30px;
            border-radius: 8px;
            width: 90%;
            max-width: 500px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.4);
            margin: 40px auto;
            color: white;
        }
        h2 { 
            text-align: center; 
            color: #1abc9c;
            margin-bottom: 25px;
        }
        .form-group { 
            margin-bottom: 20px;
        }
        label { 
            display: block; 
            margin-bottom: 10px;
            font-weight: 600;
        }
        input[type="text"],
        input[type="number"],
        input[type="date"],
        input[type="time"],
        select,
        input[type="file"] {
            width: 100%;
            padding: 12px;
            border: none;
            border-radius: 5px;
            background-color: rgba(255, 255, 255, 0.2);
            color: white;
            font-size: 14px;
            margin-top: 5px;
        }
        input::placeholder {
            color: rgba(255, 255, 255, 0.6);
        }
        .error-message {
            color: red;
            font-size: 13px;
            margin-top: 5px;
        }
        button {
            background-color: #1abc9c;
            color: white;
            width: 100%;
            padding: 14px;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 20px;
        }
        button:hover { 
            background-color: #16a085;
            transform: translateY(-2px);
        }
        .error { 
            color: #e74c3c; 
            text-align: center;
            padding: 10px;
            margin-bottom: 20px;
            background: rgba(231, 76, 60, 0.1);
            border-radius: 5px;
        }
        img {
            max-width: 200px;
            margin: 15px 0;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        }
        .btn-delete {
            display: block;
            text-align: center;
            color: #e74c3c;
            margin-top: 20px;
            text-decoration: none;
            font-weight: 500;
        }
        .btn-delete:hover {
            color: #c0392b;
        }
    </style>
</head>
<body>
    <div class="form-wrapper">
        <h2>Modifier l'événement</h2>
        
        <?php if(isset($_SESSION['error'])): ?>
            <div class="error">
                <?= $_SESSION['error']; unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data" id="eventForm">
            <div class="form-group">
                <label for="nom">Nom de l'événement</label>
                <input type="text" id="nom" name="nom" value="<?= htmlspecialchars($event['nom']) ?>">
                <div class="error-message"></div>
            </div>

            <div class="form-group">
                <label for="date_evenement">Date de l'événement</label>
                <input type="date" id="date_evenement" name="date_evenement" value="<?= $event['date_evenement'] ?>">
                <div class="error-message"></div>
            </div>

            <div class="form-group">
                <label for="type">Type d'événement</label>
                <select id="type" name="type">
                    <option value="">-- Choisir un type --</option>
                    <option value="Présentiel" <?= $event['type'] === 'Présentiel' ? 'selected' : '' ?>>Présentiel</option>
                    <option value="En ligne" <?= $event['type'] === 'En ligne' ? 'selected' : '' ?>>En ligne</option>
                </select>
                <div class="error-message"></div>
            </div>

            <div class="form-group">
                <label for="horaire">Horaire</label>
                <input type="time" id="horaire" name="horaire" value="<?= $event['horaire'] ?>">
                <div class="error-message"></div>
            </div>

            <div class="form-group">
                <label for="nb_place">Nombre de places</label>
                <input type="number" id="nb_place" name="nb_place" value="<?= $event['nb_place'] ?>" min="1">
                <div class="error-message"></div>
            </div>

            <div class="form-group">
                <label for="affiche">Affiche actuelle</label>
                <?php if($event['affiche']): ?>
                    <img src="../uploads/<?= $event['affiche'] ?>" alt="Affiche actuelle">
                <?php endif; ?>
                <input type="file" id="affiche" name="affiche" accept="image/*">
            </div>

            <button type="submit">Sauvegarder les modifications</button>
            <a href="events.php" class="btn-delete">Annuler et retourner</a>
        </form>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('eventForm');
        const fields = ['nom', 'date_evenement', 'type', 'horaire', 'nb_place'];

        function validateField(id) {
            const input = document.getElementById(id);
            const errorDiv = input.parentElement.querySelector('.error-message');
            if (!input.value.trim()) {
                errorDiv.textContent = 'Ce champ est obligatoire.';
                return false;
            } else {
                errorDiv.textContent = '';

                // Specific validation for date_evenement
                if (id === 'date_evenement') {
                    const selectedDate = new Date(input.value);
                    const today = new Date();
                    today.setHours(0,0,0,0); // Remove time part for today
                    if (selectedDate < today) {
                        errorDiv.textContent = "La date n'est pas valide.";
                        return false;
                    } else {
                        errorDiv.textContent = '';
                    }
                }

                return true;
            }
        }

        fields.forEach(function(id) {
            const input = document.getElementById(id);
            input.addEventListener('keyup', function() {
                validateField(id);
            });
            input.addEventListener('change', function() {
                validateField(id);
            });
        });

        form.addEventListener('submit', function(e) {
            let valid = true;
            fields.forEach(function(id) {
                if (!validateField(id)) {
                    valid = false;
                }
            });

            if (!valid) {
                e.preventDefault();
            }
        });
    });
    </script>
</body>
</html>
