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

    // Validation
    if (empty($nom)) $errors[] = "Le nom est obligatoire";
    if (empty($prenom)) $errors[] = "Le prénom est obligatoire";
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Email invalide";
    if (empty($mdp) || strlen($mdp) < 8) $errors[] = "Le mot de passe doit contenir au moins 8 caractères";
    if ($mdp !== $confirmation_mdp) $errors[] = "Les mots de passe ne correspondent pas";
    if (!in_array($role, ['consultant', 'investisseur', 'admin'])) $errors[] = "Rôle invalide";

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
                    (id_utilisateur, nom, prenom, email, mdp, role, status, adresse, date_creation, tel)
                    VALUES 
                    (?, ?, ?, ?, ?, ?, 'actif', ?, CURDATE(), ?)
                ");
                
                $success = $stmt->execute([
                    $newId,
                    $nom,
                    $prenom,
                    $email,
                    $hashed_password,
                    $role,
                    $adresse,
                    $tel
                ]);
                
                if ($success) {
                    header("Location: connexion.php?inscription=success");
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
    <link rel="stylesheet" href="css/styleincription.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
   
</head>
<body>
    <div class="container">
        <div class="registration-container">
            <div class="registration-image">
                <h1>Rejoignez FundFlow</h1>
                <p>La plateforme de financement pour vos projets innovants</p>
                <ul style="margin-top: 2rem; list-style-type: none;">
                    <li style="margin-bottom: 1rem;"><i class="fas fa-check-circle" style="margin-right: 0.5rem;"></i> Accès à des investisseurs</li>
                    <li style="margin-bottom: 1rem;"><i class="fas fa-check-circle" style="margin-right: 0.5rem;"></i> Gestion simplifiée</li>
                    <li><i class="fas fa-check-circle" style="margin-right: 0.5rem;"></i> Support personnalisé</li>
                </ul>
            </div>
            
            <div class="registration-form">
                <div class="logo">
                <img src="assets/logo.png" alt="FundFlow" height="60">
                    <p>Créez votre compte</p>
                </div>
                
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-error">
                        <?php foreach ($errors as $error): ?>
                            <p><?= htmlspecialchars($error) ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                
                <form action="inscription.php" method="POST" id="registrationForm">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="nom">Nom *</label>
                            <input type="text" id="nom" name="nom" value="<?= htmlspecialchars($_POST['nom'] ?? '') ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="prenom">Prénom *</label>
                            <input type="text" id="prenom" name="prenom" value="<?= htmlspecialchars($_POST['prenom'] ?? '') ?>" >
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email *</label>
                        <input type="email" id="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="role">Rôle *</label>
                        <select id="role" name="role" >
                            <option value="">Choisir Un Role</option>
                            <option value="consultant" <?= ($_POST['role'] ?? 'consultant') === 'consultant' ? 'selected' : '' ?>>Consultant</option>
                            <option value="investisseur" <?= ($_POST['role'] ?? '') === 'investisseur' ? 'selected' : '' ?>>Investisseur</option>
                            <option value="admin" <?= ($_POST['role'] ?? '') === 'admin' ? 'selected' : '' ?>>Administrateur</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="mdp">Mot de passe *</label>
                        <input type="password" id="mdp" name="mdp" >
                        <div class="password-strength" id="passwordStrength">Force: <span id="strengthText">Faible</span></div>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirmation_mdp">Confirmer le mot de passe *</label>
                        <input type="password" id="confirmation_mdp" name="confirmation_mdp" >
                    </div>
                    
                    <div class="form-group">
                        <label for="adresse">Adresse</label>
                        <input type="text" id="adresse" name="adresse" value="<?= htmlspecialchars($_POST['adresse'] ?? '') ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="tel">Téléphone</label>
                        <input type="tel" id="tel" name="tel" value="<?= htmlspecialchars($_POST['tel'] ?? '') ?>">
                    </div>
                    
                    <button type="submit" class="btn">S'inscrire</button>
                    
                    <div class="login-link">
                        Déjà un compte? <a href="connexion.php">Se connecter</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

   
    <script src="js/jsincri.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>