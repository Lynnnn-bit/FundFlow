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
    <!-- Dans le <head> -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<!-- Avant la fermeture du </body> -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
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
        /* Ajoutez ceci dans votre balise <style> */
.modal-content {
    background-color: rgba(30, 41, 59, 0.95);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.1);
}

.modal-backdrop {
    background-color: rgba(0, 0, 0, 0.7);
}

.btn-close-white {
    filter: invert(1) brightness(100%);
}

.form-control.bg-secondary:focus {
    background-color: #3a4a6b !important;
    border-color: #00d09c;
    box-shadow: 0 0 0 0.25rem rgba(0, 208, 156, 0.25);
}
/* Styles pour la nouvelle modale */
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
  background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
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
  border-color: #38bdf8;
  box-shadow: 0 0 0 2px rgba(56, 189, 248, 0.2);
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
  border-color: #38bdf8;
  box-shadow: 0 0 0 2px rgba(56, 189, 248, 0.2);
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
  background: #38bdf8;
  color: #082f49;
}

.submit-btn:hover {
  background: #0ea5e9;
}

/* Icônes (utilisez Font Awesome ou un système similaire) */
.icon-edit:before { content: "\f044"; font-family: "Font Awesome"; }
.icon-cancel:before { content: "\f00d"; font-family: "Font Awesome"; }
.icon-save:before { content: "\f0c7"; font-family: "Font Awesome"; }

/* Table Styles for "Vos Contrats" Section */
.table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 1rem;
    background-color: rgba(30, 60, 82, 0.8);
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
}

.table th, .table td {
    padding: 1rem;
    text-align: left;
    color: #e2e8f0;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.table td:first-child, /* ID column */
.table td:nth-child(2), /* Date Début column */
.table td:nth-child(3), /* Date Fin column */
.table td:nth-child(4) { /* Termes column */
    color: #1abc9c; /* Brighter color for better readability */
    font-weight: 600; /* Slightly bolder text */
    background-color: rgba(26, 188, 156, 0.1); /* Subtle background highlight */
    border-radius: 4px; /* Rounded corners for the highlight */
    padding: 0.8rem; /* Add padding for better spacing */
}

.table th {
    background-color: rgba(15, 32, 39, 0.9);
    font-weight: 600;
    text-transform: uppercase;
    font-size: 0.85rem;
}

.table tbody tr:hover {
    background-color: rgba(46, 79, 102, 0.6);
    transition: background-color 0.3s ease;
}

.table tbody tr:last-child td {
    border-bottom: none;
}

/* Badge Styles */
.badge {
    display: inline-block;
    padding: 0.4rem 0.8rem;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: capitalize;
}

.badge-success {
    background: linear-gradient(to right, #00d09c, #2ecc71);
    color: white;
}

.badge-warning {
    background: linear-gradient(to right, #f39c12, #e67e22);
    color: white;
}

.badge-danger {
    background: linear-gradient(to right, #e74c3c, #c0392b);
    color: white;
}

.badge-secondary {
    background: linear-gradient(to right, #95a5a6, #7f8c8d);
    color: white;
}

/* Button Styles */
.btn-sm {
    padding: 0.4rem 0.8rem;
    font-size: 0.8rem;
    border-radius: 8px;
}

.btn-primary {
    background: linear-gradient(to right, #3498db, #2980b9);
    color: white;
}

.btn-primary:hover {
    background: linear-gradient(to right, #2980b9, #1abc9c);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(52, 152, 219, 0.3);
}

.btn-danger {
    background: linear-gradient(to right, #e74c3c, #c0392b);
    color: white;
}

.btn-danger:hover {
    background: linear-gradient(to right, #c0392b, #a5281b);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(231, 76, 60, 0.3);
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
                            <input type="text" class="form-control" name="nom" value="<?= htmlspecialchars($partner['nom']) ?>" >
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email" value="<?= htmlspecialchars($partner['email']) ?>" >
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Téléphone</label>
                            <input type="text" class="form-control" name="telephone" value="<?= htmlspecialchars($partner['telephone']) ?>" >
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Montant investi (€)</label>
                            <input type="text" class="form-control" name="montant" value="<?= htmlspecialchars($partner['montant']) ?>" >
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" rows="4" ><?= htmlspecialchars($partner['description']) ?></textarea>
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

        <div class="card mt-4">
            <div class="card-body">
                <h3><i class="fas fa-file-signature me-2"></i>Gérer vos Contrats</h3>

                <!-- Add Contract Form -->
                <form method="POST" class="mb-4">
                    <input type="hidden" name="id_partenaire" value="<?= $partner['id_partenaire'] ?>">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="date_deb" class="form-label">Date Début</label>
                            <input type="date" class="form-control" id="date_deb" name="date_deb" >
                        </div>
                        <div class="col-md-6">
                            <label for="date_fin" class="form-label">Date Fin</label>
                            <input type="date" class="form-control" id="date_fin" name="date_fin" >
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="terms" class="form-label">Termes</label>
                        <textarea class="form-control" id="terms" name="terms"></textarea>
                    </div>
                    <button type="submit" name="add_contract" class="btn btn-primary">
                        <i class="fas fa-plus-circle me-2"></i>Ajouter un Contrat
                    </button>
                </form>

                <!-- List of Contracts -->
                <h4>Vos Contrats</h4>
                <table class="table table-striped">
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
                                    <span class="badge bg-<?= $contract['status'] === 'actif' ? 'success' : ($contract['status'] === 'en attente' ? 'warning text-dark' : ($contract['status'] === 'expiré' ? 'danger' : 'secondary')) ?>">
                                        <?= htmlspecialchars(getStatusLabel($contract['status'])) ?>
                                    </span>
                                </td>
                                <td>
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="id_contrat" value="<?= $contract['id_contrat'] ?>">
                                        <button type="button" class="btn btn-primary btn-sm" 
                                                data-bs-toggle="modal" 
                                                data-modal-target="editContractModal<?= $contract['id_contrat'] ?>">
                                            <i class="fas fa-edit"></i> Modifier
                                        </button>
                                    </form>
                                    <?php if (in_array($contract['status'], ['en attente', 'actif'])): ?>
                                        <form method="POST" class="d-inline">
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
                                                    <i class="icon-edit"></i>
                                                    <h3>Édition du contrat #<?= $contract['id_contrat'] ?></h3>
                                                </div>
                                                <button type="button" class="close-modal-btn" aria-label="Fermer">
                                                    &times;
                                                </button>
                                            </div>
                                            
                                            <div class="contract-modal-body">
                                                <input type="hidden" name="id_contrat" value="<?= $contract['id_contrat'] ?>">
                                                
                                                <div class="form-field-group">
                                                    <label class="form-field-label">Dates du contrat</label>
                                                    <div class="date-fields">
                                                        <div class="date-field">
                                                            <label>Début</label>
                                                            <input type="date" name="date_deb" 
                                                                   value="<?= date('Y-m-d', strtotime($contract['date_deb'])) ?>" 
                                                                   class="date-input" >
                                                        </div>
                                                        <div class="date-field">
                                                            <label>Fin</label>
                                                            <input type="date" name="date_fin" 
                                                                   value="<?= date('Y-m-d', strtotime($contract['date_fin'])) ?>" 
                                                                   class="date-input" >
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                <div class="form-field-group">
                                                    <label class="form-field-label">Termes du contrat</label>
                                                    <textarea name="terms" class="contract-terms"><?= htmlspecialchars($contract['terms']) ?></textarea>
                                                </div>
                                            </div>
                                            
                                            <div class="contract-modal-footer">
                                                <button type="button" class="cancel-btn">
                                                    <i class="icon-cancel"></i> Annuler
                                                </button>
                                                <button type="submit" name="update_contract" class="submit-btn">
                                                    <i class="icon-save"></i> Enregistrer
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
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
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
    </script>
    <script>
document.addEventListener('DOMContentLoaded', function() {
    // Vérifie que Bootstrap est bien chargé
    if (typeof bootstrap === 'undefined') {
        console.error("Bootstrap 5 n'est pas chargé correctement");
        alert("Erreur technique - Veuillez recharger la page");
    } else {
        console.log("Bootstrap est correctement chargé");
        
        // Active le débogage des modales
        document.querySelectorAll('[data-bs-toggle="modal"]').forEach(btn => {
            btn.addEventListener('click', function() {
                console.log("Ouverture de la modale:", this.dataset.bsTarget);
            });
        });
    }
    
    // Formatage du téléphone
    document.querySelector('input[name="telephone"]')?.addEventListener('input', function() {
        this.value = this.value.replace(/\D/g, '').slice(0, 10);
    });
});
</script>
<script>
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
  document.querySelectorAll('[data-modal-target]').forEach(btn => {
    btn.addEventListener('click', function() {
      const modalId = this.getAttribute('data-modal-target');
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
});
</script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const updateForm = document.querySelector('form');
    const nomInput = document.querySelector('input[name="nom"]');
    const emailInput = document.querySelector('input[name="email"]');
    const telephoneInput = document.querySelector('input[name="telephone"]');
    const montantInput = document.querySelector('input[name="montant"]');
    const descriptionInput = document.querySelector('textarea[name="description"]');

    // Helper function to show error
    function showError(input, message) {
        const formGroup = input.closest('.mb-3');
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
        const formGroup = input.closest('.mb-3');
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

document.addEventListener('DOMContentLoaded', function () {
    const termsInput = document.querySelector('textarea[name="terms"]');
    const addContractForm = document.querySelector('form[method="POST"]');

    // Helper function to show error
    function showError(input, message) {
        const formGroup = input.closest('.mb-3');
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
        const formGroup = input.closest('.mb-3');
        const errorElement = formGroup.querySelector('.error-message');
        if (errorElement) {
            errorElement.remove();
        }
        input.classList.remove('is-invalid');
    }

    // Validation function for "Termes"
    function validateTerms() {
        const terms = termsInput.value.trim();
        if (terms === '') {
            showError(termsInput, 'Les termes doivent être remplis.');
            return false;
        }
        clearError(termsInput);
        return true;
    }

    // Attach blur event listener for real-time validation
    termsInput.addEventListener('blur', validateTerms);

    // Form submission validation
    addContractForm.addEventListener('submit', function (e) {
        const isTermsValid = validateTerms();

       
    });
});
</script>
</body>
</html>