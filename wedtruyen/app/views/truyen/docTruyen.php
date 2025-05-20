<?php
session_start();
require_once '../../controllers/LichSuDocController.php';

// Kiểm tra đăng nhập và lưu lịch sử
if (isset($_SESSION['user_id'])) {
    $id_truyen = $_GET['id_truyen'];
    $chuong = $_GET['chuong'];
    luuLichSuDoc($conn, $_SESSION['user_id'], $id_truyen, $chuong);
}

// ... existing code ... 