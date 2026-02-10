<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../helpers/functions.php';
require_once __DIR__ . '/../models/FAQModel.php';
require_once __DIR__ . '/../models/CategoryModel.php';
require_once __DIR__ . '/../models/SupportModel.php';

class AdminController {
    private $faqModel;
    private $categoryModel;
    private $supportModel;
    
    public function __construct() {
        $this->faqModel = new FAQModel();
        $this->categoryModel = new CategoryModel();
        $this->supportModel = new SupportModel();
    }
    
    /**
     * Get dashboard statistics
     */
    public function getDashboardStats() {
        // Get FAQ count
        $faqCount = $this->faqModel->getTotalFAQCount();
        
        // Get pending support questions
        $pendingCount = $this->supportModel->getPendingSupportCount();
        
        // Get new questions for notification badge
        $newQuestions = $this->supportModel->getPendingSupportCount();
        
        // Get categories count
        $categories = $this->categoryModel->getAllCategories();
        $categoryCount = count($categories->fetchAll());
        
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
        // This method would need to be implemented in FAQModel
        // For now, keeping the direct database access
        global $db;
        return $db->query("
            INSERT INTO faqs (question, answer, category_id) 
            VALUES (?, ?, ?)
        ", [$question, $answer, $categoryId]);
    }
    
    /**
     * Update FAQ
     */
    public function updateFAQ($id, $question, $answer, $categoryId) {
        // This method would need to be implemented in FAQModel
        // For now, keeping the direct database access
        global $db;
        return $db->query("
            UPDATE faqs 
            SET question = ?, answer = ?, category_id = ? 
            WHERE id = ?
        ", [$question, $answer, $categoryId, $id]);
    }
    
    /**
     * Archive FAQ
     */
    public function archiveFAQ($id, $archivedBy) {
        // This method would need to be implemented in FAQModel
        // For now, keeping the direct database access
        global $db;
        return $db->query("
            UPDATE faqs 
            SET is_archived = TRUE, archived_by = ? 
            WHERE id = ?
        ", [$archivedBy, $id]);
    }
    
    /**
     * Restore FAQ
     */
    public function restoreFAQ($id) {
        // This method would need to be implemented in FAQModel
        // For now, keeping the direct database access
        global $db;
        return $db->query("
            UPDATE faqs 
            SET is_archived = FALSE, archived_by = NULL 
            WHERE id = ?
        ", [$id]);
    }
    
    /**
     * Get all FAQs (including archived for admin)
     */
    public function getAllFAQs($includeArchived = false) {
        // This method would need to be implemented in FAQModel
        // For now, keeping the direct database access
        global $db;
        $sql = "SELECT f.*, c.name as category_name 
                FROM faqs f 
                LEFT JOIN categories c ON f.category_id = c.id";
        
        if (!$includeArchived) {
            $sql .= " WHERE f.is_archived = FALSE";
        }
        
        $sql .= " ORDER BY f.id DESC";
        
        return $db->query($sql);
    }
    
    /**
     * Get all categories (including archived for admin)
     */
    public function getAllCategories($includeArchived = false) {
        // This method would need to be implemented in CategoryModel
        // For now, keeping the direct database access
        global $db;
        $sql = "SELECT * FROM categories";
        
        if (!$includeArchived) {
            $sql .= " WHERE is_archived = FALSE";
        }
        
        $sql .= " ORDER BY name";
        
        return $db->query($sql);
    }
}
?>
