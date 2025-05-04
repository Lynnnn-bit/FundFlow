<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../models/Contrat.php';

class ContratController {
    private $db;

    public function __construct() {
        $this->db = Config::getConnexion();
    }

    public function createContract($id_partenaire, $date_deb, $date_fin, $terms = '', $status = 'en attente') {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO contrat 
                (id_partenaire, date_deb, date_fin, terms, status, created_at)
                VALUES (:id_partenaire, :date_deb, :date_fin, :terms, :status, NOW())
            ");
            $stmt->bindParam(':id_partenaire', $id_partenaire, PDO::PARAM_INT);
            $stmt->bindParam(':date_deb', $date_deb, PDO::PARAM_STR);
            $stmt->bindParam(':date_fin', $date_fin, PDO::PARAM_STR);
            $stmt->bindParam(':terms', $terms, PDO::PARAM_STR);
            $stmt->bindParam(':status', $status, PDO::PARAM_STR);

            $result = $stmt->execute();

            if (!$result) {
                error_log("Failed to create contract: " . implode(", ", $stmt->errorInfo()));
                return false;
            }

            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Error creating contract: " . $e->getMessage());
            return false;
        }
    }

    public function updateContract($id_contrat, $date_deb, $date_fin, $terms, $status) {
        try {
            $stmt = $this->db->prepare("
                UPDATE contrat 
                SET date_deb = :date_deb, 
                    date_fin = :date_fin, 
                    terms = :terms, 
                    status = :status
                WHERE id_contrat = :id_contrat
            ");
            $stmt->bindParam(':date_deb', $date_deb, PDO::PARAM_STR);
            $stmt->bindParam(':date_fin', $date_fin, PDO::PARAM_STR);
            $stmt->bindParam(':terms', $terms, PDO::PARAM_STR);
            $stmt->bindParam(':status', $status, PDO::PARAM_STR);
            $stmt->bindParam(':id_contrat', $id_contrat, PDO::PARAM_INT);

            $result = $stmt->execute();

            if ($stmt->rowCount() === 0) {
                error_log("No rows updated for contract ID: $id_contrat");
            }

            return $result;
        } catch (PDOException $e) {
            error_log("Error updating contract: " . $e->getMessage());
            return false;
        }
    }

    public function deleteContract($id_contrat) {
        try {
            $stmt = $this->db->prepare("DELETE FROM contrat WHERE id_contrat = ?");
            return $stmt->execute([$id_contrat]);
        } catch (PDOException $e) {
            error_log("Error deleting contract: " . $e->getMessage());
            return false;
        }
    }

    public function getContract($id_contrat) {
        $query = "SELECT c.*, p.nom AS partenaire_nom 
                  FROM contrat c
                  LEFT JOIN fiche_partenaire p ON c.id_partenaire = p.id_partenaire
                  WHERE c.id_contrat = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$id_contrat]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getAllContracts() {
        $query = "SELECT c.*, p.nom AS partenaire_nom 
                  FROM contrat c
                  LEFT JOIN fiche_partenaire p ON c.id_partenaire = p.id_partenaire
                  ORDER BY c.created_at DESC";
        $stmt = $this->db->query($query);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getContractsByPartner($id_partenaire) {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM contrat 
                WHERE id_partenaire = ?
                ORDER BY created_at DESC
            ");
            $stmt->execute([$id_partenaire]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching contracts by partner: " . $e->getMessage());
            return [];
        }
    }

    public function getContractStatistics() {
        try {
            $stmt = $this->db->query("
                SELECT 
                    SUM(CASE WHEN status = 'expiré' THEN 1 ELSE 0 END) AS expired,
                    SUM(CASE WHEN status = 'actif' THEN 1 ELSE 0 END) AS active,
                    SUM(CASE WHEN status = 'en attente' THEN 1 ELSE 0 END) AS pending
                FROM contrats
            ");
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching contract statistics: " . $e->getMessage());
            return ['expired' => 0, 'active' => 0, 'pending' => 0];
        }
    }

    public function filterContractsByAdvancedSearch($dateStart = null, $dateEnd = null, $status = null) {
        $query = "SELECT c.*, p.nom AS partenaire_nom 
                  FROM contrat c
                  LEFT JOIN fiche_partenaire p ON c.id_partenaire = p.id_partenaire 
                  WHERE 1=1";
        $params = [];

        if ($dateStart) {
            $query .= " AND c.date_deb >= :dateStart";
            $params[':dateStart'] = $dateStart;
        }

        if ($dateEnd) {
            $query .= " AND c.date_fin <= :dateEnd";
            $params[':dateEnd'] = $dateEnd;
        }

        if ($status) {
            $query .= " AND c.status = :status";
            $params[':status'] = $status;
        }

        $query .= " ORDER BY c.created_at DESC";

        try {
            $stmt = $this->db->prepare($query);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error filtering contracts: " . $e->getMessage());
            return [];
        }
    }

    public function updateExpiredContracts() {
        $query = "UPDATE contrat 
                  SET status = 'expiré' 
                  WHERE status != 'expiré' AND date_fin < CURDATE()";
        try {
            $stmt = $this->db->prepare($query);
            $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error updating expired contracts: " . $e->getMessage());
        }
    }
}