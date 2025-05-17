<?php
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../helpers/ApiResponse.php';
require_once __DIR__ . '/../middleware/Auth.php';

class CommentController {
    private $conn;
    private $story_table = "comments";
    private $chapter_table = "chapter_comments";

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function createStoryComment() {
        $auth = new Auth();
        $user = $auth->authenticate();

        if (!is_array($user)) {
            return $user;
        }

        $data = json_decode(file_get_contents("php://input"));
        
        if (!isset($data->id_truyen) || !isset($data->noi_dung)) {
            return ApiResponse::error("Missing required fields");
        }

        try {
            $query = "INSERT INTO " . $this->story_table . " 
                    (id_truyen, id_nguoidung, noi_dung) 
                    VALUES (:id_truyen, :id_nguoidung, :noi_dung)";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":id_truyen", $data->id_truyen);
            $stmt->bindParam(":id_nguoidung", $user['id_nguoidung']);
            $stmt->bindParam(":noi_dung", $data->noi_dung);

            if ($stmt->execute()) {
                return ApiResponse::success(['id_comment' => $this->conn->lastInsertId()], "Comment added successfully");
            }

            return ApiResponse::error("Unable to add comment");
        } catch(PDOException $e) {
            return ApiResponse::error("Database error: " . $e->getMessage());
        }
    }

    public function createChapterComment() {
        $auth = new Auth();
        $user = $auth->authenticate();

        if (!is_array($user)) {
            return $user;
        }

        $data = json_decode(file_get_contents("php://input"));
        
        if (!isset($data->id_chuong) || !isset($data->noi_dung)) {
            return ApiResponse::error("Missing required fields");
        }

        try {
            $query = "INSERT INTO " . $this->chapter_table . " 
                    (id_chuong, id_nguoidung, noi_dung) 
                    VALUES (:id_chuong, :id_nguoidung, :noi_dung)";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":id_chuong", $data->id_chuong);
            $stmt->bindParam(":id_nguoidung", $user['id_nguoidung']);
            $stmt->bindParam(":noi_dung", $data->noi_dung);

            if ($stmt->execute()) {
                return ApiResponse::success(['id_comment' => $this->conn->lastInsertId()], "Comment added successfully");
            }

            return ApiResponse::error("Unable to add comment");
        } catch(PDOException $e) {
            return ApiResponse::error("Database error: " . $e->getMessage());
        }
    }

    public function deleteStoryComment($id) {
        $auth = new Auth();
        $user = $auth->authenticate();

        if (!is_array($user)) {
            return $user;
        }

        try {
            $query = "DELETE FROM " . $this->story_table . " 
                     WHERE id_comment = :id_comment 
                     AND (id_nguoidung = :id_nguoidung OR :is_admin = 1)";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":id_comment", $id);
            $stmt->bindParam(":id_nguoidung", $user['id_nguoidung']);
            $is_admin = $user['vai_tro'] === 'admin' ? 1 : 0;
            $stmt->bindParam(":is_admin", $is_admin);

            if ($stmt->execute()) {
                return ApiResponse::success(null, "Comment deleted successfully");
            }

            return ApiResponse::error("Unable to delete comment");
        } catch(PDOException $e) {
            return ApiResponse::error("Database error: " . $e->getMessage());
        }
    }

    public function deleteChapterComment($id) {
        $auth = new Auth();
        $user = $auth->authenticate();

        if (!is_array($user)) {
            return $user;
        }

        try {
            $query = "DELETE FROM " . $this->chapter_table . " 
                     WHERE id_comment = :id_comment 
                     AND (id_nguoidung = :id_nguoidung OR :is_admin = 1)";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":id_comment", $id);
            $stmt->bindParam(":id_nguoidung", $user['id_nguoidung']);
            $is_admin = $user['vai_tro'] === 'admin' ? 1 : 0;
            $stmt->bindParam(":is_admin", $is_admin);

            if ($stmt->execute()) {
                return ApiResponse::success(null, "Comment deleted successfully");
            }

            return ApiResponse::error("Unable to delete comment");
        } catch(PDOException $e) {
            return ApiResponse::error("Database error: " . $e->getMessage());
        }
    }

    public function getStoryComments($id_truyen, $page = 1, $limit = 10) {
        try {
            $offset = ($page - 1) * $limit;

            $query = "SELECT c.*, n.ten_dang_nhap 
                     FROM " . $this->story_table . " c
                     LEFT JOIN nguoidung n ON c.id_nguoidung = n.id_nguoidung
                     WHERE c.id_truyen = :id_truyen
                     ORDER BY c.ngay_binh_luan DESC
                     LIMIT :limit OFFSET :offset";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":id_truyen", $id_truyen);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();

            $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Get total count for pagination
            $count_query = "SELECT COUNT(*) as total 
                           FROM " . $this->story_table . " 
                           WHERE id_truyen = :id_truyen";
            
            $count_stmt = $this->conn->prepare($count_query);
            $count_stmt->bindParam(":id_truyen", $id_truyen);
            $count_stmt->execute();
            $total = $count_stmt->fetch(PDO::FETCH_ASSOC)['total'];

            return ApiResponse::success([
                'comments' => $comments,
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

    public function getChapterComments($id_chuong, $page = 1, $limit = 10) {
        try {
            $offset = ($page - 1) * $limit;

            $query = "SELECT c.*, n.ten_dang_nhap 
                     FROM " . $this->chapter_table . " c
                     LEFT JOIN nguoidung n ON c.id_nguoidung = n.id_nguoidung
                     WHERE c.id_chuong = :id_chuong
                     ORDER BY c.ngay_binh_luan DESC
                     LIMIT :limit OFFSET :offset";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":id_chuong", $id_chuong);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();

            $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Get total count for pagination
            $count_query = "SELECT COUNT(*) as total 
                           FROM " . $this->chapter_table . " 
                           WHERE id_chuong = :id_chuong";
            
            $count_stmt = $this->conn->prepare($count_query);
            $count_stmt->bindParam(":id_chuong", $id_chuong);
            $count_stmt->execute();
            $total = $count_stmt->fetch(PDO::FETCH_ASSOC)['total'];

            return ApiResponse::success([
                'comments' => $comments,
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
}
?> 