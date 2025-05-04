<?php
session_start();
require_once '../../config/connect.php';
require_once '../../controllers/binhLuanController.php';

if (!isset($_SESSION['user'])) {
    die("Bạn cần đăng nhập để bình luận.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_chuong = $_POST['id_chuong'];
    $id_nguoidung = $_SESSION['user']['id_nguoidung'];
    $noi_dung = $_POST['noi_dung'];

    $binhLuanController = new BinhLuanController($conn);

    try {
        $binhLuanController->themBinhLuanChuong($id_chuong, $id_nguoidung, $noi_dung);
        header("Location: ../chapter/docChapter.php?id_chuong=$id_chuong");
        exit();
    } catch (Exception $e) {
        die("Lỗi: " . $e->getMessage());
    }
}
?>