<?php
require_once '../../config/connect.php';

if (!isset($_GET['id_truyen']) || empty($_GET['id_truyen'])) {
    die("Lỗi: Không tìm thấy ID truyện.");
}

$id_truyen = $_GET['id_truyen'];

// Lấy thông tin truyện
$sql = "SELECT anh_bia FROM truyen WHERE id_truyen = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_truyen);
$stmt->execute();
$result = $stmt->get_result();
$truyen = $result->fetch_assoc();

// Xóa ảnh bìa nếu tồn tại
if (file_exists(__DIR__ . "/../../../" . $truyen['anh_bia'])) {
    unlink(__DIR__ . "/../../../" . $truyen['anh_bia']);
}

// Xóa truyện
$sql = "DELETE FROM truyen WHERE id_truyen = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_truyen);

if ($stmt->execute()) {
    header("Location: manage.php?success=1");
    exit();
} else {
    echo "Lỗi: Không thể xóa truyện.";
}
?>