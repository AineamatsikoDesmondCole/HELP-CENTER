<?php
// api/vote.php - handle FAQ upvote/downvote requests
require_once __DIR__ . '/../controllers/FAQController.php';
require_once __DIR__ . '/../controllers/BaseController.php';

class VoteController extends BaseController {
    public function handleRequest() {
        // Debug: Check for any output before JSON
        error_log("Vote API called - Method: " . $_SERVER['REQUEST_METHOD'] . ", Input: " . file_get_contents('php://input'));
        
        // Basic validation
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->errorResponse('Method not allowed', 405);
        }

        // Read JSON body
        $input = json_decode(file_get_contents('php://input'), true);
        $faqId = isset($input['faq_id']) ? (int)$input['faq_id'] : 0;
        $type  = isset($input['type']) ? $input['type'] : '';

        if ($faqId <= 0 || !in_array($type, ['up', 'down'], true)) {
            $this->errorResponse('Invalid parameters');
        }

        try {
            $faqController = new FAQController();
            $result = $faqController->voteFAQ($faqId, $type);
            
            error_log("Vote result: " . print_r($result, true));
            
            $this->jsonResponse([
                'success'   => true,
                'upvotes'   => (int)$result['upvotes'],
                'downvotes' => (int)$result['downvotes'],
            ]);
        } catch (Exception $e) {
            error_log("Vote exception: " . $e->getMessage());
            $this->errorResponse('Failed to record vote', 500);
        }
    }
}

// Handle the request
$controller = new VoteController();
$controller->handleRequest();
?>


