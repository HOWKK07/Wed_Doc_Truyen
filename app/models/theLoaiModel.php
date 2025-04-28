<?php
class TheLoaiModel {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    // Thêm thể loại mới
    public function themTheLoai($ten_theloai) {
        $sql = "INSERT INTO theloai (ten_theloai) VALUES (?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $ten_theloai);
        return $stmt->execute();
    }

    // Lấy danh sách thể loại
    public function layDanhSachTheLoai() {
        $sql = "SELECT * FROM theloai ORDER BY ngay_tao DESC";
        return $this->conn->query($sql);
    }

    // Lấy thông tin thể loại theo ID
    public function layTheLoaiTheoId($id) {
        $sql = "SELECT * FROM theloai WHERE id_theloai = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    // Cập nhật thể loại
    public function capNhatTheLoai($id, $ten_theloai) {
        $sql = "UPDATE theloai SET ten_theloai = ? WHERE id_theloai = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("si", $ten_theloai, $id);
        return $stmt->execute();
    }

    // Xóa thể loại
    public function xoaTheLoai($id) {
        $sql = "DELETE FROM theloai WHERE id_theloai = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }
}
?>