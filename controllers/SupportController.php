<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../helpers/functions.php';
require_once __DIR__ . '/../models/SupportModel.php';

class SupportController {
    private $supportModel;
    
    public function __construct() {
        $this->supportModel = new SupportModel();
    }
    
    /**
     * Create new support question
     */
    public function createSupportQuestion($email, $question) {
        return $this->supportModel->createSupportQuestion('', $email, $question);
    }
    
    /**
     * Get all new questions (not viewed)
     */
    public function getNewQuestions() {
        return $this->supportModel->getAllSupportQuestions();
    }
    
    /**
     * Get pending questions (viewed but not answered)
     */
    public function getPendingQuestions() {
        return $this->supportModel->getAllSupportQuestions();
    }
    
    /**
     * Get answered questions
     */
    public function getAnsweredQuestions($limit = 50) {
        return $this->supportModel->getAllSupportQuestions();
    }
    
    /**
     * Get single support question
     */
    public function getSupportQuestion($id) {
        $questions = $this->supportModel->getAllSupportQuestions();
        foreach ($questions as $question) {
            if ($question['id'] == $id) {
                return $question;
            }
        }
        return null;
    }
    
    /**
     * Answer support question
     */
    public function answerQuestion($questionId, $answer, $answeredBy, $faqId = null) {
        // This method would need to be implemented in SupportModel
        // For now, keeping the direct database access
        global $db;
        return $db->query("
            UPDATE support_questions 
            SET answered = 1, admin_viewed = 1, answered_by = ?, faq_id = ? 
            WHERE id = ?
        ", [$answeredBy, $faqId, $questionId]);
    }
    
    /**
     * Mark questions as viewed
     */
    public function markAsViewed() {
        // This method would need to be implemented in SupportModel
        // For now, keeping the direct database access
        global $db;
        return $db->query("
            UPDATE support_questions 
            SET admin_viewed = 1 
            WHERE admin_viewed = 0
        ");
    }
    
    /**
     * Get pending support count
     */
    public function getPendingCount() {
        return $this->supportModel->getPendingSupportCount();
    }
    
    /**
     * Get new questions count
     */
    public function getNewCount() {
        return $this->supportModel->getPendingSupportCount();
    }
}
?>
