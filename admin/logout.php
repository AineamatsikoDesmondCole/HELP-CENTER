<<<<<<< HEAD
<?php
// admin/logout.php - SIMPLE LOGOUT
session_start();

// Clear all session variables
$_SESSION = array();

// Destroy the session
session_destroy();

// Redirect to login with logout message
header('Location: login.php?logout=1');
exit;
=======
<?php
// admin/logout.php - SIMPLE LOGOUT
session_start();

// Clear all session variables
$_SESSION = array();

// Destroy the session
session_destroy();

// Redirect to login with logout message
header('Location: login.php?logout=1');
exit;
>>>>>>> 36257f9ac8a2c27b93a4b73606d4a36660c330d7
?>