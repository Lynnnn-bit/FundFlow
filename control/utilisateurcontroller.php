<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../control/utilisateurcontroller.php';

class UtilisateurController
{
    private $db;

    public function __construct()
    {
        $this->db = Config::getConnexion();
    }

    public function createUser(Utilisateur $user)
    {
        $stmt = $this->db->prepare("
            INSERT INTO utilisateur
            (id_utilisateur, nom, prenom, email, mdp, role, status, adresse, date_creation, tel)
            VALUES
            (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        // Hash password
        $hashedPassword = password_hash($user->getMdp(), PASSWORD_DEFAULT);
        
        return $stmt->execute([
            $user->getId(),
            $user->getNom(),
            $user->getPrenom(),
            $user->getEmail(),
            $hashedPassword,
            $user->getRole(),
            $user->getStatus(),
            $user->getAdresse(),
            $user->getDateCreation(),
            $user->getTel(),
           // $user->getImage() // Added image field
        ]);
    }

    public function updateUser(Utilisateur $user)
{
    $stmt = $this->db->prepare("
        UPDATE utilisateur
        SET nom = ?, prenom = ?, email = ?, mdp = ?, role = ?, status = ?, adresse = ?, tel = ?
        WHERE id_utilisateur = ?
    ");

    return $stmt->execute([
        $user->getNom(),
        $user->getPrenom(),
        $user->getEmail(),
        $user->getMdp(),
        $user->getRole(),
        $user->getStatus(),
        $user->getAdresse(),
        $user->getTel(),
        //$user->getImage(),
        $user->getId()
    ]);
}

    public function getUserById($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM utilisateur WHERE id_utilisateur = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getAllUsers()
    {
        $stmt = $this->db->query("SELECT * FROM utilisateur ORDER BY id_utilisateur DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function deleteUser($id)
    {
        $stmt = $this->db->prepare("DELETE FROM utilisateur WHERE id_utilisateur = ?");
        return $stmt->execute([$id]);
    }

    public function getNextUserId()
    {
        $stmt = $this->db->query("SELECT MAX(id_utilisateur) FROM utilisateur");
        $maxId = $stmt->fetchColumn();
        return $maxId ? $maxId + 1 : 1;
    }
}