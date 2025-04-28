<?php
require_once '../../config/connect.php';
require_once '../../controllers/loaiTruyenController.php';

$id = $_GET['id']; // Lấy ID loại truyện từ URL
$controller = new LoaiTruyenController($conn);
$controller->xoaLoaiTruyen($id); // Gọi controller để xử lý xóa loại truyện
?>