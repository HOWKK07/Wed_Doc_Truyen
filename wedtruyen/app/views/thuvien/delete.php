<?php
session_start();
require_once '../../config/connect.php';

// Kiểm tra người dùng đã đăng nhập
if (!isset($_SESSION['user']) || !is_array($_SESSION['user'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Bạn cần đăng nhập để thực hiện thao tác này.']);
    exit();
}

// Kiểm tra kết nối database
if (!isset($conn) || !($conn instanceof mysqli)) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Lỗi kết nối cơ sở dữ liệu.']);
    exit();
}

// Kiểm tra và lấy ID người dùng
if (!isset($_SESSION['user']['id_nguoidung'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Thông tin người dùng không hợp lệ.']);
    exit();
}

$id_nguoidung = $_SESSION['user']['id_nguoidung'];
$id_truyen = $_GET['id_truyen'] ?? null;

// Kiểm tra ID truyện
if (!$id_truyen || !is_numeric($id_truyen)) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'ID truyện không hợp lệ.']);
    exit();
}

// Xóa truyện khỏi thư viện
$sql = "DELETE FROM follows WHERE id_nguoidung = ? AND id_truyen = ?";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Lỗi chuẩn bị truy vấn.']);
    exit();
}

$stmt->bind_param("ii", $id_nguoidung, $id_truyen);

if ($stmt->execute()) {
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'message' => 'Truyện đã được xóa khỏi thư viện.']);
} else {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Không thể xóa truyện khỏi thư viện.']);
}

// Đóng statement
$stmt->close();
?>