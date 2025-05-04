<?php
require_once __DIR__ . '/../../controlle/CategorieController.php';

$controller = new CategorieController();
$searchTerm = $_GET['search'] ?? null;

if ($searchTerm) {
    $categories = $controller->searchCategoriesByName($searchTerm);
} else {
    $categories = $controller->getAllCategories();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>
    <header class="navbar">
        <!-- ...existing code... -->
    </header>

    <div class="main-container">
        <div class="header-section">
            <h1><i class="fas fa-tags"></i> Gestion des Catégories</h1>
            <div class="button-container" style="display: flex; gap: 1rem; justify-content: center; margin-bottom: 1.5rem;">
                <a href="export_categories.php" class="btn btn-primary">
                    <i class="fas fa-file-pdf"></i> Exporter en PDF
                </a>
                <button class="btn btn-info" 
                        data-bs-toggle="modal" 
                        data-bs-target="#historyModal" 
                        onclick="loadAllHistory()">
                    <i class="fas fa-history"></i> Voir l'historique des modifications
                </button>
            </div>
        </div>

        <!-- Search Bar -->
        <div class="search-container" style="margin-bottom: 1.5rem;">
            <form method="GET" action="categories.php" class="d-flex justify-content-center">
                <input type="text" name="search" class="form-control w-50" placeholder="Rechercher par nom de catégorie..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
                <button type="submit" class="btn btn-primary ms-2"><i class="fas fa-search"></i> Rechercher</button>
            </form>
        </div>

        <div class="table-container">
            <table class="table table-striped">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Nom</th>
                        <th>Description</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($categories as $category): ?>
                        <tr>
                            <td><?= htmlspecialchars($category['id_categorie']) ?></td>
                            <td><?= htmlspecialchars($category['nom_categorie']) ?></td>
                            <td><?= htmlspecialchars($category['description']) ?></td>
                            <td>
                                <a href="edit_category.php?id=<?= $category['id_categorie'] ?>" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i> Modifier</a>
                                <a href="delete_category.php?id=<?= $category['id_categorie'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette catégorie ?')"><i class="fas fa-trash"></i> Supprimer</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal for Modification History -->
    <div class="modal fade" id="historyModal" tabindex="-1" aria-labelledby="historyModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="historyModalLabel">Historique des Modifications</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Action</th>
                                <th>Date</th>
                                <th>Utilisateur</th>
                                <th>Détails</th>
                            </tr>
                        </thead>
                        <tbody id="historyTableBody">
                            <!-- History data will be loaded dynamically -->
                        </tbody>
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function loadAllHistory() {
            fetch(`historique.php?all=true`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    const historyTableBody = document.getElementById('historyTableBody');
                    historyTableBody.innerHTML = '';
                    data.forEach(entry => {
                        const row = document.createElement('tr');
                        row.innerHTML = `
                            <td>${entry.action || 'N/A'}</td>
                            <td>${entry.date || 'N/A'}</td>
                            <td>${entry.user || 'N/A'}</td>
                            <td>${entry.details || 'N/A'}</td>
                        `;
                        historyTableBody.appendChild(row);
                    });
                })
                .catch(error => console.error('Erreur lors du chargement de l\'historique:', error));
        }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
