<?php
include_once '../../config.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/user/FundFlow/models/Evennement.php';

class EvennementC {
    public function ajouterEvenement($evenement) {
        // Ensure you have a valid connection
        $db = Config::getConnexion();  // Make sure Config.php exists and works

        // Prepare the SQL query for inserting the evenement into the database
        $query = 'INSERT INTO evenement (id_startup, date_evenement, type, horaire, nb_place, affiche, nom)
                  VALUES (:id_startup, :date_evenement, :type, :horaire, :nb_place, :affiche, :nom)';

        // Prepare the statement
        $stmt = $db->prepare($query);

        // Bind the values from the Evennement object
        $stmt->bindValue(':id_startup', $evenement->getIdStartup());
        $stmt->bindValue(':date_evenement', $evenement->getDateEvenement());
        $stmt->bindValue(':type', $evenement->getType());
        $stmt->bindValue(':horaire', $evenement->getHoraire());
        $stmt->bindValue(':nb_place', $evenement->getNbPlace());
        $stmt->bindValue(':affiche', $evenement->getAffiche());
        $stmt->bindValue(':nom', $evenement->getNom());

        // Execute the query and return the result (true if successful)
        return $stmt->execute();
    }
    public function getEvenementsByStartup($id_startup)
{
    $sql = "SELECT * FROM evenement WHERE id_startup = :id_startup";
    $db = config::getConnexion();
    try {
        $query = $db->prepare($sql);
        $query->bindParam(':id_startup', $id_startup);
        $query->execute();
        return $query->fetchAll();
    } catch (PDOException $e) {
        die('Erreur: ' . $e->getMessage());
    }
}

public function deleteEvenement($id)
{
    $sql = "DELETE FROM evenement WHERE id_evenement = :id_evenement";
    $db = config::getConnexion();
    $req = $db->prepare($sql);
    $req->bindValue(':id_evenement', $id);

    try {
        $req->execute();
    } catch (Exception $e) {
        die('Error: ' . $e->getMessage());
    }

    header("Location: addstartup.php"); // Redirection vers la page de front office
    exit();
}

public function deleteEvenements($id)
{
    $sql = "DELETE FROM evenement WHERE id_evenement = :id_evenement";
    $db = config::getConnexion();
    $req = $db->prepare($sql);
    $req->bindValue(':id_evenement', $id);

    try {
        $req->execute();
    } catch (Exception $e) {
        die('Error: ' . $e->getMessage());
    }

    header("Location: events.php"); // Redirection vers la page de front office
    exit();
}
public function getEvenementById($id)
{
    try {
        $pdo = config::getConnexion();
        $stmt = $pdo->prepare("SELECT * FROM evenement WHERE id_evenement = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        die("Erreur : " . $e->getMessage());
    }
}
public function updateEvenement($evenement, $id_evenement)
{
    try {
        $pdo = config::getConnexion();
        $query = $pdo->prepare(
            "UPDATE evenement SET 
                id_startup = :id_startup,
                date_evenement = :date_evenement,
                type = :type,
                horaire = :horaire,
                nb_place = :nb_place,
                affiche = :affiche,
                nom = :nom
            WHERE id_evenement = :id_evenement"
        );

        $query->execute([
            'id_evenement' => $id_evenement,
            'id_startup' => $evenement->getIdStartup(),
            'date_evenement' => $evenement->getDateEvenement(),
            'type' => $evenement->getType(),
            'horaire' => $evenement->getHoraire(),
            'nb_place' => $evenement->getNbPlace(),
            'affiche' => $evenement->getAffiche(),
            'nom' => $evenement->getNom()
        ]);
    } catch (PDOException $e) {
        throw new Exception("Erreur lors de la mise à jour de l'événement : " . $e->getMessage());
    }
}
public function getAllEvenements()
{
    try {
        $sql = "SELECT * FROM evenement";
        $db = Config::getConnexion();
        $query = $db->prepare($sql);
        $query->execute();
        return $query->fetchAll();
    } catch (PDOException $e) {
        die('Erreur: ' . $e->getMessage());
    }
    $enLigneCount = 0;
$presentielCount = 0;

foreach ($evenements as $event) {
    if (strtolower($event['type']) === 'en ligne') {
        $enLigneCount++;
    } elseif (strtolower($event['type']) === 'présentiel') {
        $presentielCount++;
    }
}
}

public function modifierEvennement($data) {
    $sql = "UPDATE evenement SET 
            nom = :nom,
            date_evenement = :date_evenement,
            type = :type,
            horaire = :horaire,
            nb_place = :nb_place,
            affiche = :affiche
            WHERE id_evenement = :id";

    $db = config::getConnexion();
    $query = $db->prepare($sql);
    
    return $query->execute([
        ':id' => $data['id'],
        ':nom' => $data['nom'],
        ':date_evenement' => $data['date_evenement'],
        ':type' => $data['type'],
        ':horaire' => $data['horaire'],
        ':nb_place' => $data['nb_place'],
        ':affiche' => $data['affiche']
    ]);
}

public function updateNbPlace($id_evenement, $newNbPlace) {
    $sql = "UPDATE evenement SET nb_place = :nb_place WHERE id_evenement = :id_evenement";
    $db = config::getConnexion();
    try {
        $query = $db->prepare($sql);
        $query->execute([
            'nb_place' => $newNbPlace,
            'id_evenement' => $id_evenement
        ]);
    } catch (PDOException $e) {
        echo 'Erreur : ' . $e->getMessage();
    }
}
public function decrementerPlaces($id_evenement) {
    $sql = "UPDATE evenement SET nb_place = nb_place - 1 WHERE id_evenement = :id AND nb_place > 0";
    $db = config::getConnexion();
    $query = $db->prepare($sql);
    $query->bindValue(':id', $id_evenement);
    $query->execute();
}

}
?>
