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

    // Example for createFinanceRequest with better error handling
    public function createFinanceRequest(Finance $finance)
    {
        $stmt = $this->db->prepare("SELECT id_projet FROM projet WHERE id_projet = ?");
        $stmt->execute([$finance->getIdProject()]);
        if (!$stmt->fetch()) {
            throw new Exception("Le projet spécifié n'existe pas");
        }
        try {
            $stmt = $this->db->prepare("
                INSERT INTO demande_financement 
                (id_demande, id_project, id_utilisateur, duree, montant_demandee, status) 
                VALUES 
                (?, ?, ?, ?, ?, ?)
            ");
            
            $result = $stmt->execute([
                $finance->getIdDemande(),
                $finance->getIdProject(),
                $finance->getIdUtilisateur(),
                $finance->getDuree(),
                $finance->getMontantDemandee(),
                $finance->getStatus()
            ]);
            
            if (!$result) {
                throw new Exception("Failed to create finance request");
            }
            
            return $result;
        } catch (PDOException $e) {
            error_log("Database error in createFinanceRequest: " . $e->getMessage());
            throw $e;
        }
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

    public function updateDemandeStatus($id_demande, $status)
    {
        $stmt = $this->db->prepare("
            UPDATE demande_financement 
            SET status = ? 
            WHERE id_demande = ?
        ");
        return $stmt->execute([$status, $id_demande]);
    }

    public function getFinanceRequestById($id_demande)
    {
        $stmt = $this->db->prepare("
            SELECT d.*, 
                IFNULL(p.titre, 'Projet supprimé') as projet_titre, 
                u.nom, u.prenom 
            FROM demande_financement d
            LEFT JOIN projet p ON d.id_project = p.id_projet
            JOIN utilisateur u ON d.id_utilisateur = u.id_utilisateur
            WHERE d.id_demande = ?
        ");
        $stmt->execute([$id_demande]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getAllFinanceRequests()
    {
        $stmt = $this->db->query("
            SELECT d.*, p.titre as projet_titre, u.nom, u.prenom,
                   (SELECT COUNT(*) FROM reponse r WHERE r.id_demande = d.id_demande) as nb_reponses
            FROM demande_financement d
            JOIN projet p ON d.id_project = p.id_projet
            JOIN utilisateur u ON d.id_utilisateur = u.id_utilisateur
            ORDER BY d.id_demande DESC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getFinanceRequestsByUser($userId)
    {
        $stmt = $this->db->prepare("
            SELECT d.*, p.titre as projet_titre, u.nom, u.prenom
            FROM demande_financement d
            JOIN projet p ON d.id_project = p.id_projet
            JOIN utilisateur u ON d.id_utilisateur = u.id_utilisateur
            WHERE d.id_utilisateur = ?
            ORDER BY d.id_demande DESC
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getProjectOwner($project_id)
    {
        $stmt = $this->db->prepare("SELECT id_utilisateur FROM projet WHERE id_projet = ?");
        $stmt->execute([$project_id]);
        return $stmt->fetchColumn();
    }

    public function getProjects($user_id)
    {
        $stmt = $this->db->prepare("SELECT id_projet, titre FROM projet WHERE id_utilisateur = ?");
        $stmt->execute([$user_id]);
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