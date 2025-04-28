<?php
class ChapterModel {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    // Thêm chapter mới
    public function themChapter($id_truyen, $so_chuong, $tieu_de) {
        $sql = "INSERT INTO chuong (id_truyen, so_chuong, tieu_de) VALUES (?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("iis", $id_truyen, $so_chuong, $tieu_de);

        if ($stmt->execute()) {
            return $this->conn->insert_id; // Trả về ID của chapter vừa thêm
        } else {
            return false;
        }
    }

    // Sửa chapter
    public function suaChapter($id_chuong, $so_chuong, $tieu_de) {
        $sql = "UPDATE chuong SET so_chuong = ?, tieu_de = ? WHERE id_chuong = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("isi", $so_chuong, $tieu_de, $id_chuong);

        return $stmt->execute();
    }

    // Xóa chapter
    public function xoaChapter($id_chuong) {
        $sql = "DELETE FROM chuong WHERE id_chuong = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id_chuong);

        return $stmt->execute();
    }

    // Lấy danh sách chapter theo ID truyện
    public function layDanhSachChapter($id_truyen) {
        $sql = "SELECT * FROM chuong WHERE id_truyen = ? ORDER BY so_chuong ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id_truyen);
        $stmt->execute();
        return $stmt->get_result();
    }

    // Lấy thông tin chi tiết của một chapter
    public function layThongTinChapter($id_chuong) {
        $sql = "SELECT * FROM chuong WHERE id_chuong = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id_chuong);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
}
?>