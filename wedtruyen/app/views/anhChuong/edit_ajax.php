<?php
session_start();
require_once '../../config/connect.php';
require_once '../../controllers/anhChuongController.php';

header('Content-Type: application/json; charset=utf-8');

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $controller = new AnhChuongController($conn);
        $controller->suaAnhAjax(); // Bạn cần thêm hàm này vào controller
        echo json_encode(['success' => true]);
        exit;
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    exit;
}
echo json_encode(['success' => false, 'error' => 'Yêu cầu không hợp lệ']);
exit;
?>
