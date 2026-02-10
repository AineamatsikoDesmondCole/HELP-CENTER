<?php
require_once __DIR__ . '/../config/db.php';

class CategoryModel {
    private $db;
    
    public function __construct() {
        global $db;
        $this->db = $db;
    }
    
    /**
     * Get all active categories
     */
    public function getAllCategories() {
        return $this->db->query("
            SELECT * FROM categories 
            WHERE is_archived = FALSE 
            ORDER BY name
        ");
    }
    
    /**
     * Get category by ID
     */
    public function getCategory($categoryId) {
        $result = $this->db->query("
            SELECT * FROM categories 
            WHERE id = ? AND is_archived = FALSE
        ", [$categoryId]);
        
        return $result->fetch();
    }
    
    /**
     * Get FAQ count by category
     */
    public function getFAQCountByCategory($categoryId) {
        $result = $this->db->query("
            SELECT COUNT(*) as count 
            FROM faqs 
            WHERE category_id = ? AND is_archived = FALSE
        ", [$categoryId]);
        
        return $result->fetch()['count'];
    }
    
    /**
     * Create new category
     */
    public function createCategory($name, $description) {
        return $this->db->query("
            INSERT INTO categories (name, description) 
            VALUES (?, ?)
        ", [$name, $description]);
    }
    
    /**
     * Update category
     */
    public function updateCategory($id, $name, $description) {
        return $this->db->query("
            UPDATE categories 
            SET name = ?, description = ? 
            WHERE id = ?
        ", [$name, $description, $id]);
    }
    
    /**
     * Archive category
     */
    public function archiveCategory($id) {
        return $this->db->query("
            UPDATE categories 
            SET is_archived = TRUE 
            WHERE id = ?
        ", [$id]);
    }
    
    /**
     * Restore category
     */
    public function restoreCategory($id) {
        return $this->db->query("
            UPDATE categories 
            SET is_archived = FALSE 
            WHERE id = ?
        ", [$id]);
    }
}
?>
