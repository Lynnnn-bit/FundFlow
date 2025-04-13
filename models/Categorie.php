<?php
class Categorie
{
    private $id_categorie;
    private $nom_categorie;
    private $description;

    public function __construct($id_categorie, $nom_categorie, $description)
    {
        $this->id_categorie = $id_categorie;
        $this->nom_categorie = $nom_categorie;
        $this->description = $description;
    }

    // Getters
    public function getIdCategorie() { return $this->id_categorie; }
    public function getNomCategorie() { return $this->nom_categorie; }
    public function getDescription() { return $this->description; }

    // Setters
    public function setIdCategorie($id_categorie) { $this->id_categorie = $id_categorie; }
    public function setNomCategorie($nom_categorie) { $this->nom_categorie = $nom_categorie; }
    public function setDescription($description) { $this->description = $description; }
}