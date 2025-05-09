<?php
class UserModel {
    public function checkCredentials($email, $password) {
        // Juste pour la démonstration
        return $email === 'nom@exemple.com' && $password === '123456';
    }
}
