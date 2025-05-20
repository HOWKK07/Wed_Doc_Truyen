<?php
class ThuVienModel {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    // Lấy danh sách truyện trong thư viện của người dùng
    public function layThuVien($id_nguoidung) {
        $sql = "
            SELECT 
                t.id_truyen, t.ten_truyen, t.anh_bia, t.trang_thai,
                c.id_chuong, c.so_chuong, c.tieu_de,
                lsd.thoi_gian_doc,
                (SELECT MAX(so_chuong) FROM chuong WHERE id_truyen = t.id_truyen) AS max_so_chuong
            FROM follows f
            JOIN truyen t ON f.id_truyen = t.id_truyen
            LEFT JOIN (
                SELECT lsd1.*
                FROM lich_su_doc lsd1
                INNER JOIN (
                    SELECT c2.id_truyen, MAX(lsd2.thoi_gian_doc) AS max_time
                    FROM lich_su_doc lsd2
                    JOIN chuong c2 ON lsd2.id_chuong = c2.id_chuong
                    WHERE lsd2.id_nguoidung = ?
                    GROUP BY c2.id_truyen
                ) newest ON newest.id_truyen = (SELECT c3.id_truyen FROM chuong c3 WHERE c3.id_chuong = lsd1.id_chuong)
                AND lsd1.thoi_gian_doc = newest.max_time
                WHERE lsd1.id_nguoidung = ?
            ) lsd ON (SELECT c4.id_truyen FROM chuong c4 WHERE c4.id_chuong = lsd.id_chuong) = t.id_truyen
            LEFT JOIN chuong c ON lsd.id_chuong = c.id_chuong
            WHERE f.id_nguoidung = ?
            ORDER BY f.ngay_theo_doi DESC
        ";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("iii", $id_nguoidung, $id_nguoidung, $id_nguoidung);
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