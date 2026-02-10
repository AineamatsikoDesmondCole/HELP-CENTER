<?php
require_once __DIR__ . '/../config/db.php';

class SupportModel {
    private $db;
    
    public function __construct() {
        global $db;
        $this->db = $db;
    }
    
    /**
     * Get pending support questions count
     */
    public function getPendingSupportCount() {
        $result = $this->db->query("SELECT COUNT(*) as count FROM support_questions WHERE admin_viewed = FALSE");
        return $result->fetch()['count'];
    }
    
    /**
     * Create new support question
     */
    public function createSupportQuestion($name, $email, $question) {
        return $this->db->query("
            INSERT INTO support_questions (name, email, question, created_at) 
            VALUES (?, ?, ?, NOW())
        ", [$name, $email, $question]);
    }
    
    /**
     * Get all support questions
     */
    public function getAllSupportQuestions() {
        return $this->db->query("
            SELECT * FROM support_questions 
            ORDER BY created_at DESC
        ");
    }
    
    /**
     * Mark support question as viewed
     */
    public function markAsViewed($questionId) {
        return $this->db->query("
            UPDATE support_questions 
            SET admin_viewed = TRUE 
            WHERE id = ?
        ", [$questionId]);
    }
    
    /**
     * Delete support question
     */
    public function deleteSupportQuestion($questionId) {
        return $this->db->query("
            DELETE FROM support_questions 
            WHERE id = ?
        ", [$questionId]);
    }
}
?>
