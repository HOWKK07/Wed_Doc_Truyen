<?php
require_once '../../config/connect.php';

$id = $_GET['id'];

// Thêm kiểm tra trước khi xóa
$sql = "SELECT vai_tro FROM nguoidung WHERE id_nguoidung = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if ($user && $user['vai_tro'] === 'admin') {
    $sql = "SELECT COUNT(*) as total FROM nguoidung WHERE vai_tro = 'admin'";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    if ($row['total'] <= 1) {
        echo "Không thể xóa tài khoản admin cuối cùng.";
        exit();
    }
}

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