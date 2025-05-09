<?php
require_once __DIR__ . '/../config.php';

class FeedbackController {
    private $db;

    public function __construct() {
        $this->db = config::getConnexion();
    }

    public function getAllFeedback() {
        try {
            $query = "SELECT id_feedback, id_consultation, note FROM feedback";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching feedback: " . $e->getMessage());
            return [];
        }
    }

    public function getFeedbackById($id) {
        try {
            $query = "SELECT * FROM feedback WHERE id_feedback = :id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching feedback: " . $e->getMessage());
            return false;
        }
    }

    public function createFeedback($id_consultation, $note) {
        try {
            $stmt = $this->db->prepare("INSERT INTO feedback (id_consultation, note) VALUES (:id_consultation, :note)");
            $stmt->bindParam(':id_consultation', $id_consultation, PDO::PARAM_INT);
            $stmt->bindParam(':note', $note, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Feedback creation error: " . $e->getMessage());
            return false;
        }
    }

    public function updateFeedback($id, $note) {
        try {
            $stmt = $this->db->prepare("UPDATE feedback SET note = :note WHERE id_feedback = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->bindParam(':note', $note, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error updating feedback: " . $e->getMessage());
            return false;
        }
    }

    public function deleteFeedback($id) {
        try {
            $stmt = $this->db->prepare("DELETE FROM feedback WHERE id_feedback = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error deleting feedback: " . $e->getMessage());
            return false;
        }
    }

    public function getAllConsultations() {
        try {
            $query = "SELECT id_consultation FROM consultation";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching consultations: " . $e->getMessage());
            return [];
        }
    }
}
?>