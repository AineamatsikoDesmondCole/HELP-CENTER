<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../helpers/functions.php';

class SupportController {
    private $db;
    
    public function __construct() {
        $this->db = new Database();
    }
    
    /**
     * Create new support question
     */
    public function createSupportQuestion($email, $question) {
        return $this->db->query("
            INSERT INTO support_questions (user_email, question) 
            VALUES (?, ?)
        ", [$email, $question]);
    }
    
    /**
     * Get all new questions (not viewed)
     */
    public function getNewQuestions() {
        return $this->db->query("
            SELECT * FROM support_questions 
            WHERE admin_viewed = 0 AND answered = 0 
            ORDER BY id DESC
        ")->fetchAll();
    }
    
    /**
     * Get pending questions (viewed but not answered)
     */
    public function getPendingQuestions() {
        return $this->db->query("
            SELECT * FROM support_questions 
            WHERE admin_viewed = 1 AND answered = 0 
            ORDER BY id DESC
        ")->fetchAll();
    }
    
    /**
     * Get answered questions
     */
    public function getAnsweredQuestions($limit = 50) {
        return $this->db->query("
            SELECT * FROM support_questions 
            WHERE answered = 1 
            ORDER BY id DESC 
            LIMIT ?
        ", [$limit])->fetchAll();
    }
    
    /**
     * Get single support question
     */
    public function getSupportQuestion($id) {
        $result = $this->db->query("
            SELECT * FROM support_questions 
            WHERE id = ?
        ", [$id]);
        
        return $result->fetch();
    }
    
    /**
     * Answer support question
     */
    public function answerQuestion($questionId, $answer, $answeredBy, $faqId = null) {
        return $this->db->query("
            UPDATE support_questions 
            SET answered = 1, admin_viewed = 1, answered_by = ?, faq_id = ? 
            WHERE id = ?
        ", [$answeredBy, $faqId, $questionId]);
    }
    
    /**
     * Mark questions as viewed
     */
    public function markAsViewed() {
        return $this->db->query("
            UPDATE support_questions 
            SET admin_viewed = 1 
            WHERE admin_viewed = 0
        ");
    }
    
    /**
     * Get pending support count
     */
    public function getPendingCount() {
        $result = $this->db->query("
            SELECT COUNT(*) as count 
            FROM support_questions 
            WHERE answered = 0
        ");
        
        return $result->fetch()['count'];
    }
    
    /**
     * Get new questions count
     */
    public function getNewCount() {
        $result = $this->db->query("
            SELECT COUNT(*) as count 
            FROM support_questions 
            WHERE admin_viewed = 0
        ");
        
        return $result->fetch()['count'];
    }
}
?>
