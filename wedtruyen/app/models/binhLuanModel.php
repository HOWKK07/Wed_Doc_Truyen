<?php
class BinhLuanModel {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    // Lấy bình luận theo ID truyện
    public function layBinhLuanTheoTruyen($id_truyen) {
        $sql = "SELECT c.*, u.ten_dang_nhap 
                FROM comments c
                JOIN nguoidung u ON c.id_nguoidung = u.id_nguoidung
                WHERE c.id_truyen = ?
                ORDER BY c.ngay_binh_luan DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id_truyen);
        $stmt->execute();
        return $stmt->get_result();
    }

    // Lấy bình luận theo ID chương
    public function layBinhLuanTheoChuong($id_chuong) {
        $sql = "SELECT c.*, u.ten_dang_nhap 
                FROM chapter_comments c
                JOIN nguoidung u ON c.id_nguoidung = u.id_nguoidung
                WHERE c.id_chuong = ?
                ORDER BY c.ngay_binh_luan DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id_chuong);
        $stmt->execute();
        return $stmt->get_result();
    }

    // Thêm bình luận cho truyện
    public function themBinhLuanTruyen($id_truyen, $id_nguoidung, $noi_dung) {
        $sql = "INSERT INTO comments (id_truyen, id_nguoidung, noi_dung, ngay_binh_luan) VALUES (?, ?, ?, NOW())";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("iis", $id_truyen, $id_nguoidung, $noi_dung);
        return $stmt->execute();
    }

    // Thêm bình luận cho chương
    public function themBinhLuanChuong($id_chuong, $id_nguoidung, $noi_dung) {
        $sql = "INSERT INTO chapter_comments (id_chuong, id_nguoidung, noi_dung, ngay_binh_luan) VALUES (?, ?, ?, NOW())";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("iis", $id_chuong, $id_nguoidung, $noi_dung);
        return $stmt->execute();
    }
}