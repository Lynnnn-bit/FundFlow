<?php
class Partenaire {
    private $id_partenaire;
    private $nom;
    private $email;
    private $telephone;
    private $montant;
    private $description;
    private $is_approved;

    public function __construct($nom, $email, $telephone, $montant, $description, $is_approved = false, $id_partenaire = null) {
        $this->nom = $nom;
        $this->email = $email;
        $this->telephone = $telephone;
        $this->montant = $montant;
        $this->description = $description;
        $this->is_approved = $is_approved;
        $this->id_partenaire = $id_partenaire;
    }

    // Getters
    public function getId() { return $this->id_partenaire; }
    public function getNom() { return $this->nom; }
    public function getEmail() { return $this->email; }
    public function getTelephone() { return $this->telephone; }
    public function getMontant() { return $this->montant; }
    public function getDescription() { return $this->description; }
    public function isApproved() { return $this->is_approved; }

    // Setters
    public function setApproval($status) { $this->is_approved = (bool)$status; }
    
    public function trackInSession() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['current_partner'] = $this->email;
    }
    
    public function isCurrentUser() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        return isset($_SESSION['current_partner']) && $_SESSION['current_partner'] === $this->email;
    }
}