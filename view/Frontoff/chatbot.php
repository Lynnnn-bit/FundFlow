<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../controlle/financecontroller.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$controller = new FinanceController();
$existingDemands = $controller->getAllFinanceRequests();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FundFlow - Chatbot</title>
    <link rel="stylesheet" href="css/stylefinan.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <header class="navbar">
        <div class="logo-container">
            <span class="brand-name">FundFlow</span>
        </div>
        <nav>
            <a href="financemet.php"><i class="fas fa-home"></i> Accueil</a>
            <a href="#"><i class="fas fa-info-circle"></i> À propos</a>
            <a href="#"><i class="fas fa-envelope"></i> Contact</a>
            <a href="#" class="logout"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
        </nav>
    </header>

    <div class="main-container">
        <h1 class="mb-4"><i class="fas fa-robot"></i> Chatbot</h1>
        <div id="chatbot-container" class="chatbot-container">
            <div id="chatbot-messages" class="chatbot-messages">
                <div class="message bot-message">Bonjour! Je suis votre assistant. Posez-moi une question sur les demandes.</div>
            </div>
            <div class="chatbot-input">
                <input type="text" id="chatbot-input" placeholder="Posez votre question...">
                <button id="send-message"><i class="fas fa-paper-plane"></i></button>
            </div>
        </div>
    </div>

    <script>
        const chatbotMessages = document.getElementById('chatbot-messages');
        const chatbotInput = document.getElementById('chatbot-input');
        const sendMessageButton = document.getElementById('send-message');

        // Function to add a message to the chat
        function addMessage(sender, message) {
            const messageElement = document.createElement('div');
            messageElement.classList.add('message', `${sender}-message`);
            messageElement.textContent = message;
            chatbotMessages.appendChild(messageElement);
            chatbotMessages.scrollTop = chatbotMessages.scrollHeight;
        }

        // Function to send the message to the server
        function sendToServer(message) {
            fetch('chatbot_handler.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ message }),
            })
            .then(response => response.json())
            .then(data => {
                if (data.response) {
                    addMessage('bot', data.response);
                } else {
                    addMessage('bot', 'Je suis désolé, une erreur est survenue.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                addMessage('bot', 'Je suis désolé, une erreur est survenue.');
            });
        }

        // Function to handle sending the message
        function handleSendMessage() {
            const userMessage = chatbotInput.value.trim();
            if (userMessage) {
                addMessage('user', userMessage);
                chatbotInput.value = '';
                sendToServer(userMessage);
            }
        }

        // Event listener for the send button
        sendMessageButton.addEventListener('click', handleSendMessage);

        // Event listener for the "Enter" key
        chatbotInput.addEventListener('keypress', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                handleSendMessage();
            }
        });
    </script>

    <style>
        .chatbot-container {
            background: rgba(30, 60, 82, 0.8);
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            max-width: 600px;
            margin: 0 auto;
        }

        .chatbot-messages {
            height: 300px;
            overflow-y: auto;
            background: #f4f4f4;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
        }

        .chatbot-input {
            display: flex;
            gap: 0.5rem;
        }

        .chatbot-input input {
            flex: 1;
            padding: 0.8rem;
            border-radius: 8px;
            border: 1px solid #ccc;
        }

        .chatbot-input button {
            background: #1abc9c;
            color: white;
            border: none;
            padding: 0.8rem 1rem;
            border-radius: 8px;
            cursor: pointer;
        }

        .message {
            margin-bottom: 1rem;
            padding: 0.8rem;
            border-radius: 8px;
        }

        .user-message {
            background: #3498db;
            color: white;
            align-self: flex-end;
        }

        .bot-message {
            background: #ecf0f1;
            color: #333;
            align-self: flex-start;
        }
    </style>
</body>
</html>
