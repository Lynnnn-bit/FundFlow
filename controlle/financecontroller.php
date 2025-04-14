<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../models/Finance.php';

class FinanceController
{
    private $db;

    public function __construct()
    {
        $this->db = Config::getConnexion();
    }

    public function getProjectOwner($project_id)
    {
        $stmt = $this->db->prepare("SELECT id_utilisateur FROM projet WHERE id_projet = ?");
        $stmt->execute([$project_id]);
        return $stmt->fetchColumn();
    }

    public function createFinanceRequest(Finance $finance)
    {
        $stmt = $this->db->prepare("
            INSERT INTO demande_financement 
            (id_demande, id_project, id_utilisateur, duree, montant_demandee, status) 
            VALUES 
            (?, ?, ?, ?, ?, ?)
        ");
        return $stmt->execute([
            $finance->getIdDemande(),
            $finance->getIdProject(),
            $finance->getIdUtilisateur(),
            $finance->getDuree(),
            $finance->getMontantDemandee(),
            $finance->getStatus()
        ]);
    }

    public function updateFinanceRequest(Finance $finance)
    {
        $stmt = $this->db->prepare("
            UPDATE demande_financement 
            SET id_project = ?, duree = ?, montant_demandee = ?, status = ? 
            WHERE id_demande = ?
        ");
        return $stmt->execute([
            $finance->getIdProject(),
            $finance->getDuree(),
            $finance->getMontantDemandee(),
            $finance->getStatus(),
            $finance->getIdDemande()
        ]);
    }

    public function getFinanceRequestById($id_demande)
    {
        $stmt = $this->db->prepare("SELECT * FROM demande_financement WHERE id_demande = ?");
        $stmt->execute([$id_demande]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getProjects($user_id)
    {
        $stmt = $this->db->prepare("SELECT id_projet, titre FROM projet WHERE id_utilisateur = ?");
        $stmt->execute([$user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllFinanceRequests()
    {
        $stmt = $this->db->query("SELECT * FROM demande_financement ORDER BY id_demande DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getProjectById($project_id)
    {
        $stmt = $this->db->prepare("SELECT id_projet, titre FROM projet WHERE id_projet = ?");
        $stmt->execute([$project_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function deleteFinanceRequest($id_demande)
    {
        $stmt = $this->db->prepare("DELETE FROM demande_financement WHERE id_demande = ?");
        return $stmt->execute([$id_demande]);
    }
}