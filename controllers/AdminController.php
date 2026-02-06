<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../helpers/functions.php';

class AdminController {
    private $db;
    
    public function __construct() {
        $this->db = new Database();
    }
    
    /**
     * Get dashboard statistics
     */
    public function getDashboardStats() {
        $conn = $this->db->getConnection();
        
        // Get FAQ count
        $faqCount = $conn->query("SELECT COUNT(*) as count FROM faqs WHERE is_archived = 0")->fetch()['count'];
        
        // Get pending support questions
        $pendingCount = $conn->query("SELECT COUNT(*) as count FROM support_questions WHERE answered = 0")->fetch()['count'];
        
        // Get new questions for notification badge
        $newQuestions = $conn->query("SELECT COUNT(*) as count FROM support_questions WHERE admin_viewed = 0")->fetch()['count'];
        
        // Get categories count
        $categoryCount = $conn->query("SELECT COUNT(*) as count FROM categories WHERE is_archived = 0")->fetch()['count'];
        
        return [
            'faq_count' => $faqCount,
            'pending_count' => $pendingCount,
            'new_questions' => $newQuestions,
            'category_count' => $categoryCount
        ];
    }
    
    /**
     * Create new FAQ
     */
    public function createFAQ($question, $answer, $categoryId) {
        return $this->db->query("
            INSERT INTO faqs (question, answer, category_id) 
            VALUES (?, ?, ?)
        ", [$question, $answer, $categoryId]);
    }
    
    /**
     * Update FAQ
     */
    public function updateFAQ($id, $question, $answer, $categoryId) {
        return $this->db->query("
            UPDATE faqs 
            SET question = ?, answer = ?, category_id = ? 
            WHERE id = ?
        ", [$question, $answer, $categoryId, $id]);
    }
    
    /**
     * Archive FAQ
     */
    public function archiveFAQ($id, $archivedBy) {
        return $this->db->query("
            UPDATE faqs 
            SET is_archived = TRUE, archived_by = ? 
            WHERE id = ?
        ", [$archivedBy, $id]);
    }
    
    /**
     * Restore FAQ
     */
    public function restoreFAQ($id) {
        return $this->db->query("
            UPDATE faqs 
            SET is_archived = FALSE, archived_by = NULL 
            WHERE id = ?
        ", [$id]);
    }
    
    /**
     * Get all FAQs (including archived for admin)
     */
    public function getAllFAQs($includeArchived = false) {
        $sql = "SELECT f.*, c.name as category_name 
                FROM faqs f 
                LEFT JOIN categories c ON f.category_id = c.id";
        
        if (!$includeArchived) {
            $sql .= " WHERE f.is_archived = FALSE";
        }
        
        $sql .= " ORDER BY f.id DESC";
        
        return $this->db->query($sql);
    }
    
    /**
     * Get all categories (including archived for admin)
     */
    public function getAllCategories($includeArchived = false) {
        $sql = "SELECT * FROM categories";
        
        if (!$includeArchived) {
            $sql .= " WHERE is_archived = FALSE";
        }
        
        $sql .= " ORDER BY name";
        
        return $this->db->query($sql);
    }
}
?>
