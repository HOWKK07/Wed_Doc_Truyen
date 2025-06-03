<?php
session_start();
require_once '../../config/connect.php';
require_once '../../controllers/thuVienController.php';

// Set JSON header
header('Content-Type: application/json');

if (!isset($_SESSION['user'])) {
    echo json_encode(['success' => false, 'message' => 'Bạn cần đăng nhập để thêm vào thư viện.']);
    exit();
}

// Get JSON data from request body
$data = json_decode(file_get_contents('php://input'), true);
$id_nguoidung = $_SESSION['user']['id_nguoidung'];
$id_truyen = $data['id_truyen'] ?? null;

if (!$id_truyen) {
    echo json_encode(['success' => false, 'message' => 'ID truyện không hợp lệ.']);
    exit();
}

$thuVienController = new ThuVienController($conn);
try {
    if ($thuVienController->themVaoThuVien($id_nguoidung, $id_truyen)) {
        echo json_encode(['success' => true, 'message' => 'Đã thêm vào thư viện.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Truyện đã có trong thư viện.']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
}
?>