<?php
include_once dirname(__DIR__) . '/Config.php'; // adapte le chemin selon ton projet

class startupC {
    private $conn;

    public function __construct() {
        $this->conn = config::getConnexion();
    }

    public function createStartup($startup) {
        $sql = "INSERT INTO startup (nom_startup, secteur, adresse_site, logo, description, email, video_presentation)
                VALUES (:nom, :secteur, :adresse, :logo, :description, :email, :video)";
        
        try {
            $query = $this->conn->prepare($sql);
            $query->execute([
                'nom' => $startup->getNomStartup(),
                'secteur' => $startup->getSecteur(),
                'adresse' => $startup->getAdresseSite(),
                'logo' => $startup->getLogo(),
                'description' => $startup->getDescription(),
                'email' => $startup->getEmail(),
                'video' => $startup->getVideoPresentation()
            ]);
            return true; // Retourne vrai si réussi
        } catch(PDOException $e) {
            error_log("Erreur DB: " . $e->getMessage()); // Log l'erreur
            return false;
        }
    }

    public function getAllStartups() {
        $sql = "SELECT * FROM startup";
        return $this->conn->query($sql)->fetchAll();
    }
    function deleteStartup($id)
    {
        $sql = "DELETE FROM startup WHERE id_startup = :id_startup";
        $db = config::getConnexion();
        $req = $db->prepare($sql);
        $req->bindValue(':id_startup', $id);
    
        try {
            $req->execute();
        } catch (Exception $e) {
            die('Error:' . $e->getMessage());
        }
        header("Location: addstartup.php");
        exit();
    }
    public function getStartupById($id)
    {
        try {
            $pdo = config::getConnexion();
            $stmt = $pdo->prepare("SELECT * FROM startup WHERE id_startup = ?");
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die("Erreur : " . $e->getMessage());
        }
    }
    
    public function updateStartup($startup, $id)
    {
        try {
            $pdo = config::getConnexion();
            $query = $pdo->prepare(
                "UPDATE startup SET 
                    nom_startup = :nom_startup,
                    secteur = :secteur,
                    adresse_site = :adresse_site,
                    logo = :logo,
                    description = :description,
                    email = :email,
                    video_presentation = :video_presentation
                WHERE id_startup = :id_startup"
            );
    
            $query->execute([
                'id_startup' => $id,
                'nom_startup' => $startup->getNomStartup(),
                'secteur' => $startup->getSecteur(),
                'adresse_site' => $startup->getAdresseSite(),
                'logo' => $startup->getLogo(),
                'description' => $startup->getDescription(),
                'email' => $startup->getEmail(),
                'video_presentation' => $startup->getVideoPresentation()
            ]);
        } catch (PDOException $e) {
            throw new Exception("Erreur lors de la mise à jour : " . $e->getMessage());
        }
    }
    
// In startupC.php, call the method from Evennement.php to get events for each startup


public function modifierStartup($data) {
    $sql = "UPDATE startup SET 
            nom_startup = :nom_startup,
            secteur = :secteur,
            adresse_site = :adresse_site,
            description = :description,
            email = :email,
            logo = :logo,
            video_presentation = :video_presentation
            WHERE id_startup = :id_startup";

    $db = config::getConnexion();
    $query = $db->prepare($sql);
    
    return $query->execute([
        ':id_startup' => $data['id_startup'],
        ':nom_startup' => $data['nom_startup'],
        ':secteur' => $data['secteur'],
        ':adresse_site' => $data['adresse_site'],
        ':description' => $data['description'],
        ':email' => $data['email'],
        ':logo' => $data['logo'],
        ':video_presentation' => $data['video_presentation']
    ]);
}
public function supprimerStartup($id) {
    $sql = "DELETE FROM startup WHERE id_startup = :id";
    $db = config::getConnexion();
    $query = $db->prepare($sql);
    $query->bindValue(':id', $id, PDO::PARAM_INT);
    
    try {
        return $query->execute();
    } catch (PDOException $e) {
        error_log("Erreur suppression startup #$id: " . $e->getMessage());
        return false;
    }
}
public function getStartupsBySecteur($secteur)
{
    $sql = "SELECT * FROM startup WHERE secteur LIKE :secteur";
    $db = config::getConnexion();
    try {
        $query = $db->prepare($sql);
        $query->execute(['secteur' => '%' . $secteur . '%']);
        return $query->fetchAll();
    } catch (Exception $e) {
        die('Erreur: ' . $e->getMessage());
    }
}


}
?>
