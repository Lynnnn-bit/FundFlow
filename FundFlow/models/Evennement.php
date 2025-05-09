<?php
class Evennement {
    private $id_evenement, $id_startup, $date_evenement, $type, $horaire, $nb_place, $affiche, $nom;

    public function __construct($id_startup, $date_evenement, $type, $horaire, $nb_place, $affiche, $nom) {
        $this->id_startup = $id_startup;
        $this->date_evenement = $date_evenement;
        $this->type = $type;
        $this->horaire = $horaire;
        $this->nb_place = $nb_place;
        $this->affiche = $affiche;
        $this->nom = $nom;
    }

    // Getters
    public function getIdStartup() { return $this->id_startup; }
    public function getDateEvenement() { return $this->date_evenement; }
    public function getType() { return $this->type; }
    public function getHoraire() { return $this->horaire; }
    public function getNbPlace() { return $this->nb_place; }
    public function getAffiche() { return $this->affiche; }
    public function getNom() { return $this->nom; }
}
?>
