<?php
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../helpers/ApiResponse.php';
require_once __DIR__ . '/../middleware/Auth.php';

class NotificationController {
    private $conn;
    private $table_name = "notifications";

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function getNotifications($page = 1, $limit = 10) {
        $auth = new Auth();
        $user = $auth->authenticate();

        if (!is_array($user)) {
            return $user;
        }

        try {
            $offset = ($page - 1) * $limit;

            $query = "SELECT * FROM " . $this->table_name . "
                     WHERE id_nguoidung = :id_nguoidung
                     ORDER BY ngay_tao DESC
                     LIMIT :limit OFFSET :offset";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":id_nguoidung", $user['id_nguoidung']);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();

            $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Get total count for pagination
            $count_query = "SELECT COUNT(*) as total 
                           FROM " . $this->table_name . "
                           WHERE id_nguoidung = :id_nguoidung";
            
            $count_stmt = $this->conn->prepare($count_query);
            $count_stmt->bindParam(":id_nguoidung", $user['id_nguoidung']);
            $count_stmt->execute();
            $total = $count_stmt->fetch(PDO::FETCH_ASSOC)['total'];

            return ApiResponse::success([
                'notifications' => $notifications,
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

    public function markAsRead($id) {
        $auth = new Auth();
        $user = $auth->authenticate();

        if (!is_array($user)) {
            return $user;
        }

        try {
            $query = "UPDATE " . $this->table_name . "
                     SET da_doc = 1
                     WHERE id_notification = :id_notification
                     AND id_nguoidung = :id_nguoidung";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":id_notification", $id);
            $stmt->bindParam(":id_nguoidung", $user['id_nguoidung']);

            if ($stmt->execute()) {
                return ApiResponse::success(null, "Notification marked as read");
            }

            return ApiResponse::error("Unable to mark notification as read");
        } catch(PDOException $e) {
            return ApiResponse::error("Database error: " . $e->getMessage());
        }
    }

    public function markAllAsRead() {
        $auth = new Auth();
        $user = $auth->authenticate();

        if (!is_array($user)) {
            return $user;
        }

        try {
            $query = "UPDATE " . $this->table_name . "
                     SET da_doc = 1
                     WHERE id_nguoidung = :id_nguoidung
                     AND da_doc = 0";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":id_nguoidung", $user['id_nguoidung']);

            if ($stmt->execute()) {
                return ApiResponse::success(null, "All notifications marked as read");
            }

            return ApiResponse::error("Unable to mark notifications as read");
        } catch(PDOException $e) {
            return ApiResponse::error("Database error: " . $e->getMessage());
        }
    }

    public function delete($id) {
        $auth = new Auth();
        $user = $auth->authenticate();

        if (!is_array($user)) {
            return $user;
        }

        try {
            $query = "DELETE FROM " . $this->table_name . "
                     WHERE id_notification = :id_notification
                     AND id_nguoidung = :id_nguoidung";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":id_notification", $id);
            $stmt->bindParam(":id_nguoidung", $user['id_nguoidung']);

            if ($stmt->execute()) {
                return ApiResponse::success(null, "Notification deleted successfully");
            }

            return ApiResponse::error("Unable to delete notification");
        } catch(PDOException $e) {
            return ApiResponse::error("Database error: " . $e->getMessage());
        }
    }

    public function deleteAll() {
        $auth = new Auth();
        $user = $auth->authenticate();

        if (!is_array($user)) {
            return $user;
        }

        try {
            $query = "DELETE FROM " . $this->table_name . "
                     WHERE id_nguoidung = :id_nguoidung";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":id_nguoidung", $user['id_nguoidung']);

            if ($stmt->execute()) {
                return ApiResponse::success(null, "All notifications deleted successfully");
            }

            return ApiResponse::error("Unable to delete notifications");
        } catch(PDOException $e) {
            return ApiResponse::error("Database error: " . $e->getMessage());
        }
    }

    public function getUnreadCount() {
        $auth = new Auth();
        $user = $auth->authenticate();

        if (!is_array($user)) {
            return $user;
        }

        try {
            $query = "SELECT COUNT(*) as unread_count 
                     FROM " . $this->table_name . "
                     WHERE id_nguoidung = :id_nguoidung
                     AND da_doc = 0";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":id_nguoidung", $user['id_nguoidung']);
            $stmt->execute();

            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return ApiResponse::success(['unread_count' => $result['unread_count']]);
        } catch(PDOException $e) {
            return ApiResponse::error("Database error: " . $e->getMessage());
        }
    }
}
?> 