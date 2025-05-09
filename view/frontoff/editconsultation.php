<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../control/consultationcontroller.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$controller = new ConsultationController();

$consultationId = isset($_GET['id']) ? $_GET['id'] : null;

if (!$consultationId) {
    die("ID de consultation manquant.");
}

$consultation = $controller->getConsultationById($consultationId);

if (!$consultation) {
    die("Consultation introuvable.");
}

$users = $controller->getAllUsers();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Create a Consultation object using the posted data
        $updatedConsultation = new Consultation([
            'id_consultation' => $_POST['id_consultation'],
            'id_utilisateur1' => $_POST['id_utilisateur1'],
            'id_utilisateur2' => $_POST['id_utilisateur2'],
            'date_consultation' => $_POST['date_consultation'],
            'heure_deb' => $_POST['heure_deb'],
            'heure_fin' => $_POST['heure_fin'],
            'tarif' => $_POST['tarif']
        ]);

        // Pass the Consultation object to the updateConsultation method
        if ($controller->updateConsultation($updatedConsultation)) {
            $_SESSION['success'] = "Consultation mise à jour avec succès!";
            header("Location: mesconsultations.php");
            exit;
        }
    } catch (Exception $e) {
        $error_message = $e->getMessage();
    }
}

$success_message = isset($_SESSION['success']) ? $_SESSION['success'] : null;
unset($_SESSION['success']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier Consultation</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/consultation.css">
</head>
<body>
    <header class="navbar">
        <div class="logo-container">
            <span class="brand-name">FundFlow</span>
        </div>
        <nav>
            <a href="mesconsultations.php"><i class="fas fa-list"></i> Mes Consultations</a>
            <a href="apropos.html"><i class="fas fa-info-circle"></i> À propos</a>
            <a href="contact.html"><i class="fas fa-envelope"></i> Contact</a>
            <a href="accueil.php" class="logout"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
        </nav>
    </header>

    <div class="main-container">
        <div class="header-section">
            <h1><i class="fas fa-edit"></i> Modifier Consultation</h1>
            <?php if (!empty($success_message)): ?>
                <div class="alert alert-success"><?= htmlspecialchars($success_message) ?></div>
            <?php endif; ?>
            <?php if (!empty($error_message)): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error_message) ?></div>
            <?php endif; ?>
        </div>

        <div class="form-container">
            <form id="consultationForm" method="POST" action="editconsultation.php?id=<?= htmlspecialchars($consultationId) ?>">
                <input type="hidden" name="id_consultation" value="<?= htmlspecialchars($consultation['id_consultation']) ?>">

                <div class="form-group">
                    <label class="form-label"><i class="fas fa-user-md"></i> Consultant *</label>
                    <select class="form-select" name="id_utilisateur1">
                        <option value="">Sélectionnez un consultant</option>
                        <?php foreach ($users as $user): ?>
                            <option value="<?= $user['id_utilisateur'] ?>"
                                <?= $consultation['id_utilisateur1'] == $user['id_utilisateur'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($user['prenom'] . ' ' . $user['nom']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label"><i class="fas fa-user"></i> Client *</label>
                    <select class="form-select" name="id_utilisateur2">
                        <option value="">Sélectionnez un client</option>
                        <?php foreach ($users as $user): ?>
                            <option value="<?= $user['id_utilisateur'] ?>"
                                <?= $consultation['id_utilisateur2'] == $user['id_utilisateur'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($user['prenom'] . ' ' . $user['nom']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label"><i class="fas fa-calendar-day"></i> Date *</label>
                    <input type="date" class="form-control" name="date_consultation"
                           value="<?= htmlspecialchars($consultation['date_consultation']) ?>">
                </div>

                <div class="form-group">
                    <label class="form-label"><i class="fas fa-clock"></i> Heure de début *</label>
                    <input type="time" class="form-control" name="heure_deb"
                           value="<?= htmlspecialchars($consultation['heure_deb']) ?>">
                </div>

                <div class="form-group">
                    <label class="form-label"><i class="fas fa-clock"></i> Heure de fin *</label>
                    <input type="time" class="form-control" name="heure_fin"
                           value="<?= htmlspecialchars($consultation['heure_fin']) ?>">
                </div>

                <div class="form-group">
                    <label class="form-label"><i class="fas fa-euro-sign"></i> Tarif (€) *</label>
                    <input type="number" class="form-control" name="tarif" step="0.01"
                           value="<?= htmlspecialchars($consultation['tarif']) ?>">
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Enregistrer
                    </button>
                    <a href="mesconsultations.php" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Annuler
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('consultationForm').addEventListener('submit', function(e) {
            let isValid = true;

            // Clear previous error messages
            document.querySelectorAll('.error-message').forEach(error => error.textContent = '');

            // Validate consultant selection
            const consultantSelect = document.querySelector('select[name="id_utilisateur1"]');
            if (!consultantSelect.value) {
                let errorMessage = consultantSelect.closest('.form-group').querySelector('.error-message');
                if (!errorMessage) {
                    errorMessage = document.createElement('div');
                    errorMessage.className = 'error-message';
                    consultantSelect.closest('.form-group').appendChild(errorMessage);
                }
                errorMessage.textContent = "Veuillez sélectionner un consultant.";
                isValid = false;
            }

            // Validate client selection
            const clientSelect = document.querySelector('select[name="id_utilisateur2"]');
            if (!clientSelect.value) {
                let errorMessage = clientSelect.closest('.form-group').querySelector('.error-message');
                if (!errorMessage) {
                    errorMessage = document.createElement('div');
                    errorMessage.className = 'error-message';
                    clientSelect.closest('.form-group').appendChild(errorMessage);
                }
                errorMessage.textContent = "Veuillez sélectionner un client.";
                isValid = false;
            }

            // Validate date
            const dateInput = document.querySelector('input[name="date_consultation"]');
            if (!dateInput.value) {
                let errorMessage = dateInput.closest('.form-group').querySelector('.error-message');
                if (!errorMessage) {
                    errorMessage = document.createElement('div');
                    errorMessage.className = 'error-message';
                    dateInput.closest('.form-group').appendChild(errorMessage);
                }
                errorMessage.textContent = "Veuillez sélectionner une date.";
                isValid = false;
            }

            // Validate start time
            const heureDeb = document.querySelector('input[name="heure_deb"]');
            if (!heureDeb.value) {
                let errorMessage = heureDeb.closest('.form-group').querySelector('.error-message');
                if (!errorMessage) {
                    errorMessage = document.createElement('div');
                    errorMessage.className = 'error-message';
                    heureDeb.closest('.form-group').appendChild(errorMessage);
                }
                errorMessage.textContent = "Veuillez saisir une heure de début.";
                isValid = false;
            }

            // Validate end time
            const heureFin = document.querySelector('input[name="heure_fin"]');
            if (!heureFin.value) {
                let errorMessage = heureFin.closest('.form-group').querySelector('.error-message');
                if (!errorMessage) {
                    errorMessage = document.createElement('div');
                    errorMessage.className = 'error-message';
                    heureFin.closest('.form-group').appendChild(errorMessage);
                }
                errorMessage.textContent = "Veuillez saisir une heure de fin.";
                isValid = false;
            } else if (heureDeb.value && heureDeb.value >= heureFin.value) {
                let errorMessage = heureFin.closest('.form-group').querySelector('.error-message');
                if (!errorMessage) {
                    errorMessage = document.createElement('div');
                    errorMessage.className = 'error-message';
                    heureFin.closest('.form-group').appendChild(errorMessage);
                }
                errorMessage.textContent = "L'heure de fin doit être après l'heure de début.";
                isValid = false;
            }

            // Validate tarif
            const tarifInput = document.querySelector('input[name="tarif"]');
            if (!tarifInput.value || parseFloat(tarifInput.value) <= 0) {
                let errorMessage = tarifInput.closest('.form-group').querySelector('.error-message');
                if (!errorMessage) {
                    errorMessage = document.createElement('div');
                    errorMessage.className = 'error-message';
                    tarifInput.closest('.form-group').appendChild(errorMessage);
                }
                errorMessage.textContent = "Veuillez saisir un tarif valide.";
                isValid = false;
            }

            // Prevent form submission if validation fails
            if (!isValid) {
                e.preventDefault();
            }
        });
    </script>
</body>
</html>