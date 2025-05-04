<?php
session_start();
require_once '../../config/connect.php';
require_once '../../controllers/danhGiaController.php';

if (!isset($_SESSION['user'])) {
    die("Bạn cần đăng nhập để đánh giá.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_truyen = $_POST['id_truyen'];
    $id_nguoidung = $_SESSION['user']['id_nguoidung'];
    $so_sao = (int)$_POST['so_sao'];

    $danhGiaController = new danhGiaController($conn);

    try {
        $danhGiaController->saveRating($id_truyen, $id_nguoidung, $so_sao);
        header("Location: chiTietTruyen.php?id_truyen=$id_truyen");
        exit();
    } catch (Exception $e) {
        die("Lỗi: " . $e->getMessage());
    }
}
?>