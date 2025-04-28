<?php
class AnhChuongModel {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    // Thêm ảnh vào chương
    public function themAnh($id_chuong, $duong_dan_anh, $so_trang) {
        $sql = "INSERT INTO anh_chuong (id_chuong, duong_dan_anh, so_trang) VALUES (?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("isi", $id_chuong, $duong_dan_anh, $so_trang);

        return $stmt->execute();
    }

    // Lấy danh sách ảnh theo ID chương
    public function layDanhSachAnh($id_chuong) {
        $sql = "SELECT * FROM anh_chuong WHERE id_chuong = ? ORDER BY so_trang ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id_chuong);
        $stmt->execute();
        return $stmt->get_result();
    }

    public function layIdTruyenTheoIdChuong($id_chuong) {
        $sql = "SELECT id_truyen FROM chuong WHERE id_chuong = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id_chuong);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }
}
?>