<?php
require_once __DIR__ . '/../config.php'; // Fixed missing closing quote
require_once __DIR__ . '/../Model/consultationmodel.php';

class ConsultationController
{
    private $db;

    public function __construct()
    {
        $this->db = Config::getConnexion();
    }

    
    public function createConsultation(Consultation $consultation)
    {
        $stmt = $this->db->prepare("
            INSERT INTO consultation
            (id_consultation, id_utilisateur1, id_utilisateur2, heure_deb, heure_fin, tarif)
            VALUES
            (?, ?, ?, ?, ?, ?)
        ");
        $success = $stmt->execute([
            $consultation->getIdConsultation(),
            $consultation->getIdUtilisateur1(),
            $consultation->getIdUtilisateur2(),
            $consultation->getHeureDeb(),
            $consultation->getHeureFin(),
            $consultation->getTarif()
        ]);

        if ($success) {
            // Send WhatsApp message after successful consultation creation
            $this->envoyerMessageWhatsApp($consultation);
        }

        return $success;
    }

    public function addConsultation($data)
    {
        $stmt = $this->db->prepare("
            INSERT INTO consultation (id_consultation, id_utilisateur1, id_utilisateur2, date_consultation, heure_deb, heure_fin, tarif)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $result = $stmt->execute([
            $data['id_consultation'],
            $data['id_utilisateur1'],
            $data['id_utilisateur2'],
            $data['date_consultation'],
            $data['heure_deb'],
            $data['heure_fin'],
            $data['tarif']
        ]);
        return $result;
    }

    public function updateConsultation($consultation)
    {
        // Validate if id_utilisateur1 exists
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM utilisateur WHERE id_utilisateur = ?");
        $stmt->execute([$consultation->getIdUtilisateur1()]);
        if ($stmt->fetchColumn() == 0) {
            throw new Exception("Le consultant sélectionné n'existe pas.");
        }

        // Validate if id_utilisateur2 exists
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM utilisateur WHERE id_utilisateur = ?");
        $stmt->execute([$consultation->getIdUtilisateur2()]);
        if ($stmt->fetchColumn() == 0) {
            throw new Exception("Le client sélectionné n'existe pas.");
        }

        // Proceed with the update
        $stmt = $this->db->prepare("
            UPDATE consultation
            SET id_utilisateur1 = ?, id_utilisateur2 = ?, date_consultation = ?, heure_deb = ?, heure_fin = ?, tarif = ?
            WHERE id_consultation = ?
        ");
        return $stmt->execute([
            $consultation->getIdUtilisateur1(),
            $consultation->getIdUtilisateur2(),
            $consultation->getDateConsultation(),
            $consultation->getHeureDeb(),
            $consultation->getHeureFin(),
            $consultation->getTarif(),
            $consultation->getIdConsultation()
        ]);
    }

    public function getConsultationById($id_consultation)
    {
        $stmt = $this->db->prepare("SELECT * FROM consultation WHERE id_consultation = ?");
        $stmt->execute([$id_consultation]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getAllUsers()
    {
        $stmt = $this->db->query("SELECT id_utilisateur, nom, prenom FROM utilisateur ORDER BY nom, prenom");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllConsultations()
    {
        // Corrected table name from 'consultations' to 'consultation'
        $query = "SELECT id_consultation, id_utilisateur1, id_utilisateur2, date_consultation, heure_deb, heure_fin, tarif FROM consultation";
        $stmt = $this->db->query($query);

        $consultations = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) { // Use fetch(PDO::FETCH_ASSOC) instead of fetch_assoc()
            // Debugging: Log the fetched row
            error_log("Fetched consultation: " . print_r($row, true));
            $consultations[] = $row;
        }

        $maxId = max(array_column($consultations, 'id_consultation')); // Ensure this is assigned to a variable
        $newId = $maxId + 1;

        return $consultations;
    }

    public function getUserById($user_id)
    {
        $stmt = $this->db->prepare("SELECT id_utilisateur, nom, prenom FROM utilisateur WHERE id_utilisateur = ?");
        $stmt->execute([$user_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function deleteConsultation($consultationId)
    {
        try {
            // Delete associated feedback first
            $deleteFeedbackQuery = "DELETE FROM feedback WHERE id_consultation = :consultationId";
            $stmtFeedback = $this->db->prepare($deleteFeedbackQuery);
            $stmtFeedback->bindParam(':consultationId', $consultationId, PDO::PARAM_INT);
            $stmtFeedback->execute();

            // Delete the consultation
            $deleteConsultationQuery = "DELETE FROM consultation WHERE id_consultation = :consultationId";
            $stmtConsultation = $this->db->prepare($deleteConsultationQuery);
            $stmtConsultation->bindParam(':consultationId', $consultationId, PDO::PARAM_INT);
            return $stmtConsultation->execute();
        } catch (PDOException $e) {
            throw $e; // Re-throw the exception to handle it in the calling code
        }
    }

    public function getAllFeedbacks()
    {
        // Update the query to match the actual column names in the feedback table
        $stmt = $this->db->query("SELECT id_consultation, feedback_text AS commentaire, rating AS note FROM feedback");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}