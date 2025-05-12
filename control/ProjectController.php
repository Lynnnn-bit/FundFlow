<?php
require_once __DIR__ . '../../config.php';
require_once __DIR__ . '../../models/Project.php';

class ProjectController
{
    private $db;

    public function __construct()
    {
        $this->db = Config::getConnexion();
    }

    // -------------------------------
    // CRUD Operations for Projects
    // -------------------------------

    public function createProject(Project $project)
    {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO projet (id_utilisateur, titre, description, montant_cible, duree, id_categorie, status) 
                VALUES (:id_utilisateur, :titre, :description, :montant_cible, :duree, :id_categorie, :status)
            ");
            $stmt->bindValue(':id_utilisateur', $project->getUserId(), PDO::PARAM_INT); // Ensure user ID is set
            $stmt->bindValue(':titre', $project->getTitre(), PDO::PARAM_STR);
            $stmt->bindValue(':description', $project->getDescription(), PDO::PARAM_STR);
            $stmt->bindValue(':montant_cible', $project->getMontantCible(), PDO::PARAM_INT);
            $stmt->bindValue(':duree', $project->getDuree(), PDO::PARAM_INT);
            $stmt->bindValue(':id_categorie', $project->getIdCategorie(), PDO::PARAM_INT);
            $stmt->bindValue(':status', $project->getStatus(), PDO::PARAM_STR);
            return $stmt->execute();
        } catch (PDOException $e) {
            throw new Exception("Erreur lors de la création du projet : " . $e->getMessage());
        }
    }

    public function updateProject(Project $project)
    {
        $stmt = $this->db->prepare("
            UPDATE projet 
            SET titre = ?, description = ?, montant_cible = ?, status = ?, 
                duree = ?, id_categorie = ?
            WHERE id_projet = ?
        ");
        return $stmt->execute([
            $project->getTitre(),
            $project->getDescription(),
            $project->getMontantCible(),
            $project->getStatus(),
            $project->getDuree(),
            $project->getIdCategorie(),
            $project->getIdProjet()
        ]);
    }

    public function deleteProject($id_projet)
    {
        $stmt = $this->db->prepare("DELETE FROM projet WHERE id_projet = ?");
        return $stmt->execute([$id_projet]);
    }

    // -------------------------------
    // Fetching Projects
    // -------------------------------

    public function getProjectById($id_projet)
    {
        $stmt = $this->db->prepare("
            SELECT p.*, c.nom_categorie 
            FROM projet p
            LEFT JOIN categorie c ON p.id_categorie = c.id_categorie
            WHERE p.id_projet = ?
        ");
        $stmt->execute([$id_projet]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getAllProjects()
    {
        $query = "
            SELECT p.id_projet, p.titre, p.description, p.montant_cible, p.duree, p.status, c.nom_categorie
            FROM projet p
            LEFT JOIN categorie c ON p.id_categorie = c.id_categorie
        ";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllProjectsWithCategories()
    {
        $query = "
            SELECT p.id_projet, p.titre, p.description, p.montant_cible, p.duree, p.statut, c.nom_categorie
            FROM projet p
            LEFT JOIN categorie c ON p.id_categorie = c.id_categorie
        ";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getUserProjects($user_id)
    {
        $stmt = $this->db->prepare("
            SELECT p.*, c.nom_categorie 
            FROM projet p
            LEFT JOIN categorie c ON p.id_categorie = c.id_categorie
            WHERE p.id_utilisateur = ?
            ORDER BY p.id_projet DESC
        ");
        $stmt->execute([$user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getPublicProjects($id_utilisateur = null, $orderBy = null)
    {
        try {
            $sql = "SELECT p.*, c.nom_categorie 
                   FROM projet p 
                   LEFT JOIN categorie c ON p.id_categorie = c.id_categorie
                   WHERE p.status IN ('actif', 'en_attente', 'termine')";
            
            if ($id_utilisateur !== null) {
                $sql .= " AND p.id_utilisateur = :id_utilisateur";
                $stmt = $this->db->prepare($sql);
                $stmt->bindParam(':id_utilisateur', $id_utilisateur, PDO::PARAM_INT);
            } else {
                $stmt = $this->db->prepare($sql);
            }

            if ($orderBy) {
                $sql .= " ORDER BY $orderBy";
            }
            
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erreur getPublicProjects: " . $e->getMessage());
            return [];
        }
    }

    public function getProjectsByUserId($user_id)
    {
        $stmt = $this->db->prepare("
            SELECT p.*, c.nom_categorie 
            FROM projet p
            LEFT JOIN categorie c ON p.id_categorie = c.id_categorie
            WHERE p.id_utilisateur = ?
            ORDER BY p.id_projet DESC
        ");
        $stmt->execute([$user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function searchProjectsByName($searchTerm, $orderBy = null)
    {
        try {
            $query = "SELECT * FROM projet WHERE titre LIKE :searchTerm";
            if ($orderBy) {
                $query .= " ORDER BY $orderBy";
            }
            $stmt = $this->db->prepare($query);
            $stmt->bindValue(':searchTerm', '%' . $searchTerm . '%', PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Erreur lors de la recherche des projets : " . $e->getMessage());
        }
    }

    // -------------------------------
    // Fetching Categories
    // -------------------------------

    public function getCategories()
    {
        $stmt = $this->db->query("SELECT * FROM categorie");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // -------------------------------
    // Statistics
    // -------------------------------

    public function getStatisticsByCategory()
    {
        try {
            $query = "
                SELECT c.nom_categorie, COUNT(p.id_projet) AS total_projets, SUM(p.montant_cible) AS montant_total
                FROM categorie c
                LEFT JOIN projet p ON c.id_categorie = p.id_categorie
                GROUP BY c.nom_categorie
            ";
            $stmt = $this->db->query($query);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Erreur lors de la récupération des statistiques : " . $e->getMessage());
        }
    }
}