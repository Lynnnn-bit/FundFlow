<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../control/PartenaireController.php';

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

// Handle form submission (same as original)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update'])) {
        $data = [
            'nom' => $_POST['nom'],
            'email' => $_POST['email'],
            'telephone' => $_POST['telephone'],
            'montant' => $_POST['montant'],
            'description' => $_POST['description']
        ];

        if ($partenaireController->updatePartenaire($partner['id_partenaire'], $data)) {
            $partner = $partenaireController->getPartenaireByEmail($data['email']);
            $_SESSION['current_partner'] = $partner['email'];
            $_SESSION['partner_data'] = $partner;
            $_SESSION['success'] = "Demande mise à jour avec succès!";
        } else {
            $_SESSION['error'] = "Erreur lors de la mise à jour";
        }

        header("Location: partner_dashboard.php");
        exit();
    } elseif (isset($_POST['cancel'])) {
        if ($partenaireController->deletePartenaire($partner['id_partenaire'])) {
            unset($_SESSION['current_partner']);
            $_SESSION['success'] = "Demande annulée avec succès";
            header("Location: partenaire.php");
            exit();
        } else {
            $_SESSION['error'] = "Erreur lors de l'annulation";
        }
    } elseif (isset($_POST['add_contract'])) {
        $contractData = [
            'id_partenaire' => $partner['id_partenaire'],
            'date_deb' => $_POST['date_deb'],
            'date_fin' => $_POST['date_fin'],
            'terms' => $_POST['terms']
        ];

        // Validate input
        if (empty($contractData['id_partenaire']) || empty($contractData['date_deb']) || empty($contractData['date_fin']) || empty($contractData['terms'])) {
            $_SESSION['error'] = "Tous les champs sont obligatoires.";
        } elseif ($contractData['date_deb'] > $contractData['date_fin']) {
            $_SESSION['error'] = "La date de début ne peut pas être après la date de fin.";
        } else {
            if ($partenaireController->submitContractForApproval($contractData)) {
                $_SESSION['success'] = "Contrat soumis pour approbation!";
            } else {
                $_SESSION['error'] = "Erreur lors de la soumission du contrat.";
            }
        }

        header("Location: partner_dashboard.php");
        exit();
    } elseif (isset($_POST['delete_contract'])) {
        $contractId = $_POST['id_contrat'];

        if ($partenaireController->deleteContract($contractId)) {
            $_SESSION['success'] = "Contrat supprimé avec succès!";
        } else {
            $_SESSION['error'] = "Erreur lors de la suppression du contrat";
        }

        header("Location: partner_dashboard.php");
        exit();
    } elseif (isset($_POST['update_contract'])) {
        $contractData = [
            'id_contrat' => $_POST['id_contrat'],
            'date_deb' => $_POST['date_deb'],
            'date_fin' => $_POST['date_fin'],
            'terms' => $_POST['terms']
        ];

        // Validate input
        if (empty($contractData['id_contrat']) || empty($contractData['date_deb']) || empty($contractData['date_fin']) || empty($contractData['terms'])) {
            $_SESSION['error'] = "Tous les champs sont obligatoires.";
        } elseif ($contractData['date_deb'] > $contractData['date_fin']) {
            $_SESSION['error'] = "La date de début ne peut pas être après la date de fin.";
        } else {
            if ($partenaireController->updateContract($contractData)) {
                $_SESSION['success'] = "Contrat mis à jour avec succès!";
            } else {
                $_SESSION['error'] = "Erreur lors de la mise à jour du contrat.";
            }
        }

        header("Location: partner_dashboard.php");
        exit();
    }
}

// Get partner's contracts
$contracts = $partenaireController->getPartnerContracts($partner['id_partenaire']);

// Display messages
$success_message = $_SESSION['success'] ?? null;
$error_message = $_SESSION['error'] ?? null;
unset($_SESSION['success'], $_SESSION['error']);

// Map status values to their corresponding labels
function getStatusLabel($status) {
    $statusLabels = [
        'en attente' => 'En attente',
        'actif' => 'Actif',
        'expiré' => 'Expiré',
        'approuvé' => 'Approuvé'
    ];
    return $statusLabels[$status] ?? 'Inconnu';
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Espace Partenaire - FundFlow</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&family=Playfair+Display:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <style>
        /* Partner Dashboard Specific Styles */
        .partner-hero {
            display: flex;
            align-items: center;
            gap: 4rem;
            margin: 3rem 0;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-radius: 24px;
            padding: 4rem;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            animation: fadeIn 1s ease-out;
        }

        .partner-header {
            text-align: center;
            flex: 1;
            max-width: 300px;
        }

        .partner-avatar {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            background: rgba(255,255,255,0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 5rem;
            color: var(--primary-light);
            border: 3px solid rgba(255,255,255,0.2);
            margin-bottom: 1.5rem;
        }

        .partner-status {
            margin: 1rem 0;
        }

        .status-badge.partner {
            background: rgba(236, 72, 153, 0.2);
            color: #ec4899;
            border: 1px solid rgba(236, 72, 153, 0.3);
        }

        .partner-details {
            flex: 2;
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 2rem;
        }

        .contracts-section {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-radius: 24px;
            padding: 3rem;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            margin-bottom: 3rem;
        }

        .contract-form {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 2rem;
            margin-bottom: 3rem;
        }

        .contract-form .full-width {
            grid-column: span 2;
        }

        .contract-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 2rem;
        }

        .contract-table th,
        .contract-table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }

        .contract-table th {
            background: rgba(255,255,255,0.05);
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.85rem;
        }

        .contract-table tr:hover {
            background: rgba(255,255,255,0.05);
        }

        .badge {
            display: inline-block;
            padding: 0.4rem 1rem;
            border-radius: 50px;
            font-weight: 600;
            font-size: 0.8rem;
            text-transform: uppercase;
        }

        .badge-pending {
            background: rgba(245, 158, 11, 0.2);
            color: #f59e0b;
            border: 1px solid rgba(245, 158, 11, 0.3);
        }

        .badge-active {
            background: rgba(16, 185, 129, 0.2);
            color: #10b981;
            border: 1px solid rgba(16, 185, 129, 0.3);
        }

        .badge-expired {
            background: rgba(239, 68, 68, 0.2);
            color: #ef4444;
            border: 1px solid rgba(239, 68, 68, 0.3);
        }

        .badge-approved {
            background: rgba(99, 102, 241, 0.2);
            color: #6366f1;
            border: 1px solid rgba(99, 102, 241, 0.3);
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            color: rgba(255,255,255,0.8);
            font-weight: 500;
        }

        .form-control {
            width: 100%;
            padding: 1rem;
            background: rgba(255,255,255,0.1);
            border: 1px solid rgba(255,255,255,0.2);
            border-radius: 12px;
            color: white;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary-light);
            box-shadow: 0 0 0 3px rgba(76, 201, 240, 0.3);
        }

        textarea.form-control {
            min-height: 120px;
        }

        .partner-actions {
            display: flex;
            gap: 1.5rem;
            margin-top: 3rem;
            justify-content: center;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
        }

        .btn i {
            margin-right: 0.5rem;
        }

        .btn-primary {
            background: linear-gradient(to right, var(--primary), var(--primary-dark));
            color: white;
        }

        .btn-primary:hover {
            background: linear-gradient(to right, var(--primary-dark), var(--primary));
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(67, 97, 238, 0.6);
        }

        .btn-danger {
            background: linear-gradient(to right, #e74c3c, #c0392b);
            color: white;
        }

        .btn-danger:hover {
            background: linear-gradient(to right, #c0392b, #a5281b);
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(231, 76, 60, 0.4);
        }

        .btn-sm {
            padding: 0.5rem 1rem;
            font-size: 0.85rem;
        }

        .alert {
            padding: 1rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 1rem;
            background: rgba(255,255,255,0.1);
            box-shadow: var(--shadow-md);
            border-left: 4px solid transparent;
        }

        .alert i {
            font-size: 1.2rem;
        }

        .alert-success {
            border-left-color: var(--secondary);
            color: var(--secondary);
        }

        .alert-error {
            border-left-color: #dc2626;
            color: #dc2626;
        }

        /* Modal Styles */
        .contract-modal-wrapper {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0, 0, 0, 0.7);
            z-index: 1000;
            display: none;
            align-items: center;
            justify-content: center;
        }

        .contract-modal-container {
            width: 90%;
            max-width: 600px;
            animation: modalFadeIn 0.3s ease-out;
        }

        @keyframes modalFadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .contract-modal-content {
            background: rgba(30, 41, 59, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .contract-modal-header {
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: rgba(15, 23, 42, 0.8);
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }

        .contract-modal-title {
            display: flex;
            align-items: center;
            gap: 10px;
            color: #e2e8f0;
        }

        .contract-modal-title h3 {
            margin: 0;
            font-weight: 600;
        }

        .close-modal-btn {
            background: none;
            border: none;
            color: #94a3b8;
            font-size: 24px;
            cursor: pointer;
            transition: color 0.2s;
        }

        .close-modal-btn:hover {
            color: #e2e8f0;
        }

        .contract-modal-body {
            padding: 20px;
        }

        .form-field-group {
            margin-bottom: 20px;
        }

        .form-field-label {
            display: block;
            margin-bottom: 8px;
            color: #94a3b8;
            font-size: 14px;
            font-weight: 500;
        }

        .date-fields {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        .date-field {
            display: flex;
            flex-direction: column;
        }

        .date-field label {
            margin-bottom: 6px;
            color: #cbd5e1;
            font-size: 13px;
        }

        .date-input {
            padding: 10px 12px;
            background: rgba(15, 23, 42, 0.5);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 6px;
            color: #f8fafc;
            font-size: 14px;
        }

        .date-input:focus {
            outline: none;
            border-color: var(--primary-light);
            box-shadow: 0 0 0 2px rgba(76, 201, 240, 0.2);
        }

        .contract-terms {
            width: 100%;
            min-height: 150px;
            padding: 12px;
            background: rgba(15, 23, 42, 0.5);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 6px;
            color: #f8fafc;
            font-size: 14px;
            line-height: 1.5;
            resize: vertical;
        }

        .contract-terms:focus {
            outline: none;
            border-color: var(--primary-light);
            box-shadow: 0 0 0 2px rgba(76, 201, 240, 0.2);
        }

        .contract-modal-footer {
            padding: 15px 20px;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            background: rgba(15, 23, 42, 0.8);
            border-top: 1px solid rgba(255, 255, 255, 0.05);
        }

        .cancel-btn, .submit-btn {
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s;
        }

        .cancel-btn {
            background: rgba(255, 255, 255, 0.05);
            color: #94a3b8;
        }

        .cancel-btn:hover {
            background: rgba(255, 255, 255, 0.1);
            color: #e2e8f0;
        }

        .submit-btn {
            background: var(--primary);
            color: white;
        }

        .submit-btn:hover {
            background: var(--primary-dark);
        }

        @media (max-width: 992px) {
            .partner-hero {
                flex-direction: column;
                text-align: center;
            }

            .partner-header {
                max-width: 100%;
            }

            .partner-details {
                grid-template-columns: 1fr;
                width: 100%;
            }

            .contract-form {
                grid-template-columns: 1fr;
            }

            .contract-form .full-width {
                grid-column: span 1;
            }
        }

        @media (max-width: 576px) {
            .partner-hero {
                padding: 2rem;
            }

            .partner-actions {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
<div class="background-effect"></div>
<div class="particles-container" id="particles-js"></div>

<div class="dashboard-container">
<header class="navbar">
        <div class="logo-container">
            <a href="acceuil2.php">
                <img src="assets/Logo_FundFlow.png" alt="FundFlow Logo" class="brand-logo">
            </a>
        </div>
        
        <div class="nav-links">
            <a href="acceuil2.php" class="nav-link"><i class="fas fa-home"></i> Accueil</a>
            <a href="apropos.html" class="nav-link"><i class="fas fa-info-circle"></i> À propos</a>
            <a href="contact.html" class="nav-link"><i class="fas fa-envelope"></i> Contact</a>
            <a href="events.php" class="nav-link"><i class="fas fa-calendar-alt"></i> Événements</a>
            <a href="partenaire.php" class="nav-link"><i class="fas fa-handshake"></i> Partenariats</a>
            
            <div class="profile-menu-container">
                <button class="profile-menu-btn">Mon compte ▼</button>
                <ul class="profile-menu">
                    <li><a href="profiles.php">Profil</a></li>
                    <?php if ($_SESSION['user']['role'] === 'investisseur'): ?>
                        <li><a href="demands_list.php">Liste des demandes</a></li>
                    <?php endif; ?>
                    <?php if ($_SESSION['user']['role'] === 'entrepreneur'): ?>
                        <li><a href="mesprojet.php">Mes projets</a></li>
                        <li><a href="historique.php">mes demandes</a></li>
                    <?php endif; ?>
                    <li><a href="allconsult.php">Consultation</a></li>
                    <li><a href="connexion.php?logout=1" class="logout">Déconnexion</a></li>
                </ul>
            </div>
        </div>
    </header>

    <div class="page-header animate__animated animate__fadeInDown">
        <h1><i class="fas fa-handshake header-icon"></i> Espace Partenaire</h1>
        <p>Gérez vos informations de partenaire et vos contrats</p>
    </div>

    <?php if ($success_message): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i><?= htmlspecialchars($success_message) ?>
        </div>
    <?php endif; ?>

    <?php if ($error_message): ?>
        <div class="alert alert-error">
            <i class="fas fa-exclamation-circle"></i><?= htmlspecialchars($error_message) ?>
        </div>
    <?php endif; ?>

    <div class="partner-hero">
        <div class="partner-header">
            <div class="partner-avatar">
                <i class="fas fa-handshake"></i>
            </div>
            <h2><?= htmlspecialchars($partner['nom']) ?></h2>
            <div class="partner-status">
                <span class="status-badge partner">
                    Partenaire
                </span>
            </div>
        </div>

        <div class="partner-details">
            <div class="detail-card">
                <div class="detail-label"><i class="fas fa-envelope"></i> Email</div>
                <div class="detail-value"><?= htmlspecialchars($partner['email']) ?></div>
            </div>
            <div class="detail-card">
                <div class="detail-label"><i class="fas fa-phone"></i> Téléphone</div>
                <div class="detail-value"><?= htmlspecialchars($partner['telephone']) ?></div>
            </div>
            <div class="detail-card">
                <div class="detail-label"><i class="fas fa-euro-sign"></i> Montant investi</div>
                <div class="detail-value"><?= htmlspecialchars($partner['montant']) ?> €</div>
            </div>
            <div class="detail-card">
                <div class="detail-label"><i class="fas fa-info-circle"></i> Description</div>
                <div class="detail-value"><?= htmlspecialchars($partner['description']) ?></div>
            </div>
        </div>
    </div>

    <form method="POST" class="contracts-section">
        <h2><i class="fas fa-file-contract"></i> Mettre à jour vos informations</h2>
        <p>Modifiez les détails de votre demande de partenariat</p>
       
        <input type="hidden" name="id_partenaire" value="<?= $partner['id_partenaire'] ?>">
       
        <div class="form-group">
            <label class="form-label">Nom de l'entreprise</label>
            <input type="text" class="form-control" name="nom" value="<?= htmlspecialchars($partner['nom']) ?>" required>
        </div>
       
        <div class="form-group">
            <label class="form-label">Email</label>
            <input type="email" class="form-control" name="email" value="<?= htmlspecialchars($partner['email']) ?>" required>
        </div>
       
        <div class="form-group">
            <label class="form-label">Téléphone</label>
            <input type="text" class="form-control" name="telephone" value="<?= htmlspecialchars($partner['telephone']) ?>" required>
        </div>
       
        <div class="form-group">
            <label class="form-label">Montant investi (€)</label>
            <input type="number" class="form-control" name="montant" value="<?= htmlspecialchars($partner['montant']) ?>" required>
        </div>
       
        <div class="form-group">
            <label class="form-label">Description</label>
            <textarea class="form-control" name="description" rows="4" required><?= htmlspecialchars($partner['description']) ?></textarea>
        </div>
       
        <div class="partner-actions">
            <button type="submit" name="update" class="btn btn-primary">
                <i class="fas fa-save"></i> Mettre à jour
            </button>

            <button type="submit" name="cancel" class="btn btn-danger"
                onclick="return confirm('Êtes-vous sûr de vouloir annuler votre demande?')">
                <i class="fas fa-trash-alt"></i> Annuler la demande
            </button>
        </div>
    </form>

    <div class="contracts-section">
        <h2><i class="fas fa-file-signature"></i> Gérer vos contrats</h2>
        <p>Ajoutez ou modifiez vos contrats de partenariat</p>

        <form method="POST" class="contract-form">
            <input type="hidden" name="id_partenaire" value="<?= $partner['id_partenaire'] ?>">
           
            <div class="form-group">
                <label class="form-label">Date Début</label>
                <input type="date" class="form-control" name="date_deb" required>
            </div>
           
            <div class="form-group">
                <label class="form-label">Date Fin</label>
                <input type="date" class="form-control" name="date_fin" required>
            </div>
           
            <div class="form-group full-width">
                <label class="form-label">Termes</label>
                <textarea class="form-control" name="terms" required></textarea>
            </div>
           
            <button type="submit" name="add_contract" class="btn btn-primary full-width">
                <i class="fas fa-plus-circle"></i> Ajouter un Contrat
            </button>
        </form>

        <h3><i class="fas fa-list"></i> Vos Contrats</h3>
        <table class="contract-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Date Début</th>
                    <th>Date Fin</th>
                    <th>Termes</th>
                    <th>Statut</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($contracts as $contract): ?>
                    <tr>
                        <td><?= htmlspecialchars($contract['id_contrat']) ?></td>
                        <td><?= htmlspecialchars($contract['date_deb']) ?></td>
                        <td><?= htmlspecialchars($contract['date_fin']) ?></td>
                        <td><?= htmlspecialchars($contract['terms']) ?></td>
                        <td>
                            <span class="badge badge-<?=
                                $contract['status'] === 'en attente' ? 'pending' :
                                ($contract['status'] === 'actif' ? 'active' :
                                ($contract['status'] === 'expiré' ? 'expired' : 'approved')) ?>">
                                <?= htmlspecialchars(getStatusLabel($contract['status'])) ?>
                            </span>
                        </td>
                        <td>
                            <button type="button" class="btn btn-primary btn-sm"
                                    onclick="openContractModal('editContractModal<?= $contract['id_contrat'] ?>')">
                                <i class="fas fa-edit"></i> Modifier
                            </button>
                           
                            <?php if (in_array($contract['status'], ['en attente', 'actif'])): ?>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="id_contrat" value="<?= $contract['id_contrat'] ?>">
                                    <button type="submit" name="delete_contract" class="btn btn-danger btn-sm"
                                        onclick="return confirm('Voulez-vous vraiment supprimer ce contrat?')">
                                        <i class="fas fa-trash"></i> Supprimer
                                    </button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>

                    <!-- Update Contract Modal -->
                    <div class="contract-modal-wrapper" id="editContractModal<?= $contract['id_contrat'] ?>" aria-hidden="true">
                        <div class="contract-modal-container">
                            <div class="contract-modal-content">
                                <form method="POST" class="contract-form">
                                    <div class="contract-modal-header">
                                        <div class="contract-modal-title">
                                            <i class="fas fa-edit"></i>
                                            <h3>Édition du contrat #<?= $contract['id_contrat'] ?></h3>
                                        </div>
                                        <button type="button" class="close-modal-btn" aria-label="Fermer"
                                                onclick="closeContractModal('editContractModal<?= $contract['id_contrat'] ?>')">
                                            &times;
                                        </button>
                                    </div>
                                   
                                    <div class="contract-modal-body">
                                        <input type="hidden" name="id_contrat" value="<?= $contract['id_contrat'] ?>">
                                       
                                        <div class="form-field-group">
                                            <label class="form-field-label">Dates du contrat</label>
                                            <div class="date-fields">
                                                <div class="form-group">
                                                    <label>Début</label>
                                                    <input type="date" name="date_deb"
                                                           value="<?= date('Y-m-d', strtotime($contract['date_deb'])) ?>"
                                                           class="form-control" required>
                                                </div>
                                                <div class="form-group">
                                                    <label>Fin</label>
                                                    <input type="date" name="date_fin"
                                                           value="<?= date('Y-m-d', strtotime($contract['date_fin'])) ?>"
                                                           class="form-control" required>
                                                </div>
                                            </div>
                                        </div>
                                       
                                        <div class="form-group">
                                            <label class="form-label">Termes du contrat</label>
                                            <textarea name="terms" class="form-control" required><?= htmlspecialchars($contract['terms']) ?></textarea>
                                        </div>
                                    </div>
                                   
                                    <div class="contract-modal-footer">
                                        <button type="button" class="btn btn-secondary"
                                                onclick="closeContractModal('editContractModal<?= $contract['id_contrat'] ?>')">
                                            <i class="fas fa-times"></i> Annuler
                                        </button>
                                        <button type="submit" name="update_contract" class="btn btn-primary">
                                            <i class="fas fa-save"></i> Enregistrer
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
                <?php if (empty($contracts)): ?>
                    <tr>
                        <td colspan="6" class="text-center">Aucun contrat trouvé</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<footer class="footer">
  <div class="footer-container">
    <div class="footer-col logo-col">
      <img src="assets/Logo_FundFlow.png" alt="Company Logo" class="footer-logo">
      <p class="footer-description">Plateforme de financement collaboratif</p>
    </div>
    <div class="footer-col links-col">
      <h4>Liens Rapides</h4>
      <ul>
        <li><a href="financemet.php">Accueil</a></li>
        <li><a href="#">À propos</a></li>
        <li><a href="#">Services</a></li>
        <li><a href="#">Blog</a></li>
        <li><a href="#">Contact</a></li>
      </ul>
    </div>
    <div class="footer-col contact-col">
      <h4>Contactez-nous</h4>
      <p>123 Rue de Finance, Paris 75001</p>
      <p>Email: <a href="mailto:contact@fundflow.com">contact@fundflow.com</a></p>
      <p>Tél: +33 1 23 45 67 89</p>
    </div>
    <div class="footer-col social-col">
      <h4>Suivez-nous</h4>
      <div class="social-icons">
        <a href="#" aria-label="Facebook"><i class="fab fa-facebook"></i></a>
        <a href="#" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
        <a href="#" aria-label="LinkedIn"><i class="fab fa-linkedin"></i></a>
        <a href="#" aria-label="Instagram"><i class="fas fa-instagram"></i></a>
      </div>
    </div>
  </div>
  <div class="footer-legal">
    <a href="#">Politique de confidentialité</a> |
    <a href="#">Conditions d'utilisation</a> |
    <span>&copy; 2025 FundFlow. Tous droits réservés.</span>
  </div>
</footer>

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

function handleMenu(select) {
    const value = select.value;
    if (value === 'logout') {
        window.location.href = 'connexion.php?logout=1';
    } else if (value) {
        window.location.href = value + '.php';
    }
    select.value = ''; // Réinitialiser la sélection
}

// Fonctions pour gérer la modale
function openContractModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }
}

function closeContractModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = '';
    }
}

// Gestion des clics
document.addEventListener('DOMContentLoaded', function() {
    // Boutons d'ouverture
    document.querySelectorAll('[onclick^="openContractModal"]').forEach(btn => {
        btn.addEventListener('click', function() {
            const modalId = this.getAttribute('onclick').match(/'([^']+)'/)[1];
            openContractModal(modalId);
        });
    });
   
    // Boutons de fermeture
    document.querySelectorAll('.close-modal-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const modal = this.closest('.contract-modal-wrapper');
            closeContractModal(modal.id);
        });
    });
   
    // Fermer en cliquant à l'extérieur
    document.querySelectorAll('.contract-modal-wrapper').forEach(modal => {
        modal.addEventListener('click', function(e) {
            if (e.target === this) {
                closeContractModal(this.id);
            }
        });
    });

    // Telephone formatting
    document.querySelector('input[name="telephone"]')?.addEventListener('input', function() {
        this.value = this.value.replace(/\D/g, '').slice(0, 8);
    });

    // Amount formatting
    document.querySelector('input[name="montant"]')?.addEventListener('input', function () {
        let value = this.value.replace(/\D/g, ''); // Allow only digits
        value = parseInt(value, 10) || 0; // Parse as integer or default to 0
        if (value < 1000) {
            this.classList.add('is-invalid'); // Add invalid class if less than 1000
        } else {
            this.classList.remove('is-invalid'); // Remove invalid class if valid
        }
        this.value = value; // Set the cleaned value back to the input
    });
});

// Form validation
document.addEventListener('DOMContentLoaded', function () {
    const updateForm = document.querySelector('form');
    const nomInput = document.querySelector('input[name="nom"]');
    const emailInput = document.querySelector('input[name="email"]');
    const telephoneInput = document.querySelector('input[name="telephone"]');
    const montantInput = document.querySelector('input[name="montant"]');
    const descriptionInput = document.querySelector('textarea[name="description"]');

    // Helper function to show error
    function showError(input, message) {
        const formGroup = input.closest('.form-group');
        const errorElement = formGroup.querySelector('.error-message');
        if (!errorElement) {
            const errorDiv = document.createElement('div');
            errorDiv.className = 'error-message text-danger mt-1';
            errorDiv.textContent = message;
            formGroup.appendChild(errorDiv);
        } else {
            errorElement.textContent = message;
        }
        input.classList.add('is-invalid');
    }

    // Helper function to clear error
    function clearError(input) {
        const formGroup = input.closest('.form-group');
        const errorElement = formGroup.querySelector('.error-message');
        if (errorElement) {
            errorElement.remove();
        }
        input.classList.remove('is-invalid');
    }

    // Validation functions
    function validateNom() {
        const nom = nomInput.value.trim();
        if (nom.length < 2 || nom.length > 50) {
            showError(nomInput, 'Le nom doit contenir entre 2 et 50 caractères.');
            return false;
        }
        clearError(nomInput);
        return true;
    }

    function validateEmail() {
        const email = emailInput.value.trim();
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            showError(emailInput, 'Veuillez entrer une adresse email valide.');
            return false;
        }
        clearError(emailInput);
        return true;
    }

    function validateTelephone() {
        const telephone = telephoneInput.value.trim();
        const telephoneRegex = /^[0-9]{8}$/;
        if (!telephoneRegex.test(telephone)) {
            showError(telephoneInput, 'Le téléphone doit contenir exactement 8 chiffres.');
            return false;
        }
        clearError(telephoneInput);
        return true;
    }

    function validateMontant() {
        const montant = parseFloat(montantInput.value.replace(/\s/g, '').replace(',', '.'));
        if (isNaN(montant) || montant <= 0) {
            showError(montantInput, 'Le montant doit être un nombre positif.');
            return false;
        }
        clearError(montantInput);
        return true;
    }

    function validateDescription() {
        const description = descriptionInput.value.trim();
        if (description.length < 20 || description.length > 500) {
            showError(descriptionInput, 'La description doit contenir entre 10 et 500 caractères.');
            return false;
        }
        clearError(descriptionInput);
        return true;
    }

    // Attach blur event listeners for real-time validation
    nomInput.addEventListener('blur', validateNom);
    emailInput.addEventListener('blur', validateEmail);
    telephoneInput.addEventListener('blur', validateTelephone);
    montantInput.addEventListener('blur', validateMontant);
    descriptionInput.addEventListener('blur', validateDescription);

    // Form submission validation
    updateForm.addEventListener('submit', function (e) {
        const isNomValid = validateNom();
        const isEmailValid = validateEmail();
        const isTelephoneValid = validateTelephone();
        const isMontantValid = validateMontant();
        const isDescriptionValid = validateDescription();

        if (!isNomValid || !isEmailValid || !isTelephoneValid || !isMontantValid || !isDescriptionValid) {
            e.preventDefault(); // Prevent form submission if validation fails
            alert('Veuillez corriger les erreurs avant de soumettre le formulaire.');
        }
    });
});
</script>
</body>
</html>