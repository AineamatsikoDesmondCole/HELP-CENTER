<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../helpers/functions.php';
require_once __DIR__ . '/../models/CategoryModel.php';

class CategoryController {
    private $categoryModel;
    
    public function __construct() {
        $this->categoryModel = new CategoryModel();
    }
    
    /**
     * Get all active categories
     */
    public function getAllCategories() {
        return $this->categoryModel->getAllCategories();
    }
    
    /**
     * Get category by ID
     */
    public function getCategory($categoryId) {
        return $this->categoryModel->getCategory($categoryId);
    }
    
    /**
     * Get FAQ count by category
     */
    public function getFAQCountByCategory($categoryId) {
        return $this->categoryModel->getFAQCountByCategory($categoryId);
    }
    
    /**
     * Create new category
     */
    public function createCategory($name, $description) {
        return $this->categoryModel->createCategory($name, $description);
    }
    
    /**
     * Update category
     */
    public function updateCategory($id, $name, $description) {
        return $this->categoryModel->updateCategory($id, $name, $description);
    }
    
    /**
     * Archive category
     */
    public function archiveCategory($id) {
        return $this->categoryModel->archiveCategory($id);
    }
    
    /**
     * Restore category
     */
    public function restoreCategory($id) {
        return $this->categoryModel->restoreCategory($id);
    }
}
?>
