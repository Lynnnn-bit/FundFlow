<?php
class Project
{
    private $id_projet;
    private $id_utilisateur;
    private $titre;
    private $description;
    private $montant_cible;
    private $status;
    private $duree;
    private $id_categorie;

    public function __construct($id_utilisateur, $titre, $description, $montant_cible, $duree, $id_categorie = null, $status = 'en_attente', $id_projet = null)
    {
        $this->id_utilisateur = $id_utilisateur;
        $this->titre = $titre;
        $this->description = $description;
        $this->montant_cible = $montant_cible;
        $this->status = $status;
        $this->duree = $duree;
        $this->id_categorie = $id_categorie;
        $this->id_projet = $id_projet;
    }

    // Getters
    public function getIdProjet() { return $this->id_projet; }
    public function getIdUtilisateur() { return $this->id_utilisateur; }
    public function getTitre() { return $this->titre; }
    public function getDescription() { return $this->description; }
    public function getMontantCible() { return $this->montant_cible; }
    public function getStatus() { return $this->status; }
    public function getDuree() { return $this->duree; }
    public function getIdCategorie() { return $this->id_categorie; }

    // Setters
    public function setIdProjet($id_projet) { $this->id_projet = $id_projet; }
    public function setTitre($titre) { $this->titre = $titre; }
    public function setDescription($description) { $this->description = $description; }
    public function setMontantCible($montant_cible) { $this->montant_cible = $montant_cible; }
    public function setStatus($status) { $this->status = $status; }
    public function setDuree($duree) { $this->duree = $duree; }
    public function setIdCategorie($id_categorie) { $this->id_categorie = $id_categorie; }
}