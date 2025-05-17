<?php
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../helpers/ApiResponse.php';

class UserController {
    private $conn;
    private $table_name = "nguoidung";

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function register() {
        $data = json_decode(file_get_contents("php://input"));
        
        if (!isset($data->ten_dang_nhap) || !isset($data->mat_khau) || !isset($data->email)) {
            return ApiResponse::error("Missing required fields");
        }

        try {
            // Check if username exists
            $query = "SELECT id_nguoidung FROM " . $this->table_name . " WHERE ten_dang_nhap = :ten_dang_nhap";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":ten_dang_nhap", $data->ten_dang_nhap);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                return ApiResponse::error("Username already exists");
            }

            // Hash password
            $hashed_password = password_hash($data->mat_khau, PASSWORD_DEFAULT);

            // Insert new user
            $query = "INSERT INTO " . $this->table_name . " 
                    (ten_dang_nhap, mat_khau, email) 
                    VALUES (:ten_dang_nhap, :mat_khau, :email)";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":ten_dang_nhap", $data->ten_dang_nhap);
            $stmt->bindParam(":mat_khau", $hashed_password);
            $stmt->bindParam(":email", $data->email);

            if ($stmt->execute()) {
                return ApiResponse::success(null, "User registered successfully");
            }

            return ApiResponse::error("Unable to register user");
        } catch(PDOException $e) {
            return ApiResponse::error("Database error: " . $e->getMessage());
        }
    }

    public function login() {
        $data = json_decode(file_get_contents("php://input"));
        
        if (!isset($data->ten_dang_nhap) || !isset($data->mat_khau)) {
            return ApiResponse::error("Missing username or password");
        }

        try {
            $query = "SELECT id_nguoidung, ten_dang_nhap, mat_khau, vai_tro 
                     FROM " . $this->table_name . " 
                     WHERE ten_dang_nhap = :ten_dang_nhap";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":ten_dang_nhap", $data->ten_dang_nhap);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if (password_verify($data->mat_khau, $row['mat_khau'])) {
                    // Generate token
                    $token = bin2hex(random_bytes(32));
                    $token_expiry = date('Y-m-d H:i:s', strtotime('+24 hours'));

                    // Update user token
                    $update_query = "UPDATE " . $this->table_name . " 
                                   SET token = :token, token_expiry = :token_expiry 
                                   WHERE id_nguoidung = :id_nguoidung";
                    
                    $update_stmt = $this->conn->prepare($update_query);
                    $update_stmt->bindParam(":token", $token);
                    $update_stmt->bindParam(":token_expiry", $token_expiry);
                    $update_stmt->bindParam(":id_nguoidung", $row['id_nguoidung']);
                    $update_stmt->execute();

                    unset($row['mat_khau']);
                    $row['token'] = $token;
                    
                    return ApiResponse::success($row, "Login successful");
                }
            }

            return ApiResponse::unauthorized("Invalid username or password");
        } catch(PDOException $e) {
            return ApiResponse::error("Database error: " . $e->getMessage());
        }
    }

    public function getProfile() {
        $auth = new Auth();
        $user = $auth->authenticate();

        if (!is_array($user)) {
            return $user;
        }

        try {
            $query = "SELECT id_nguoidung, ten_dang_nhap, email, vai_tro, ngay_tao 
                     FROM " . $this->table_name . " 
                     WHERE id_nguoidung = :id_nguoidung";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":id_nguoidung", $user['id_nguoidung']);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                return ApiResponse::success($row);
            }

            return ApiResponse::notFound("User not found");
        } catch(PDOException $e) {
            return ApiResponse::error("Database error: " . $e->getMessage());
        }
    }

    public function updateProfile() {
        $auth = new Auth();
        $user = $auth->authenticate();

        if (!is_array($user)) {
            return $user;
        }

        $data = json_decode(file_get_contents("php://input"));
        
        try {
            $query = "UPDATE " . $this->table_name . " 
                     SET email = :email 
                     WHERE id_nguoidung = :id_nguoidung";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":email", $data->email);
            $stmt->bindParam(":id_nguoidung", $user['id_nguoidung']);

            if ($stmt->execute()) {
                return ApiResponse::success(null, "Profile updated successfully");
            }

            return ApiResponse::error("Unable to update profile");
        } catch(PDOException $e) {
            return ApiResponse::error("Database error: " . $e->getMessage());
        }
    }

    public function changePassword() {
        $auth = new Auth();
        $user = $auth->authenticate();

        if (!is_array($user)) {
            return $user;
        }

        $data = json_decode(file_get_contents("php://input"));
        
        if (!isset($data->current_password) || !isset($data->new_password)) {
            return ApiResponse::error("Missing required fields");
        }

        try {
            // Verify current password
            $query = "SELECT mat_khau FROM " . $this->table_name . " 
                     WHERE id_nguoidung = :id_nguoidung";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":id_nguoidung", $user['id_nguoidung']);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if (password_verify($data->current_password, $row['mat_khau'])) {
                    // Hash new password
                    $hashed_password = password_hash($data->new_password, PASSWORD_DEFAULT);

                    // Update password
                    $update_query = "UPDATE " . $this->table_name . " 
                                   SET mat_khau = :mat_khau 
                                   WHERE id_nguoidung = :id_nguoidung";
                    
                    $update_stmt = $this->conn->prepare($update_query);
                    $update_stmt->bindParam(":mat_khau", $hashed_password);
                    $update_stmt->bindParam(":id_nguoidung", $user['id_nguoidung']);

                    if ($update_stmt->execute()) {
                        return ApiResponse::success(null, "Password changed successfully");
                    }
                }
            }

            return ApiResponse::error("Current password is incorrect");
        } catch(PDOException $e) {
            return ApiResponse::error("Database error: " . $e->getMessage());
        }
    }
}
?> 