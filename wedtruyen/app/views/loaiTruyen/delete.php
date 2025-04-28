<?php
require_once '../../config/connect.php';
require_once '../../controllers/loaiTruyenController.php';

if (isset($_GET['id']) && !empty($_GET['id'])) {
    $id = $_GET['id'];
    $controller = new LoaiTruyenController($conn);
    $controller->xoaLoaiTruyen($id);
} else {
    echo "Lỗi: ID không hợp lệ.";
    exit();
}
?>