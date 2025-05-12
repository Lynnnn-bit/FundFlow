<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../config.php';

// Initialize variables
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
    $mdp = $_POST['mdp'] ?? '';
    
    // Validation
    if (empty($email)) $errors[] = "L'email est obligatoire";
    if (empty($mdp)) $errors[] = "Le mot de passe est obligatoire";
    
    if (empty($errors)) {
        try {
            $db = Config::getConnexion();
            if (!$db) {
                die("Erreur: Impossible de se connecter à la base de données.");
            }

            $stmt = $db->prepare("SELECT * FROM utilisateur WHERE email = ?");
            if (!$stmt) {
                die("Erreur: La requête SQL n'a pas pu être préparée.");
            }

            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && ($mdp === $user['mdp'] || password_verify($mdp, $user['mdp']))) {
                // Check account status
                if ($user['status'] === 'inactif') {
                    $errors[] = "Votre compte est inactif. Veuillez contacter l'administrateur.";
                } 
                elseif ($user['status'] === 'suspendu') { // Vérifiez si le compte est suspendu
                    $errors[] = "Désolé, votre compte est bloqué.";
                } 
                
                else {
                    // Set session variables
                    $_SESSION['user_id'] = $user['id_utilisateur']; // Store user ID in session
                    $_SESSION['user'] = [
                        'nom' => $user['nom'],
                        'prenom' => $user['prenom'],
                        'email' => $user['email'],
                        'role' => $user['role'],
                        'status' => $user['status']
                    ];
                    
                    // Redirect based on role
                    if ($user['role'] === 'admin') {
                        header("Location: ../backoff/backoffice.php");
                    } else {
                        header("Location: acceuil2.php");
                    }
                    exit();
                }
            } else {
                $errors[] = "Email ou mot de passe incorrect";
            }
        } catch (PDOException $e) {
            $errors[] = "Erreur de connexion: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FundFlow - Connexion</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&family=Playfair+Display:wght@500;600;700&display=swap" rel="stylesheet">
    <style>
        /* Login Page Specific Styles */
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background: linear-gradient(135deg, #3a56d4 0%, #10b981 100%);
            background-size: 200% 200%;
            animation: gradientBG 12s ease infinite;
            font-family: 'Montserrat', sans-serif;
        }

        .login-container {
            width: 100%;
            max-width: 450px;
            padding: 2.5rem;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-radius: 24px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            position: relative;
            overflow: hidden;
            z-index: 2;
        }

        .login-container::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(67, 97, 238, 0.1) 0%, transparent 70%);
            z-index: -1;
        }

        .logo-container {
            text-align: center;
            margin-bottom: 2rem;
        }

        .brand-logo {
            height: 60px;
            width: auto;
            margin-bottom: 1rem;
        }

        .login-title {
            font-size: 2rem;
            font-weight: 700;
            color: white;
            text-align: center;
            margin-bottom: 1.5rem;
            font-family: 'Playfair Display', serif;
        }

        .form-group {
            margin-bottom: 1.5rem;
            position: relative;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: rgba(255,255,255,0.9);
            font-weight: 500;
        }

        .form-control {
            width: 100%;
            padding: 1rem 1.5rem;
            background: rgba(255,255,255,0.9);
            border: 1px solid rgba(255,255,255,0.3);
            border-radius: 12px;
            font-size: 1rem;
            color: #333;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.3);
        }

        .password-toggle {
            position: relative;
        }

        .password-toggle i {
            position: absolute;
            right: 1.5rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gray);
            cursor: pointer;
        }

        .btn-login {
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

        .btn-login:hover {
            background: linear-gradient(45deg, var(--primary-dark), var(--primary));
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(67, 97, 238, 0.6);
        }

        .forgot-password {
            text-align: right;
            margin-top: -1rem;
            margin-bottom: 1.5rem;
        }

        .forgot-password a {
            color: rgba(255,255,255,0.8);
            font-size: 0.9rem;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .forgot-password a:hover {
            color: white;
            text-decoration: underline;
        }

        .register-link {
            text-align: center;
            margin-top: 2rem;
            color: rgba(255,255,255,0.8);
        }

        .register-link a {
            color: white;
            font-weight: 600;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .register-link a:hover {
            text-decoration: underline;
        }

        .alert {
            padding: 1rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 1rem;
            background: rgba(255,255,255,0.9);
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

        .alert-success {
            border-left-color: var(--secondary);
            color: var(--secondary-dark);
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
        @media (max-width: 576px) {
            .login-container {
                padding: 1.5rem;
                margin: 1rem;
            }
            
            .login-title {
                font-size: 1.8rem;
            }
        }
    </style>
</head>
<body>
<div class="background-effect"></div>
<div class="particles-container" id="particles-js"></div>

<div class="login-container">
    <div class="logo-container">
        <img src="assets/Logo_FundFlow.png" alt="FundFlow Logo" class="brand-logo">
    </div>
    
    <h1 class="login-title">Connexion</h1>
    
    <?php if (isset($_GET['inscription']) && $_GET['inscription'] === 'success'): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i>
            <p>Inscription réussie! Vous pouvez maintenant vous connecter.</p>
        </div>
    <?php endif; ?>
    
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
    
    <form action="connexion.php" method="POST">
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" class="form-control" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
        </div>
        
        <div class="form-group password-toggle">
            <label for="mdp">Mot de passe</label>
            <input type="password" id="mdp" name="mdp" class="form-control" required>
            <i class="fas fa-eye" id="togglePassword"></i>
        </div>
        
        <div class="forgot-password">
            <a href="mdp.php">Mot de passe oublié ?</a>
        </div>

        <button type="submit" class="btn-login" href="">Se connecter</button>
        
        <div class="register-link">
            Pas encore de compte? <a href="inscription.php">S'inscrire</a>
        </div>
    </form>
</div>

<script src="js/jsconnexion.js"></script>
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

// Password toggle
document.getElementById('togglePassword').addEventListener('click', function() {
    const passwordInput = document.getElementById('mdp');
    const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
    passwordInput.setAttribute('type', type);
    this.classList.toggle('fa-eye-slash');
    this.classList.toggle('fa-eye');
});
</script>
</body>
</html>