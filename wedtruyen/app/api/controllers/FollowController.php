<?php
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../helpers/ApiResponse.php';
require_once __DIR__ . '/../middleware/Auth.php';

class FollowController {
    private $conn;
    private $table_name = "follows";

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function follow() {
        $auth = new Auth();
        $user = $auth->authenticate();

        if (!is_array($user)) {
            return $user;
        }

        $data = json_decode(file_get_contents("php://input"));
        
        if (!isset($data->id_truyen)) {
            return ApiResponse::error("Missing required fields");
        }

        try {
            // Check if already following
            $check_query = "SELECT id_follow FROM " . $this->table_name . " 
                          WHERE id_truyen = :id_truyen AND id_nguoidung = :id_nguoidung";
            
            $check_stmt = $this->conn->prepare($check_query);
            $check_stmt->bindParam(":id_truyen", $data->id_truyen);
            $check_stmt->bindParam(":id_nguoidung", $user['id_nguoidung']);
            $check_stmt->execute();

            if ($check_stmt->rowCount() > 0) {
                return ApiResponse::error("Already following this story");
            }

            // Add follow
            $query = "INSERT INTO " . $this->table_name . " 
                     (id_nguoidung, id_truyen) 
                     VALUES (:id_nguoidung, :id_truyen)";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":id_nguoidung", $user['id_nguoidung']);
            $stmt->bindParam(":id_truyen", $data->id_truyen);

            if ($stmt->execute()) {
                return ApiResponse::success(null, "Story followed successfully");
            }

            return ApiResponse::error("Unable to follow story");
        } catch(PDOException $e) {
            return ApiResponse::error("Database error: " . $e->getMessage());
        }
    }

    public function unfollow($id_truyen) {
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
                return ApiResponse::success(null, "Story unfollowed successfully");
            }

            return ApiResponse::error("Unable to unfollow story");
        } catch(PDOException $e) {
            return ApiResponse::error("Database error: " . $e->getMessage());
        }
    }

    public function getFollowedStories($page = 1, $limit = 10) {
        $auth = new Auth();
        $user = $auth->authenticate();

        if (!is_array($user)) {
            return $user;
        }

        try {
            $offset = ($page - 1) * $limit;

            $query = "SELECT t.*, lt.ten_loai_truyen,
                     GROUP_CONCAT(th.ten_theloai) as theloai,
                     f.ngay_theo_doi
                     FROM " . $this->table_name . " f
                     LEFT JOIN truyen t ON f.id_truyen = t.id_truyen
                     LEFT JOIN loai_truyen lt ON t.id_loai_truyen = lt.id_loai_truyen
                     LEFT JOIN truyen_theloai tt ON t.id_truyen = tt.id_truyen
                     LEFT JOIN theloai th ON tt.id_theloai = th.id_theloai
                     WHERE f.id_nguoidung = :id_nguoidung
                     GROUP BY t.id_truyen
                     ORDER BY f.ngay_theo_doi DESC
                     LIMIT :limit OFFSET :offset";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":id_nguoidung", $user['id_nguoidung']);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();

            $stories = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $row['theloai'] = explode(',', $row['theloai']);
                $stories[] = $row;
            }

            // Get total count for pagination
            $count_query = "SELECT COUNT(DISTINCT f.id_truyen) as total
                           FROM " . $this->table_name . " f
                           WHERE f.id_nguoidung = :id_nguoidung";
            
            $count_stmt = $this->conn->prepare($count_query);
            $count_stmt->bindParam(":id_nguoidung", $user['id_nguoidung']);
            $count_stmt->execute();
            $total = $count_stmt->fetch(PDO::FETCH_ASSOC)['total'];

            return ApiResponse::success([
                'stories' => $stories,
                'pagination' => [
                    'total' => $total,
                    'page' => $page,
                    'limit' => $limit,
                    'total_pages' => ceil($total / $limit)
                ]
            ]);
        } catch(PDOException $e) {
            return ApiResponse::error("Database error: " . $e->getMessage());
        }
    }

    public function checkFollow($id_truyen) {
        $auth = new Auth();
        $user = $auth->authenticate();

        if (!is_array($user)) {
            return $user;
        }

        try {
            $query = "SELECT id_follow, ngay_theo_doi 
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

            return ApiResponse::success(['is_following' => false]);
        } catch(PDOException $e) {
            return ApiResponse::error("Database error: " . $e->getMessage());
        }
    }
}
?> 