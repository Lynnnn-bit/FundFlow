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
                VALUES (?, ?, ?, ?, ?, NOW())
            ");
            $result = $stmt->execute([
                $id_partenaire, 
                $date_deb, 
                $date_fin, 
                $terms, 
                $status
            ]);
            
            // Return true only if a row was affected
            return $result && $stmt->rowCount() > 0;
            
        } catch (PDOException $e) {
            error_log("Error creating contract: " . $e->getMessage());
            return false;
        }
    }

    public function updateContract($id_contrat, $date_deb, $date_fin, $terms, $status) {
        try {
            $stmt = $this->db->prepare("
                UPDATE contrat 
                SET date_deb = ?,
                    date_fin = ?,
                    terms = ?,
                    status = ?
                WHERE id_contrat = ?
            ");
            
            $result = $stmt->execute([
                $date_deb,
                $date_fin,
                $terms,
                $status,
                $id_contrat
            ]);
            
            return $result && $stmt->rowCount() > 0;
            
        } catch (PDOException $e) {
            error_log("Update Contract Error: " . $e->getMessage());
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
        $stmt = $this->db->prepare("
            SELECT c.*, p.nom as partenaire_nom 
            FROM contrat c
            JOIN fiche_partenaire p ON c.id_partenaire = p.id_partenaire
            WHERE c.id_contrat = ?
        ");
        $stmt->execute([$id_contrat]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getAllContracts() {
        $stmt = $this->db->query("
            SELECT c.*, p.nom as partenaire_nom 
            FROM contrat c
            JOIN fiche_partenaire p ON c.id_partenaire = p.id_partenaire
            ORDER BY c.created_at DESC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getContractsByPartner($id_partenaire) {
        $stmt = $this->db->prepare("
            SELECT * FROM contrat 
            WHERE id_partenaire = ?
            ORDER BY created_at DESC
        ");
        $stmt->execute([$id_partenaire]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}