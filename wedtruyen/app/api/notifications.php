<?php
session_start();
require_once __DIR__ . '/../config/connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user'])) {
    echo json_encode([]);
    exit;
}

$id_nguoidung = $_SESSION['user']['id_nguoidung'];

$sql = "SELECT noi_dung, da_doc, ngay_tao, id_chuong FROM notifications WHERE id_nguoidung = ? ORDER BY ngay_tao DESC LIMIT 20";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_nguoidung);
$stmt->execute();
$result = $stmt->get_result();

$noti = [];
while ($row = $result->fetch_assoc()) {
    $noti[] = $row;
}
echo json_encode($noti);
?>