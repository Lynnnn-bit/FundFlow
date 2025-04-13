<?php
class Utilisateur
{
    private $id_utilisateur;
    private $nom;
    private $prenom;
    private $email;
    private $mdp;
    private $role;
    private $status;
    private $adresse;
    private $date_creation;
    private $tel;

    public function __construct($nom, $prenom, $email, $mdp, $role, $status, $adresse, $tel, $id_utilisateur = null, $date_creation = null)
    {
        $this->nom = $nom;
        $this->prenom = $prenom;
        $this->email = $email;
        $this->mdp = $mdp;
        $this->role = $role;
        $this->status = $status;
        $this->adresse = $adresse;
        $this->tel = $tel;
        $this->id_utilisateur = $id_utilisateur;
        $this->date_creation = $date_creation ?: date('Y-m-d');
    }

    // Getters
    public function getId() { return $this->id_utilisateur; }
    public function getNom() { return $this->nom; }
    public function getPrenom() { return $this->prenom; }
    public function getEmail() { return $this->email; }
    public function getMdp() { return $this->mdp; }
    public function getRole() { return $this->role; }
    public function getStatus() { return $this->status; }
    public function getAdresse() { return $this->adresse; }
    public function getDateCreation() { return $this->date_creation; }
    public function getTel() { return $this->tel; }

    // Setters
    public function setNom($nom) { $this->nom = $nom; }
    public function setPrenom($prenom) { $this->prenom = $prenom; }
    public function setEmail($email) { $this->email = $email; }
    public function setMdp($mdp) { $this->mdp = $mdp; }
    public function setRole($role) { $this->role = $role; }
    public function setStatus($status) { $this->status = $status; }
    public function setAdresse($adresse) { $this->adresse = $adresse; }
    public function setTel($tel) { $this->tel = $tel; }
}