<?php
require_once __DIR__ . '/../config/db.php';

class Auth {
    private $db;
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }
    
    // Admin login
    public function login($username, $password) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE username = ? AND role = 'admin'");
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password_hash'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            return true;
        }
        return false;
    }
    
    // Check if user is logged in as admin
    public function isAdmin() {
        return isset($_SESSION['user_id']) && $_SESSION['role'] === 'admin';
    }
    
    // Logout
    public function logout() {
        session_destroy();
        header('Location: ../index.php');
        exit;
    }
    
    // Protect admin pages
    public function requireAdmin() {
        if (!$this->isAdmin()) {
            header('Location: login.php');
            exit;
        }
    }
}
?>