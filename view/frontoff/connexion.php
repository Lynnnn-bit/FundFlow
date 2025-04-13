<?php
session_start();
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
            $stmt = $db->prepare("SELECT * FROM utilisateur WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user && ($mdp === $user['mdp'] || password_verify($mdp, $user['mdp']))) {
            /*if ($user && password_verify($mdp, $user['mdp'])) {*/
                // Check account status
                if ($user['status'] === 'inactif') {
                    $errors[] = "Votre compte est inactif. Veuillez contacter l'administrateur.";
                } else {
                    // Set session variables
                    $_SESSION['user'] = [
                        'id' => $user['id_utilisateur'],
                        'nom' => $user['nom'],
                        'prenom' => $user['prenom'],
                        'email' => $user['email'],
                        'role' => $user['role'],
                        'status' => $user['status']
                    ];
                    
                    // Redirect based on role
                    switch ($user['role']) {
                        case 'administrateur':  // New case for administrateurs
                            header("Location: utilisateurmet.php");
                            break;
                        case 'investisseur':
                            header("Location: investisseur/dashboard.php");
                            break;
                        case 'consultant':
                            header("Location: consultant/dashboard.php");
                            break;
                        default:
                            header("Location: profil.php");
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
    <link rel="stylesheet" href="css/sttyleconnexion.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="login-container">
        <div class="logo">
        <img src="assets/logo.png" alt="FundFlow" height="60">
        </div>
        
        <h2>Connexion</h2>
        
        <?php if (isset($_GET['inscription']) && $_GET['inscription'] === 'success'): ?>
            <div class="alert alert-success">
                <p>Inscription réussie! Vous pouvez maintenant vous connecter.</p>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <?php foreach ($errors as $error): ?>
                    <p><?= htmlspecialchars($error) ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <form action="connexion.php" method="POST">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
            </div>
            
            <div class="form-group password-toggle">
                <label for="mdp">Mot de passe</label>
                <input type="password" id="mdp" name="mdp" required>
                <i class="fas fa-eye" id="togglePassword"></i>
            </div>
            
            <button type="submit" class="btn">Se connecter</button>
            
            <div class="register-link">
                Pas encore de compte? <a href="inscription.php">S'inscrire</a>
            </div>
        </form>
    </div>

    
    <script src="js/jsconnexion.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>