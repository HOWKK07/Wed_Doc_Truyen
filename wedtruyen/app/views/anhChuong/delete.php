<?php
session_start();
require_once '../../config/connect.php';

if (!isset($_GET['id_anh']) || !isset($_GET['id_chuong'])) {
    die("Lỗi: Không tìm thấy ID ảnh hoặc ID chương.");
}

$id_anh = $_GET['id_anh'];
$id_chuong = $_GET['id_chuong'];

// Lấy thông tin ảnh để xóa file
$sql = "SELECT duong_dan_anh, so_trang FROM anh_chuong WHERE id_anh = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_anh);
$stmt->execute();
$result = $stmt->get_result();
$anh = $result->fetch_assoc();

if ($anh) {
    $file_path = __DIR__ . "/../../../" . $anh['duong_dan_anh'];
    if (file_exists($file_path)) {
        unlink($file_path); // Xóa file ảnh
    }

    $so_trang = $anh['so_trang'];

    // Xóa ảnh khỏi cơ sở dữ liệu
    $sql = "DELETE FROM anh_chuong WHERE id_anh = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id_anh);
    $stmt->execute();

    // Cập nhật số trang của các ảnh sau ảnh bị xóa
    $sql = "UPDATE anh_chuong SET so_trang = so_trang - 1 WHERE id_chuong = ? AND so_trang > ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $id_chuong, $so_trang);
    $stmt->execute();
} else {
    echo "Lỗi: Không tìm thấy ID chương hoặc ID truyện.";
}

header("Location: list.php?id_chuong=$id_chuong");
exit();
?>