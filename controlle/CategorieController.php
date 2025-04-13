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
        return $stmt->execute([
            $categorie->getIdCategorie(),
            $categorie->getNomCategorie(),
            $categorie->getDescription()
        ]);
    }

    public function updateCategorie(Categorie $categorie)
    {
        $stmt = $this->db->prepare("
            UPDATE categorie 
            SET nom_categorie = ?, description = ?
            WHERE id_categorie = ?
        ");
        return $stmt->execute([
            $categorie->getNomCategorie(),
            $categorie->getDescription(),
            $categorie->getIdCategorie()
        ]);
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
        // First check if category is used in projects
        $count = $this->countProjectsInCategory($id_categorie);
        if ($count > 0) {
            throw new Exception("Impossible de supprimer : cette catégorie est utilisée par $count projet(s)");
        }

        $stmt = $this->db->prepare("DELETE FROM categorie WHERE id_categorie = ?");
        return $stmt->execute([$id_categorie]);
    }

    public function countProjectsInCategory($id_categorie)
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM projet WHERE id_categorie = ?");
        $stmt->execute([$id_categorie]);
        return $stmt->fetchColumn();
    }
}