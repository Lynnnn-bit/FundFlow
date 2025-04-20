<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../controlle/PartenaireController.php';

session_start();

// Redirect if not logged in
if (!isset($_SESSION['current_partner'])) {
    header("Location: partenaire.php");
    exit();
}

$partenaireController = new PartenaireController();
$partner = $partenaireController->getPartenaireByEmail($_SESSION['current_partner']);

// Redirect if partner not found
if (!$partner) {
    unset($_SESSION['current_partner']);
    header("Location: partenaire.php");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update'])) {
        $data = [
            'nom' => $_POST['nom'],
            'email' => $_POST['email'],
            'telephone' => $_POST['telephone'],
            'montant' => $_POST['montant'],
            'description' => $_POST['description']
        ];
        
        // Update partner info
        if ($partenaireController->updatePartenaire($partner['id_partenaire'], $data)) {
            // Refresh partner data after update
            $partner = $partenaireController->getPartenaireByEmail($_POST['email']);
            $_SESSION['current_partner'] = $_POST['email']; // Update session email if changed
            $_SESSION['success'] = "Demande mise à jour avec succès!";
        } else {
            $_SESSION['error'] = "Erreur lors de la mise à jour";
        }
        
        // Redirect to prevent form resubmission
        header("Location: partner_dashboard.php");
        exit();
    }
    elseif (isset($_POST['cancel'])) {
        if ($partenaireController->deletePartenaire($partner['id_partenaire'])) {
            unset($_SESSION['current_partner']);
            $_SESSION['success'] = "Demande annulée avec succès";
            header("Location: partenaire.php");
            exit();
        } else {
            $_SESSION['error'] = "Erreur lors de l'annulation";
        }
    }
}

// Display messages
$success_message = $_SESSION['success'] ?? null;
$error_message = $_SESSION['error'] ?? null;
unset($_SESSION['success'], $_SESSION['error']);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Espace Partenaire - FundFlow</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            margin: 0;
            font-family: 'Inter', sans-serif;
            background: linear-gradient(to right, #0f2027, #203a43, #2c5364);
            color: white;
            min-height: 100vh;
        }

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

        .main-container {
            padding: 2rem;
            max-width: 800px;
            margin: 0 auto;
        }

        .header-section {
            margin-bottom: 2rem;
        }

        .header-section h1 {
            margin-bottom: 1.5rem;
            font-size: 1.8rem;
            display: flex;
            align-items: center;
            color: white;
        }

        .header-section h1 i {
            margin-right: 15px;
            color: #00d09c;
        }

        .card {
            background: rgba(30, 60, 82, 0.6);
            border-radius: 16px;
            padding: 2rem;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.3);
            margin-bottom: 2rem;
            backdrop-filter: blur(5px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

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

        .btn-danger {
            background: linear-gradient(to right, #e74c3c, #c0392b);
            color: white;
        }

        .btn-danger:hover {
            background: linear-gradient(to right, #c0392b, #a5281b);
        }

        .form-label {
            display: block;
            margin-bottom: 0.6rem;
            font-weight: 500;
            color: #cbd5e1;
            font-size: 0.9rem;
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

        textarea.form-control {
            min-height: 120px;
        }

        .text-muted {
            color: #adb5bd !important;
        }

        @media (max-width: 768px) {
            .main-container {
                padding: 1rem;
            }
            
            .card {
                padding: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="logo-container">
            <i class="fas fa-handshake fa-lg" style="color: #00d09c;"></i>
            <span class="brand-name">FundFlow</span>
        </div>
        <nav>
            <a href="/"><i class="fas fa-home"></i> Accueil</a>
            <a href="/partenaires"><i class="fas fa-users"></i> Partenaires</a>
            <a href="logout.php" class="logout"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
        </nav>
    </nav>

    <div class="main-container">
        <div class="header-section">
            <h1><i class="fas fa-user-circle me-2"></i>Votre Demande de Partenariat</h1>
            <p class="text-muted">Connecté en tant que: <?= htmlspecialchars($partner['email']) ?></p>
        </div>
        
        <?php if ($success_message): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($success_message) ?>
            </div>
        <?php endif; ?>
        
        <?php if ($error_message): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle me-2"></i><?= htmlspecialchars($error_message) ?>
            </div>
        <?php endif; ?>

        <div class="card shadow-lg">
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="id_partenaire" value="<?= $partner['id_partenaire'] ?>">
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Nom de l'entreprise</label>
                            <input type="text" class="form-control" name="nom" value="<?= htmlspecialchars($partner['nom']) ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email" value="<?= htmlspecialchars($partner['email']) ?>" required>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Téléphone</label>
                            <input type="text" class="form-control" name="telephone" value="<?= htmlspecialchars($partner['telephone']) ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Montant investi (€)</label>
                            <input type="text" class="form-control" name="montant" value="<?= htmlspecialchars($partner['montant']) ?>" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" rows="4" required><?= htmlspecialchars($partner['description']) ?></textarea>
                    </div>
                    <!-- Add this button in your card-body div -->
                    <div class="mb-3">
                        <a href="partenaire.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Retour au formulaire
                        </a>
                    </div>
                    
                    <div class="d-flex justify-content-between">
                        <button type="submit" name="update" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Mettre à jour
                        </button>
                        
                        <button type="submit" name="cancel" class="btn btn-danger" 
                            onclick="return confirm('Êtes-vous sûr de vouloir annuler votre demande?')">
                            <i class="fas fa-trash-alt me-2"></i>Annuler la demande
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Telephone formatting
        document.querySelector('input[name="telephone"]')?.addEventListener('input', function() {
            this.value = this.value.replace(/\D/g, '').slice(0, 8);
        });

        // Amount formatting
        document.querySelector('input[name="montant"]')?.addEventListener('input', function() {
            let value = this.value.replace(/\D/g, '');
            value = value ? parseInt(value, 10) : '';
            this.value = value === '' ? '' : value.toLocaleString('fr-FR');
        });
    </script>
</body>
</html>