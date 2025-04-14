<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../controlle/ContratController.php';
require_once __DIR__ . '/../../controlle/PartenaireController.php';

session_start();

// Admin check
/*if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}*/

$contratController = new ContratController();
$partenaireController = new PartenaireController();

// Handle all form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['delete_id'])) {
            if ($contratController->deleteContract($_POST['delete_id'])) {
                $_SESSION['success'] = "Contrat supprimé avec succès!";
            } else {
                throw new Exception("Erreur lors de la suppression du contrat");
            }
        } 
        elseif (isset($_POST['approve_partner'])) {
            $partner_id = $_POST['approve_partner'];
            
            if ($partenaireController->approvePartenaire($partner_id)) {
                $_SESSION['success'] = "Partenaire approuvé avec succès!";
            } else {
                throw new Exception("Erreur lors de l'approbation du partenaire");
            }
        }
        elseif (isset($_POST['reject_partner'])) {
            $partner_id = $_POST['id_partenaire'] ?? null;
            
            if (!$partner_id) {
                throw new Exception("ID partenaire manquant");
            }
            
            if ($partenaireController->rejectPartenaire($partner_id)) {
                $_SESSION['success'] = "Partenaire rejeté avec succès";
            } else {
                throw new Exception("Erreur lors du rejet du partenaire");
            }
        }
    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
    }
    
    header("Location: contrats.php");
    exit();
}

// Get data
$contrats = $contratController->getAllContracts();
$unapprovedPartenaires = $partenaireController->getUnapprovedPartenaires();
$approvedPartenaires = $partenaireController->getAllApprovedPartenaires();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Partenaires et Contrats</title>
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

        .navbar a {
            color: #cbd5e1;
            text-decoration: none;
            margin-left: 20px;
            font-size: 0.95rem;
            transition: color 0.3s;
        }

        .navbar a:hover {
            color: #00d09c;
        }

        .navbar a.logout:hover {
            color: #e74c3c;
        }

        .navbar i {
            margin-right: 8px;
        }

        /* Main Container */
        .main-container {
            padding: 2rem;
            max-width: 1400px;
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

        /* Card Styles */
        .card {
            background: rgba(30, 60, 82, 0.6);
            border-radius: 16px;
            padding: 2rem;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.3);
            margin-bottom: 2rem;
            backdrop-filter: blur(5px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        /* Table Styles */
        .table-container {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            color: white;
        }

        th, td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        th {
            background-color: rgba(15, 32, 39, 0.8);
            color: white;
            font-weight: 600;
            position: sticky;
            top: 0;
        }

        tr:hover {
            background-color: rgba(46, 79, 102, 0.4);
        }

        /* Badge Styles */
        .badge {
            display: inline-block;
            padding: 0.4rem 0.8rem;
            border-radius: 20px;
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
            padding: 0.6rem 1.2rem;
            border: none;
            border-radius: 8px;
            font-size: 0.9rem;
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

        .btn-success {
            background: linear-gradient(to right, #2ecc71, #27ae60);
            color: white;
        }

        .btn-success:hover {
            background: linear-gradient(to right, #27ae60, #219653);
        }

        .btn-danger {
            background: linear-gradient(to right, #e74c3c, #c0392b);
            color: white;
        }

        .btn-danger:hover {
            background: linear-gradient(to right, #c0392b, #a5281b);
        }

        .btn-sm {
            padding: 0.4rem 0.8rem;
            font-size: 0.8rem;
        }

        .btn-group {
            display: flex;
            gap: 0.5rem;
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(5px);
        }

        .modal-content {
            background: rgba(30, 60, 82, 0.9);
            margin: 5% auto;
            padding: 2rem;
            border-radius: 16px;
            box-shadow: 0 0 30px rgba(0, 0, 0, 0.4);
            width: 60%;
            max-width: 700px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: white;
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
            transition: color 0.3s;
        }

        .close:hover {
            color: white;
        }

        /* Form Styles */
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

        .form-control, .form-select {
            width: 100%;
            padding: 0.8rem 1rem;
            border: none;
            border-radius: 8px;
            font-size: 0.95rem;
            transition: all 0.3s;
            background-color: rgba(46, 79, 102, 0.8);
            color: white;
        }

        .form-control:focus, .form-select:focus {
            outline: none;
            box-shadow: 0 0 0 2px rgba(0, 208, 156, 0.3);
            background-color: rgba(46, 79, 102, 1);
        }

        textarea.form-control {
            min-height: 120px;
        }

        /* Responsive Adjustments */
        @media (max-width: 768px) {
            .main-container {
                padding: 1rem;
            }
            
            .modal-content {
                width: 90%;
                margin: 10% auto;
            }
            
            .btn-group {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
                margin-bottom: 0.5rem;
            }
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar">
        <div class="logo-container">
            <i class="fas fa-file-contract fa-lg" style="color: #00d09c;"></i>
            <span class="brand-name">Gestion Contrats</span>
        </div>
        <nav>
            <a href="/"><i class="fas fa-home"></i> Accueil</a>
            <a href="/partenaires"><i class="fas fa-users"></i> Partenaires</a>
            <a href="/logout" class="logout"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
        </nav>
    </nav>

    <div class="main-container">
        <div class="header-section">
            <h1><i class="fas fa-handshake"></i> Gestion des Partenaires et Contrats</h1>
        </div>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($_SESSION['success']) ?>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle me-2"></i><?= htmlspecialchars($_SESSION['error']) ?>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <!-- Partner Requests Section -->
        <div class="card">
            <h2><i class="fas fa-user-clock me-2"></i>Demandes de Partenariat</h2>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nom</th>
                            <th>Email</th>
                            <th>Téléphone</th>
                            <th>Montant</th>
                            <th>Description</th>
                            <th>Créé le</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($unapprovedPartenaires as $partner): ?>
                        <tr>
                            <td><?= htmlspecialchars($partner['id_partenaire']) ?></td>
                            <td><?= htmlspecialchars($partner['nom']) ?></td>
                            <td><?= htmlspecialchars($partner['email']) ?></td>
                            <td><?= htmlspecialchars($partner['telephone']) ?></td>
                            <td><?= htmlspecialchars($partner['montant']) ?> €</td>
                            <td><?= htmlspecialchars($partner['description']) ?></td>
                            <td><?= date('d/m/Y H:i', strtotime($partner['created_at'])) ?></td>
                            <td>
                                <div class="btn-group">
                                    <form method="POST">
                                        <input type="hidden" name="approve_partner" value="<?= $partner['id_partenaire'] ?>">
                                        <button type="submit" class="btn btn-success btn-sm">
                                            <i class="fas fa-check"></i> Approuver
                                        </button>
                                    </form>
                                    <form method="POST">
                                        <input type="hidden" name="id_partenaire" value="<?= $partner['id_partenaire'] ?>">
                                        <button type="submit" name="reject_partner" class="btn btn-danger btn-sm" 
                                        onclick="return confirm('Voulez-vous vraiment rejeter cette demande?')">
                                            <i class="fas fa-times"></i> Rejeter
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($unapprovedPartenaires)): ?>
                        <tr>
                            <td colspan="8" style="text-align:center;">Aucune demande de partenariat en attente</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Contracts Section -->
        <div class="card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                <h2><i class="fas fa-file-signature me-2"></i>Contrats</h2>
                <button onclick="openModal('addModal')" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Nouveau Contrat
                </button>
            </div>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Partenaire</th>
                            <th>Date Début</th>
                            <th>Date Fin</th>
                            <th>Statut</th>
                            <th>Créé le</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($contrats as $contrat): ?>
                        <tr>
                            <td><?= htmlspecialchars($contrat['id_contrat']) ?></td>
                            <td><?= htmlspecialchars($contrat['partenaire_nom']) ?></td>
                            <td><?= date('d/m/Y', strtotime($contrat['date_deb'])) ?></td>
                            <td><?= date('d/m/Y', strtotime($contrat['date_fin'])) ?></td>
                            <td>
                                <span class="badge badge-<?= 
                                    $contrat['status'] === 'actif' ? 'success' : 
                                    ($contrat['status'] === 'en attente' ? 'warning' : 'danger') 
                                ?>">
                                    <?= ucfirst($contrat['status']) ?>
                                </span>
                                <?php if ($contrat['status'] === 'en attente'): ?>
                                <form method="POST" action="activate_contract.php" style="display:inline; margin-left:5px;">
                                    <input type="hidden" name="id_contrat" value="<?= $contrat['id_contrat'] ?>">
                                    <button type="submit" class="btn btn-success btn-sm">
                                        <i class="fas fa-power-off"></i> Activer
                                    </button>
                                </form>
                                <?php endif; ?>
                            </td>
                            <td><?= date('d/m/Y H:i', strtotime($contrat['created_at'])) ?></td>
                            <td>
                                <div class="btn-group">
                                    <button onclick="openEditModal(<?= $contrat['id_contrat'] ?>)" class="btn btn-primary btn-sm">
                                        <i class="fas fa-edit"></i> Modifier
                                    </button>
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="delete_id" value="<?= $contrat['id_contrat'] ?>">
                                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Supprimer ce contrat?')">
                                            <i class="fas fa-trash"></i> Supprimer
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Add Contract Modal -->
    <div id="addModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('addModal')">&times;</span>
            <h2><i class="fas fa-plus-circle me-2"></i>Nouveau Contrat</h2>
            <form method="POST">
                <input type="hidden" name="add_contract" value="1">
                <div class="form-group">
                    <label class="form-label">Partenaire:</label>
                    <select name="id_partenaire" class="form-control" required>
                        <?php foreach ($approvedPartenaires as $partenaire): ?>
                        <option value="<?= $partenaire['id_partenaire'] ?>"><?= htmlspecialchars($partenaire['nom']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Date Début:</label>
                    <input type="date" name="date_deb" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Date Fin:</label>
                    <input type="date" name="date_fin" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Statut:</label>
                    <select name="status" class="form-control" required>
                        <option value="en attente">En attente</option>
                        <option value="actif">Actif</option>
                        <option value="expiré">Expiré</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Termes:</label>
                    <textarea name="terms" rows="4" class="form-control"></textarea>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Enregistrer
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Contract Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('editModal')">&times;</span>
            <h2><i class="fas fa-edit me-2"></i>Modifier Contrat</h2>
            <form method="POST" id="editForm">
                <input type="hidden" name="id_contrat" id="edit_id_contrat">
                
                <div class="form-group">
                    <label class="form-label">Date Début:</label>
                    <input type="date" name="date_deb" id="edit_date_deb" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Date Fin:</label>
                    <input type="date" name="date_fin" id="edit_date_fin" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Statut:</label>
                    <select name="status" id="edit_status" class="form-control" required>
                        <option value="en attente">En attente</option>
                        <option value="actif">Actif</option>
                        <option value="expiré">Expiré</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Termes:</label>
                    <textarea name="terms" id="edit_terms" rows="4" class="form-control" required></textarea>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Mettre à jour
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openModal(modalId) {
            document.getElementById(modalId).style.display = 'block';
        }

        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }

        function openEditModal(contratId) {
            fetch(`get_contract.php?id=${contratId}`)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('edit_id_contrat').value = data.id_contrat;
                    document.getElementById('edit_date_deb').value = data.date_deb;
                    document.getElementById('edit_date_fin').value = data.date_fin;
                    document.getElementById('edit_status').value = data.status;
                    document.getElementById('edit_terms').value = data.terms;
                    openModal('editModal');
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Une erreur est survenue lors du chargement du contrat');
                });
        }

        window.onclick = function(event) {
            if (event.target.className === 'modal') {
                event.target.style.display = 'none';
            }
        }
    </script>
</body>
</html>