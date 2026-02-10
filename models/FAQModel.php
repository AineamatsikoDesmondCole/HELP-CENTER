<?php
require_once __DIR__ . '/../config/db.php';

class FAQModel {
    private $db;
    
    public function __construct() {
        global $db;
        $this->db = $db;
    }
    
    /**
     * Get most helpful FAQs for homepage
     */
    public function getMostHelpfulFAQs($limit = 15) {
        $limit = (int)$limit; // Ensure it's an integer
        return $this->db->query("
            SELECT f.*, c.name as category_name 
            FROM faqs f 
            LEFT JOIN categories c ON f.category_id = c.id 
            WHERE f.is_archived = FALSE 
            ORDER BY (f.upvotes - f.downvotes) DESC 
            LIMIT $limit
        ");
    }
    
    /**
     * Get FAQs by category
     */
    public function getFAQsByCategory($categoryId) {
        return $this->db->query("
            SELECT * FROM faqs 
            WHERE category_id = ? AND is_archived = FALSE 
            ORDER BY id DESC
        ", [$categoryId]);
    }
    
    /**
     * Get single FAQ by ID
     */
    public function getFAQ($faqId) {
        $result = $this->db->query("
            SELECT f.*, c.name as category_name 
            FROM faqs f 
            LEFT JOIN categories c ON f.category_id = c.id 
            WHERE f.id = ? AND f.is_archived = FALSE
        ", [$faqId]);
        
        return $result->fetch();
    }
    
    /**
     * Get related FAQs (same category, excluding current)
     */
    public function getRelatedFAQs($faqId, $categoryId, $limit = 5) {
        $limit = (int)$limit; // Ensure it's an integer
        return $this->db->query("
            SELECT id, question 
            FROM faqs 
            WHERE category_id = ? 
              AND id != ? 
              AND is_archived = FALSE 
            ORDER BY (upvotes - downvotes) DESC 
            LIMIT $limit
        ", [$categoryId, $faqId]);
    }
    
    /**
     * Search FAQs
     */
    public function searchFAQs($query, $limit = 10) {
        $searchTerm = '%' . $query . '%';
        $limit = (int)$limit; // Ensure it's an integer
        return $this->db->query("
            SELECT id, question, answer 
            FROM faqs 
            WHERE (question LIKE ? OR answer LIKE ?) 
              AND is_archived = FALSE 
            ORDER BY (upvotes - downvotes) DESC 
            LIMIT $limit
        ", [$searchTerm, $searchTerm]);
    }
    
    /**
     * Vote on FAQ
     */
    public function voteFAQ($faqId, $type) {
        if ($type === 'up') {
            $stmt = $this->db->getConnection()->prepare('UPDATE faqs SET upvotes = upvotes + 1 WHERE id = ? AND is_archived = 0');
        } else {
            $stmt = $this->db->getConnection()->prepare('UPDATE faqs SET downvotes = downvotes + 1 WHERE id = ? AND is_archived = 0');
        }
        
        $stmt->execute([$faqId]);
        
        // Return updated counts
        $result = $this->db->query('SELECT upvotes, downvotes FROM faqs WHERE id = ?', [$faqId]);
        return $result->fetch();
    }
    
    /**
     * Get total FAQ count
     */
    public function getTotalFAQCount() {
        $result = $this->db->query("SELECT COUNT(*) as count FROM faqs WHERE is_archived = FALSE");
        return $result->fetch()['count'];
    }
}
?>
