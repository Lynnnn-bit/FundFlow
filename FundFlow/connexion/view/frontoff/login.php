<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion - FundFlow</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<div class="container">
    <header>
        <nav>
            <a href="#">Investisseurs</a> |
            <a href="#">Entrepreneurs</a> |
            <a href="#">À propos</a> |
            <a href="#">Contact</a>
            <div style="float:right;">
                <button>Connexion</button>
                <button style="background-color: #2ecc9c;">Inscription</button>
            </div>
        </nav>
    </header>

    <div class="form-box">
        <h2>Connexion</h2>
        <p>Entrez vos identifiants pour accéder à votre espace</p>
        <?php if (isset($error)) echo "<p style='color:red;'>$error</p>"; ?>
        <form method="POST">
            <label>Email</label>
            <input type="email" name="email" placeholder="nom@exemple.com" required>

            <label>Mot de passe</label>
            <input type="password" name="password" placeholder="Votre mot de passe" required>

            <label><input type="checkbox" name="remember"> Se souvenir de moi</label>
            <div style="text-align:right;"><a href="#">Mot de passe oublié?</a></div>

            <button type="submit">Se connecter</button>
        </form>
        <p>Pas encore de compte? <a href="#">S’inscrire</a></p>
    </div>

    <footer>
        <div class="footer-columns">
            <div>
                <h4>FundFlow</h4>
                <p>Connecter l'innovation avec le capital pour un avenir financier plus inclusif.</p>
            </div>
            <div>
                <h4>Liens Rapides</h4>
                <p>Pour les investisseurs<br>Pour les entrepreneurs<br>À propos de nous<br>FAQ</p>
            </div>
            <div>
                <h4>Légal</h4>
                <p>Politique de confidentialité<br>Conditions d’utilisation<br>Mentions légales</p>
            </div>
            <div>
                <h4>Contact</h4>
                <p>123 Avenue de la Finance, Paris<br>+33 1 23 45 67 89<br>contact@fundflow.fr</p>
            </div>
        </div>
        <p style="text-align: center;">© 2025 FundFlow. Tous droits réservés.</p>
    </footer>
</div>
</body>
</html>
