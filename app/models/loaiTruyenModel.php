<?php
class LoaiTruyenModel {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    // Thêm loại truyện mới
    public function themLoaiTruyen($ten_loaitruyen) {
        $sql = "INSERT INTO loai_truyen (ten_loai_truyen) VALUES (?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $ten_loaitruyen);
        return $stmt->execute();
    }

    // Lấy danh sách loại truyện
    public function layDanhSachLoaiTruyen() {
        $sql = "SELECT * FROM loai_truyen ORDER BY ngay_tao DESC";
        return $this->conn->query($sql);
    }

    // Lấy thông tin loại truyện theo ID
    public function layLoaiTruyenTheoId($id) {
        $sql = "SELECT * FROM loai_truyen WHERE id_loai_truyen = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc(); // Trả về một mảng kết hợp hoặc null nếu không tìm thấy
    }

    // Cập nhật loại truyện
    public function capNhatLoaiTruyen($id, $ten_loaitruyen) {
        $sql = "UPDATE loai_truyen SET ten_loai_truyen = ? WHERE id_loai_truyen = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("si", $ten_loaitruyen, $id);
        return $stmt->execute();
    }

    // Xóa loại truyện
    public function xoaLoaiTruyen($id) {
        $sql = "DELETE FROM loai_truyen WHERE id_loai_truyen = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }
}
?>