<?php
session_start();
require_once '../../config/connect.php';
require_once '../../controllers/thuVienController.php';

if (!isset($_SESSION['user'])) {
    echo json_encode(['success' => false, 'message' => 'Bạn cần đăng nhập để xóa khỏi thư viện.']);
    exit();
}

$id_nguoidung = $_SESSION['user']['id_nguoidung'];
$id_truyen = $_GET['id_truyen'] ?? null;

if (!$id_truyen) {
    echo json_encode(['success' => false, 'message' => 'ID truyện không hợp lệ.']);
    exit();
}

$thuVienController = new ThuVienController($conn);
if ($thuVienController->xoaKhoiThuVien($id_nguoidung, $id_truyen)) {
    echo json_encode(['success' => true, 'message' => 'Đã xóa khỏi thư viện.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Không thể xóa khỏi thư viện.']);
}
?>