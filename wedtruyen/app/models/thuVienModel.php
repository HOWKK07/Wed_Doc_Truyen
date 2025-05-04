<?php
class ThuVienModel {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    // Lấy danh sách truyện trong thư viện của người dùng
    public function layThuVien($id_nguoidung) {
        $sql = "SELECT truyen.id_truyen, truyen.ten_truyen, truyen.anh_bia 
                FROM follows 
                JOIN truyen ON follows.id_truyen = truyen.id_truyen 
                WHERE follows.id_nguoidung = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id_nguoidung);
        $stmt->execute();
        return $stmt->get_result();
    }

    // Thêm truyện vào thư viện
    public function themVaoThuVien($id_nguoidung, $id_truyen) {
        $sql = "INSERT INTO follows (id_nguoidung, id_truyen) VALUES (?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $id_nguoidung, $id_truyen);
        return $stmt->execute();
    }

    // Xóa truyện khỏi thư viện
    public function xoaKhoiThuVien($id_nguoidung, $id_truyen) {
        $sql = "DELETE FROM follows WHERE id_nguoidung = ? AND id_truyen = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $id_nguoidung, $id_truyen);
        return $stmt->execute();
    }
}