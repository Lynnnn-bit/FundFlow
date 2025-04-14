<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../models/Partenaire.php';
require_once __DIR__ . '/ContratController.php';

class PartenaireController {
    private $db;

    public function __construct() {
        try {
            $this->db = Config::getConnexion();
        } catch (PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }

    public function createPartenaire(Partenaire $partenaire) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO fiche_partenaire 
                (nom, email, telephone, montant, description, is_approved, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, NOW())
            ");
            
            $success = $stmt->execute([
                $partenaire->getNom(),
                $partenaire->getEmail(),
                $partenaire->getTelephone(),
                $partenaire->getMontant(),
                $partenaire->getDescription(),
                $partenaire->isApproved() ? 1 : 0
            ]);
            
            return $success ? $this->db->lastInsertId() : false;
            
        } catch (PDOException $e) {
            error_log("Error creating partner: " . $e->getMessage());
            return false;
        }
    }

    public function approvePartenaire($id_partenaire) {
        try {
            $this->db->beginTransaction();
            
            // Approve partner
            $stmt = $this->db->prepare("
                UPDATE fiche_partenaire 
                SET is_approved = 1 
                WHERE id_partenaire = ?
            ");
            $stmt->execute([$id_partenaire]);
            
            // Create contract
            $contratController = new ContratController();
            $success = $contratController->createContract(
                $id_partenaire,
                date('Y-m-d'),
                date('Y-m-d', strtotime('+1 year')),
                'Standard contract terms',
                'en attente'
            );
            
            $this->db->commit();
            return $success;
            
        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log("APPROVAL ERROR: " . $e->getMessage());
            error_log("SQL STATE: " . $e->errorInfo()[0]);
            return false;
        }
    }

    public function getAllApprovedPartenaires() {
        try {
            $stmt = $this->db->query("
                SELECT * FROM fiche_partenaire 
                WHERE is_approved = 1
                ORDER BY nom
            ");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching approved partners: " . $e->getMessage());
            return [];
        }
    }

    public function rejectPartenaire($id_partenaire) {
        try {
            $stmt = $this->db->prepare("
                UPDATE fiche_partenaire 
                SET is_deleted = 1 
                WHERE id_partenaire = ?
            ");
            $result = $stmt->execute([$id_partenaire]);
            
            // Check affected rows
            if ($stmt->rowCount() === 0) {
                error_log("No rows affected - partner ID $id_partenaire not found");
                return false;
            }
            
            return $result;
        } catch (PDOException $e) {
            error_log("REJECTION ERROR: " . $e->getMessage());
            error_log("SQL ERROR INFO: " . print_r($stmt->errorInfo(), true));
            return false;
        }
    }
    
    public function getUnapprovedPartenaires() {
        try {
            $stmt = $this->db->query("
                SELECT * FROM fiche_partenaire 
                WHERE is_approved = 0
                AND is_deleted = 0
                ORDER BY created_at DESC
            ");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching unapproved partners: " . $e->getMessage());
            return [];
        }
    }

    public function getAllPartenaires() {
        try {
            $stmt = $this->db->query("
                SELECT * FROM fiche_partenaire 
                ORDER BY created_at DESC
            ");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching all partners: " . $e->getMessage());
            return [];
        }
    }

    public function getPartenaire($id_partenaire) {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM fiche_partenaire 
                WHERE id_partenaire = ?
            ");
            $stmt->execute([$id_partenaire]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching partner: " . $e->getMessage());
            return false;
        }
    }

    public function getPartenaireByEmail($email) {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM fiche_partenaire 
                WHERE email = ?
            ");
            $stmt->execute([$email]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching partner by email: " . $e->getMessage());
            return false;
        }
    }

    public function updatePartenaire($id_partenaire, $data) {
        try {
            $stmt = $this->db->prepare("
                UPDATE fiche_partenaire 
                SET nom = ?, email = ?, telephone = ?, montant = ?, description = ?
                WHERE id_partenaire = ?
            ");
            return $stmt->execute([
                $data['nom'],
                $data['email'],
                $data['telephone'],
                $data['montant'],
                $data['description'],
                $id_partenaire
            ]);
        } catch (PDOException $e) {
            error_log("Error updating partner: " . $e->getMessage());
            return false;
        }
    }

    public function deletePartenaire($id_partenaire) {
        try {
            $stmt = $this->db->prepare("DELETE FROM fiche_partenaire WHERE id_partenaire = ?");
            return $stmt->execute([$id_partenaire]);
        } catch (PDOException $e) {
            error_log("Error deleting partner: " . $e->getMessage());
            return false;
        }
    }
    
}