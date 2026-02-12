<?php
require_once 'config/config.php';

echo "Testing environment variables:\n";
echo "DB_HOST: " . DB_HOST . "\n";
echo "DB_NAME: " . DB_NAME . "\n";
echo "SITE_URL: " . SITE_URL . "\n";
echo "Full API URL: " . SITE_URL . "api/vote.php\n";
?>
