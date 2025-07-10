<?php
session_start();
require_once __DIR__ . '/../config/database.php';

class Auth {
    private $conn;
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function getConnection() {
        return $this->conn;
    }
    
    public function login($username, $password) {
        try {
            $query = "SELECT * FROM users WHERE username = :username LIMIT 1";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":username", $username);
            $stmt->execute();
            
            if($stmt->rowCount() > 0) {
                $row = $stmt->fetch();
                if(password_verify($password, $row['password'])) {
                    $_SESSION['user_id'] = $row['id'];
                    $_SESSION['username'] = $row['username'];
                    $_SESSION['role'] = $row['role'];
                    return true;
                }
            }
            return false;
        } catch(PDOException $e) {
            return false;
        }
    }
    
    public function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }
    
    public function isAdmin() {
        return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
    }
    
    public function getCurrentUser() {
        if($this->isLoggedIn()) {
            return [
                'id' => $_SESSION['user_id'],
                'username' => $_SESSION['username'],
                'role' => $_SESSION['role']
            ];
        }
        return null;
    }
    
    public function logout() {
        session_unset();
        session_destroy();
        return true;
    }
    
    public function requireLogin() {
        if(!$this->isLoggedIn()) {
            header("Location: /spk_mora/login.php");
            exit();
        }
    }
    
    public function requireAdmin() {
        if(!$this->isAdmin()) {
            header("Location: /spk_mora/index.php");
            exit();
        }
    }
}

// Initialize Auth
$auth = new Auth();
?>
