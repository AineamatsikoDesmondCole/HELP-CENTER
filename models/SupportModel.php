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
            INSERT INTO support_questions (user_email, question) 
            VALUES (?, ?)
        ", [$email, $question]);
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
     * Get single support question
     */
    public function getSupportQuestion($questionId) {
        $result = $this->db->query("
            SELECT * FROM support_questions 
            WHERE id = ?
        ", [$questionId]);
        return $result->fetch();
    }
    
    /**
     * Answer support question
     */
    public function answerSupportQuestion($questionId, $adminId, $answerText, $faqId = null) {
        return $this->db->query("
            UPDATE support_questions 
            SET answered = 1, admin_viewed = 1, answered_by = ?, faq_id = COALESCE(?, faq_id)
            WHERE id = ?
        ", [$adminId, $faqId, $questionId]);
    }
    
    /**
     * Mark all questions as viewed
     */
    public function markAllQuestionsViewed() {
        return $this->db->query("
            UPDATE support_questions 
            SET admin_viewed = 1 
            WHERE admin_viewed = 0
        ");
    }
    
    /**
     * Get new/unseen questions count
     */
    public function getNewSupportCount() {
        $result = $this->db->query("
            SELECT COUNT(*) as count 
            FROM support_questions 
            WHERE admin_viewed = 0 AND answered = 0
        ");
        return $result->fetch()['count'];
    }
    
    /**
     * Get new support questions
     */
    public function getNewSupportQuestions() {
        return $this->db->query("
            SELECT * FROM support_questions 
            WHERE admin_viewed = 0 AND answered = 0 
            ORDER BY id DESC
        ");
    }
    
    /**
     * Get pending support questions
     */
    public function getPendingSupportQuestions() {
        return $this->db->query("
            SELECT * FROM support_questions 
            WHERE admin_viewed = 1 AND answered = 0 
            ORDER BY id DESC
        ");
    }
    
    /**
     * Get answered support questions
     */
    public function getAnsweredSupportQuestions() {
        return $this->db->query("
            SELECT * FROM support_questions 
            WHERE answered = 1 
            ORDER BY id DESC 
            LIMIT 50
        ");
    }
}
?>
