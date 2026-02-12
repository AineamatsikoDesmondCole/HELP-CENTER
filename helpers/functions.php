<?php
// Ensure session is started for helpers that rely on $_SESSION
// if (session_status() === PHP_SESSION_NONE) {
//     session_start();
// }


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