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
    <title>Réinitialisation du mot de passe</title>
    <link rel="stylesheet" href="css/sttyleconnexion.css">
</head>
<body>
    <div class="login-container">
        <h2>Mot de passe oublié</h2>
        
        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <?php foreach ($errors as $error): ?>
                    <p><?= htmlspecialchars($error) ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success">
                <p><?= htmlspecialchars($success) ?></p>
            </div>
        <?php else: ?>
            <form method="POST">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" name="email" required>
                </div>
                <button type="submit" class="btn">Envoyer le mot de passe</button>
            </form>
        <?php endif; ?>
        
        <div class="register-link">
            <a href="connexion.php">Retour à la connexion</a>
        </div>
    </div>
</body>
</html>