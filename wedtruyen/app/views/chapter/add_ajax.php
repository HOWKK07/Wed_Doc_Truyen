<?php
session_start();
require_once '../../config/connect.php';
require_once '../../controllers/chapterController.php';

header('Content-Type: application/json; charset=utf-8');

if (!isset($_GET['id_truyen']) || empty($_GET['id_truyen'])) {
    echo json_encode(['success' => false, 'error' => 'Không tìm thấy ID truyện.']);
    exit;
}

$id_truyen = (int)$_GET['id_truyen'];
$controller = new ChapterController($conn);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $controller->themChapter();
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