<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../controlle/PartenaireController.php';
require_once __DIR__ . '/../../controlle/ContratController.php';

session_start();

$partenaireController = new PartenaireController();
$contratController = new ContratController();

// Handle search inputs
$searchPartenaireId = $_GET['search_partenaire_id'] ?? null;
$searchContratId = $_GET['search_contrat_id'] ?? null;

if ($searchPartenaireId) {
    $partenaires = [$partenaireController->getPartenaire($searchPartenaireId)];
    $partenaires = array_filter($partenaires); // Remove null results
} else {
    $partenaires = $partenaireController->getAllPartenaires();
}

if ($searchContratId) {
    $contrats = [$contratController->getContract($searchContratId)];
    $contrats = array_filter($contrats); // Remove null results
} else {
    $contrats = $contratController->getAllContracts();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liste des Partenariats et Contrats</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <h1>Rechercher par ID</h1>
        <form method="GET" class="mb-4">
            <div class="row">
                <div class="col-md-6">
                    <label for="search_partenaire_id" class="form-label">ID Partenaire</label>
                    <input type="text" class="form-control" id="search_partenaire_id" name="search_partenaire_id" placeholder="Entrez l'ID du partenaire">
                </div>
                <div class="col-md-6">
                    <label for="search_contrat_id" class="form-label">ID Contrat</label>
                    <input type="text" class="form-control" id="search_contrat_id" name="search_contrat_id" placeholder="Entrez l'ID du contrat">
                </div>
            </div>
            <button type="submit" class="btn btn-primary mt-3">Rechercher</button>
        </form>

        <h2>Liste des Demandes de Partenariat</h2>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nom</th>
                    <th>Email</th>
                    <th>Téléphone</th>
                    <th>Montant</th>
                    <th>Description</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($partenaires as $partenaire): ?>
                    <tr>
                        <td><?= htmlspecialchars($partenaire['id_partenaire']) ?></td>
                        <td><?= htmlspecialchars($partenaire['nom']) ?></td>
                        <td><?= htmlspecialchars($partenaire['email']) ?></td>
                        <td><?= htmlspecialchars($partenaire['telephone']) ?></td>
                        <td><?= htmlspecialchars($partenaire['montant']) ?></td>
                        <td><?= htmlspecialchars($partenaire['description']) ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($partenaires)): ?>
                    <tr>
                        <td colspan="6" class="text-center">Aucune demande trouvée</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <h2>Liste des Contrats</h2>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>ID Partenaire</th>
                    <th>Date Début</th>
                    <th>Date Fin</th>
                    <th>Termes</th>
                    <th>Statut</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($contrats as $contrat): ?>
                    <tr>
                        <td><?= htmlspecialchars($contrat['id_contrat']) ?></td>
                        <td><?= htmlspecialchars($contrat['id_partenaire']) ?></td>
                        <td><?= htmlspecialchars($contrat['date_deb']) ?></td>
                        <td><?= htmlspecialchars($contrat['date_fin']) ?></td>
                        <td><?= htmlspecialchars($contrat['terms']) ?></td>
                        <td><?= htmlspecialchars($contrat['status']) ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($contrats)): ?>
                    <tr>
                        <td colspan="6" class="text-center">Aucun contrat trouvé</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
