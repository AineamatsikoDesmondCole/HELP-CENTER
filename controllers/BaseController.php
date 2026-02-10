<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../helpers/functions.php';

/**
 * Base Controller Class
 * Provides common functionality for all controllers
 */
abstract class BaseController {
    protected $db;
    
    public function __construct() {
        global $db;
        $this->db = $db;
    }
    
    /**
     * Send JSON response
     */
    protected function jsonResponse($data, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
    
    /**
     * Send error response
     */
    protected function errorResponse($message, $statusCode = 400) {
        $this->jsonResponse(['error' => $message], $statusCode);
    }
    
    /**
     * Validate required fields
     */
    protected function validateRequired($data, $fields) {
        foreach ($fields as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                $this->errorResponse("Field '$field' is required");
            }
        }
    }
    
    /**
     * Get database instance
     */
    protected function getDB() {
        return $this->db;
    }
}
?>
