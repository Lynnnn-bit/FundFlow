<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../controlle/PartenaireController.php';

session_start();

$partenaireController = new PartenaireController();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
    try {
        $nom = htmlspecialchars($_POST['nom']);
        $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
        $telephone = preg_replace('/[^0-9]/', '', $_POST['telephone']);
        $montant = floatval($_POST['montant']);
        $description = htmlspecialchars($_POST['description']);

        // Server-side validation
        if (!$email) throw new Exception("Email invalide");
        if (strlen($telephone) !== 8) throw new Exception("Téléphone invalide");
        if ($montant < 1000 || $montant > 1000000) throw new Exception("Montant invalide");

        $partenaire = new Partenaire($nom, $email, $telephone, $montant, $description);
        
        if ($partenaireController->createPartenaire($partenaire)) {
            $_SESSION['success'] = "Demande envoyée avec succès!";
        } else {
            throw new Exception("Erreur lors de la création");
        }
        
        header("Location: partenaire.php");
        exit();
    } catch (Exception $e) {
        $error_message = $e->getMessage();
    }
}

// Display messages
$success_message = $_SESSION['success'] ?? null;
unset($_SESSION['success']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FundFlow - Devenir Partenaire</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Base Styles */
        body {
            margin: 0;
            font-family: 'Inter', sans-serif;
            background: linear-gradient(to right, #0f2027, #203a43, #2c5364);
            color: white;
            min-height: 100vh;
        }

        /* Navbar Styles */
        .navbar {
            background-color: rgba(15, 32, 39, 0.9);
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.3);
        }

        .logo-container {
            display: flex;
            align-items: center;
        }

        .brand-name {
            color: white;
            font-size: 1.5rem;
            font-weight: 600;
            margin-left: 10px;
            background: linear-gradient(to right, #00d09c, #1abc9c);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        /* Main Container */
        .main-container {
            padding: 2rem;
            max-width: 800px;
            margin: 0 auto;
        }

        .header-section {
            margin-bottom: 2rem;
            text-align: center;
        }

        .header-section h1 {
            margin-bottom: 1.5rem;
            font-size: 2rem;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
        }

        .header-section h1 i {
            margin-right: 15px;
            color: #00d09c;
        }

        /* Form Styles */
        .finance-form-container {
            background: rgba(30, 60, 82, 0.6);
            border-radius: 16px;
            padding: 2rem;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.3);
            margin-bottom: 2rem;
            backdrop-filter: blur(5px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .finance-form-container h2 {
            margin-bottom: 1.5rem;
            font-size: 1.5rem;
            text-align: center;
            color: white;
        }

        .form-group {
            margin-bottom: 1.2rem;
        }

        .form-label {
            display: block;
            margin-bottom: 0.6rem;
            font-weight: 500;
            color: #cbd5e1;
            font-size: 0.9rem;
        }

        .form-label i {
            margin-right: 8px;
            color: #00d09c;
        }

        .form-control {
            width: 100%;
            padding: 0.8rem 1rem;
            border: none;
            border-radius: 8px;
            font-size: 0.95rem;
            transition: all 0.3s;
            background-color: rgba(46, 79, 102, 0.8);
            color: white;
        }

        .form-control:focus {
            outline: none;
            box-shadow: 0 0 0 2px rgba(0, 208, 156, 0.3);
            background-color: rgba(46, 79, 102, 1);
        }

        /* Alert Messages */
        .alert {
            padding: 0.8rem 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
        }

        .alert-success {
            background-color: rgba(46, 204, 113, 0.2);
            color: #d4edda;
            border: 1px solid rgba(46, 204, 113, 0.3);
        }

        .alert-danger {
            background-color: rgba(231, 76, 60, 0.2);
            color: #f8d7da;
            border: 1px solid rgba(231, 76, 60, 0.3);
        }

        /* Button Styles */
        .btn {
            padding: 0.8rem 1.5rem;
            border: none;
            border-radius: 12px;
            font-size: 0.95rem;
            cursor: pointer;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
        }

        .btn i {
            margin-right: 8px;
        }

        .btn-primary {
            background: linear-gradient(to right, #00d09c, #1abc9c);
            color: white;
        }

        .btn-primary:hover {
            background: linear-gradient(to right, #1abc9c, #3498db);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 208, 156, 0.3);
        }

        .form-actions {
            display: flex;
            justify-content: center;
            margin-top: 1.5rem;
        }

        /* Responsive Adjustments */
        @media (max-width: 768px) {
            .main-container {
                padding: 1rem;
            }
            
            .finance-form-container {
                padding: 1.5rem;
            }
            
            .header-section h1 {
                font-size: 1.5rem;
            }
        }

        /* Custom form elements */
        .input-group-text {
            background-color: rgba(46, 79, 102, 0.8);
            color: #cbd5e1;
            border: none;
        }

        .form-text {
            color: #cbd5e1;
            font-size: 0.8rem;
            margin-top: 0.25rem;
        }

        textarea.form-control {
            min-height: 120px;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar">
        <div class="logo-container">
            <i class="fas fa-handshake fa-lg" style="color: #00d09c;"></i>
            <span class="brand-name">FundFlow</span>
        </div>
        <nav>
            <a href="/"><i class="fas fa-home"></i> Accueil</a>
            <a href="/partenaires"><i class="fas fa-users"></i> Partenaires</a>
            <a href="/login"><i class="fas fa-sign-in-alt"></i> Connexion</a>
        </nav>
    </nav>

    <div class="main-container">
        <div class="header-section">
            <h1><i class="fas fa-handshake"></i> Devenir Partenaire</h1>
            <p>Rejoignez notre réseau de partenaires privilégiés</p>
        </div>
        
        <?php if ($success_message): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle me-2"></i><?= $success_message ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle me-2"></i><?= $error_message ?>
            </div>
        <?php endif; ?>

        <div class="finance-form-container">
            <h2><i class="fas fa-file-signature"></i> Formulaire de Partenariat</h2>
            
            <form method="POST" id="partnerForm">
                <div class="form-group">
                    <label for="nom" class="form-label">
                        <i class="fas fa-building"></i> Nom de l'entreprise *
                    </label>
                    <input type="text" class="form-control" id="nom" name="nom" required>
                </div>
                
                <div class="form-group">
                    <label for="email" class="form-label">
                        <i class="fas fa-envelope"></i> Email *
                    </label>
                    <input type="email" class="form-control" id="email" name="email" required>
                </div>
                
                <div class="form-group">
                    <label for="telephone" class="form-label">
                        <i class="fas fa-phone"></i> Téléphone *
                    </label>
                    <input type="tel" class="form-control" id="telephone" name="telephone" 
                           pattern="[0-9]{8}" title="8 chiffres requis" required>
                    <small class="form-text">Format: 8 chiffres (ex: 0612345678)</small>
                </div>
                
                <div class="form-group">
                    <label for="montant" class="form-label">
                        <i class="fas fa-euro-sign"></i> Montant investi (€) *
                    </label>
                    <div class="input-group">
                        <input type="number" class="form-control" id="montant" name="montant" 
                               min="1000" max="1000000" value="1000" step="100" required>
                        <span class="input-group-text">€</span>
                    </div>
                    <small class="form-text">Entre 1,000€ et 1,000,000€</small>
                </div>
                
                <div class="form-group">
                    <label for="description" class="form-label">
                        <i class="fas fa-align-left"></i> Description *
                    </label>
                    <textarea class="form-control" id="description" name="description" rows="4" required></textarea>
                    <small class="form-text">Décrivez votre entreprise et vos motivations</small>
                </div>
                
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                
                <div class="form-actions">
                    <button type="submit" name="submit" class="btn btn-primary">
                        <i class="fas fa-paper-plane"></i> Soumettre la demande
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/form-validation.js"></script>
</body>
</html>