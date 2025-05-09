<?php
class Feedback {
    private $id_consultation;
    private $note;
    private $commentaire;
    
    public function __construct($id_consultation, $note, $commentaire = null) {
        $this->id_consultation = $id_consultation;
        $this->note = $note;
        $this->commentaire = $commentaire;
    }
    
    public function getIdConsultation() {
        return $this->id_consultation;
    }
    
    public function getNote() {
        return $this->note;
    }
    
    public function getCommentaire() {
        return $this->commentaire;
    }
}