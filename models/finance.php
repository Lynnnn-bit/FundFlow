<?php
class Finance
{
    private $id_demande;
    private $id_project;
    private $id_utilisateur;
    private $duree;
    private $montant_demandee;
    private $status;

    public function __construct($id_project, $id_utilisateur, $duree, $montant_demandee, $status = 'en_attente', $id_demande = null)
    {
        $this->id_project = $id_project;
        $this->id_utilisateur = $id_utilisateur;
        $this->duree = $duree;
        $this->montant_demandee = $montant_demandee;
        $this->status = $status;
        $this->id_demande = $id_demande;
    }

    // Getters
    public function getIdDemande() { return $this->id_demande; }
    public function getIdProject() { return $this->id_project; }
    public function getIdUtilisateur() { return $this->id_utilisateur; }
    public function getDuree() { return $this->duree; }
    public function getMontantDemandee() { return $this->montant_demandee; }
    public function getStatus() { return $this->status; }
    
    // Setters
    public function setIdDemande($id_demande) { $this->id_demande = $id_demande; }
    public function setIdProject($id_project) { $this->id_project = $id_project; }
    public function setIdUtilisateur($id_utilisateur) { $this->id_utilisateur = $id_utilisateur; }
    public function setDuree($duree) { $this->duree = $duree; }
    public function setMontantDemandee($montant_demandee) { $this->montant_demandee = $montant_demandee; }
    public function setStatus($status) { $this->status = $status; }
}