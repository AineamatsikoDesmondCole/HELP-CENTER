<?php
require_once 'config/config.php';
require_once 'models/FAQModel.php';

try {
    $faqModel = new FAQModel();
    $results = $faqModel->searchFAQs('test', 10);
    
    echo "Search results count: " . $results->rowCount() . "\n";
    
    while ($row = $results->fetch()) {
        echo "Found: " . $row['question'] . "\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
