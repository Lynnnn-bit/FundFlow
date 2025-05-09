<?php
class Consultation {
    private $id_consultation;
    private $id_utilisateur1;
    private $id_utilisateur2;
    private $date_consultation;
    private $heure_deb;
    private $heure_fin;
    private $tarif;

    public function __construct($data) {
        $this->id_consultation = $data['id_consultation'];
        $this->id_utilisateur1 = $data['id_utilisateur1'];
        $this->id_utilisateur2 = $data['id_utilisateur2'];
        $this->date_consultation = $data['date_consultation'];
        $this->heure_deb = $data['heure_deb'];
        $this->heure_fin = $data['heure_fin'];
        $this->tarif = $data['tarif'];
    }

    public function getIdConsultation() {
        return $this->id_consultation;
    }

    public function getIdUtilisateur1() {
        return $this->id_utilisateur1;
    }

    public function getIdUtilisateur2() {
        return $this->id_utilisateur2;
    }

    public function getDateConsultation() {
        return $this->date_consultation;
    }

    public function getHeureDeb() {
        return $this->heure_deb;
    }

    public function getHeureFin() {
        return $this->heure_fin;
    }

    public function getTarif() {
        return $this->tarif;
    }
}