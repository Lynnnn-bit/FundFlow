<?php
require_once __DIR__ . '/../../config.php';

// Initialize variables
$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect and sanitize form data
    $nom = htmlspecialchars(trim($_POST['nom'] ?? ''));
    $prenom = htmlspecialchars(trim($_POST['prenom'] ?? ''));
    $email = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
    $mdp = $_POST['mdp'] ?? '';
    $confirmation_mdp = $_POST['confirmation_mdp'] ?? '';
    $role = htmlspecialchars(trim($_POST['role'] ?? 'consultant'));
    $adresse = htmlspecialchars(trim($_POST['adresse'] ?? ''));
    $tel = htmlspecialchars(trim($_POST['tel'] ?? ''));
    $image = ''; // Initialize image variable

    // Validation
    if (empty($nom)) $errors[] = "Le nom est obligatoire";
    if (empty($prenom)) $errors[] = "Le prénom est obligatoire";
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Email invalide";
    if (empty($mdp) || strlen($mdp) < 8) $errors[] = "Le mot de passe doit contenir au moins 8 caractères";
    if ($mdp !== $confirmation_mdp) $errors[] = "Les mots de passe ne correspondent pas";
    if (!in_array($role, ['consultant', 'investisseur', 'entrepreneur'])) $errors[] = "Rôle invalide";

    // Handle image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $fileType = $_FILES['image']['type'];
        
        if (in_array($fileType, $allowedTypes)) {
            $uploadDir = __DIR__ . '/../../uploads/';
            
            // Create uploads directory if it doesn't exist
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            // Generate unique filename
            $extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $filename = uniqid() . '.' . $extension;
            $destination = $uploadDir . $filename;
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $destination)) {
                $image = 'uploads/' . $filename; // Store relative path
            } else {
                $errors[] = "Erreur lors du téléchargement de l'image";
            }
        } else {
            $errors[] = "Type de fichier non autorisé. Seuls JPEG, PNG et GIF sont acceptés";
        }
    }

    // If no errors, proceed with registration
    if (empty($errors)) {
        try {
            $db = Config::getConnexion();
            
            // Check if email already exists
            $stmt = $db->prepare("SELECT COUNT(*) FROM utilisateur WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetchColumn() > 0) {
                $errors[] = "Cet email est déjà utilisé";
            } else {
                // Hash password
                $hashed_password = password_hash($mdp, PASSWORD_DEFAULT);
                
                // Get the next available ID
                $stmt = $db->query("SELECT MAX(id_utilisateur) FROM utilisateur");
                $maxId = $stmt->fetchColumn();
                $newId = ($maxId !== null) ? $maxId + 1 : 1;
                
                // Insert new user with all fields
                $stmt = $db->prepare("
                    INSERT INTO utilisateur 
                    (id_utilisateur, nom, prenom, email, mdp, role, status, adresse, date_creation, tel, image)
                    VALUES 
                    (?, ?, ?, ?, ?, ?, 'actif', ?, CURDATE(), ?, ?)
                ");
                
                $success = $stmt->execute([
                    $newId,
                    $nom,
                    $prenom,
                    $email,
                    $hashed_password,
                    $role,
                    $adresse,
                    $tel,
                    $image
                ]);
                
                if ($success) {
                    session_start();
                    $_SESSION['user_id'] = $newId;  // Store user ID in session
                    header("Location: acceuil2.php");
                    exit();
                }
            }
        } catch (PDOException $e) {
            $errors[] = "Erreur de base de données: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FundFlow - Inscription</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&family=Playfair+Display:wght@500;600;700&display=swap" rel="stylesheet">
    <style>
        /* Registration Page Specific Styles */
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background: linear-gradient(135deg, #3a56d4 0%, #10b981 100%);
            background-size: 200% 200%;
            animation: gradientBG 12s ease infinite;
            font-family: 'Montserrat', sans-serif;
            padding: 2rem;
        }

        .registration-container {
            display: flex;
            width: 100%;
            max-width: 1000px;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-radius: 24px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            overflow: hidden;
            position: relative;
            z-index: 2;
        }

        .registration-container::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(67, 97, 238, 0.1) 0%, transparent 70%);
            z-index: -1;
        }

        .registration-hero {
            flex: 1;
            padding: 3rem;
            background: linear-gradient(135deg, rgba(67, 97, 238, 0.7), rgba(16, 185, 129, 0.7));
            color: white;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .registration-hero h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            font-family: 'Playfair Display', serif;
        }

        .registration-hero p {
            font-size: 1.1rem;
            margin-bottom: 2rem;
            opacity: 0.9;
        }

        .benefits-list {
            list-style: none;
            padding: 0;
        }

        .benefits-list li {
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            font-size: 1rem;
        }

        .benefits-list i {
            margin-right: 1rem;
            font-size: 1.2rem;
            color: white;
            background: rgba(255,255,255,0.2);
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .registration-form {
            flex: 1;
            padding: 3rem;
            background: rgba(255, 255, 255, 0.9);
        }

        .form-logo {
            text-align: center;
            margin-bottom: 2rem;
        }

        .brand-logo {
            height: 60px;
            width: auto;
            margin-bottom: 1rem;
        }

        .form-logo p {
            color: var(--dark);
            font-size: 1.2rem;
            font-weight: 500;
        }

        .form-title {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 1.5rem;
            text-align: center;
            font-family: 'Playfair Display', serif;
        }

        .form-row {
            display: flex;
            gap: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .form-group {
            flex: 1;
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: var(--dark);
            font-weight: 500;
        }

        .form-control {
            width: 100%;
            padding: 1rem 1.5rem;
            background: white;
            border: 1px solid var(--light-gray);
            border-radius: 12px;
            font-size: 1rem;
            color: var(--dark);
            transition: all 0.3s ease;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.2);
        }

        select.form-control {
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='%236c757d' viewBox='0 0 16 16'%3E%3Cpath d='M7.247 11.14 2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 1rem center;
            background-size: 16px 12px;
        }

        .password-strength {
            margin-top: 0.5rem;
            font-size: 0.85rem;
            color: var(--gray);
        }

        #strengthText {
            font-weight: 600;
        }

        .btn-register {
            width: 100%;
            padding: 1rem;
            background: linear-gradient(45deg, var(--primary), var(--primary-dark));
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 1rem;
            box-shadow: 0 4px 15px rgba(67, 97, 238, 0.4);
        }

        .btn-register:hover {
            background: linear-gradient(45deg, var(--primary-dark), var(--primary));
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(67, 97, 238, 0.6);
        }

        .login-link {
            text-align: center;
            margin-top: 2rem;
            color: var(--gray);
        }

        .login-link a {
            color: var(--primary);
            font-weight: 600;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .login-link a:hover {
            text-decoration: underline;
        }

        #image-preview {
            border-radius: 8px;
            border: 1px solid var(--light-gray);
            margin-top: 1rem;
        }

        .alert {
            padding: 1rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 1rem;
            background: white;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            border-left: 4px solid transparent;
        }

        .alert i {
            font-size: 1.2rem;
        }

        .alert-error {
            border-left-color: #dc2626;
            color: #dc2626;
        }

        /* Background Effects */
        .background-effect {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle at 20% 30%, rgba(58, 86, 212, 0.3) 0%, transparent 50%),
                        radial-gradient(circle at 80% 70%, rgba(16, 185, 129, 0.3) 0%, transparent 50%);
            z-index: -2;
        }

        .particles-container {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
        }

        @keyframes gradientBG {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .registration-container {
                flex-direction: column;
            }
            
            .registration-hero {
                padding: 2rem;
            }
            
            .registration-form {
                padding: 2rem;
            }
            
            .form-row {
                flex-direction: column;
                gap: 0;
            }
        }

        @media (max-width: 576px) {
            body {
                padding: 1rem;
            }
            
            .registration-hero h1 {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>
<div class="background-effect"></div>
<div class="particles-container" id="particles-js"></div>

<div class="registration-container">
    <div class="registration-hero">
        <h1>Rejoignez FundFlow</h1>
        <p>La plateforme de financement pour vos projets innovants</p>
        
        <ul class="benefits-list">
            <li>
                <i class="fas fa-check-circle"></i>
                <span>Accès à des investisseurs qualifiés</span>
            </li>
            <li>
                <i class="fas fa-check-circle"></i>
                <span>Gestion simplifiée de vos projets</span>
            </li>
            <li>
                <i class="fas fa-check-circle"></i>
                <span>Support personnalisé 24/7</span>
            </li>
        </ul>
    </div>
    
    <div class="registration-form">
        <div class="form-logo">
            <img src="assets/Logo_FundFlow.png" alt="FundFlow Logo" class="brand-logo">
            <p>Créez votre compte</p>
        </div>
        
        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <div>
                    <?php foreach ($errors as $error): ?>
                        <p><?= htmlspecialchars($error) ?></p>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
        
        <form action="inscription.php" method="POST" id="registrationForm" enctype="multipart/form-data">
            <div class="form-row">
                <div class="form-group">
                    <label for="nom">Nom *</label>
                    <input type="text" id="nom" name="nom" class="form-control" value="<?= htmlspecialchars($_POST['nom'] ?? '') ?>"  >
                </div>
                
                <div class="form-group">
                    <label for="prenom">Prénom *</label>
                    <input type="text" id="prenom" name="prenom" class="form-control" value="<?= htmlspecialchars($_POST['prenom'] ?? '') ?>"  >
                </div>
            </div>
            
            <div class="form-group">
                <label for="email">Email *</label>
                <input type="email" id="email" name="email" class="form-control" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"  >
            </div>
            
            <div class="form-group">
                <label for="role">Rôle *</label>
                <select id="role" name="role" class="form-control"  >
                    <option value="" disabled selected>Choisir un rôle</option>
                    <option value="consultant" <?php echo (isset($_POST['role']) && $_POST['role'] === 'consultant' ? 'selected' : ''); ?>>Consultant</option>
                    <option value="investisseur" <?= (isset($_POST['role']) && $_POST['role'] === 'investisseur' ? 'selected' : ''); ?>>Investisseur</option>
                    <option value="entrepreneur" <?= (isset($_POST['role']) && $_POST['role'] === 'entrepreneur' ? 'selected' : ''); ?>>entrepreneur</option>

                </select>
            </div>
            
            <div class="form-group">
                <label for="mdp">Mot de passe *</label>
                <input type="password" id="mdp" name="mdp" class="form-control"  >
                <div class="password-strength">Force: <span id="strengthText">Faible</span></div>
            </div>
            
            <div class="form-group">
                <label for="confirmation_mdp">Confirmer le mot de passe *</label>
                <input type="password" id="confirmation_mdp" name="confirmation_mdp" class="form-control"  >
            </div>
            
            <div class="form-group">
                <label for="adresse">Adresse</label>
                <input type="text" id="adresse" name="adresse" class="form-control" value="<?= htmlspecialchars($_POST['adresse'] ?? '') ?>">
            </div>
            
            <div class="form-group">
                <label for="tel">Téléphone</label>
                <input type="tel" id="tel" name="tel" class="form-control" value="<?= htmlspecialchars($_POST['tel'] ?? '') ?>">
            </div>
            
            <div class="form-group">
                <label for="image">Image de profil</label>
                <input type="file" id="image" name="image" class="form-control" accept="image/jpeg, image/png, image/gif" onchange="previewImage(this)">
                <img id="image-preview" src="#" alt="Preview" style="display: none; max-width: 200px; margin-top: 10px;">
            </div>
            
            <button type="submit" class="btn-register">
                <i class="fas fa-user-plus"></i> S'inscrire
            </button>
            
            <div class="login-link">
                Déjà un compte? <a href="connexion.php">Se connecter</a>
            </div>
        </form>
    </div>
</div>

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

// Image preview function
function previewImage(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('image-preview').style.display = 'block';
            document.getElementById('image-preview').src = e.target.result;
        }
        reader.readAsDataURL(input.files[0]);
    }
}

// Password strength checker (keep your existing jsincri.js functionality)
</script>
<script src="js/jsincri.js"></script>
</body>
</html>