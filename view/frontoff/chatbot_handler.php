<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../control/financecontroller.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $message = trim($input['message'] ?? '');
    $language = $input['language'] ?? 'fr'; // Default to French

    $controller = new FinanceController();
    
    // Get user-specific data if available
    $userId = $_SESSION['user_id'] ?? null; // Get the logged-in user's ID
    $userDemands = $userId ? $controller->getFinanceRequestsByUser($userId) : []; // Fetch only the user's demands

    // Enhanced response system with better NLP
    $responses = [
        // Financial summaries
        'montant total|total amount' => [
            'fr' => function() use ($userDemands) {
                $total = array_sum(array_column($userDemands, 'montant_demandee'));
                return "Le montant total demandé pour vos projets est de " . number_format($total, 2, ',', ' ') . " €.";
            },
            'en' => function() use ($userDemands) {
                $total = array_sum(array_column($userDemands, 'montant_demandee'));
                return "The total amount requested for your projects is €" . number_format($total, 2, '.', ',');
            }
        ],
        
        // Status queries
        'statut|status' => [
            'fr' => function() use ($userDemands) {
                $stats = [
                    'accepte' => 0,
                    'rejete' => 0,
                    'en_attente' => 0
                ];
                
                foreach ($userDemands as $demand) {
                    $stats[$demand['status']]++;
                }
                
                return "Statut de vos demandes :\n- Acceptées : {$stats['accepte']}\n- Rejetées : {$stats['rejete']}\n- En attente : {$stats['en_attente']}";
            },
            'en' => function() use ($userDemands) {
                $stats = [
                    'accepte' => 0,
                    'rejete' => 0,
                    'en_attente' => 0
                ];
                
                foreach ($userDemands as $demand) {
                    $stats[$demand['status']]++;
                }
                
                return "Your request status:\n- Accepted: {$stats['accepte']}\n- Rejected: {$stats['rejete']}\n- Pending: {$stats['en_attente']}";
            }
        ],
        
        // User-specific queries
        'mes demandes|my requests' => [
            'fr' => function() use ($userDemands) {
                if (empty($userDemands)) {
                    return "Vous n'avez aucune demande de financement enregistrée.";
                }
                
                $response = "Voici vos demandes de financement :\n";
                foreach ($userDemands as $demand) {
                    $status = [
                        'accepte' => 'Acceptée',
                        'rejete' => 'Rejetée',
                        'en_attente' => 'En attente'
                    ][$demand['status']];
                    
                    $response .= "- **{$demand['titre']}** : " . number_format($demand['montant_demandee'], 2, ',', ' ') . 
                                " € (Statut: {$status})\n";
                }
                return $response;
            },
            'en' => function() use ($userDemands) {
                if (empty($userDemands)) {
                    return "You don't have any registered funding requests.";
                }
                
                $response = "Here are your funding requests:\n";
                foreach ($userDemands as $demand) {
                    $status = [
                        'accepte' => 'Accepted',
                        'rejete' => 'Rejected',
                        'en_attente' => 'Pending'
                    ][$demand['status']];
                    
                    $response .= "- **{$demand['titre']}** : €" . number_format($demand['montant_demandee'], 2, '.', ',') . 
                                " (Status: {$status})\n";
                }
                return $response;
            }
        ],
        
        // Project details
        'projet .+|project .+' => [
            'fr' => function() use ($userDemands, $message) {
                // Extract project name from message
                preg_match('/projet (.+)/i', $message, $matches);
                $projectName = $matches[1] ?? '';
                
                foreach ($userDemands as $demand) {
                    if (stripos($demand['titre'], $projectName) !== false) {
                        $status = [
                            'accepte' => 'Acceptée',
                            'rejete' => 'Rejetée',
                            'en_attente' => 'En attente'
                        ][$demand['status']];
                        
                        return "**Projet: {$demand['titre']}**\n" .
                               "- Montant demandé: " . number_format($demand['montant_demandee'], 2, ',', ' ') . " €\n" .
                               "- Durée: {$demand['duree']} mois\n" .
                               "- Statut: {$status}\n" .
                               "- Description: {$demand['description']}";
                    }
                }
                return "Je n'ai pas trouvé de projet correspondant à '$projectName'.";
            },
            'en' => function() use ($userDemands, $message) {
                preg_match('/project (.+)/i', $message, $matches);
                $projectName = $matches[1] ?? '';
                
                foreach ($userDemands as $demand) {
                    if (stripos($demand['titre'], $projectName) !== false) {
                        $status = [
                            'accepte' => 'Accepted',
                            'rejete' => 'Rejected',
                            'en_attente' => 'Pending'
                        ][$demand['status']];
                        
                        return "**Project: {$demand['titre']}**\n" .
                               "- Requested amount: €" . number_format($demand['montant_demandee'], 2, '.', ',') . "\n" .
                               "- Duration: {$demand['duree']} months\n" .
                               "- Status: {$status}\n" .
                               "- Description: {$demand['description']}";
                    }
                }
                return "I couldn't find a project matching '$projectName'.";
            }
        ],
        
        // Process information
        'comment créer|how to create' => [
            'fr' => "Pour créer une nouvelle demande de financement :\n" .
                    "1. Allez dans la section *Nouvelle Demande*\n" .
                    "2. Remplissez le formulaire avec les détails de votre projet\n" .
                    "3. Soumettez la demande pour approbation\n" .
                    "4. Vous recevrez une notification une fois la demande traitée",
            'en' => "To create a new funding request:\n" .
                    "1. Go to the *New Request* section\n" .
                    "2. Fill out the form with your project details\n" .
                    "3. Submit the request for approval\n" .
                    "4. You'll receive a notification once the request is processed"
        ],
        
        // Language switching
        'parle en français|speak french' => [
            'fr' => "Je parle déjà en français ! Comment puis-je vous aider ?",
            'en' => function() {
                return [
                    'response' => "D'accord ! Je vais maintenant répondre en français.",
                    'suggestedLanguage' => 'fr'
                ];
            }
        ],
        
        'speak english|parle en anglais' => [
            'fr' => function() {
                return [
                    'response' => "Okay! I will now respond in English.",
                    'suggestedLanguage' => 'en'
                ];
            },
            'en' => "I'm already speaking in English! How can I help you?"
        ],
        
        // Greetings
        'bonjour|salut|hello|hi' => [
            'fr' => "Bonjour ! Je suis l'assistant virtuel de FundFlow. Comment puis-je vous aider aujourd'hui ?",
            'en' => "Hello! I'm FundFlow's virtual assistant. How can I help you today?"
        ],
        
        'merci|thank you' => [
            'fr' => "Je vous en prie ! N'hésitez pas si vous avez d'autres questions.",
            'en' => "You're welcome! Feel free to ask if you have more questions."
        ],
        
        // Help
        'aide|help' => [
            'fr' => "Voici ce que je peux faire :\n" .
                    "- Vous informer sur *vos demandes* de financement\n" .
                    "- Donner des détails sur un *projet spécifique*\n" .
                    "- Fournir des *statistiques* globales\n" .
                    "- Expliquer le *processus* de demande\n" .
                    "- Changer de langue (*français/anglais*)\n\n" .
                    "Essayez des questions comme :\n" .
                    "- *Quel est le statut de ma demande ?*\n" .
                    "- *Donne-moi des détails sur le projet X*\n" .
                    "- *Combien de demandes sont en attente ?*",
            'en' => "Here's what I can do:\n" .
                    "- Inform you about *your funding requests*\n" .
                    "- Provide details about a *specific project*\n" .
                    "- Give global *statistics*\n" .
                    "- Explain the application *process*\n" .
                    "- Switch languages (*French/English*)\n\n" .
                    "Try questions like:\n" .
                    "- *What's the status of my request?*\n" .
                    "- *Give me details about project X*\n" .
                    "- *How many requests are pending?*"
        ],
        
        // Default fallback
        'default' => [
            'fr' => "Je n'ai pas compris votre demande. Voici ce que je peux faire :\n" .
                    "- Vous informer sur vos demandes (*mes demandes*)\n" .
                    "- Donner des statistiques globales (*statut des demandes*)\n" .
                    "- Expliquer comment créer une demande (*comment créer*)\n\n" .
                    "Dites *aide* pour plus d'options.",
            'en' => "I didn't understand your request. Here's what I can do:\n" .
                    "- Inform you about your requests (*my requests*)\n" .
                    "- Provide global statistics (*request status*)\n" .
                    "- Explain how to create a request (*how to create*)\n\n" .
                    "Say *help* for more options."
        ]
    ];

    // Process the message and find the best response
    $response = null;
    $suggestedLanguage = null;
    
    foreach ($responses as $pattern => $langResponses) {
        // Split pattern alternatives
        $alternatives = explode('|', $pattern);
        
        foreach ($alternatives as $altPattern) {
            // Check if message matches this pattern (case insensitive)
            if (preg_match("/$altPattern/i", $message)) {
                $handler = $langResponses[$language] ?? $langResponses['en'] ?? null;
                
                if (is_callable($handler)) {
                    $result = $handler();
                    if (is_array($result)) {
                        $response = $result['response'];
                        $suggestedLanguage = $result['suggestedLanguage'] ?? null;
                    } else {
                        $response = $result;
                    }
                } else {
                    $response = $handler;
                }
                
                break 2; // Break both loops
            }
        }
    }
    
    // If no response matched, use default
    if ($response === null) {
        $response = $responses['default'][$language] ?? $responses['default']['en'];
    }
    
    // Prepare response
    $output = ['response' => $response];
    if ($suggestedLanguage) {
        $output['suggestedLanguage'] = $suggestedLanguage;
    }
    
    echo json_encode($output);
    exit;
}

http_response_code(400);
echo json_encode([
    'response' => 'Invalid request. Please send a POST request with a message.',
    'suggestedLanguage' => 'en'
]);