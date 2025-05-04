<?php
session_start();
require_once '../../config/connect.php';
require_once '../../controllers/thuVienController.php';

if (!isset($_SESSION['user'])) {
    echo json_encode(['success' => false, 'message' => 'Bạn cần đăng nhập để thêm vào thư viện.']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);
$id_nguoidung = $_SESSION['user']['id_nguoidung'];
$id_truyen = $data['id_truyen'] ?? null;

if (!$id_truyen) {
    echo json_encode(['success' => false, 'message' => 'ID truyện không hợp lệ.']);
    exit();
}

$thuVienController = new ThuVienController($conn);
if ($thuVienController->themVaoThuVien($id_nguoidung, $id_truyen)) {
    echo json_encode(['success' => true, 'message' => 'Đã thêm vào thư viện.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Không thể thêm vào thư viện.']);
}
?>