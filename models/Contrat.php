<?php
class Contrat {
    private $id_contrat;
    private $id_partenaire;
    private $status;
    private $date_deb;
    private $date_fin;

    public function __construct($id_partenaire, $date_deb, $date_fin, $status = 'en attente', $id_contrat = null) {
        $this->id_partenaire = $id_partenaire;
        $this->date_deb = $date_deb;
        $this->date_fin = $date_fin;
        $this->status = $status;
        $this->id_contrat = $id_contrat;
    }

    // Getters
    public function getId() { return $this->id_contrat; }


    public function getPartnerId() { return $this->id_partenaire; }
    public function getStatus() { return $this->status; }
    public function getStartDate() { return $this->date_deb; }
    public function getEndDate() { return $this->date_fin; }

    // Setters
    public function setStatus($status) { 
        $valid = ['en attente', 'actif', 'expiré', 'rejeté'];
        if (in_array($status, $valid)) {
            $this->status = $status;
        }
    }
}