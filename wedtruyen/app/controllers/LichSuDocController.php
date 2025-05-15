<?php
function luuLichSuDoc($conn, $id_user, $id_truyen, $chuong_doc) {
    // Kiểm tra xem đã có lịch sử đọc chưa
    $check_sql = "SELECT id FROM lich_su_doc WHERE id_user = ? AND id_truyen = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("ii", $id_user, $id_truyen);
    $check_stmt->execute();
    $result = $check_stmt->get_result();

    if ($result->num_rows > 0) {
        // Cập nhật lịch sử đọc hiện có
        $update_sql = "UPDATE lich_su_doc SET chuong_doc = ?, ngay_doc = CURRENT_TIMESTAMP 
                      WHERE id_user = ? AND id_truyen = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("iii", $chuong_doc, $id_user, $id_truyen);
        return $update_stmt->execute();
    } else {
        // Thêm lịch sử đọc mới
        $insert_sql = "INSERT INTO lich_su_doc (id_user, id_truyen, chuong_doc) VALUES (?, ?, ?)";
        $insert_stmt = $conn->prepare($insert_sql);
        $insert_stmt->bind_param("iii", $id_user, $id_truyen, $chuong_doc);
        return $insert_stmt->execute();
    }
}

function xoaLichSuDoc($conn, $id_user, $id_truyen = null) {
    if ($id_truyen) {
        // Xóa lịch sử đọc của một truyện cụ thể
        $sql = "DELETE FROM lich_su_doc WHERE id_user = ? AND id_truyen = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $id_user, $id_truyen);
    } else {
        // Xóa toàn bộ lịch sử đọc
        $sql = "DELETE FROM lich_su_doc WHERE id_user = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id_user);
    }
    return $stmt->execute();
}
?> 