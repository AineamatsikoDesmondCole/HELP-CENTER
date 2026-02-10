<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../helpers/functions.php';
require_once __DIR__ . '/../models/FAQModel.php';

class FAQController {
    private $faqModel;
    
    public function __construct() {
        $this->faqModel = new FAQModel();
    }
    
    /**
     * Get most helpful FAQs for homepage
     */
    public function getMostHelpfulFAQs($limit = 15) {
        return $this->faqModel->getMostHelpfulFAQs($limit);
    }
    
    /**
     * Get FAQs by category
     */
    public function getFAQsByCategory($categoryId) {
        return $this->faqModel->getFAQsByCategory($categoryId);
    }
    
    /**
     * Get single FAQ by ID
     */
    public function getFAQ($faqId) {
        return $this->faqModel->getFAQ($faqId);
    }
    
    /**
     * Get related FAQs (same category, excluding current)
     */
    public function getRelatedFAQs($faqId, $categoryId, $limit = 5) {
        return $this->faqModel->getRelatedFAQs($faqId, $categoryId, $limit);
    }
    
    /**
     * Search FAQs
     */
    public function searchFAQs($query, $limit = 10) {
        return $this->faqModel->searchFAQs($query, $limit);
    }
    
    /**
     * Vote on FAQ
     */
    public function voteFAQ($faqId, $type) {
        return $this->faqModel->voteFAQ($faqId, $type);
    }
}
?>
