<?php
class Startup {
    private $id_startup;
    private $nom_startup;
    private $secteur;
    private $adresse_site;
    private $logo;
    private $description;
    private $email;
    private $video_presentation;

    public function __construct($id, $nom, $secteur, $adresse, $logo, $description, $email, $video) {
        $this->id_startup = $id;
        $this->nom_startup = $nom;
        $this->secteur = $secteur;
        $this->adresse_site = $adresse;
        $this->logo = $logo;
        $this->description = $description;
        $this->email = $email;
        $this->video_presentation = $video;
    }

    // Getters
    public function getIdStartup() { return $this->id_startup; }
    public function getNomStartup() { return $this->nom_startup; }
    public function getSecteur() { return $this->secteur; }
    public function getAdresseSite() { return $this->adresse_site; }
    public function getLogo() { return $this->logo; }
    public function getDescription() { return $this->description; }
    public function getEmail() { return $this->email; }
    public function getVideoPresentation() { return $this->video_presentation; }
}
?>
