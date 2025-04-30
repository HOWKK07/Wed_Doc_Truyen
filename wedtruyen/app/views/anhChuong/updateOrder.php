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
    foreach ($data as $item) {
        $id_anh = $item['id'];
        $so_trang = $item['so_trang'];

        // Cập nhật thứ tự trang
        $sql = "UPDATE anh_chuong SET so_trang = ? WHERE id_anh = ?";
        $stmt = $conn->prepare($sql);
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