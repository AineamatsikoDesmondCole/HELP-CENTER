<?php
require_once 'config/config.php';
require_once 'controllers/FAQController.php';

echo "Testing FAQController directly:\n\n";

try {
    $faqController = new FAQController();
    $result = $faqController->voteFAQ(1, 'up');
    
    echo "Vote result:\n";
    print_r($result);
    
    echo "\nSuccess!\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
?>
