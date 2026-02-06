<?php
// Ensure session is started for helpers that rely on $_SESSION
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/db.php';

/**
 * Truncate text to specified length
 */
function truncateText($text, $length = 100) {
    if (empty($text)) {
        return '';
    }
    
    $text = strip_tags($text);
    if (strlen($text) > $length) {
        $text = substr($text, 0, $length);
        // Don't cut in the middle of a word
        $text = substr($text, 0, strrpos($text, ' ')) . '...';
    }
    return $text;
}

/**
 * Get all active categories
 */
function getCategories() {
    $db = new Database();
    return $db->query("SELECT * FROM categories WHERE is_archived = FALSE ORDER BY name");
}

/**
 * Get most helpful FAQs
 */
function getMostHelpfulFAQs($limit = 10) {
    $db = new Database();
    return $db->query("
        SELECT f.*, c.name as category_name 
        FROM faqs f 
        LEFT JOIN categories c ON f.category_id = c.id 
        WHERE f.is_archived = FALSE 
        ORDER BY (f.upvotes - f.downvotes) DESC 
        LIMIT ?
    ", [$limit]);
}

/**
 * Search FAQs
 */
function searchFAQs($query, $limit = 10) {
    $db = new Database();
    $searchTerm = '%' . $query . '%';
    return $db->query("
        SELECT f.*, c.name as category_name 
        FROM faqs f 
        LEFT JOIN categories c ON f.category_id = c.id 
        WHERE (f.question LIKE ? OR f.answer LIKE ?) 
          AND f.is_archived = FALSE 
        ORDER BY (f.upvotes - f.downvotes) DESC 
        LIMIT ?
    ", [$searchTerm, $searchTerm, $limit]);
}

/**
 * Get FAQs by category ID
 */
function getFAQsByCategory($categoryId) {
    $db = new Database();
    return $db->query("
        SELECT f.*, c.name as category_name 
        FROM faqs f 
        LEFT JOIN categories c ON f.category_id = c.id 
        WHERE f.category_id = ? 
          AND f.is_archived = FALSE 
        ORDER BY (f.upvotes - f.downvotes) DESC
    ", [$categoryId]);
}

/**
 * Get single FAQ by ID
 */
function getFAQ($faqId) {
    $db = new Database();
    $result = $db->query("
        SELECT f.*, c.name as category_name 
        FROM faqs f 
        LEFT JOIN categories c ON f.category_id = c.id 
        WHERE f.id = ? AND f.is_archived = FALSE
    ", [$faqId]);
    
    return $result->fetch();
}

/**
 * Get category by ID
 */
function getCategory($categoryId) {
    $db = new Database();
    $result = $db->query("
        SELECT * FROM categories 
        WHERE id = ? AND is_archived = FALSE
    ", [$categoryId]);
    
    return $result->fetch();
}

/**
 * Get related FAQs (same category, excluding current)
 */
function getRelatedFAQs($faqId, $categoryId, $limit = 5) {
    $db = new Database();
    return $db->query("
        SELECT id, question 
        FROM faqs 
        WHERE category_id = ? 
          AND id != ? 
          AND is_archived = FALSE 
        ORDER BY (upvotes - downvotes) DESC 
        LIMIT ?
    ", [$categoryId, $faqId, $limit]);
}

/**
 * Get FAQ count by category
 */
function getFAQCountByCategory($categoryId) {
    $db = new Database();
    $result = $db->query("
        SELECT COUNT(*) as count 
        FROM faqs 
        WHERE category_id = ? AND is_archived = FALSE
    ", [$categoryId]);
    
    return $result->fetch()['count'];
}

/**
 * Get total FAQ count
 */
function getTotalFAQCount() {
    $db = new Database();
    $result = $db->query("SELECT COUNT(*) as count FROM faqs WHERE is_archived = FALSE");
    return $result->fetch()['count'];
}

/**
 * Get pending support questions count
 */
function getPendingSupportCount() {
    $db = new Database();
    $result = $db->query("SELECT COUNT(*) as count FROM support_questions WHERE admin_viewed = FALSE");
    return $result->fetch()['count'];
}

/**
 * Calculate helpfulness percentage
 */
function calculateHelpfulness($upvotes, $downvotes) {
    $total = $upvotes + $downvotes;
    if ($total == 0) {
        return 0;
    }
    return round(($upvotes / $total) * 100);
}

/**
 * Sanitize user input
 */
function sanitizeInput($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

/**
 * Redirect with message
 */
function redirect($url, $message = null) {
    if ($message) {
        $_SESSION['flash_message'] = $message;
    }
    header("Location: $url");
    exit;
}

/**
 * Get flash message
 */
function getFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $message;
    }
    return null;
}

/**
 * Check if user is admin
 */
function isAdmin() {
    return isset($_SESSION['user_id']) && isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

/**
 * Require admin access
 */
function requireAdmin() {
    if (!isAdmin()) {
        redirect('admin/login.php', 'Please login as administrator');
    }
}

/**
 * Generate excerpt with full words
 */
function getExcerpt($text, $length = 200) {
    $text = strip_tags($text);
    if (strlen($text) <= $length) {
        return $text;
    }
    
    $excerpt = substr($text, 0, $length);
    $excerpt = substr($excerpt, 0, strrpos($excerpt, ' '));
    return $excerpt . '...';
}

/**
 * Format date nicely
 */
function formatDate($dateString, $format = 'F j, Y') {
    if (empty($dateString)) {
        return 'N/A';
    }
    return date($format, strtotime($dateString));
}
?>