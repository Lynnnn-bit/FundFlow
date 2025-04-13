<?php
class ResponseController
{
    private $db;

    public function __construct()
    {
        $this->db = Config::getConnexion();
    }

    public function createResponse(Response $response)
    {
        // Generate new ID if not set (modified)
        if ($response->getIdReponse() === null) {
            $newId = $this->generateResponseId();
            $response->setIdReponse($newId);
        }

        $stmt = $this->db->prepare("
            INSERT INTO reponse 
            (id_reponse, id_demande, decision, message, montant_accorde, date_reponse, status) 
            VALUES 
            (?, ?, ?, ?, ?, ?, ?)
        ");
        
        return $stmt->execute([
            $response->getIdReponse(),
            $response->getIdDemande(),
            $response->getDecision(),
            $response->getMessage(),
            $response->getMontantAccorde(),
            $response->getDateReponse(),
            $response->getStatus()
        ]);
    }

    // Add this new private method for ID generation
    private function generateResponseId()
    {
        $stmt = $this->db->query("SELECT MAX(id_reponse) as max_id FROM reponse");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return ($result['max_id'] ?? 0) + 1;
    }
    

    public function updateResponseStatus($id_reponse, $status)
    {
        $stmt = $this->db->prepare("
            UPDATE reponse 
            SET status = ? 
            WHERE id_reponse = ?
        ");
        return $stmt->execute([$status, $id_reponse]);
    }

    public function getResponseById($id_reponse)
    {
        $stmt = $this->db->prepare("
            SELECT r.*, d.id_project, p.titre as projet_titre 
            FROM reponse r
            JOIN demande_financement d ON r.id_demande = d.id_demande
            JOIN projet p ON d.id_project = p.id_projet
            WHERE id_reponse = ?
        ");
        $stmt->execute([$id_reponse]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getResponsesByDemande($id_demande)
    {
        $stmt = $this->db->prepare("
            SELECT r.*, u.nom, u.prenom
            FROM reponse r
            JOIN demande_financement d ON r.id_demande = d.id_demande
            JOIN utilisateur u ON d.id_utilisateur = u.id_utilisateur
            WHERE r.id_demande = ?
            ORDER BY r.date_reponse DESC
        ");
        $stmt->execute([$id_demande]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllDemandesWithResponses()
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

    public function acceptResponse($id_reponse)
    {
        $this->db->beginTransaction();
        try {
            // Get the response
            $reponse = $this->getResponseById($id_reponse);
            
            // Update the response status
            $this->updateResponseStatus($id_reponse, 'accepte');
            
            // Update all other responses for this demand to 'rejete'
            $stmt = $this->db->prepare("
                UPDATE reponse 
                SET status = 'rejete' 
                WHERE id_demande = ? AND id_reponse != ?
            ");
            $stmt->execute([$reponse['id_demande'], $id_reponse]);
            
            // Update the demand status
            $stmt = $this->db->prepare("
                UPDATE demande_financement 
                SET status = 'accepte' 
                WHERE id_demande = ?
            ");
            $stmt->execute([$reponse['id_demande']]);
            
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function rejectResponse($id_reponse)
    {
        return $this->updateResponseStatus($id_reponse, 'rejete');
    }

    public function getPendingDemandes()
    {
        $stmt = $this->db->query("
            SELECT d.*, p.titre as projet_titre 
            FROM demande_financement d
            JOIN projet p ON d.id_project = p.id_projet
            WHERE d.status = 'en_attente'
            ORDER BY d.id_demande DESC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function deleteResponse($id_reponse)
    {
        $stmt = $this->db->prepare("DELETE FROM reponse WHERE id_reponse = ?");
        return $stmt->execute([$id_reponse]);
    }
}