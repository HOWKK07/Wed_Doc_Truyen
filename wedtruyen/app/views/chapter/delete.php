<?php
session_start();
require_once '../../config/connect.php';
require_once '../../controllers/chapterController.php';

$controller = new ChapterController($conn);

if (isset($_GET['id_chuong']) && isset($_GET['id_truyen'])) {
    try {
        $controller->xoaChapter($_GET['id_chuong'], $_GET['id_truyen']);
    } catch (Exception $e) {
        echo "<p style='color: red;'>" . $e->getMessage() . "</p>";
    }
} else {
    echo "Lỗi: Không tìm thấy ID chương hoặc ID truyện.";
}
?>