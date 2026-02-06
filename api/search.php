<?php
// api/search.php - handle FAQ search requests
require_once __DIR__ . '/../controllers/FAQController.php';
require_once __DIR__ . '/../controllers/BaseController.php';

class SearchController extends BaseController {
    public function handleRequest() {
        // Allow CORS for local development
        header('Access-Control-Allow-Origin: *');
        header('Content-Type: application/json');

        // Check if search parameter exists
        if (!isset($_GET['q']) || strlen($_GET['q']) < 2) {
            $this->jsonResponse([]);
        }

        $searchTerm = $_GET['q'];

        try {
            $faqController = new FAQController();
            $stmt = $faqController->searchFAQs($searchTerm);
            
            $results = [];
            while ($row = $stmt->fetch()) {
                $results[] = [
                    'id' => $row['id'],
                    'question' => htmlspecialchars($row['question']),
                    'snippet' => substr(strip_tags($row['answer']), 0, 100) . '...'
                ];
            }
            
            $this->jsonResponse($results);
            
        } catch (Exception $e) {
            $this->errorResponse('Search failed', 500);
        }
    }
}

// Handle the request
$controller = new SearchController();
$controller->handleRequest();
?>