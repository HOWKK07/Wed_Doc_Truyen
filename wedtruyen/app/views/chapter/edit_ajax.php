<?php
session_start();
require_once '../../config/connect.php';
require_once '../../controllers/chapterController.php';

header('Content-Type: application/json; charset=utf-8');

if (!isset($_GET['id_chuong']) || empty($_GET['id_chuong'])) {
    echo json_encode(['success' => false, 'error' => 'Không tìm thấy ID chương.']);
    exit;
}

$id_chuong = $_GET['id_chuong'];
$controller = new ChapterController($conn);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $controller->suaChapter();
        echo json_encode(['success' => true]);
        exit;
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        exit;
    }
}
echo json_encode(['success' => false, 'error' => 'Yêu cầu không hợp lệ']);
exit;
?>