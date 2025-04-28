<?php
require_once '../../config/connect.php';

$id = $_GET['id'];

// Xóa tài khoản
$sql = "DELETE FROM nguoidung WHERE id_nguoidung = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    header("Location: list.php?success=1");
    exit();
} else {
    echo "Lỗi: Không thể xóa tài khoản.";
}
?>