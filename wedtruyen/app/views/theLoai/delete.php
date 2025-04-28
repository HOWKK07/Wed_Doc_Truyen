<?php
require_once '../../config/connect.php';

$id = $_GET['id'];

// Xóa thể loại
$sql = "DELETE FROM theloai WHERE id_theloai = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    header("Location: list.php?success=1");
    exit();
} else {
    echo "Lỗi: Không thể xóa thể loại.";
}
?>