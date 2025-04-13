<?php
class Response
{
    private $id_reponse;
    private $id_demande;
    private $decision;
    private $message;
    private $montant_accorde;
    private $date_reponse;
    private $status;

    public function __construct($id_demande, $decision, $message, $montant_accorde, $date_reponse, $status = 'en_attente', $id_reponse = null)
    {
        $this->id_demande = $id_demande;
        $this->decision = $decision;
        $this->message = $message;
        $this->montant_accorde = $montant_accorde;
        $this->date_reponse = $date_reponse;
        $this->status = $status;
        $this->id_reponse = $id_reponse;
    }
    public static function generateNewId($db)
{
    $stmt = $db->query("SELECT MAX(id_reponse) as max_id FROM reponse");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return ($result['max_id'] ?? 0) + 1;
}

    // Getters
    public function getIdReponse() { return $this->id_reponse; }
    public function getIdDemande() { return $this->id_demande; }
    public function getDecision() { return $this->decision; }
    public function getMessage() { return $this->message; }
    public function getMontantAccorde() { return $this->montant_accorde; }
    public function getDateReponse() { return $this->date_reponse; }
    public function getStatus() { return $this->status; }
    
    // Setters
    public function setIdReponse($id_reponse) { $this->id_reponse = $id_reponse; }
    public function setIdDemande($id_demande) { $this->id_demande = $id_demande; }
    public function setDecision($decision) { $this->decision = $decision; }
    public function setMessage($message) { $this->message = $message; }
    public function setMontantAccorde($montant_accorde) { $this->montant_accorde = $montant_accorde; }
    public function setDateReponse($date_reponse) { $this->date_reponse = $date_reponse; }
    public function setStatus($status) { $this->status = $status; }
}