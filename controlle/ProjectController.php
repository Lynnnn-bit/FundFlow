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

    public function createProject(Project $project)
    {
        $stmt = $this->db->prepare("
            INSERT INTO projet 
            (id_utilisateur, titre, description, montant_cible, status, duree, id_categorie) 
            VALUES 
            (?, ?, ?, ?, ?, ?, ?)
        ");
        return $stmt->execute([
            $project->getIdUtilisateur(),
            $project->getTitre(),
            $project->getDescription(),
            $project->getMontantCible(),
            $project->getStatus(),
            $project->getDuree(),
            $project->getIdCategorie()
        ]);
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
        $stmt = $this->db->query("
            SELECT p.*, c.nom_categorie, u.nom, u.prenom
            FROM projet p
            LEFT JOIN categorie c ON p.id_categorie = c.id_categorie
            JOIN utilisateur u ON p.id_utilisateur = u.id_utilisateur
            ORDER BY p.id_projet DESC
        ");
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

    public function getCategories()
    {
        $stmt = $this->db->query("SELECT * FROM categorie");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function deleteProject($id_projet)
    {
        $stmt = $this->db->prepare("DELETE FROM projet WHERE id_projet = ?");
        return $stmt->execute([$id_projet]);
    }
}