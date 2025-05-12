<?php
session_start();
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../vendor/autoload.php'; // Assurez-vous d'avoir PHPMailer installé via Composer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function generateRandomPassword($length = 8) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $password = '';
    for ($i = 0; $i < $length; $i++) {
        $password .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $password;
}

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);

    try {
        $db = Config::getConnexion();
        $stmt = $db->prepare("SELECT * FROM utilisateur WHERE email = ?");
        $stmt->execute([$email]);
        
        if ($stmt->rowCount() > 0) {
            // Générer un nouveau mot de passe
            $newPassword = generateRandomPassword();
            
            // Hasher le mot de passe
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            
            // Mettre à jour la base de données
            $updateStmt = $db->prepare("UPDATE utilisateur SET mdp = ? WHERE email = ?");
            $updateStmt->execute([$hashedPassword, $email]);
            
            // Envoyer l'email avec PHPMailer
            $mail = new PHPMailer(true);
            try {
                // Configuration du serveur SMTP
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com'; // Set SMTP server
    $mail->SMTPAuth   = true;
    $mail->Username   = 'arouasarra5@gmail.com'; // Your Gmail address
    $mail->Password   = 'qmnxpjinvapbdnxx'; // Your Gmail password or App Password
    //$mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
  
    $mail->Port = 587;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;  // Use TLS encryption
    //Recipients
    $mail->setFrom('arouasarra5@gmail.com', 'FundFlow');
    $mail->addAddress($email ); // To user


                // Contenu de l'email
                $mail->isHTML(true);
                $mail->Subject = 'Réinitialisation de votre mot de passe';
                $mail->Body = '
                    <html>
                    <head>
                        <style>
                            body {
                                font-family: Arial, sans-serif;
                                margin: 0;
                                padding: 0;
                                background-color: #f4f4f4;
                            }
                            .container {
                                max-width: 600px;
                                margin: auto;
                                background: #ffffff;
                                padding: 20px;
                                border-radius: 10px;
                                box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
                            }
                            h1 {
                                color: #FF5722;
                                text-align: center;
                            }
                            p {
                                color: #333;
                                line-height: 1.5;
                            }
                            .password {
                                background-color: #f8f8f8;
                                padding: 10px;
                                border-radius: 5px;
                                font-size: 18px;
                                text-align: center;
                                margin: 20px 0;
                                color: #4CAF50;
                                font-weight: bold;
                            }
                            .footer {
                                text-align: center;
                                font-size: 12px;
                                color: #888;
                                margin-top: 20px;
                            }
                        </style>
                    </head>
                    <body>
                        <div class="container">
                            <h1>Réinitialisation de votre mot de passe</h1>
                            <p>Bonjour,</p>
                            <p>Vous avez demandé la réinitialisation de votre mot de passe pour votre compte FundFlow.</p>
                            <p>Voici votre nouveau mot de passe temporaire :</p>
                            <div class="password">' . htmlspecialchars($newPassword) . '</div>
                            <p>Nous vous recommandons de changer ce mot de passe après vous être connecté.</p>
                            <p>Cordialement,</p>
                            <p>L\'équipe FundFlow</p>
                        </div>
                        <div class="footer">
                            <p>Si vous n\'avez pas demandé cette réinitialisation, veuillez ignorer cet email.</p>
                        </div>
                    </body>
                    </html>
                ';

                // Envoyer l'email
                if ($mail->send()) {
                    $success = "Un email avec votre nouveau mot de passe a été envoyé à $email !";
                } else {
                    $errors[] = "Erreur lors de l'envoi de l'email. Veuillez réessayer.";
                }
            } catch (Exception $e) {
                $errors[] = "Erreur lors de l'envoi de l'email: " . $mail->ErrorInfo;
            }
        } else {
            $errors[] = "Aucun compte trouvé avec cet email.";
        }
    } catch (PDOException $e) {
        $errors[] = "Erreur de base de données: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FundFlow - Réinitialisation du mot de passe</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&family=Playfair+Display:wght@500;600;700&display=swap" rel="stylesheet">
    <style>
        /* Password Reset Specific Styles */
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

        .reset-container {
            width: 100%;
            max-width: 500px;
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

        .reset-container::before {
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

        .reset-title {
            font-size: 2rem;
            font-weight: 700;
            color: white;
            text-align: center;
            margin-bottom: 1.5rem;
            font-family: 'Playfair Display', serif;
        }

        .reset-subtitle {
            color: rgba(255,255,255,0.8);
            text-align: center;
            margin-bottom: 2rem;
            font-size: 1.1rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
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

        .btn-reset {
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

        .btn-reset:hover {
            background: linear-gradient(45deg, var(--primary-dark), var(--primary));
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(67, 97, 238, 0.6);
        }

        .back-link {
            text-align: center;
            margin-top: 2rem;
            color: rgba(255,255,255,0.8);
        }

        .back-link a {
            color: white;
            font-weight: 600;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .back-link a:hover {
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
            .reset-container {
                padding: 1.5rem;
                margin: 1rem;
            }
            
            .reset-title {
                font-size: 1.8rem;
            }
        }
    </style>
</head>
<body>
<div class="background-effect"></div>
<div class="particles-container" id="particles-js"></div>

<div class="reset-container">
    <div class="logo-container">
        <img src="assets/Logo_FundFlow.png" alt="FundFlow Logo" class="brand-logo">
    </div>
    
    <h1 class="reset-title">Réinitialisation du mot de passe</h1>
    <p class="reset-subtitle">Entrez votre email pour recevoir un nouveau mot de passe</p>
    
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
    
    <?php if ($success): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i>
            <p><?= htmlspecialchars($success) ?></p>
        </div>
    <?php else: ?>
        <form method="POST">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" class="form-control" required>
            </div>
            
            <button type="submit" class="btn-reset">
                <i class="fas fa-paper-plane"></i> Envoyer le mot de passe
            </button>
        </form>
    <?php endif; ?>
    
    <div class="back-link">
        <a href="connexion.php"><i class="fas fa-arrow-left"></i> Retour à la connexion</a>
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
</script>
</body>
</html>