<?php
session_start();
require_once __DIR__ . '/../config/connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user'])) {
    echo json_encode(['notifications' => [], 'unread_count' => 0]);
    exit;
}

$id_nguoidung = $_SESSION['user']['id_nguoidung'];
$action = $_GET['action'] ?? 'list';

switch ($action) {
    case 'list':
        // Lấy danh sách thông báo
        $sql = "SELECT n.*, t.ten_truyen, c.so_chuong, c.tieu_de 
                FROM notifications n
                LEFT JOIN chuong c ON n.id_chuong = c.id_chuong
                LEFT JOIN truyen t ON c.id_truyen = t.id_truyen
                WHERE n.id_nguoidung = ? 
                ORDER BY n.ngay_tao DESC 
                LIMIT 20";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id_nguoidung);
        $stmt->execute();
        $result = $stmt->get_result();

        $notifications = [];
        while ($row = $result->fetch_assoc()) {
            $notifications[] = $row;
        }

        // Đếm số thông báo chưa đọc
        $count_sql = "SELECT COUNT(*) as unread FROM notifications WHERE id_nguoidung = ? AND da_doc = 0";
        $count_stmt = $conn->prepare($count_sql);
        $count_stmt->bind_param("i", $id_nguoidung);
        $count_stmt->execute();
        $count_result = $count_stmt->get_result();
        $unread_count = $count_result->fetch_assoc()['unread'];

        echo json_encode([
            'notifications' => $notifications,
            'unread_count' => $unread_count
        ]);
        break;

    case 'mark_read':
        // Đánh dấu thông báo đã đọc
        $id_notification = (int)$_POST['id_notification'];
        $sql = "UPDATE notifications SET da_doc = 1 WHERE id_notification = ? AND id_nguoidung = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $id_notification, $id_nguoidung);
        $success = $stmt->execute();
        
        echo json_encode(['success' => $success]);
        break;

    case 'mark_all_read':
        // Đánh dấu tất cả đã đọc
        $sql = "UPDATE notifications SET da_doc = 1 WHERE id_nguoidung = ? AND da_doc = 0";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id_nguoidung);
        $success = $stmt->execute();
        
        echo json_encode(['success' => $success]);
        break;

    case 'check_new':
        // Kiểm tra thông báo mới (để polling)
        $last_check = $_GET['last_check'] ?? date('Y-m-d H:i:s', strtotime('-1 minute'));
        
        $sql = "SELECT COUNT(*) as new_count FROM notifications 
                WHERE id_nguoidung = ? AND ngay_tao > ? AND da_doc = 0";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("is", $id_nguoidung, $last_check);
        $stmt->execute();
        $result = $stmt->get_result();
        $new_count = $result->fetch_assoc()['new_count'];
        
        echo json_encode(['new_count' => $new_count]);
        break;
}
?>