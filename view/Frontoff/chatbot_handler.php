<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../controlle/financecontroller.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $message = strtolower(trim($input['message'] ?? ''));

    $controller = new FinanceController();
    $existingDemands = $controller->getAllFinanceRequests();

    $responses = [
        'montant total' => [
            'fr' => function () use ($existingDemands) {
                $totalAmount = number_format(array_sum(array_column($existingDemands, 'montant_demandee')), 2);
                return "Le montant total demandé est de €$totalAmount.";
            },
            'en' => function () use ($existingDemands) {
                $totalAmount = number_format(array_sum(array_column($existingDemands, 'montant_demandee')), 2);
                return "The total amount requested is €$totalAmount.";
            }
        ],
        'acceptées' => [
            'fr' => function () use ($existingDemands) {
                $acceptedCount = count(array_filter($existingDemands, fn($d) => $d['status'] === 'accepte'));
                return "Il y a $acceptedCount demandes acceptées.";
            },
            'en' => function () use ($existingDemands) {
                $acceptedCount = count(array_filter($existingDemands, fn($d) => $d['status'] === 'accepte'));
                return "There are $acceptedCount accepted demands.";
            }
        ],
        'rejetées' => [
            'fr' => function () use ($existingDemands) {
                $rejectedCount = count(array_filter($existingDemands, fn($d) => $d['status'] === 'rejete'));
                return "Il y a $rejectedCount demandes rejetées.";
            },
            'en' => function () use ($existingDemands) {
                $rejectedCount = count(array_filter($existingDemands, fn($d) => $d['status'] === 'rejete'));
                return "There are $rejectedCount rejected demands.";
            }
        ],
        'en attente' => [
            'fr' => function () use ($existingDemands) {
                $pendingCount = count(array_filter($existingDemands, fn($d) => $d['status'] === 'en_attente'));
                return "Il y a $pendingCount demandes en attente.";
            },
            'en' => function () use ($existingDemands) {
                $pendingCount = count(array_filter($existingDemands, fn($d) => $d['status'] === 'en_attente'));
                return "There are $pendingCount pending demands.";
            }
        ],
        'statut' => [
            'fr' => function () use ($existingDemands) {
                $acceptedCount = count(array_filter($existingDemands, fn($d) => $d['status'] === 'accepte'));
                $rejectedCount = count(array_filter($existingDemands, fn($d) => $d['status'] === 'rejete'));
                $pendingCount = count(array_filter($existingDemands, fn($d) => $d['status'] === 'en_attente'));
                return "Statut des demandes : $acceptedCount acceptées, $rejectedCount rejetées, $pendingCount en attente.";
            },
            'en' => function () use ($existingDemands) {
                $acceptedCount = count(array_filter($existingDemands, fn($d) => $d['status'] === 'accepte'));
                $rejectedCount = count(array_filter($existingDemands, fn($d) => $d['status'] === 'rejete'));
                $pendingCount = count(array_filter($existingDemands, fn($d) => $d['status'] === 'en_attente'));
                return "Status of demands: $acceptedCount accepted, $rejectedCount rejected, $pendingCount pending.";
            }
        ],
        'bonjour' => [
            'fr' => 'Bonjour! Comment puis-je vous aider?',
            'en' => 'Hello! How can I assist you?'
        ],
        'merci' => [
            'fr' => 'Avec plaisir! Si vous avez d\'autres questions, n\'hésitez pas.',
            'en' => 'You\'re welcome! If you have more questions, feel free to ask.'
        ],
        'aide' => [
            'fr' => 'Voici quelques exemples de questions que vous pouvez poser : "Combien de demandes sont acceptées ?", "Quel est le montant total demandé ?", ou "Quel est le statut des demandes ?"',
            'en' => 'Here are some examples of questions you can ask: "How many demands are accepted?", "What is the total amount requested?", or "What is the status of the demands?"'
        ],
        'speak in french' => [
            'fr' => 'Je parle déjà en français! Posez-moi une question.',
            'en' => 'D\'accord! Je vais répondre en français. Posez-moi une question.'
        ],
        'speak in english' => [
            'fr' => 'Okay! I will respond in English. Ask me a question.',
            'en' => 'I am already speaking in English! Ask me a question.'
        ]
    ];

    // Function to calculate similarity
    function getBestMatch($message, $phrases) {
        $bestMatch = null;
        $highestSimilarity = 0;

        foreach ($phrases as $phrase => $response) {
            similar_text($message, $phrase, $similarity);
            if ($similarity > $highestSimilarity) {
                $highestSimilarity = $similarity;
                $bestMatch = $phrase;
            }
        }

        return $highestSimilarity >= 80 ? $bestMatch : null;
    }

    // Detect language
    $language = preg_match('/[a-z]/i', $message) ? 'en' : 'fr';

    // Find the best match
    $bestMatch = getBestMatch($message, $responses);

    if ($bestMatch && isset($responses[$bestMatch][$language])) {
        $response = is_callable($responses[$bestMatch][$language])
            ? $responses[$bestMatch][$language]()
            : $responses[$bestMatch][$language];
    } else {
        $response = $language === 'fr'
            ? 'Je suis désolé, je ne comprends pas votre question. Essayez de poser une question comme : "Combien de demandes sont acceptées ?" ou "Quel est le montant total demandé ?"'
            : 'I\'m sorry, I don\'t understand your question. Try asking something like: "How many demands are accepted?" or "What is the total amount requested?"';
    }

    echo json_encode(['response' => $response]);
    exit;
}

http_response_code(400);
echo json_encode(['response' => 'Invalid request.']);
