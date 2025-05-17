<?php
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../helpers/ApiResponse.php';
require_once __DIR__ . '/../middleware/Auth.php';

class ChapterController {
    private $conn;
    private $table_name = "chuong";

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function create() {
        $auth = new Auth();
        $user = $auth->requireAdmin();

        if (!is_array($user)) {
            return $user;
        }

        $data = json_decode(file_get_contents("php://input"));
        
        if (!isset($data->id_truyen) || !isset($data->so_chuong) || !isset($data->tieu_de)) {
            return ApiResponse::error("Missing required fields");
        }

        try {
            $this->conn->beginTransaction();

            // Create chapter
            $query = "INSERT INTO " . $this->table_name . " 
                    (id_truyen, so_chuong, tieu_de) 
                    VALUES (:id_truyen, :so_chuong, :tieu_de)";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":id_truyen", $data->id_truyen);
            $stmt->bindParam(":so_chuong", $data->so_chuong);
            $stmt->bindParam(":tieu_de", $data->tieu_de);

            if ($stmt->execute()) {
                $id_chuong = $this->conn->lastInsertId();

                // Add images if provided
                if (isset($data->images) && is_array($data->images)) {
                    $this->addImages($id_chuong, $data->images);
                }

                $this->conn->commit();
                return ApiResponse::success(['id_chuong' => $id_chuong], "Chapter created successfully");
            }

            $this->conn->rollBack();
            return ApiResponse::error("Unable to create chapter");
        } catch(PDOException $e) {
            $this->conn->rollBack();
            return ApiResponse::error("Database error: " . $e->getMessage());
        }
    }

    private function addImages($id_chuong, $images) {
        $query = "INSERT INTO anh_chuong (id_chuong, so_trang, duong_dan_anh) 
                 VALUES (:id_chuong, :so_trang, :duong_dan_anh)";
        $stmt = $this->conn->prepare($query);

        foreach ($images as $index => $image) {
            $page_number = $index + 1;
            $stmt->bindParam(":id_chuong", $id_chuong);
            $stmt->bindParam(":so_trang", $page_number);
            $stmt->bindParam(":duong_dan_anh", $image);
            $stmt->execute();
        }
    }

    public function update($id) {
        $auth = new Auth();
        $user = $auth->requireAdmin();

        if (!is_array($user)) {
            return $user;
        }

        $data = json_decode(file_get_contents("php://input"));
        
        try {
            $this->conn->beginTransaction();

            $query = "UPDATE " . $this->table_name . " 
                     SET so_chuong = :so_chuong,
                         tieu_de = :tieu_de
                     WHERE id_chuong = :id_chuong";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":so_chuong", $data->so_chuong);
            $stmt->bindParam(":tieu_de", $data->tieu_de);
            $stmt->bindParam(":id_chuong", $id);

            if ($stmt->execute()) {
                // Update images if provided
                if (isset($data->images) && is_array($data->images)) {
                    // Remove existing images
                    $delete_query = "DELETE FROM anh_chuong WHERE id_chuong = :id_chuong";
                    $delete_stmt = $this->conn->prepare($delete_query);
                    $delete_stmt->bindParam(":id_chuong", $id);
                    $delete_stmt->execute();

                    // Add new images
                    $this->addImages($id, $data->images);
                }

                $this->conn->commit();
                return ApiResponse::success(null, "Chapter updated successfully");
            }

            $this->conn->rollBack();
            return ApiResponse::error("Unable to update chapter");
        } catch(PDOException $e) {
            $this->conn->rollBack();
            return ApiResponse::error("Database error: " . $e->getMessage());
        }
    }

    public function delete($id) {
        $auth = new Auth();
        $user = $auth->requireAdmin();

        if (!is_array($user)) {
            return $user;
        }

        try {
            $query = "DELETE FROM " . $this->table_name . " WHERE id_chuong = :id_chuong";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":id_chuong", $id);

            if ($stmt->execute()) {
                return ApiResponse::success(null, "Chapter deleted successfully");
            }

            return ApiResponse::error("Unable to delete chapter");
        } catch(PDOException $e) {
            return ApiResponse::error("Database error: " . $e->getMessage());
        }
    }

    public function get($id) {
        try {
            $query = "SELECT c.*, t.ten_truyen,
                     (SELECT COUNT(*) FROM anh_chuong WHERE id_chuong = c.id_chuong) as so_trang
                     FROM " . $this->table_name . " c
                     LEFT JOIN truyen t ON c.id_truyen = t.id_truyen
                     WHERE c.id_chuong = :id_chuong";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":id_chuong", $id);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                $chapter = $stmt->fetch(PDO::FETCH_ASSOC);

                // Get images
                $images_query = "SELECT * FROM anh_chuong 
                               WHERE id_chuong = :id_chuong 
                               ORDER BY so_trang ASC";
                $images_stmt = $this->conn->prepare($images_query);
                $images_stmt->bindParam(":id_chuong", $id);
                $images_stmt->execute();

                $chapter['images'] = $images_stmt->fetchAll(PDO::FETCH_ASSOC);
                return ApiResponse::success($chapter);
            }

            return ApiResponse::notFound("Chapter not found");
        } catch(PDOException $e) {
            return ApiResponse::error("Database error: " . $e->getMessage());
        }
    }

    public function getByStory($id_truyen, $page = 1, $limit = 10) {
        try {
            $offset = ($page - 1) * $limit;

            $query = "SELECT c.*, 
                     (SELECT COUNT(*) FROM anh_chuong WHERE id_chuong = c.id_chuong) as so_trang
                     FROM " . $this->table_name . " c
                     WHERE c.id_truyen = :id_truyen
                     ORDER BY c.so_chuong ASC
                     LIMIT :limit OFFSET :offset";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":id_truyen", $id_truyen);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();

            $chapters = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Get total count for pagination
            $count_query = "SELECT COUNT(*) as total 
                           FROM " . $this->table_name . " 
                           WHERE id_truyen = :id_truyen";
            
            $count_stmt = $this->conn->prepare($count_query);
            $count_stmt->bindParam(":id_truyen", $id_truyen);
            $count_stmt->execute();
            $total = $count_stmt->fetch(PDO::FETCH_ASSOC)['total'];

            return ApiResponse::success([
                'chapters' => $chapters,
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