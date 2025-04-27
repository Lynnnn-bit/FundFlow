<?php
ob_start(); // Start output buffering to prevent premature output

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../controlle/PartenaireController.php';

session_start();

$partenaireController = new PartenaireController();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['submit'])) {
        $partenaire = new Partenaire(
            $_POST['nom'],
            $_POST['email'],
            $_POST['telephone'],
            $_POST['montant'],
            $_POST['description']
        );

        $result = $partenaireController->createPartenaire($partenaire);

        if ($result) {
            $_SESSION['current_partner'] = $_POST['email'];
            header("Location: partner_dashboard.php");
            exit();
        } else {
            $_SESSION['error'] = "Erreur lors de la création";
        }
    } elseif (isset($_POST['update_request'])) {
        $data = [
            'nom' => $_POST['nom'],
            'email' => $_POST['email'],
            'telephone' => $_POST['telephone'],
            'montant' => $_POST['montant'],
            'description' => $_POST['description']
        ];

        if ($partenaireController->updatePartenaire($_POST['id_partenaire'], $data)) {
            $_SESSION['success'] = "Demande mise à jour avec succès!";
            header("Location: partner_dashboard.php");
            exit();
        } else {
            $_SESSION['error'] = "Erreur lors de la mise à jour";
        }
    } elseif (isset($_POST['add_contract'])) {
        $result = $partenaireController->addContractForPartner(
            $_POST['id_partenaire'],
            $_POST['date_deb'],
            $_POST['date_fin'],
            $_POST['terms'],
            'en attente'
        );

        if ($result) {
            $_SESSION['success'] = "Contrat ajouté avec succès!";
        } else {
            $_SESSION['error'] = "Erreur lors de l'ajout du contrat";
        }
        header("Location: partenaire.php");
        exit();
    } elseif (isset($_POST['update_contract'])) {
        $result = $partenaireController->updatePartnerContract(
            $_POST['id_contrat'],
            $_POST['id_partenaire'],
            $_POST['date_deb'],
            $_POST['date_fin'],
            $_POST['terms'],
            $_POST['status']
        );

        if ($result) {
            $_SESSION['success'] = "Contrat mis à jour avec succès!";
        } else {
            $_SESSION['error'] = "Erreur lors de la mise à jour du contrat";
        }
        header("Location: partner_dashboard.php");
        exit();
    } elseif (isset($_POST['delete_contract'])) {
        $result = $partenaireController->deletePartnerContract(
            $_POST['id_contrat'],
            $_POST['id_partenaire']
        );

        if ($result) {
            $_SESSION['success'] = "Contrat supprimé avec succès!";
        } else {
            $_SESSION['error'] = "Erreur lors de la suppression du contrat";
        }
        header("Location: partenaire.php");
        exit();
    }
}

// Handle partner login to view their request
if (isset($_GET['view_request']) && isset($_GET['email'])) {
    $partner = $partenaireController->getPartenaireByEmail($_GET['email']);
    if ($partner && !$partner['is_approved']) {
        $_SESSION['current_partner'] = $partner['email'];
        $_SESSION['current_partner_id'] = $partner['id_partenaire'];
        header("Location: partner_dashboard.php");
        exit();
    } else {
        $_SESSION['error'] = "Demande non trouvée ou déjà approuvée";
        header("Location: partenaire.php");
        exit();
    }
}

// Get partner's contracts
$contracts = [];
if (isset($_SESSION['current_partner_id'])) {
    $contracts = $partenaireController->getPartnerContracts($_SESSION['current_partner_id']);
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
    <title>FundFlow - Devenir Partenaire</title>
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

        .is-invalid {
            border-color: #dc3545 !important;
            box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25) !important;
        }

        .invalid-feedback {
            color: #dc3545;
            font-size: 0.875rem;
            margin-top: 0.25rem;
        }

        #error-container .alert-danger {
            background-color: rgba(220, 53, 69, 0.1);
            border: 1px solid rgba(220, 53, 69, 0.3);
            color: #dc3545;
            padding: 0.75rem;
            border-radius: 0.375rem;
            margin-bottom: 1rem;
        }

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

        .form-actions {
            display: flex;
            justify-content: center;
            margin-top: 1.5rem;
        }

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
        
        .card {
            background: rgba(30, 60, 82, 0.6);
            border-radius: 16px;
            padding: 1.5rem;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.3);
            margin-bottom: 2rem;
            backdrop-filter: blur(5px);
            border: 1px solid rgba(255, 255, 255, 0.1);
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
            <?php if (isset($_SESSION['current_partner'])): ?>
                <a href="partner_dashboard.php"><i class="fas fa-user-circle"></i> Mon Espace</a>
                <a href="logout.php" class="logout"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
            <?php else: ?>
                <a href="/login"><i class="fas fa-sign-in-alt"></i> Connexion</a>
            <?php endif; ?>
        </nav>
    </nav>

    <div class="main-container">
        <?php if (isset($_SESSION['current_partner'])): ?>
            <?php header("Location: partner_dashboard.php"); exit(); ?>
        <?php endif; ?>

        <div class="header-section">
            <h1><i class="fas fa-handshake"></i> Devenir Partenaire</h1>
            <p>Rejoignez notre réseau de partenaires privilégiés</p>
            
            <?php if (isset($_SESSION['current_partner'])): ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>Vous avez déjà soumis une demande. Vous pouvez la modifier ou l'annuler depuis votre espace partenaire.
                </div>
            <?php endif; ?>
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

        <?php if (!isset($_SESSION['current_partner'])): ?>
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
                    <input type="text" class="form-control" id="telephone" name="telephone" required>    
                    <small class="form-text">Format: 8 chiffres (ex: 0612345678)</small>
                </div>
                
                <div class="form-group">
                    <label for="montant" class="form-label">
                        <i class="fas fa-euro-sign"></i> Montant investi (€) *
                    </label>
                    <div class="mb-3">
                        <label for="montantInput" class="form-label">Montant (€)</label>
                        <input type="text" class="form-control" id="montant" name="montant" placeholder="Entrez le montant" required>
                        <div id="montant_error" class="invalid-feedback"></div>
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
        <?php endif; ?>
        
        <div class="card mt-4">
            <div class="card-body">
                <h3><i class="fas fa-question-circle me-2"></i>Suivi de votre demande</h3>
                <p>Pour consulter ou modifier votre demande existante, veuillez entrer votre email :</p>
                
                <form method="GET" class="row g-3">
                    <div class="col-md-8">
                        <input type="email" name="email" class="form-control" placeholder="Votre email" required>
                    </div>
                    <div class="col-md-4">
                        <button type="submit" name="view_request" class="btn btn-primary w-100">
                            <i class="fas fa-search me-2"></i>Voir ma demande
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-body">
                <h3>Proposer un Contrat</h3>
                <form method="POST">
                    <input type="hidden" name="id_partenaire" value="<?= $_SESSION['current_partner_id'] ?? '' ?>">
                    <div class="mb-3">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const form = document.getElementById('partnerForm');
            if (!form) {
                console.error("Form not found!");
                return;
            }

            const errorContainer = document.createElement('div');
            errorContainer.id = 'error-container';
            form.prepend(errorContainer);

            // Telephone formatting
            const telephone = form.querySelector('[name="telephone"]');
            if (telephone) {
                telephone.addEventListener('input', () => {
                    telephone.value = telephone.value.replace(/\D/g, '').slice(0, 8);
                });
            }

            // Amount formatting
            const montant = form.querySelector('[name="montant"]');
            if (montant) {
                montant.addEventListener('input', () => {
                    let value = montant.value.replace(/\D/g, '');
                    value = value ? parseInt(value, 10) : '';
                    montant.value = value === '' ? '' : value.toLocaleString('fr-FR');
                });
            }

            if (form) {
                form.addEventListener('submit', async (e) => {
                    e.preventDefault();
                    clearErrors();

                    const errors = validateForm();
                    if (Object.keys(errors).length > 0) return displayErrors(errors);

                    // Prepare data for submission
                    const formData = new FormData();
                    formData.append('nom', form.nom.value.trim());
                    formData.append('email', form.email.value.trim());
                    formData.append('telephone', form.telephone.value.replace(/\D/g, ''));
                    
                    // Convert French-formatted number to database format
                    const montantValue = parseFloat(
                        form.montant.value.replace(/\s/g, '').replace(',', '.')
                    ).toFixed(2);
                    formData.append('montant', montantValue);
                    
                    formData.append('description', form.description.value.trim());
                    formData.append('submit', '1');

                    try {
                        const response = await fetch(form.action, {
                            method: 'POST',
                            body: formData
                        });
                        
                        if (response.redirected) {
                            window.location.href = response.url;
                        } else {
                            const result = await response.text();
                            if (!response.ok) throw new Error(result);
                        }
                    } catch (error) {
                        console.error('Submission error:', error);
                        alert("Erreur lors de la soumission. Voir la console pour les détails.");
                    }
                });
            }

            function validateForm() {
                const errors = {};
                const values = {
                    nom: form.nom.value.trim(),
                    email: form.email.value.trim(),
                    telephone: form.telephone.value.replace(/\D/g, ''),
                    montant: form.montant.value.replace(/\D/g, ''),
                    description: form.description.value.trim()
                };

                if (!values.nom) errors.nom = "Nom de l'entreprise requis";
                if (!values.email) errors.email = "Email requis";
                else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(values.email)) errors.email = "Email invalide";
                if (!values.telephone) errors.telephone = "Téléphone requis";
                else if (values.telephone.length !== 8) errors.telephone = "8 chiffres requis";
                if (!values.montant) errors.montant = "Montant requis";
                else {
                    const amount = parseInt(values.montant);
                    if (amount < 1000) errors.montant = "Minimum 1 000 €";
                    if (amount > 1000000) errors.montant = "Maximum 1 000 000 €";
                }
                if (!values.description) errors.description = "Description requise";
                else if (values.description.length < 20) errors.description = "20 caractères minimum";

                return errors;
            }

            function displayErrors(errors) {
                let errorHTML = '<div class="alert alert-danger"><ul>';
                Object.entries(errors).forEach(([field, message]) => {
                    errorHTML += `<li>${message}</li>`;
                    const input = form[field];
                    if (input) {
                        input.classList.add('is-invalid');
                        const errorElement = document.createElement('div');
                        errorElement.className = 'invalid-feedback';
                        errorElement.textContent = message;
                        input.parentNode.appendChild(errorElement);
                    }
                });
                errorContainer.innerHTML = errorHTML + '</ul></div>';
            }

            function clearErrors() {
                document.querySelectorAll('.invalid-feedback').forEach(el => el.remove());
                if (form) {
                    Array.from(form.elements).forEach(el => el.classList.remove('is-invalid'));
                }
                errorContainer.innerHTML = '';
            }
        });
    </script>
</body>
</html>
<?php
ob_end_flush(); // Flush the output buffer
?>