<?php
function luuLichSuDoc($conn, $id_nguoidung, $id_chuong) {
    // Kiểm tra đã có lịch sử đọc chưa
    $check_sql = "SELECT id_lich_su FROM lich_su_doc WHERE id_nguoidung = ? AND id_chuong = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("ii", $id_nguoidung, $id_chuong);
    $check_stmt->execute();
    $result = $check_stmt->get_result();

    if ($result->num_rows > 0) {
        // Đã có, cập nhật thời gian đọc
        $update_sql = "UPDATE lich_su_doc SET thoi_gian_doc = CURRENT_TIMESTAMP WHERE id_nguoidung = ? AND id_chuong = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("ii", $id_nguoidung, $id_chuong);
        return $update_stmt->execute();
    } else {
        // Chưa có, thêm mới
        $insert_sql = "INSERT INTO lich_su_doc (id_nguoidung, id_chuong) VALUES (?, ?)";
        $insert_stmt = $conn->prepare($insert_sql);
        $insert_stmt->bind_param("ii", $id_nguoidung, $id_chuong);
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

function tangLuotXemChuong1Lan1Ngay($conn, $id_nguoidung, $id_chuong) {
    // Kiểm tra đã xem hôm nay chưa
    $sql = "SELECT * FROM lich_su_doc 
            WHERE id_nguoidung = ? AND id_chuong = ? 
            AND DATE(thoi_gian_doc) = CURDATE()";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $id_nguoidung, $id_chuong);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 0) {
        // Chưa xem hôm nay, tăng lượt xem
        $sql = "UPDATE chuong SET luot_xem = luot_xem + 1 WHERE id_chuong = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id_chuong);
        $stmt->execute();

        // Lấy id_truyen từ id_chuong
        $sql = "SELECT id_truyen FROM chuong WHERE id_chuong = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id_chuong);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $id_truyen = $row['id_truyen'];

        // Cập nhật tổng lượt xem cho truyện
        $sql = "UPDATE truyen SET luot_xem = (SELECT SUM(luot_xem) FROM chuong WHERE id_truyen = ?) WHERE id_truyen = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $id_truyen, $id_truyen);
        $stmt->execute();
    }
}
?>