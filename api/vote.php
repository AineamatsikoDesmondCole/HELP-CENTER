<?php
// api/vote.php - handle FAQ upvote/downvote requests
header('Content-Type: application/json');

// Basic validation
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Read JSON body
$input = json_decode(file_get_contents('php://input'), true);
$faqId = isset($input['faq_id']) ? (int)$input['faq_id'] : 0;
$type  = isset($input['type']) ? $input['type'] : '';

if ($faqId <= 0 || !in_array($type, ['up', 'down'], true)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid parameters']);
    exit;
}

require_once '../includes/config.php';
require_once '../includes/db.php';

try {
    $db   = new Database();
    $conn = $db->getConnection();

    if ($type === 'up') {
        $stmt = $conn->prepare('UPDATE faqs SET upvotes = upvotes + 1 WHERE id = ? AND is_archived = 0');
    } else {
        $stmt = $conn->prepare('UPDATE faqs SET downvotes = downvotes + 1 WHERE id = ? AND is_archived = 0');
    }

    $stmt->execute([$faqId]);

    // Return updated counts for convenience
    $stmt = $conn->prepare('SELECT upvotes, downvotes FROM faqs WHERE id = ?');
    $stmt->execute([$faqId]);
    $row = $stmt->fetch();

    echo json_encode([
        'success'   => true,
        'upvotes'   => (int)$row['upvotes'],
        'downvotes' => (int)$row['downvotes'],
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to record vote']);
}
?>


