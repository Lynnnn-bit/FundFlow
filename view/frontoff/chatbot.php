<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../control/financecontroller.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: connexion.php");
    exit();
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
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&family=Playfair+Display:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <style>
        /* Chatbot Specific Styles */
        .chatbot-container {
            background: var(--glass-bg);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid var(--glass-border);
            box-shadow: var(--shadow-lg);
            border-radius: 16px;
            overflow: hidden;
            margin-top: 2rem;
        }

        .chatbot-header {
            display: flex;
            align-items: center;
            padding: 1.5rem;
            background: rgba(0,0,0,0.1);
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }

        .chatbot-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
            margin-right: 1rem;
        }

        .chatbot-title {
            flex: 1;
        }

        .chatbot-title h3 {
            font-size: 1.25rem;
            margin-bottom: 0.25rem;
            color: white;
        }

        .chatbot-title p {
            font-size: 0.875rem;
            color: rgba(255,255,255,0.7);
        }

        .language-switcher {
            display: flex;
            gap: 0.5rem;
        }

        .lang-btn {
            background: rgba(255,255,255,0.1);
            border: none;
            border-radius: 4px;
            padding: 0.5rem 0.75rem;
            color: white;
            cursor: pointer;
            transition: var(--transition);
        }

        .lang-btn.active {
            background: var(--primary);
        }

        .chatbot-messages {
            height: 400px;
            overflow-y: auto;
            padding: 1.5rem;
            background: rgba(0,0,0,0.05);
        }

        .message {
            display: flex;
            margin-bottom: 1.5rem;
            max-width: 80%;
        }

        .bot-message {
            align-self: flex-start;
        }

        .user-message {
            align-self: flex-end;
            flex-direction: row-reverse;
        }

        .message-content {
            padding: 1rem;
            border-radius: 12px;
            position: relative;
            line-height: 1.5;
        }

        .bot-message .message-content {
            background: rgba(255,255,255,0.1);
            border-top-left-radius: 0;
            margin-left: 1rem;
        }

        .user-message .message-content {
            background: var(--primary);
            border-top-right-radius: 0;
            margin-right: 1rem;
        }

        .message-content p {
            margin: 0;
            color: white;
        }

        .message-time {
            font-size: 0.75rem;
            color: rgba(255,255,255,0.5);
            align-self: flex-end;
            margin-bottom: 0.5rem;
        }

        .chatbot-input-container {
            display: flex;
            padding: 1rem;
            background: rgba(0,0,0,0.1);
            border-top: 1px solid rgba(255,255,255,0.1);
        }

        .chatbot-input {
            flex: 1;
            padding: 1rem;
            background: rgba(255,255,255,0.9);
            border: 1px solid rgba(255,255,255,0.3);
            border-radius: 12px;
            font-size: 1rem;
            color: var(--dark);
            transition: var(--transition);
        }

        .chatbot-input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.3);
        }

        .chatbot-send-btn {
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 12px;
            padding: 0 1.5rem;
            margin-left: 0.5rem;
            cursor: pointer;
            transition: var(--transition);
        }

        .chatbot-send-btn:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
        }

        .chatbot-voice-btn {
            background: rgba(255,255,255,0.1);
            color: white;
            border: none;
            border-radius: 12px;
            padding: 0 1rem;
            margin-left: 0.5rem;
            cursor: pointer;
            transition: var(--transition);
        }

        .chatbot-voice-btn:hover {
            background: rgba(255,255,255,0.2);
        }

        .chatbot-voice-btn.active {
            background: #ff4d4d;
        }

        .suggestions-container {
            padding: 1rem;
            background: rgba(0,0,0,0.1);
            border-top: 1px solid rgba(255,255,255,0.1);
        }

        .suggestions-container p {
            font-size: 0.875rem;
            color: rgba(255,255,255,0.7);
            margin-bottom: 0.75rem;
        }

        .suggestion-buttons {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .suggestion-btn {
            background: rgba(255,255,255,0.1);
            border: none;
            border-radius: 50px;
            padding: 0.5rem 1rem;
            font-size: 0.875rem;
            color: white;
            cursor: pointer;
            transition: var(--transition);
        }

        .suggestion-btn:hover {
            background: rgba(255,255,255,0.2);
        }

        .typing-indicator {
            display: flex;
            align-items: center;
            padding: 1rem;
        }

        .typing-dots {
            display: flex;
            align-items: center;
            height: 17px;
        }

        .typing-dots span {
            width: 8px;
            height: 8px;
            margin: 0 2px;
            background-color: rgba(255,255,255,0.7);
            border-radius: 50%;
            display: inline-block;
            animation: typingAnimation 1.4s infinite ease-in-out both;
        }

        .typing-dots span:nth-child(1) {
            animation-delay: -0.32s;
        }

        .typing-dots span:nth-child(2) {
            animation-delay: -0.16s;
        }

        @keyframes typingAnimation {
            0%, 80%, 100% { transform: scale(0); }
            40% { transform: scale(1); }
        }

        @media (max-width: 768px) {
            .message {
                max-width: 90%;
            }
            
            .chatbot-messages {
                height: 300px;
            }
            
            .chatbot-header {
                padding: 1rem;
            }
        }
        /* Button Container Styles */
        .action-buttons {
            display: flex;
            gap: 15px;
            margin: 25px 0;
            flex-wrap: wrap;
            justify-content: center;
        }

        /* Base Button Styles */
        .action-btn {
            position: relative;
            padding: 12px 25px;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            min-width: 180px;
            overflow: hidden;
            color: white;
        }

        /* Button Hover Effects */
        .action-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
        }

        .action-btn:active {
            transform: translateY(1px);
        }

        /* Button Before Pseudo-element (for animation) */
        .action-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: 0.5s;
        }

        .action-btn:hover::before {
            left: 100%;
        }

        /* Individual Button Colors */
        .btn-new {
            background: linear-gradient(135deg, #4e54c8, #8f94fb);
            border-left: 4px solid #8f94fb;
        }

        .btn-history {
            background: linear-gradient(135deg, #11998e, #38ef7d);
            border-left: 4px solid #38ef7d;
        }

        .btn-stats {
            background: linear-gradient(135deg, #f46b45, #eea849);
            border-left: 4px solid #eea849;
        }

        .btn-chatbot {
            background: linear-gradient(135deg, #8E2DE2, #4A00E0);
            border-left: 4px solid #4A00E0;
        }

        /* Button Icons */
        .action-btn i {
            margin-right: 8px;
            font-size: 18px;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .action-buttons {
                flex-direction: column;
                align-items: center;
            }
            
            .action-btn {
                width: 100%;
                max-width: 250px;
            }
        }

        /* Enhanced Styling for "Mon compte" Dropdown */
        .profile-menu-container {
            position: relative;
        }

        .profile-menu-btn {
            background: linear-gradient(135deg, #3a56d4, #10b981);
            color: white;
            border: none;
            border-radius: 50px;
            padding: 0.75rem 1.5rem;
            font-size: 0.95rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .profile-menu-btn:hover {
            background: linear-gradient(135deg, #10b981, #3a56d4);
            transform: translateY(-2px);
            box-shadow: 0 8px 12px rgba(0, 0, 0, 0.2);
        }

        .profile-menu {
            display: none;
            position: absolute;
            top: 100%;
            right: 0;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 12px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
            list-style: none;
            padding: 0.5rem 0;
            margin: 0;
            z-index: 10;
            animation: fadeIn 0.3s ease;
        }

        .profile-menu li {
            padding: 0.5rem 1rem;
            transition: background 0.3s ease;
        }

        .profile-menu li a {
            text-decoration: none;
            color: #333;
            font-weight: 500;
            display: block;
            transition: color 0.3s ease, background 0.3s ease;
        }

        .profile-menu li:hover {
            background: rgba(16, 185, 129, 0.1);
        }

        .profile-menu li a:hover {
            color: #10b981;
        }

        .profile-menu li a.logout {
            color: #dc2626;
            font-weight: 600;
        }

        .profile-menu li a.logout:hover {
            color: white;
            background: #dc2626;
        }

        .profile-menu-container:hover .profile-menu {
            display: block;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>
<body>
<div class="background-effect"></div>
<div class="particles-container" id="particles-js"></div>

<div class="dashboard-container">
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

    <div class="page-header animate__animated animate__fadeInDown">
        <h1><i class="fas fa-robot header-icon"></i> Assistant Virtuel</h1>
        <p>Obtenez des réponses à vos questions sur les demandes de financement</p>
    </div>

    <div class="action-buttons">
        <a href="financemet.php" class="action-btn btn-new animate__animated animate__fadeIn">
            <i class="fas fa-plus-circle"></i> Nouvelle Demande
        </a>
        <a href="historique.php" class="action-btn btn-history animate__animated animate__fadeIn animate__delay-1s">
            <i class="fas fa-history"></i> Historique
        </a>
        <a href="statistiquesf.php" class="action-btn btn-stats animate__animated animate__fadeIn animate__delay-2s">
            <i class="fas fa-chart-pie"></i> Statistiques
        </a>
        <a href="chatbot.php" class="action-btn btn-chatbot animate__animated animate__fadeIn animate__delay-3s">
            <i class="fas fa-robot"></i> Chatbot
        </a>
    </div>

    <div class="chatbot-container animate__animated animate__fadeInUp">
        <div class="chatbot-header">
            <div class="chatbot-avatar">
                <i class="fas fa-robot"></i>
            </div>
            <div class="chatbot-title">
                <h3>Assistant FundFlow</h3>
                <p>En ligne</p>
            </div>
            <div class="language-switcher">
                <button id="switch-to-french" class="lang-btn active" title="Français">FR</button>
                <button id="switch-to-english" class="lang-btn" title="English">EN</button>
            </div>
        </div>
        
        <div id="chatbot-messages" class="chatbot-messages">
            <div class="message bot-message">
                <div class="message-content">
                    <p>Bonjour! Je suis votre assistant virtuel. Posez-moi vos questions sur les demandes de financement, les statuts ou les procédures.</p>
                </div>
                <div class="message-time"><?= date('H:i') ?></div>
            </div>
        </div>
        
        <div class="chatbot-input-container">
            <input type="text" id="chatbot-input" placeholder="Tapez votre message ici..." class="chatbot-input">
            <button id="send-message" class="chatbot-send-btn">
                <i class="fas fa-paper-plane"></i>
            </button>
            <button id="voice-input" class="chatbot-voice-btn">
                <i class="fas fa-microphone"></i>
            </button>
        </div>
        
        <div class="suggestions-container">
            <p>Suggestions:</p>
            <div class="suggestion-buttons">
                <button class="suggestion-btn">Statut de ma demande</button>
                <button class="suggestion-btn">Montant total demandé</button>
                <button class="suggestion-btn">Comment créer une demande</button>
                <button class="suggestion-btn">Mes demandes</button>
            </div>
        </div>
    </div>
</div>

<footer class="footer">
  <div class="footer-container">
    <div class="footer-col logo-col">
      <img src="assets/Logo_FundFlow.png" alt="Company Logo" class="footer-logo">
      <p class="footer-description">Plateforme de financement collaboratif</p>
    </div>
    <div class="footer-col links-col">
      <h4>Liens Rapides</h4>
      <ul>
        <li><a href="financemet.php">Accueil</a></li>
        <li><a href="#">À propos</a></li>
        <li><a href="#">Services</a></li>
        <li><a href="#">Blog</a></li>
        <li><a href="#">Contact</a></li>
      </ul>
    </div>
    <div class="footer-col contact-col">
      <h4>Contactez-nous</h4>
      <p>123 Rue de Finance, Paris 75001</p>
      <p>Email: <a href="mailto:contact@fundflow.com">contact@fundflow.com</a></p>
      <p>Tél: +33 1 23 45 67 89</p>
    </div>
    <div class="footer-col social-col">
      <h4>Suivez-nous</h4>
      <div class="social-icons">
        <a href="#" aria-label="Facebook"><i class="fab fa-facebook"></i></a>
        <a href="#" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
        <a href="#" aria-label="LinkedIn"><i class="fab fa-linkedin"></i></a>
        <a href="#" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
      </div>
    </div>
  </div>
  <div class="footer-legal">
    <a href="#">Politique de confidentialité</a> |
    <a href="#">Conditions d'utilisation</a> |
    <span>&copy; 2025 FundFlow. Tous droits réservés.</span>
  </div>
</footer>

<script src="https://cdn.jsdelivr.net/particles.js/2.0.0/particles.min.js"></script>
<script>
// Initialize particles.js
particlesJS("particles-js", {
    "particles": {
        "number": {
            "value": 60,
            "density": {
                "enable": true,
                "value_area": 800
            }
        },
        "color": {
            "value": "#4cc9f0"
        },
        "shape": {
            "type": "circle",
            "stroke": {
                "width": 0,
                "color": "#000000"
            }
        },
        "opacity": {
            "value": 0.5,
            "random": true,
            "anim": {
                "enable": true,
                "speed": 1,
                "opacity_min": 0.1,
                "sync": false
            }
        },
        "size": {
            "value": 3,
            "random": true,
            "anim": {
                "enable": true,
                "speed": 2,
                "size_min": 0.1,
                "sync": false
            }
        },
        "line_linked": {
            "enable": true,
            "distance": 150,
            "color": "#4cc9f0",
            "opacity": 0.4,
            "width": 1
        },
        "move": {
            "enable": true,
            "speed": 1,
            "direction": "none",
            "random": true,
            "straight": false,
            "out_mode": "out",
            "bounce": false,
            "attract": {
                "enable": true,
                "rotateX": 600,
                "rotateY": 1200
            }
        }
    },
    "interactivity": {
        "detect_on": "canvas",
        "events": {
            "onhover": {
                "enable": true,
                "mode": "grab"
            },
            "onclick": {
                "enable": true,
                "mode": "push"
            },
            "resize": true
        },
        "modes": {
            "grab": {
                "distance": 140,
                "line_linked": {
                    "opacity": 1
                }
            },
            "push": {
                "particles_nb": 4
            }
        }
    },
    "retina_detect": true
});

function handleMenu(select) {
    const value = select.value;
    if (value === 'logout') {
        window.location.href = 'connexion.php?logout=1';
    } else if (value) {
        window.location.href = value + '.php';
    }
    select.value = ''; // Réinitialiser la sélection
}

// Chatbot functionality
const chatbotMessages = document.getElementById('chatbot-messages');
const chatbotInput = document.getElementById('chatbot-input');
const sendMessageButton = document.getElementById('send-message');
const voiceInputButton = document.getElementById('voice-input');
const frenchBtn = document.getElementById('switch-to-french');
const englishBtn = document.getElementById('switch-to-english');
const suggestionButtons = document.querySelectorAll('.suggestion-btn');

let currentLanguage = 'fr'; // Default to French
let isListening = false;
let recognition = null;

// Initialize speech recognition if available
if ('webkitSpeechRecognition' in window) {
    recognition = new webkitSpeechRecognition();
    recognition.continuous = false;
    recognition.interimResults = false;
    
    recognition.onstart = function() {
        isListening = true;
        voiceInputButton.innerHTML = '<i class="fas fa-microphone-slash"></i>';
        voiceInputButton.classList.add('active');
    };
    
    recognition.onresult = function(event) {
        const transcript = event.results[0][0].transcript;
        chatbotInput.value = transcript;
        handleSendMessage();
    };
    
    recognition.onerror = function(event) {
        console.error('Speech recognition error', event.error);
        addMessage('bot', currentLanguage === 'fr' 
            ? "Désolé, je n'ai pas pu comprendre votre voix. Essayez de taper votre message." 
            : "Sorry, I couldn't understand your voice. Please try typing your message.");
    };
    
    recognition.onend = function() {
        isListening = false;
        voiceInputButton.innerHTML = '<i class="fas fa-microphone"></i>';
        voiceInputButton.classList.remove('active');
    };
} else {
    voiceInputButton.style.display = 'none';
}

function toggleVoiceInput() {
    if (!recognition) return;
    
    if (isListening) {
        recognition.stop();
    } else {
        recognition.lang = currentLanguage === 'fr' ? 'fr-FR' : 'en-US';
        recognition.start();
    }
}

function setLanguage(lang) {
    currentLanguage = lang;
    if (lang === 'fr') {
        frenchBtn.classList.add('active');
        englishBtn.classList.remove('active');
        chatbotInput.placeholder = "Tapez votre message ici...";
        addMessage('bot', "Je parle maintenant en français. Comment puis-je vous aider?");
    } else {
        frenchBtn.classList.remove('active');
        englishBtn.classList.add('active');
        chatbotInput.placeholder = "Type your message here...";
        addMessage('bot', "I'm now speaking in English. How can I help you?");
    }
}

function addMessage(sender, message) {
    const messageElement = document.createElement('div');
    messageElement.classList.add('message', `${sender}-message`);
    
    const messageContent = document.createElement('div');
    messageContent.classList.add('message-content');
    
    // Handle markdown-like formatting (bold, italics, lists)
    let formattedMessage = message
        .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>') // bold
        .replace(/\*(.*?)\*/g, '<em>$1</em>') // italics
        .replace(/• (.*?)(?=\n|$)/g, '• $1<br>'); // lists
    
    messageContent.innerHTML = `<p>${formattedMessage}</p>`;
    
    const messageTime = document.createElement('div');
    messageTime.classList.add('message-time');
    messageTime.textContent = new Date().toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
    
    messageElement.appendChild(messageContent);
    messageElement.appendChild(messageTime);
    chatbotMessages.appendChild(messageElement);
    chatbotMessages.scrollTop = chatbotMessages.scrollHeight;
}

async function sendToServer(message) {
    try {
        // Show typing indicator
        showTypingIndicator();
        
        // Call the backend handler
        const response = await fetch('chatbot_handler.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                message: message,
                language: currentLanguage
            })
        });
        
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        
        const data = await response.json();
        
        // Hide typing indicator
        hideTypingIndicator();
        
        // Add the bot's response
        addMessage('bot', data.response);
        
        // Handle language switch if suggested
        if (data.suggestedLanguage && data.suggestedLanguage !== currentLanguage) {
            setLanguage(data.suggestedLanguage);
        }
        
    } catch (error) {
        console.error('Error:', error);
        hideTypingIndicator();
        const errorMsg = currentLanguage === 'fr' 
            ? "Désolé, une erreur s'est produite. Veuillez réessayer." 
            : "Sorry, an error occurred. Please try again.";
        addMessage('bot', errorMsg);
    }
}

function handleSendMessage() {
    const userMessage = chatbotInput.value.trim();
    if (userMessage) {
        addMessage('user', userMessage);
        chatbotInput.value = '';
        sendToServer(userMessage);
    }
}

// Event listeners
sendMessageButton.addEventListener('click', handleSendMessage);
chatbotInput.addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        e.preventDefault();
        handleSendMessage();
    }
});

voiceInputButton.addEventListener('click', toggleVoiceInput);
frenchBtn.addEventListener('click', () => setLanguage('fr'));
englishBtn.addEventListener('click', () => setLanguage('en'));

// Add suggestion button handlers
suggestionButtons.forEach(button => {
    button.addEventListener('click', function() {
        chatbotInput.value = this.textContent;
        handleSendMessage();
    });
});

// Typing indicator
function showTypingIndicator() {
    const typingElement = document.createElement('div');
    typingElement.classList.add('message', 'bot-message', 'typing-indicator');
    typingElement.id = 'typing-indicator';
    
    const typingContent = document.createElement('div');
    typingContent.classList.add('message-content');
    typingContent.innerHTML = '<div class="typing-dots"><span></span><span></span><span></span></div>';
    
    typingElement.appendChild(typingContent);
    chatbotMessages.appendChild(typingElement);
    chatbotMessages.scrollTop = chatbotMessages.scrollHeight;
}

function hideTypingIndicator() {
    const typingElement = document.getElementById('typing-indicator');
    if (typingElement) {
        typingElement.remove();
    }
}
</script>
</body>
</html>