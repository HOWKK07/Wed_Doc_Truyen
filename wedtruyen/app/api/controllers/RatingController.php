<?php
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../helpers/ApiResponse.php';
require_once __DIR__ . '/../middleware/Auth.php';

class RatingController {
    private $conn;
    private $table_name = "ratings";

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function rate() {
        $auth = new Auth();
        $user = $auth->authenticate();

        if (!is_array($user)) {
            return $user;
        }

        $data = json_decode(file_get_contents("php://input"));
        
        if (!isset($data->id_truyen) || !isset($data->so_sao)) {
            return ApiResponse::error("Missing required fields");
        }

        if ($data->so_sao < 1 || $data->so_sao > 5) {
            return ApiResponse::error("Rating must be between 1 and 5");
        }

        try {
            // Check if user has already rated
            $check_query = "SELECT id_rating FROM " . $this->table_name . " 
                          WHERE id_truyen = :id_truyen AND id_nguoidung = :id_nguoidung";
            
            $check_stmt = $this->conn->prepare($check_query);
            $check_stmt->bindParam(":id_truyen", $data->id_truyen);
            $check_stmt->bindParam(":id_nguoidung", $user['id_nguoidung']);
            $check_stmt->execute();

            if ($check_stmt->rowCount() > 0) {
                // Update existing rating
                $query = "UPDATE " . $this->table_name . " 
                         SET so_sao = :so_sao,
                             ngay_danh_gia = CURRENT_TIMESTAMP
                         WHERE id_truyen = :id_truyen 
                         AND id_nguoidung = :id_nguoidung";
            } else {
                // Create new rating
                $query = "INSERT INTO " . $this->table_name . " 
                         (id_truyen, id_nguoidung, so_sao) 
                         VALUES (:id_truyen, :id_nguoidung, :so_sao)";
            }

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":id_truyen", $data->id_truyen);
            $stmt->bindParam(":id_nguoidung", $user['id_nguoidung']);
            $stmt->bindParam(":so_sao", $data->so_sao);

            if ($stmt->execute()) {
                return ApiResponse::success(null, "Rating saved successfully");
            }

            return ApiResponse::error("Unable to save rating");
        } catch(PDOException $e) {
            return ApiResponse::error("Database error: " . $e->getMessage());
        }
    }

    public function getStoryRating($id_truyen) {
        try {
            $query = "SELECT 
                     COUNT(*) as total_ratings,
                     AVG(so_sao) as average_rating,
                     COUNT(CASE WHEN so_sao = 1 THEN 1 END) as one_star,
                     COUNT(CASE WHEN so_sao = 2 THEN 1 END) as two_stars,
                     COUNT(CASE WHEN so_sao = 3 THEN 1 END) as three_stars,
                     COUNT(CASE WHEN so_sao = 4 THEN 1 END) as four_stars,
                     COUNT(CASE WHEN so_sao = 5 THEN 1 END) as five_stars
                     FROM " . $this->table_name . "
                     WHERE id_truyen = :id_truyen";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":id_truyen", $id_truyen);
            $stmt->execute();

            $rating = $stmt->fetch(PDO::FETCH_ASSOC);
            $rating['average_rating'] = round($rating['average_rating'], 1);

            return ApiResponse::success($rating);
        } catch(PDOException $e) {
            return ApiResponse::error("Database error: " . $e->getMessage());
        }
    }

    public function getUserRating($id_truyen) {
        $auth = new Auth();
        $user = $auth->authenticate();

        if (!is_array($user)) {
            return $user;
        }

        try {
            $query = "SELECT so_sao, ngay_danh_gia 
                     FROM " . $this->table_name . "
                     WHERE id_truyen = :id_truyen 
                     AND id_nguoidung = :id_nguoidung";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":id_truyen", $id_truyen);
            $stmt->bindParam(":id_nguoidung", $user['id_nguoidung']);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                return ApiResponse::success($stmt->fetch(PDO::FETCH_ASSOC));
            }

            return ApiResponse::success(['so_sao' => 0]);
        } catch(PDOException $e) {
            return ApiResponse::error("Database error: " . $e->getMessage());
        }
    }

    public function deleteRating($id_truyen) {
        $auth = new Auth();
        $user = $auth->authenticate();

        if (!is_array($user)) {
            return $user;
        }

        try {
            $query = "DELETE FROM " . $this->table_name . " 
                     WHERE id_truyen = :id_truyen 
                     AND id_nguoidung = :id_nguoidung";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":id_truyen", $id_truyen);
            $stmt->bindParam(":id_nguoidung", $user['id_nguoidung']);

            if ($stmt->execute()) {
                return ApiResponse::success(null, "Rating deleted successfully");
            }

            return ApiResponse::error("Unable to delete rating");
        } catch(PDOException $e) {
            return ApiResponse::error("Database error: " . $e->getMessage());
        }
    }
}
?> 