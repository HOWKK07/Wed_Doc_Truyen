<?php
class TruyenModel {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    // Thêm truyện mới
    public function themTruyen($ten_truyen, $tac_gia, $id_loai_truyen, $anh_bia, $mo_ta, $trang_thai) {
        $sql = "INSERT INTO truyen (ten_truyen, tac_gia, id_loai_truyen, anh_bia, mo_ta, trang_thai)
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ssisss", $ten_truyen, $tac_gia, $id_loai_truyen, $anh_bia, $mo_ta, $trang_thai);

        if ($stmt->execute()) {
            return $this->conn->insert_id; // Trả về ID của truyện vừa thêm
        } else {
            return false;
        }
    }

    // Thêm thể loại cho truyện vào bảng trung gian
    public function themTheLoaiChoTruyen($id_truyen, $id_theloai) {
        $sql = "INSERT INTO truyen_theloai (id_truyen, id_theloai) VALUES (?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $id_truyen, $id_theloai);
        return $stmt->execute();
    }
}
?>