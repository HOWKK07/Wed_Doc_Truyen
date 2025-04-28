<?php
class LoaiTruyenModel {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function themLoaiTruyen($ten_loai_truyen) {
        $sql = "INSERT INTO loai_truyen (ten_loai_truyen) VALUES (?)";
        $stmt = $this->conn->prepare($sql);

        if (!$stmt) {
            throw new Exception("Lỗi chuẩn bị truy vấn: " . $this->conn->error);
        }

        $stmt->bind_param("s", $ten_loai_truyen);

        if (!$stmt->execute()) {
            throw new Exception("Lỗi: Không thể thêm loại truyện. " . $stmt->error);
        }

        return true;
    }

    public function layDanhSachLoaiTruyen() {
        $sql = "SELECT * FROM loai_truyen ORDER BY ngay_tao DESC";
        return $this->conn->query($sql);
    }

    public function layLoaiTruyenTheoId($id) {
        $sql = "SELECT * FROM loai_truyen WHERE id_loai_truyen = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function capNhatLoaiTruyen($id, $ten_loai_truyen) {
        $sql = "UPDATE loai_truyen SET ten_loai_truyen = ? WHERE id_loai_truyen = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("si", $ten_loai_truyen, $id);
        return $stmt->execute();
    }

    public function xoaLoaiTruyen($id) {
        $sql = "DELETE FROM loai_truyen WHERE id_loai_truyen = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }
}
?>
