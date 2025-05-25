<?php
session_start();
require_once '../../config/connect.php';

$data = json_decode(file_get_contents('php://input'), true);

if (!is_array($data)) {
    echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ.']);
    exit();
}

$conn->begin_transaction();

try {
    $offset = 10000;
    foreach ($data as $index => $item) {
        $id_anh = (int)$item['id'];
        $so_trang_am = -($index + 1 + $offset);
        $stmt = $conn->prepare("UPDATE anh_chuong SET so_trang = ? WHERE id_anh = ?");
        $stmt->bind_param("ii", $so_trang_am, $id_anh);
        $stmt->execute();
    }
    foreach ($data as $item) {
        $id_anh = (int)$item['id'];
        $so_trang = (int)$item['so_trang'];
        $stmt = $conn->prepare("UPDATE anh_chuong SET so_trang = ? WHERE id_anh = ?");
        $stmt->bind_param("ii", $so_trang, $id_anh);
        $stmt->execute();
    }
    $conn->commit();
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
