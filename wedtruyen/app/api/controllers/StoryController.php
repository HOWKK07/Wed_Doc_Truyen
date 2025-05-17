<?php
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../helpers/ApiResponse.php';
require_once __DIR__ . '/../middleware/Auth.php';

class StoryController {
    private $conn;
    private $table_name = "truyen";

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
        
        if (!isset($data->ten_truyen) || !isset($data->id_loai_truyen)) {
            return ApiResponse::error("Missing required fields");
        }

        try {
            $query = "INSERT INTO " . $this->table_name . " 
                    (ten_truyen, tac_gia, mo_ta, anh_bia, id_loai_truyen, nam_phat_hanh, trang_thai) 
                    VALUES (:ten_truyen, :tac_gia, :mo_ta, :anh_bia, :id_loai_truyen, :nam_phat_hanh, :trang_thai)";

            $stmt = $this->conn->prepare($query);
            
            $stmt->bindParam(":ten_truyen", $data->ten_truyen);
            $stmt->bindParam(":tac_gia", $data->tac_gia);
            $stmt->bindParam(":mo_ta", $data->mo_ta);
            $stmt->bindParam(":anh_bia", $data->anh_bia);
            $stmt->bindParam(":id_loai_truyen", $data->id_loai_truyen);
            $stmt->bindParam(":nam_phat_hanh", $data->nam_phat_hanh);
            $stmt->bindParam(":trang_thai", $data->trang_thai);

            if ($stmt->execute()) {
                $id_truyen = $this->conn->lastInsertId();

                // Add categories if provided
                if (isset($data->theloai) && is_array($data->theloai)) {
                    $this->addCategories($id_truyen, $data->theloai);
                }

                return ApiResponse::success(['id_truyen' => $id_truyen], "Story created successfully");
            }

            return ApiResponse::error("Unable to create story");
        } catch(PDOException $e) {
            return ApiResponse::error("Database error: " . $e->getMessage());
        }
    }

    private function addCategories($id_truyen, $categories) {
        $query = "INSERT INTO truyen_theloai (id_truyen, id_theloai) VALUES (:id_truyen, :id_theloai)";
        $stmt = $this->conn->prepare($query);

        foreach ($categories as $id_theloai) {
            $stmt->bindParam(":id_truyen", $id_truyen);
            $stmt->bindParam(":id_theloai", $id_theloai);
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
            $query = "UPDATE " . $this->table_name . " 
                     SET ten_truyen = :ten_truyen,
                         tac_gia = :tac_gia,
                         mo_ta = :mo_ta,
                         anh_bia = :anh_bia,
                         id_loai_truyen = :id_loai_truyen,
                         nam_phat_hanh = :nam_phat_hanh,
                         trang_thai = :trang_thai
                     WHERE id_truyen = :id_truyen";

            $stmt = $this->conn->prepare($query);
            
            $stmt->bindParam(":ten_truyen", $data->ten_truyen);
            $stmt->bindParam(":tac_gia", $data->tac_gia);
            $stmt->bindParam(":mo_ta", $data->mo_ta);
            $stmt->bindParam(":anh_bia", $data->anh_bia);
            $stmt->bindParam(":id_loai_truyen", $data->id_loai_truyen);
            $stmt->bindParam(":nam_phat_hanh", $data->nam_phat_hanh);
            $stmt->bindParam(":trang_thai", $data->trang_thai);
            $stmt->bindParam(":id_truyen", $id);

            if ($stmt->execute()) {
                // Update categories if provided
                if (isset($data->theloai) && is_array($data->theloai)) {
                    // Remove existing categories
                    $delete_query = "DELETE FROM truyen_theloai WHERE id_truyen = :id_truyen";
                    $delete_stmt = $this->conn->prepare($delete_query);
                    $delete_stmt->bindParam(":id_truyen", $id);
                    $delete_stmt->execute();

                    // Add new categories
                    $this->addCategories($id, $data->theloai);
                }

                return ApiResponse::success(null, "Story updated successfully");
            }

            return ApiResponse::error("Unable to update story");
        } catch(PDOException $e) {
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
            $query = "DELETE FROM " . $this->table_name . " WHERE id_truyen = :id_truyen";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":id_truyen", $id);

            if ($stmt->execute()) {
                return ApiResponse::success(null, "Story deleted successfully");
            }

            return ApiResponse::error("Unable to delete story");
        } catch(PDOException $e) {
            return ApiResponse::error("Database error: " . $e->getMessage());
        }
    }

    public function get($id) {
        try {
            $query = "SELECT t.*, lt.ten_loai_truyen,
                     GROUP_CONCAT(th.ten_theloai) as theloai
                     FROM " . $this->table_name . " t
                     LEFT JOIN loai_truyen lt ON t.id_loai_truyen = lt.id_loai_truyen
                     LEFT JOIN truyen_theloai tt ON t.id_truyen = tt.id_truyen
                     LEFT JOIN theloai th ON tt.id_theloai = th.id_theloai
                     WHERE t.id_truyen = :id_truyen
                     GROUP BY t.id_truyen";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":id_truyen", $id);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                $row['theloai'] = explode(',', $row['theloai']);
                return ApiResponse::success($row);
            }

            return ApiResponse::notFound("Story not found");
        } catch(PDOException $e) {
            return ApiResponse::error("Database error: " . $e->getMessage());
        }
    }

    public function getAll($page = 1, $limit = 10, $search = '', $category = null, $type = null) {
        try {
            $offset = ($page - 1) * $limit;
            $where_conditions = [];
            $params = [];

            if ($search) {
                $where_conditions[] = "(t.ten_truyen LIKE :search OR t.tac_gia LIKE :search)";
                $params[':search'] = "%$search%";
            }

            if ($category) {
                $where_conditions[] = "tt.id_theloai = :category";
                $params[':category'] = $category;
            }

            if ($type) {
                $where_conditions[] = "t.id_loai_truyen = :type";
                $params[':type'] = $type;
            }

            $where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";

            $query = "SELECT t.*, lt.ten_loai_truyen,
                     GROUP_CONCAT(th.ten_theloai) as theloai
                     FROM " . $this->table_name . " t
                     LEFT JOIN loai_truyen lt ON t.id_loai_truyen = lt.id_loai_truyen
                     LEFT JOIN truyen_theloai tt ON t.id_truyen = tt.id_truyen
                     LEFT JOIN theloai th ON tt.id_theloai = th.id_theloai
                     $where_clause
                     GROUP BY t.id_truyen
                     ORDER BY t.ngay_tao DESC
                     LIMIT :limit OFFSET :offset";

            $stmt = $this->conn->prepare($query);
            
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();

            $stories = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $row['theloai'] = explode(',', $row['theloai']);
                $stories[] = $row;
            }

            // Get total count for pagination
            $count_query = "SELECT COUNT(DISTINCT t.id_truyen) as total
                           FROM " . $this->table_name . " t
                           LEFT JOIN truyen_theloai tt ON t.id_truyen = tt.id_truyen
                           $where_clause";
            
            $count_stmt = $this->conn->prepare($count_query);
            foreach ($params as $key => $value) {
                $count_stmt->bindValue($key, $value);
            }
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
}
?> 