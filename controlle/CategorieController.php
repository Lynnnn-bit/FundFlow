<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../models/Categorie.php';

class CategorieController
{
    private $db;

    public function __construct()
    {
        $this->db = Config::getConnexion();
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public function createCategorie(Categorie $categorie)
    {
        // Check if ID already exists
        $check = $this->db->prepare("SELECT id_categorie FROM categorie WHERE id_categorie = ?");
        $check->execute([$categorie->getIdCategorie()]);
        
        if ($check->fetch()) {
            throw new Exception("L'ID de catégorie existe déjà!");
        }

        $stmt = $this->db->prepare("
            INSERT INTO categorie 
            (id_categorie, nom_categorie, description) 
            VALUES 
            (?, ?, ?)
        ");
        $result = $stmt->execute([
            $categorie->getIdCategorie(),
            $categorie->getNomCategorie(),
            $categorie->getDescription()
        ]);

        if ($result) {
            $this->logModification('Création de catégorie', 'admin', "Catégorie ID {$categorie->getIdCategorie()} créée.");
            $this->logHistoriqueOpportunite("Création", "Catégorie ID {$categorie->getIdCategorie()} créée.");
        }
        return $result;
    }

    public function updateCategorie(Categorie $categorie)
    {
        $stmt = $this->db->prepare("
            UPDATE categorie 
            SET nom_categorie = ?, description = ?
            WHERE id_categorie = ?
        ");
        $result = $stmt->execute([
            $categorie->getNomCategorie(),
            $categorie->getDescription(),
            $categorie->getIdCategorie()
        ]);

        if ($result) {
            $this->logModification('Mise à jour de catégorie', 'admin', "Catégorie ID {$categorie->getIdCategorie()} mise à jour.");
            $this->logHistoriqueOpportunite("Mise à jour", "Catégorie ID {$categorie->getIdCategorie()} mise à jour.");
        }
        return $result;
    }

    public function getCategorieById($id_categorie)
    {
        $stmt = $this->db->prepare("SELECT * FROM categorie WHERE id_categorie = ?");
        $stmt->execute([$id_categorie]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getAllCategories()
    {
        $stmt = $this->db->query("SELECT * FROM categorie ORDER BY id_categorie");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function deleteCategorie($id_categorie)
    {
        // Check if the category is used in projects
        $count = $this->countProjectsInCategory($id_categorie);
        if ($count > 0) {
            throw new Exception("Impossible de supprimer : cette catégorie est utilisée par $count projet(s)");
        }

        $stmt = $this->db->prepare("DELETE FROM categorie WHERE id_categorie = ?");
        $result = $stmt->execute([$id_categorie]);
        if ($result) {
            $this->logModification('Suppression de catégorie', 'admin', "Catégorie ID $id_categorie supprimée.");
            $this->logHistoriqueOpportunite("Suppression", "Catégorie ID $id_categorie supprimée.");
        }
        return $result;
    }

    public function countProjectsInCategory($id_categorie)
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM projet WHERE id_categorie = ?");
        $stmt->execute([$id_categorie]);
        return $stmt->fetchColumn();
    }

    public function getAllModificationHistory()
    {
        try {
            $stmt = $this->db->query("
                SELECT action, date, user, details 
                FROM modification_history 
                ORDER BY date DESC
            ");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erreur lors de la récupération de l'historique: " . $e->getMessage());
            return [];
        }
    }

    public function logModification($action, $user, $details)
    {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO modification_history (action, user, details)
                VALUES (?, ?, ?)
            ");
            $stmt->execute([$action, $user, $details]);
        } catch (PDOException $e) {
            error_log("Erreur lors de l'enregistrement de l'historique: " . $e->getMessage());
        }
    }

    public function searchCategories($searchTerm)
    {
        $stmt = $this->db->prepare("
            SELECT * FROM categorie 
            WHERE id_categorie LIKE ? 
               OR nom_categorie LIKE ? 
               OR description LIKE ?
            ORDER BY id_categorie
        ");
        $searchTerm = '%' . $searchTerm . '%';
        $stmt->execute([$searchTerm, $searchTerm, $searchTerm]);

        // Log the search action
        $this->logModification('Recherche de catégorie', 'admin', "Recherche effectuée avec le terme: '$searchTerm'");

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function searchCategoriesByName($searchTerm)
    {
        $stmt = $this->db->prepare("
            SELECT * FROM categorie 
            WHERE nom_categorie LIKE ?
            ORDER BY id_categorie
        ");
        $searchTerm = '%' . $searchTerm . '%';
        $stmt->execute([$searchTerm]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getCategoryUsageStats()
    {
        $stmt = $this->db->query("
            SELECT c.nom_categorie, COUNT(p.id_projet) AS project_count
            FROM categorie c
            LEFT JOIN projet p ON c.id_categorie = p.id_categorie
            GROUP BY c.nom_categorie
            ORDER BY project_count DESC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function testLog()
    {
        $this->logModification('Test Action', 'admin', 'Ceci est un test.');
    }

    private function logHistoriqueOpportunite($action, $details)
    {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO opportunite (Titre_opp, Date_de_Publication)
                VALUES (?, NOW())
            ");
            $stmt->execute([$action . ": " . $details]);
        } catch (PDOException $e) {
            error_log("Erreur lors de l'enregistrement dans l'historique des opportunités: " . $e->getMessage());
        }
    }
}