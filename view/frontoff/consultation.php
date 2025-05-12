<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../control/consultationcontroller.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$controller = new ConsultationController();
$users = $controller->getAllUsers();

$consultations = $controller->getAllConsultations();
$newId = 1;
if (!empty($consultations)) {
    $maxId = max(array_column($consultations, 'id_consultation'));
    $newId = $maxId + 1;
}

if (isset($_SESSION['success'])) {
    $success_message = $_SESSION['success'];
    unset($_SESSION['success']);
}

if (isset($_SESSION['form_errors'])) {
    $error_messages = $_SESSION['form_errors'];
    unset($_SESSION['form_errors']);
}

if (isset($_SESSION['form_data'])) {
    $form_data = $_SESSION['form_data'];
    unset($_SESSION['form_data']);
}

if (isset($_GET['error'])) {
    switch ($_GET['error']) {
        case '1':
            $error_messages[] = "Consultation non trouvée.";
            break;
        case '2':
            $error_messages[] = "Erreur de base de données.";
            break;
        case '3':
            $error_messages[] = "Impossible de supprimer la consultation car elle est liée à des feedbacks.";
            break;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FundFlow - Nouvelle Consultation</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/consultation.css">
    <style>
        .time-input {
            width: 120px;
            display: inline-block;
        }
        .currency-converter {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-top: 5px;
        }
        .currency-converter span {
            font-size: 0.9rem;
            color: white;
        }
        .currency-converter small {
            color: grey;
        }
        .form-group {
            margin-bottom: 1.5rem;
        }
        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
        }
        .form-control {
            width: 100%;
            padding: 0.5rem;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .form-select {
            width: 100%;
            padding: 0.5rem;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .error-message {
            color: #dc3545;
            font-size: 0.875rem;
            margin-top: 0.25rem;
        }
        .btn {
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 600;
        }
        .btn-primary {
            background-color: #007bff;
            color: white;
        }
        .alert {
            padding: 1rem;
            margin-bottom: 1rem;
            border-radius: 4px;
        }
        .alert-success {
            background-color: #d4edda;
            color: #155724;
        }
    </style>
</head>
<body>
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

    <div class="main-container">
        <div class="header-section">
            <h1><i class="fas fa-calendar-plus"></i> Nouvelle Consultation</h1>
        </div>

        <div class="form-container">
            <?php if (isset($success_message)): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success_message); ?></div>
            <?php endif; ?>

            <?php if (isset($error_messages)): ?>
                <div class="alert alert-danger">
                    <?php foreach ($error_messages as $error): ?>
                        <p><?php echo htmlspecialchars($error); ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form id="consultationForm" method="POST" action="addconsultation.php">
                <input type="hidden" name="id_consultation" value="<?php echo $newId; ?>">

                <div class="form-group">
                    <label class="form-label"><i class="fas fa-user-md"></i> Consultant *</label>
                    <select class="form-select" name="id_utilisateur1" >
                        <option value="">Sélectionnez un consultant</option>
                        <?php foreach ($users as $user): ?>
                            <option value="<?php echo $user['id_utilisateur']; ?>"
                                <?php echo (isset($form_data['id_utilisateur1']) && $form_data['id_utilisateur1'] == $user['id_utilisateur']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($user['prenom'] . ' ' . $user['nom']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="error-message"></div>
                </div>

                <div class="form-group">
                    <label class="form-label"><i class="fas fa-user"></i> Client *</label>
                    <select class="form-select" name="id_utilisateur2" >
                        <option value="">Sélectionnez un client</option>
                        <?php foreach ($users as $user): ?>
                            <option value="<?php echo $user['id_utilisateur']; ?>"
                                <?php echo (isset($form_data['id_utilisateur2']) && $form_data['id_utilisateur2'] == $user['id_utilisateur']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($user['prenom'] . ' ' . $user['nom']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="error-message"></div>
                </div>

                <div class="form-group">
                    <label class="form-label"><i class="fas fa-calendar-day"></i> Date *</label>
                    <input type="date" class="form-control" name="date_consultation" 
                           value="<?php echo isset($form_data['date_consultation']) ? htmlspecialchars($form_data['date_consultation']) : date('Y-m-d'); ?>">
                    <div class="error-message"></div>
                </div>

                <div class="form-group">
                    <label class="form-label"><i class="fas fa-clock"></i> Heure de début *</label>
                    <input type="time" class="form-control time-input" name="heure_deb"
                           value="<?php echo isset($form_data['heure_deb']) ? htmlspecialchars($form_data['heure_deb']) : ''; ?>">
                    <div class="error-message"></div>
                </div>

                <div class="form-group">
                    <label class="form-label"><i class="fas fa-clock"></i> Heure de fin *</label>
                    <input type="time" class="form-control time-input" name="heure_fin"
                           value="<?php echo isset($form_data['heure_fin']) ? htmlspecialchars($form_data['heure_fin']) : ''; ?>">
                    <div class="error-message"></div>
                </div>

                <div class="form-group">
                    <label class="form-label"><i class="fas fa-euro-sign"></i> Tarif (€) *</label>
                    <input type="number" class="form-control" name="tarif" id="tarifEuro" step="0.01" min="0"
                           value="<?php echo isset($form_data['tarif']) ? htmlspecialchars($form_data['tarif']) : '100'; ?>">
                    <div class="currency-converter">
                        <span id="conversionResult">≈ 0 DT</span>
                        <small>(1 EUR = 3.41 DT)</small>
                    </div>
                    <div class="error-message"></div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-calendar-plus"></i> Planifier
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const EUR_TO_DT_RATE = 3.41;
        const tarifEuroInput = document.getElementById('tarifEuro');
        const conversionResult = document.getElementById('conversionResult');
        
        function convertEuroToDt() {
            const euroValue = parseFloat(tarifEuroInput.value) || 0;
            const dtValue = euroValue * EUR_TO_DT_RATE;
            conversionResult.textContent = `≈ ${dtValue.toFixed(2)} DT`;
        }
        
        convertEuroToDt();
        
        tarifEuroInput.addEventListener('input', convertEuroToDt);
        
        document.getElementById('consultationForm').addEventListener('submit', function(e) {
            let isValid = true;

            document.querySelectorAll('.error-message').forEach(error => error.textContent = '');

            const consultantSelect = document.querySelector('select[name="id_utilisateur1"]');
            if (!consultantSelect.value) {
                consultantSelect.nextElementSibling.textContent = "Veuillez sélectionner un consultant.";
                isValid = false;
            }

            const clientSelect = document.querySelector('select[name="id_utilisateur2"]');
            if (!clientSelect.value) {
                clientSelect.nextElementSibling.textContent = "Veuillez sélectionner un client.";
                isValid = false;
            }

            const dateInput = document.querySelector('input[name="date_consultation"]');
            if (!dateInput.value) {
                dateInput.nextElementSibling.textContent = "Veuillez sélectionner une date.";
                isValid = false;
            }

            const heureDeb = document.querySelector('input[name="heure_deb"]');
            if (!heureDeb.value) {
                heureDeb.nextElementSibling.textContent = "Veuillez saisir une heure de début.";
                isValid = false;
            }

            const heureFin = document.querySelector('input[name="heure_fin"]');
            if (!heureFin.value) {
                heureFin.nextElementSibling.textContent = "Veuillez saisir une heure de fin.";
                isValid = false;
            } else if (heureDeb.value && heureDeb.value >= heureFin.value) {
                heureFin.nextElementSibling.textContent = "L'heure de fin doit être après l'heure de début.";
                isValid = false;
            }

            const tarifInput = document.querySelector('input[name="tarif"]');
            if (!tarifInput.value || parseFloat(tarifInput.value) <= 0) {
                tarifInput.nextElementSibling.textContent = "Veuillez saisir un tarif valide.";
                isValid = false;
            }

            if (!isValid) {
                e.preventDefault();
            }
        });
    </script>
</body>
</html>