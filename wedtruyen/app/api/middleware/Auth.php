<?php
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../helpers/ApiResponse.php';

class Auth {
    private $conn;
    private $table_name = "nguoidung";

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function authenticate() {
        $headers = getallheaders();
        $token = isset($headers['Authorization']) ? str_replace('Bearer ', '', $headers['Authorization']) : null;

        if (!$token) {
            return ApiResponse::unauthorized("No token provided");
        }

        try {
            $query = "SELECT id_nguoidung, ten_dang_nhap, vai_tro FROM " . $this->table_name . " 
                     WHERE token = :token AND token_expiry > NOW()";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":token", $token);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                return $row;
            }

            return ApiResponse::unauthorized("Invalid or expired token");
        } catch(PDOException $e) {
            return ApiResponse::error("Database error: " . $e->getMessage());
        }
    }

    public function requireAdmin() {
        $user = $this->authenticate();
        if (is_array($user) && $user['vai_tro'] === 'admin') {
            return $user;
        }
        return ApiResponse::forbidden("Admin access required");
    }
}
?> 