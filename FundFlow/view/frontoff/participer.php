<?php
// participer.php
session_start();
include_once '../../control/EvennementC.php';
$evenementC = new EvennementC();

if (!isset($_GET['id_evenement'])) {
    header('Location: events.php');
    exit();
}

$id = $_GET['id_evenement'];
$event = $evenementC->getEvenementById($id); // Assure-toi que cette méthode existe dans EvennementC

if (!$event) {
    echo "Événement introuvable.";
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Participation à l'événement</title>
    <style>
        /* Global Styles */
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
        input[type="date"],
        input[type="time"] {
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
    <script>
        // Validation du formulaire
        document.addEventListener("DOMContentLoaded", function() {
            const form = document.getElementById("participationForm");

            form.addEventListener("submit", function(event) {
                let valid = true;

                // Réinitialisation des messages d'erreur
                document.getElementById("nomError").style.display = "none";
                document.getElementById("prenomError").style.display = "none";

                // Vérification du nom
                const nom = document.getElementById("nom").value.trim();
                if (nom === "") {
                    document.getElementById("nomError").style.display = "block";
                    document.getElementById("nomError").innerText = "Le nom est requis.";
                    valid = false;
                }

                // Vérification du prénom
                const prenom = document.getElementById("prenom").value.trim();
                if (prenom === "") {
                    document.getElementById("prenomError").style.display = "block";
                    document.getElementById("prenomError").innerText = "Le prénom est requis.";
                    valid = false;
                }

                // Si le formulaire est invalide, on empêche la soumission
                if (!valid) {
                    event.preventDefault();
                }
            });
        });
    </script>
</head>
<body>
    <div class="form-wrapper">
        <h2>Participation à : <?= htmlspecialchars($event['nom']) ?></h2>
        <form action="confirmation.php" method="POST" id="participationForm">
            <input type="hidden" name="id_evenement" value="<?= $event['id_evenement'] ?>">

            <div class="form-group">
                <label for="nom">Nom:</label>
                <input type="text" name="nom" id="nom">
                <div class="error-message" id="nomError"></div>
            </div>

            <div class="form-group">
                <label for="prenom">Prénom:</label>
                <input type="text" name="prenom" id="prenom">
                <div class="error-message" id="prenomError"></div>
            </div>

            <button type="submit">Valider et générer le PDF</button>
        </form>
    </div>
</body>
</html>
