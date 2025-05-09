<?php
require_once '../../config.php';

// Check if ID was provided
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
    $id_consultation = $_GET['id'];
    
    try {
        $db = Config::getConnexion();
        
        $stmt = $db->prepare("DELETE FROM consultation WHERE id_consultation = ?");
        $stmt->execute([$id_consultation]);
        
        if ($stmt->rowCount() > 0) {
            header('Location: consultation.php?success=1'); // Success message
        } else {
            header('Location: consultation.php?error=1'); // Error: not found
        }
        exit;
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) { // Foreign key constraint violation
            error_log("Foreign key constraint violation: " . $e->getMessage());
            header('Location: consultation.php?error=3'); // Error: foreign key constraint
        } else {
            error_log("Database error: " . $e->getMessage());
            header('Location: consultation.php?error=2'); // Error: database issue
        }
        exit;
    }
} else {
    header('Location: consultation.php');
    exit;
}
?>