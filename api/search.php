<?php
// Allow CORS for local development
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

// Check if search parameter exists
if (!isset($_GET['q']) || strlen($_GET['q']) < 2) {
    echo json_encode([]);
    exit;
}

$searchTerm = $_GET['q'];

// Database connection (adjust path as needed)
require_once '../includes/config.php';
require_once '../includes/db.php';

try {
    $db = new Database();
    
    // Search in both question and answer
    $stmt = $db->query("
        SELECT id, question, answer 
        FROM faqs 
        WHERE (question LIKE ? OR answer LIKE ?) 
          AND is_archived = FALSE 
        ORDER BY (upvotes - downvotes) DESC 
        LIMIT 10
    ", ["%$searchTerm%", "%$searchTerm%"]);
    
    $results = [];
    while ($row = $stmt->fetch()) {
        $results[] = [
            'id' => $row['id'],
            'question' => htmlspecialchars($row['question']),
            'snippet' => substr(strip_tags($row['answer']), 0, 100) . '...'
        ];
    }
    
    echo json_encode($results);
    
} catch (Exception $e) {
    echo json_encode(['error' => 'Search failed']);
}
?>