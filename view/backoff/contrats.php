<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../control/ContratController.php';
require_once __DIR__ . '/../../control/PartenaireController.php';

session_start();

$contratController = new ContratController();
$partenaireController = new PartenaireController();

// Handle search inputs for "Demandes de Partenariat"
$searchPartenaireId = $_GET['search_partenaire_id'] ?? null;
$sortOrder = $_GET['sort_order'] ?? 'created_at DESC'; // Default sort order

// Add sorting by montant
if (isset($_GET['sort_by']) && $_GET['sort_by'] === 'montant') {
    $sortOrder = 'montant ' . ($_GET['sort_direction'] === 'asc' ? 'ASC' : 'DESC');
}

if ($searchPartenaireId) {
    $unapprovedPartenaires = [$partenaireController->getPartenaire($searchPartenaireId)];
    $unapprovedPartenaires = array_filter($unapprovedPartenaires); // Remove null results
} else {
    $unapprovedPartenaires = $partenaireController->getUnapprovedPartenaires($sortOrder);
}

// Handle advanced search filters for "Demandes de Partenariat"
$searchPartenaireType = $_GET['search_partenaire_type'] ?? null;
$searchPartenaireId = $_GET['search_partenaire_id'] ?? null;
$searchPartenaireNom = $_GET['search_partenaire_nom'] ?? null;
$searchMontantMin = $_GET['search_montant_min'] ?? null;
$searchMontantMax = $_GET['search_montant_max'] ?? null;

if ($searchPartenaireType === 'id' && $searchPartenaireId) {
    $unapprovedPartenaires = [$partenaireController->getPartenaire($searchPartenaireId)];
    $unapprovedPartenaires = array_filter($unapprovedPartenaires); // Remove null results
} elseif ($searchPartenaireType === 'nom' && $searchPartenaireNom) {
    $unapprovedPartenaires = $partenaireController->filterPartenairesByName($searchPartenaireNom);
} elseif ($searchPartenaireType === 'montant_range' && ($searchMontantMin || $searchMontantMax)) {
    $unapprovedPartenaires = $partenaireController->filterPartenairesByMontantRange($searchMontantMin, $searchMontantMax);
} else {
    $unapprovedPartenaires = $partenaireController->getUnapprovedPartenaires($sortOrder);
}

// Update expired contracts
$contratController->updateExpiredContracts();

// Handle advanced search filters for "Contrats"
$searchType = $_GET['search_type'] ?? null;
$searchContratId = $_GET['search_contrat_id'] ?? null;
$searchDateStart = $_GET['search_date_start'] ?? null;
$searchDateEnd = $_GET['search_date_end'] ?? null;
$searchStatus = $_GET['search_status'] ?? null;

if ($searchType === 'id' && $searchContratId) {
    $contrats = [$contratController->getContract($searchContratId)];
    $contrats = array_filter($contrats); // Remove null results
} elseif ($searchType === 'status' && $searchStatus) {
    $contrats = $contratController->filterContractsByAdvancedSearch(null, null, $searchStatus);
} elseif ($searchType === 'date_range' && ($searchDateStart || $searchDateEnd)) {
    $contrats = $contratController->filterContractsByAdvancedSearch($searchDateStart, $searchDateEnd, null);
} else {
    $contrats = $contratController->getAllContracts();
}

// Get all approved partners for adding new contracts
$approvedPartenaires = $partenaireController->getAllApprovedPartenaires();

// Fetch contract statistics
$contractStats = $contratController->getContractStatistics();

$today = new DateTime();
$expiryThreshold = (clone $today)->modify('+30 days');
$expiringContracts = [];

foreach ($contrats as $contrat) {
    $dateFin = new DateTime($contrat['date_fin']);
    if ($dateFin <= $expiryThreshold && $dateFin >= $today) {
        $expiringContracts[] = $contrat;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FundFlow - Gestion des Contrats</title>
    <link rel="stylesheet" href="../Frontoff/css/stylebackof.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .admin-background {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -2;
            background: linear-gradient(135deg, #f5f7ff 0%, #e8ecff 100%);
            overflow: hidden;
        }

        .admin-background::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('../Frontoff/assets/Logo_FundFlow.png') center/30% no-repeat;
            opacity: 0.03;
            pointer-events: none;
        }

        .admin-background::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle at 20% 30%, rgba(67, 97, 238, 0.05) 0%, transparent 50%),
                        radial-gradient(circle at 80% 70%, rgba(16, 185, 129, 0.05) 0%, transparent 50%);
        }
    </style>
</head>
<body>
    <button class="sidebar-toggle">
        <i class="fas fa-bars"></i>
    </button>

    <div class="admin-background"></div>

    <div class="admin-container">
        <!-- Sidebar Navigation -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <img src="../Frontoff/assets/Logo_FundFlow.png" alt="FundFlow Logo" class="sidebar-logo">
                <button class="sidebar-close">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <nav class="sidebar-menu">
                <ul>
                    <li><a href="backoffice.php"><i class="fas fa-tachometer-alt"></i> Tableau de bord</a></li>
                    <li ><a href="utilisateurmet.php"><i class="fas fa-users"></i> Utilisateurs</a></li>
                    <li><a href="categories.php"><i class="fas fa-project-diagram"></i> Catégories</a></li>
                    <li>
                        <a href="#" class="toggle-submenu"><i class="fas fa-hand-holding-usd"></i> Financements</a>
                        <ul class="submenu">
                            <li><a href="statistics.php"><i class="fas fa-chart-pie"></i> Statistiques</a></li>
                            <li><a href="demands.php"><i class="fas fa-file-invoice-dollar"></i> Demandes</a></li>
                        </ul>
                    </li>
                    <li><a href="feedback.php"><i class="fas fa-comments"></i> Feedbacks</a></li>
                    <li aria-describedby=""class="active"><a href="contrats.php"><i class="fas fa-handshake"></i> Contrats</a></li>
                    <li><a href="#"><i class="fas fa-rocket"></i> Startups</a></li>
                </ul>
            </nav>
            <div class="sidebar-footer">
                <p>FundFlow Admin v1.0</p>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <header class="top-nav">
            </header>

            <div class="main-container">
                <!-- Expiring Contracts Notification -->
                <section class="card">
                    <div class="card-header">
                        <h3><i class="fas fa-exclamation-triangle"></i> Contrats Expirant</h3>
                    </div>
                    <div class="card-body">
                        <p><strong><?= count($expiringContracts) ?></strong> contrat(s) expirent dans les 30 prochains jours.</p>
                        <?php if (!empty($expiringContracts)): ?>
                            <ul>
                                <?php foreach ($expiringContracts as $contrat): ?>
                                    <li>
                                        <strong>ID:</strong> <?= htmlspecialchars($contrat['id_contrat']) ?>, 
                                        <strong>Nom Partenaire:</strong> <?= htmlspecialchars($contrat['partenaire_nom']) ?>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                </section>

                <!-- Advanced Search Form -->
                <section class="card">
                    <div class="card-header">
                        <h3><i class="fas fa-search"></i> Recherche Avancée</h3>
                    </div>
                    <div class="card-body">
                        <form method="GET">
                            <div class="form-group">
                                <label for="search_type">Type de Recherche</label>
                                <select class="form-control" id="search_type" name="search_type" onchange="toggleSearchFields()">
                                    <option value="">Sélectionnez</option>
                                    <option value="id" <?= ($searchType === 'id') ? 'selected' : '' ?>>Par ID</option>
                                    <option value="status" <?= ($searchType === 'status') ? 'selected' : '' ?>>Par Statut</option>
                                    <option value="date_range" <?= ($searchType === 'date_range') ? 'selected' : '' ?>>Par Intervalle de Dates</option>
                                </select>
                            </div>
                            <div id="search_by_id" style="display: none;">
                                <label for="search_contrat_id">ID Contrat</label>
                                <input type="text" class="form-control" id="search_contrat_id" name="search_contrat_id" value="<?= htmlspecialchars($searchContratId) ?>">
                            </div>
                            <div id="search_by_status" style="display: none;">
                                <label for="search_status">Statut</label>
                                <select class="form-control" id="search_status" name="search_status">
                                    <option value="">Tous</option>
                                    <option value="actif" <?= ($searchStatus === 'actif') ? 'selected' : '' ?>>Actif</option>
                                    <option value="en attente" <?= ($searchStatus === 'en attente') ? 'selected' : '' ?>>En attente</option>
                                    <option value="expiré" <?= ($searchStatus === 'expiré') ? 'selected' : '' ?>>Expiré</option>
                                </select>
                            </div>
                            <div id="search_by_date_range" style="display: none;">
                                <label for="search_date_start">Date Début</label>
                                <input type="date" class="form-control" id="search_date_start" name="search_date_start" value="<?= htmlspecialchars($searchDateStart) ?>">
                                <label for="search_date_end">Date Fin</label>
                                <input type="date" class="form-control" id="search_date_end" name="search_date_end" value="<?= htmlspecialchars($searchDateEnd) ?>">
                            </div>
                            <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Rechercher</button>
                        </form>
                    </div>
                </section>

                <!-- Vocal Search Section -->
                <section class="card">
                    <div class="card-header">
                        <h3><i class="fas fa-microphone"></i> Recherche Vocale</h3>
                    </div>
                    <div class="card-body text-center">
                        <p class="text-muted">Utilisez votre voix pour rechercher rapidement un contrat</p>
                        <button id="voiceSearchBtn" class="btn btn-outline-primary btn-lg px-4 rounded-pill">
                            <i class="fas fa-microphone"></i> Commencer à parler
                        </button>
                        <div id="voiceStatus" class="mt-2 text-secondary small"></div>
                    </div>
                </section>

                <!-- Vocal Search Results -->
                <section id="voiceSearchResults" class="card" style="display: none;">
                    <div class="card-header">
                        <h3><i class="fas fa-list"></i> Résultats de la recherche vocale</h3>
                    </div>
                    <div class="card-body">
                        <div id="contractDetails"></div>
                    </div>
                </section>

                <!-- Contracts Table -->
                <section class="card">
                    <div class="card-header">
                        <h3><i class="fas fa-file-signature"></i> Liste des Contrats</h3>
                        <button onclick="openModal('addModal')" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Nouveau Contrat
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table">
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
                                        <td><?= htmlspecialchars($contrat['partenaire_nom'] ?? 'N/A') ?></td>
                                        <td><?= date('d/m/Y', strtotime($contrat['date_deb'])) ?></td>
                                        <td><?= date('d/m/Y', strtotime($contrat['date_fin'])) ?></td>
                                        <td>
                                            <span class="badge <?= 
                                                $contrat['status'] === 'actif' ? 'badge-success' : 
                                                ($contrat['status'] === 'en attente' ? 'badge-warning' : 'badge-danger') 
                                            ?>">
                                                <?= ucfirst($contrat['status']) ?>
                                            </span>
                                        </td>
                                        <td><?= date('d/m/Y H:i', strtotime($contrat['created_at'])) ?></td>
                                        <td>
                                            <div class="action-buttons">
                                                <button onclick="openEditModal(<?= $contrat['id_contrat'] ?>)" class="btn btn-info btn-sm">
                                                    <i class="fas fa-edit"></i> Modifier
                                                </button>
                                                <form method="POST" style="display:inline;" action="supprimer.php">
                                                    <input type="hidden" name="id_contrat" value="<?= $contrat['id_contrat'] ?>">
                                                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Supprimer ce contrat?')">
                                                        <i class="fas fa-trash"></i> Supprimer
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                    <?php if (empty($contrats)): ?>
                                    <tr>
                                        <td colspan="7" style="text-align:center;">Aucun contrat trouvé</td>
                                    </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </section>
            </div>
        </main>
    </div>

    <script>
        // Vocal Search Functionality
        document.addEventListener('DOMContentLoaded', function() {
            const voiceSearchBtn = document.getElementById('voiceSearchBtn');
            const voiceStatus = document.getElementById('voiceStatus');
            const voiceSearchResults = document.getElementById('voiceSearchResults');
            const contractDetails = document.getElementById('contractDetails');

            if ('webkitSpeechRecognition' in window) {
                const recognition = new webkitSpeechRecognition();
                recognition.continuous = false;
                recognition.interimResults = false;
                recognition.lang = 'fr-FR';

                voiceSearchBtn.addEventListener('click', function() {
                    startVoiceRecognition();
                });

                function startVoiceRecognition() {
                    try {
                        voiceSearchBtn.innerHTML = '<i class="fas fa-microphone-slash"></i> Écoute en cours...';
                        voiceSearchBtn.classList.remove('btn-outline-primary');
                        voiceSearchBtn.classList.add('btn-danger');
                        voiceStatus.textContent = "Parlez maintenant...";
                        voiceStatus.style.color = "green";

                        recognition.start();
                    } catch (error) {
                        console.error('Erreur reconnaissance vocale:', error);
                        resetVoiceRecognition();
                        voiceStatus.textContent = "Erreur: " + error.message;
                        voiceStatus.style.color = "red";
                    }
                }

                recognition.onresult = function(event) {
                    const transcript = event.results[0][0].transcript.trim();
                    voiceStatus.textContent = "Vous avez dit: " + transcript;
                    voiceStatus.style.color = "blue";

                    const contractId = transcript.match(/\d+/)?.[0];

                    if (contractId) {
                        voiceStatus.textContent += " - ID détecté: " + contractId;
                        fetchContractDetails(contractId);
                    } else {
                        voiceStatus.textContent += " - Aucun ID de contrat détecté";
                        voiceStatus.style.color = "orange";
                        resetVoiceRecognition();
                    }
                };

                recognition.onerror = function(event) {
                    console.error('Erreur reconnaissance:', event.error);
                    voiceStatus.textContent = "Erreur: " + getErrorText(event.error);
                    voiceStatus.style.color = "red";
                    resetVoiceRecognition();
                };

                recognition.onend = function() {
                    resetVoiceRecognition();
                };

                function resetVoiceRecognition() {
                    voiceSearchBtn.innerHTML = '<i class="fas fa-microphone"></i> Commencer à parler';
                    voiceSearchBtn.classList.remove('btn-danger');
                    voiceSearchBtn.classList.add('btn-outline-primary');
                }

                function getErrorText(error) {
                    const errors = {
                        'no-speech': 'Aucune parole détectée',
                        'audio-capture': 'Problème de microphone',
                        'not-allowed': 'Microphone non autorisé',
                        'aborted': 'Reconnaissance interrompue',
                        'network': 'Erreur réseau',
                        'language-not-supported': 'Langue non supportée'
                    };
                    return errors[error] || 'Erreur inconnue';
                }

                async function fetchContractDetails(contractId) {
                    try {
                        voiceStatus.textContent = "Recherche du contrat #" + contractId + "...";

                        const response = await fetch(`get_contract_info.php?id=${contractId}`);
                        if (!response.ok) throw new Error('Erreur réseau');

                        const data = await response.json();

                        if (data.error) {
                            throw new Error(data.error);
                        }

                        displayContractDetails(data);
                    } catch (error) {
                        console.error('Erreur:', error);
                        voiceStatus.textContent = "Erreur: " + error.message;
                        voiceStatus.style.color = "red";
                    }
                }

                function displayContractDetails(contract) {
                    voiceSearchResults.style.display = 'block';
                    contractDetails.innerHTML = `
                        <div class="alert alert-success">
                            <h4>Contrat #${contract.id_contrat}</h4>
                            <p><strong>Partenaire:</strong> ${contract.partenaire_nom}</p>
                            <p><strong>Date Début:</strong> ${contract.date_deb}</p>
                            <p><strong>Date Fin:</strong> ${contract.date_fin}</p>
                            <p><strong>Statut:</strong> <span class="badge badge-${getStatusBadgeClass(contract.status)}">${contract.status}</span></p>
                        </div>
                    `;

                    voiceSearchResults.scrollIntoView({ behavior: 'smooth' });
                }

                function getStatusBadgeClass(status) {
                    return {
                        'actif': 'success',
                        'en attente': 'warning',
                        'expiré': 'danger'
                    }[status] || 'secondary';
                }
            } else {
                voiceSearchBtn.style.display = 'none';
                voiceStatus.textContent = "La reconnaissance vocale n'est pas supportée par votre navigateur";
                voiceStatus.style.color = "red";
            }
        });
        // Toggle sidebar with persistent state
        const sidebar = document.querySelector('.sidebar');
        const sidebarToggle = document.querySelector('.sidebar-toggle');
        const sidebarClose = document.querySelector('.sidebar-close');
        const mainContent = document.querySelector('.main-content');

        // Check localStorage for saved state
        const sidebarState = localStorage.getItem('sidebarState');

        // Initialize sidebar state
        if (sidebarState === 'collapsed') {
            sidebar.classList.add('collapsed');
            mainContent.classList.add('expanded');
        }

        // Toggle function
        function toggleSidebar() {
            sidebar.classList.toggle('collapsed');
            mainContent.classList.toggle('expanded');
            
            // Save state to localStorage
            const isCollapsed = sidebar.classList.contains('collapsed');
            localStorage.setItem('sidebarState', isCollapsed ? 'collapsed' : 'expanded');
        }

        // Event listeners
        sidebarToggle.addEventListener('click', toggleSidebar);
        sidebarClose.addEventListener('click', toggleSidebar);

        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', (e) => {
            if (window.innerWidth <= 991 && 
                !sidebar.contains(e.target) && 
                !sidebarToggle.contains(e.target) &&
                !sidebar.classList.contains('collapsed')) {
                toggleSidebar();
            }
        });

        // Toggle submenus
        document.querySelectorAll('.toggle-submenu').forEach(item => {
            item.addEventListener('click', function(e) {
                e.preventDefault();
                const submenu = this.nextElementSibling;
                submenu.classList.toggle('active');
            });
        });
    </script>
</body>
</html>