<?php
include_once '../../control/EvennementC.php';

$evenementC = new EvennementC();
$evenements = $evenementC->getAllEvenements();

// Initialiser les compteurs
$total = count($evenements);
$en_ligne = 0;
$presentiel = 0;

foreach ($evenements as $event) {
    if (strtolower($event['type']) === 'en ligne') {
        $en_ligne++;
    } elseif (strtolower($event['type']) === 'présentiel') {
        $presentiel++;
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Statistiques des Événements</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f4f4;
            padding: 30px;
        }
        .container {
            max-width: 700px;
            margin: auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.2);
            text-align: center;
        }
        h1 {
            color: #333;
        }
        canvas {
            margin-top: 30px;
        }
    </style>
</head>
<body>

<div class="container">
    <h1>Statistiques des Événements</h1>
    <p><strong>Total :</strong> <?= $total ?></p>
    <p><strong>En ligne :</strong> <?= $en_ligne ?></p>
    <p><strong>Présentiel :</strong> <?= $presentiel ?></p>

    <canvas id="eventChart" width="400" height="400"></canvas>
</div>

<script>
const ctx = document.getElementById('eventChart').getContext('2d');
const eventChart = new Chart(ctx, {
    type: 'pie',
    data: {
        labels: ['En ligne', 'Présentiel'],
        datasets: [{
            label: 'Répartition',
            data: [<?= $en_ligne ?>, <?= $presentiel ?>],
            backgroundColor: ['#3498db', '#2ecc71'],
        }]
    },
    options: {
        responsive: true
    }
});
</script>

</body>
</html>
