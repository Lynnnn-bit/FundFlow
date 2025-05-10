<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../control/ContratController.php';

session_start();

$contratController = new ContratController();
$contrats = $contratController->getAllContracts();

// Calculate statistics
$statusCounts = [
    'en attente' => 0,
    'actif' => 0,
    'expiré' => 0,
    'rejeté' => 0,
];

foreach ($contrats as $contrat) {
    if (isset($statusCounts[$contrat['status']])) {
        $statusCounts[$contrat['status']]++;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Statistiques des Contrats</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            margin: 0;
            font-family: 'Inter', sans-serif;
            background: linear-gradient(to right, #0f2027, #203a43, #2c5364);
            color: white;
            min-height: 100vh;
        }

        .container {
            max-width: 700px; /* Reduced width for better fit */
            margin: 0 auto;
            padding: 1.5rem; /* Reduced padding */
        }

        h1 {
            font-size: 1.6rem; /* Slightly smaller font size */
            text-align: center;
            color: #00d09c;
            margin-bottom: 1.5rem; /* Reduced margin */
        }

        .btn-primary {
            background: linear-gradient(to right, #00d09c, #1abc9c);
            color: white;
            border: none;
            padding: 0.7rem 1.2rem; /* Reduced padding */
            border-radius: 8px;
            font-size: 0.9rem; /* Slightly smaller font size */
            font-weight: 600;
            transition: all 0.3s;
        }

        .btn-primary:hover {
            background: linear-gradient(to right, #1abc9c, #3498db);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 208, 156, 0.3);
        }

        .card {
            background: rgba(30, 60, 82, 0.8);
            border-radius: 16px;
            padding: 1.5rem; /* Reduced padding */
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.3); /* Slightly reduced shadow */
            margin-bottom: 1.5rem; /* Reduced margin */
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        canvas {
            max-width: 100%;
            height: 300px; /* Reduced height for better fit */
        }
    </style>
</head>
<body>
    <div class="container">
        <h1><i class="fas fa-chart-pie me-2"></i>Statistiques des Contrats</h1>

        <!-- Moved the button above the table -->
        <a href="contrats.php" class="btn btn-primary mb-4"><i class="fas fa-arrow-left"></i> Retour</a>

        <div class="card shadow-lg">
            <div class="card-body">
                <canvas id="contractsChart"></canvas>
            </div>
        </div>
    </div>

    <script>
        const ctx = document.getElementById('contractsChart').getContext('2d');
        const contractsChart = new Chart(ctx, {
            type: 'pie',
            data: {
                labels: ['En attente', 'Actif', 'Expiré', 'Rejeté'],
                datasets: [{
                    label: 'Statistiques des Contrats',
                    data: [
                        <?= $statusCounts['en attente'] ?>,
                        <?= $statusCounts['actif'] ?>,
                        <?= $statusCounts['expiré'] ?>,
                        <?= $statusCounts['rejeté'] ?>
                    ],
                    backgroundColor: [
                        '#f39c12', // En attente
                        '#00d09c', // Actif
                        '#e74c3c', // Expiré
                        '#7f8c8d'  // Rejeté
                    ],
                    borderColor: [
                        '#ffffff',
                        '#ffffff',
                        '#ffffff',
                        '#ffffff'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            color: 'white'
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.raw || 0;
                                return `${label}: ${value}`;
                            }
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>
