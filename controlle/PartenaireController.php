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
            // Verify database connection
            if (!$this->db) throw new PDOException("No database connection");
            
            // Prepare the SQL statement
            $stmt = $this->db->prepare("
                INSERT INTO fiche_partenaire 
                (nom, email, telephone, montant, description, is_approved, created_at) 
                VALUES (:nom, :email, :telephone, :montant, :description, :approved, NOW())
            ");
    
            // Bind parameters with explicit types
            $stmt->bindValue(':nom', $partenaire->getNom() ?: null, PDO::PARAM_STR);
            $stmt->bindValue(':email', $partenaire->getEmail() ?: null, PDO::PARAM_STR);
            $stmt->bindValue(':telephone', $partenaire->getTelephone() ?: null, PDO::PARAM_STR);
            $stmt->bindValue(':montant', $partenaire->getMontant(), PDO::PARAM_STR);
            $stmt->bindValue(':description', $partenaire->getDescription() ?: null, PDO::PARAM_STR);
            $stmt->bindValue(':approved', $partenaire->isApproved() ? 1 : 0, PDO::PARAM_INT);
    
            // Execute and verify
            if (!$stmt->execute()) {
                $error = $stmt->errorInfo();
                throw new PDOException("Database error: " . $error[2]);
            }
    
            return $this->db->lastInsertId();
            
        } catch (PDOException $e) {
            // Log complete error information
            error_log("Partner creation failed: " . $e->getMessage());
            error_log("SQL State: " . ($e->errorInfo[0] ?? ''));
            error_log("Driver Error: " . ($e->errorInfo[1] ?? ''));
            error_log("Error Message: " . ($e->errorInfo[2] ?? ''));
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
    
    public function getUnapprovedPartenaires($sortOrder = 'created_at DESC') {
        try {
            $query = "
                SELECT * FROM fiche_partenaire 
                WHERE is_approved = 0 
                AND is_deleted = 0
                ORDER BY $sortOrder
            ";

            $stmt = $this->db->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching unapproved partners: " . $e->getMessage());
            return [];
        }
    }

    public function getUnapprovedPartenairesSortedByMontant($order = 'ASC') {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM fiche_partenaire 
                WHERE is_approved = 0 
                AND is_deleted = 0 
                ORDER BY montant $order
            ");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching unapproved partners sorted by montant: " . $e->getMessage());
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
            $success = $stmt->execute([
                $data['nom'],
                $data['email'],
                $data['telephone'],
                $data['montant'],
                $data['description'],
                $id_partenaire
            ]);
            return $success && $stmt->rowCount() > 0; // Ensure rows were affected
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
    public function getPartnerContracts($id_partenaire) {
        try {
            $contratController = new ContratController();
            return $contratController->getContractsByPartner($id_partenaire);
        } catch (Exception $e) {
            error_log("Error fetching contracts for partner: " . $e->getMessage());
            return [];
        }
    }
    public function addContractForPartner($id_partenaire, $date_deb, $date_fin, $terms, $status) {
        try {
            $contratController = new ContratController();
            return $contratController->createContract($id_partenaire, $date_deb, $date_fin, $terms, $status);
        } catch (PDOException $e) {
            error_log("Contract creation failed: " . $e->getMessage());
            return false;
        }
    }
    
    public function updatePartnerContract($id_contrat, $id_partenaire, $date_deb, $date_fin, $terms, $status) {
        try {
            $contratController = new ContratController();
            // Verify contract belongs to partner
            $contract = $contratController->getContract($id_contrat, $id_partenaire);
            if (!$contract) {
                throw new PDOException("Contract not found or access denied");
            }
            return $contratController->updateContract($id_contrat, $date_deb, $date_fin, $terms, $status);
        } catch (PDOException $e) {
            error_log("Contract update failed: " . $e->getMessage());
            return false;
        }
    }
    
    public function deletePartnerContract($id_contrat, $id_partenaire) {
        try {
            $contratController = new ContratController();
            // Verify contract belongs to partner
            $contract = $contratController->getContract($id_contrat, $id_partenaire);
            if (!$contract) {
                throw new PDOException("Contract not found or access denied");
            }
            return $contratController->deleteContract($id_contrat);
        } catch (PDOException $e) {
            error_log("Contract deletion failed: " . $e->getMessage());
            return false;
        }
    }
    
    public function addContract($contractData) {
        try {
            $contratController = new ContratController();
            return $contratController->createContract(
                $contractData['id_partenaire'],
                $contractData['date_deb'],
                $contractData['date_fin'],
                $contractData['terms'],
                'en attente'
            );
        } catch (Exception $e) {
            error_log("Error adding contract: " . $e->getMessage());
            return false;
        }
    }

    public function submitContractForApproval($contractData) {
        try {
            $contratController = new ContratController();
            return $contratController->createContract(
                $contractData['id_partenaire'],
                $contractData['date_deb'],
                $contractData['date_fin'],
                $contractData['terms'],
                'en attente'
            );
        } catch (Exception $e) {
            error_log("Error submitting contract for approval: " . $e->getMessage());
            return false;
        }
    }

    public function deleteContract($id_contrat) {
        try {
            $contratController = new ContratController();
            return $contratController->deleteContract($id_contrat);
        } catch (Exception $e) {
            error_log("Error deleting contract: " . $e->getMessage());
            return false;
        }
    }

    public function updateContract($contractData) {
        try {
            $contratController = new ContratController();
            return $contratController->updateContract(
                $contractData['id_contrat'],
                $contractData['date_deb'],
                $contractData['date_fin'],
                $contractData['terms'],
                'en attente' // Keep the status as 'en attente' after update
            );
        } catch (Exception $e) {
            error_log("Error updating contract in PartenaireController: " . $e->getMessage());
            return false;
        }
    }

    public function filterPartenairesByName($name) {
        $query = "SELECT * FROM fiche_partenaire WHERE nom LIKE :name";
        try {
            $stmt = $this->db->prepare($query);
            $stmt->execute([':name' => '%' . $name . '%']);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error filtering partners by name: " . $e->getMessage());
            return [];
        }
    }

    public function filterPartenairesByMontantRange($min = null, $max = null) {
        $query = "SELECT * FROM fiche_partenaire WHERE 1=1";
        $params = [];

        if ($min !== null) {
            $query .= " AND montant >= :min";
            $params[':min'] = $min;
        }

        if ($max !== null) {
            $query .= " AND montant <= :max";
            $params[':max'] = $max;
        }

        try {
            $stmt = $this->db->prepare($query);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error filtering partners by montant range: " . $e->getMessage());
            return [];
        }
    }
}