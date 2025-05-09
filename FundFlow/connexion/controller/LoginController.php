<?php
require_once './model/UserModel.php';

class LoginController {
    public function handleRequest() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            $remember = isset($_POST['remember']);

            $model = new UserModel();
            if ($model->checkCredentials($email, $password)) {
                echo "Connexion réussie !";
                // Redirection ou session ici
            } else {
                $error = "Email ou mot de passe incorrect.";
            }
        }

        include './view/login.php';
    }
}
